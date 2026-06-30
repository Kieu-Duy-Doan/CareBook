<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use App\Models\ScheduleOverride;
use App\Models\WorkSchedule;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class BookingService
{
    private const DAYS_AHEAD = 14;
    private const DAY_NAMES  = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

    // ── API cho SPA Booking ───────────────────────────────────────────────

    /**
     * 14 ngày tới có lịch của bác sĩ hoặc chuyên khoa.
     */
    public function getAvailableDates(?int $doctorId, ?int $specialtyId): array
    {
        $dates = [];
        $today = Carbon::today();

        for ($i = 0; $i < self::DAYS_AHEAD; $i++) {
            $date = $today->copy()->addDays($i);
            $dow  = $this->toDow($date);

            if ($this->hasActiveSchedule($date, $dow, $doctorId, $specialtyId)) {
                $dates[] = [
                    'date'     => $date->format('Y-m-d'),
                    'display'  => $date->format('d/m'),
                    'day_name' => self::DAY_NAMES[$date->dayOfWeek],
                    'is_today' => $i === 0,
                ];
            }
        }

        return $dates;
    }

    /**
     * Danh sách slot giờ khám: [{time, available, room_name, doctor_id}].
     */
    public function getSlots(?int $doctorId, ?int $specialtyId, string $dateStr): array
    {
        $date      = Carbon::parse($dateStr);
        $dow       = $this->toDow($date);
        $schedules = $this->querySchedules($dow, $doctorId, $specialtyId);

        if ($schedules->isEmpty()) {
            return [];
        }

        $slots = [];

        foreach ($schedules as $schedule) {
            $start    = Carbon::parse("{$dateStr} {$schedule->start_time}");
            $end      = Carbon::parse("{$dateStr} {$schedule->end_time}");
            $duration = $schedule->slot_duration_minutes ?: 30;
            $maxSlots = $schedule->max_slots ?: 50;
            $count    = 0;

            $bookedTimes = Appointment::where('appointment_date', $dateStr)
                ->where('doctor_profile_id', $schedule->doctor_profile_id)
                ->whereNotIn('status', ['cancelled', 'absent'])
                ->pluck('appointment_time')
                ->map(fn($t) => substr($t, 0, 5))
                ->toArray();

            $current = $start->copy();
            while ($current->lt($end) && $count < $maxSlots) {
                $timeStr = $current->format('H:i');
                $isPast  = $date->isToday() && $current->lte(Carbon::now());

                $slots[] = [
                    'time'      => $timeStr,
                    'available' => !$isPast && !in_array($timeStr, $bookedTimes),
                    'room_name' => $schedule->room?->name ?? null,
                    'doctor_id' => $schedule->doctor_profile_id,
                ];

                $current->addMinutes($duration);
                $count++;
            }
        }

        usort($slots, fn($a, $b) => strcmp($a['time'], $b['time']));

        return $slots;
    }

    // ── Legacy (giữ tương thích) ──────────────────────────────────────────

    /**
     * Lấy danh sách slot available cho bác sĩ theo ngày
     */
    public function getAvailableSlots(int $doctorProfileId, string $date): array
    {
        $carbon = Carbon::parse($date);
        // Carbon ISO: 1=Mon,7=Sun → DB: 1=Sun,2=Mon,...,7=Sat
        $dbDayOfWeek = $carbon->dayOfWeek === 0 ? 1 : $carbon->dayOfWeek + 1;

        // Kiểm tra override
        $override = ScheduleOverride::where('doctor_profile_id', $doctorProfileId)
            ->whereDate('override_date', $date)
            ->first();

        if ($override && $override->type === 'close') {
            return [];
        }

        // Lấy work_schedule
        $schedule = WorkSchedule::where('doctor_profile_id', $doctorProfileId)
            ->where('day_of_week', $dbDayOfWeek)
            ->where('is_active', true)
            ->first();

        // Nếu có override extra thì dùng giờ override
        if ($override && $override->type === 'extra') {
            $startTime = $override->start_time;
            $endTime = $override->end_time;
            $slotDuration = $schedule?->slot_duration_minutes ?? 15;
            $maxSlots = $schedule?->max_slots ?? 30;
        } elseif ($schedule) {
            $startTime = $schedule->start_time;
            $endTime = $schedule->end_time;
            $slotDuration = $schedule->slot_duration_minutes;
            $maxSlots = $schedule->max_slots;
        } else {
            return []; // Không có lịch ngày này
        }

        // Generate slots
        $slots = [];
        $current = Carbon::parse($date . ' ' . $startTime);
        $end = Carbon::parse($date . ' ' . $endTime);
        $now = now();

        while ($current->lt($end) && count($slots) < $maxSlots) {
            $slots[] = $current->format('H:i');
            $current->addMinutes($slotDuration);
        }

        // Lấy appointments đã đặt trong ngày
        $bookedSlots = Appointment::where('doctor_profile_id', $doctorProfileId)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['cancelled', 'absent'])
            ->pluck('appointment_time')
            ->map(fn($t) => substr($t, 0, 5))
            ->toArray();

        // Build result
        $result = [];
        foreach ($slots as $slot) {
            $slotDateTime = Carbon::parse($date . ' ' . $slot);
            $result[] = [
                'time'      => $slot,
                'available' => !in_array($slot, $bookedSlots) && $slotDateTime->gt($now),
            ];
        }

        return $result;
    }

    /**
     * Tạo mã lịch hẹn unique
     */
    public function generateAppointmentCode(string $date): string
    {
        $dateStr = Carbon::parse($date)->format('Ymd');
        $prefix = 'APT' . $dateStr;

        $lastCode = Appointment::where('appointment_code', 'like', $prefix . '%')
            ->orderBy('appointment_code', 'desc')
            ->value('appointment_code');

        $sequence = $lastCode ? (int)substr($lastCode, -4) + 1 : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tạo lịch hẹn
     */
    public function createAppointment(array $data, User $bookedBy): Appointment
    {
        return DB::transaction(function() use ($data, $bookedBy) {
            // Lấy room_id từ work_schedule và lock row để tránh race condition
            $dbDayOfWeek = Carbon::parse($data['appointment_date'])->dayOfWeek === 0
                ? 1
                : Carbon::parse($data['appointment_date'])->dayOfWeek + 1;

            $schedule = WorkSchedule::where('doctor_profile_id', $data['doctor_profile_id'])
                ->where('day_of_week', $dbDayOfWeek)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            // Double-check slot còn trống (bên trong transaction sau khi có lock)
            $slots = $this->getAvailableSlots($data['doctor_profile_id'], $data['appointment_date']);
            $availableSlot = collect($slots)->firstWhere('time', substr($data['appointment_time'], 0, 5));

            if (!$availableSlot || !$availableSlot['available']) {
                throw new \Exception('Slot giờ này đã hết. Vui lòng chọn giờ khác.');
            }

            $appointmentDateTime = Carbon::parse($data['appointment_date'] . ' ' . $data['appointment_time']);
            $isWithin2Hours = $appointmentDateTime->diffInMinutes(now()) <= 120 && $appointmentDateTime->isFuture();
            $isWithin30Mins = $appointmentDateTime->diffInMinutes(now()) <= 30 && $appointmentDateTime->isFuture();

            $appointment = Appointment::create([
                'appointment_code'   => $this->generateAppointmentCode($data['appointment_date']),
                'patient_profile_id' => $data['patient_profile_id'],
                'booked_by_user_id'  => $bookedBy->id,
                'specialty_id'       => $data['specialty_id'],
                'doctor_profile_id'  => $data['doctor_profile_id'],
                'room_id'            => $schedule?->room_id ?? $data['room_id'] ?? 1,
                'appointment_date'   => $data['appointment_date'],
                'appointment_time'   => $data['appointment_time'] . ':00',
                'reason'             => $data['reason'],
                'status'             => 'pending',
                'source'             => 'web',
                'booking_method'     => $data['booking_method'] ?? 'doctor',
                'reminded_2h'        => $isWithin2Hours,
                'reminded_30m'       => $isWithin30Mins,
            ]);

            // Ghi log
            AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'changed_by'     => $bookedBy->id,
                'old_status'     => null,
                'new_status'     => 'pending',
                'action'         => 'APPOINTMENT_CREATED',
                'reason'         => 'Bệnh nhân đặt lịch qua website',
            ]);

            return $appointment;
        });
    }

    // ── Private helpers ───────────────────────────────────────────────────

    /** Carbon dayOfWeek (0=Sun) → DB day_of_week (1=CN,2=T2,...,7=T7) */
    private function toDow(Carbon $date): int
    {
        return $date->dayOfWeek === 0 ? 1 : $date->dayOfWeek + 1;
    }

    private function hasActiveSchedule(Carbon $date, int $dow, ?int $doctorId, ?int $specialtyId): bool
    {
        // Nếu bác sĩ bị override close thì bỏ qua
        if ($doctorId) {
            $closed = ScheduleOverride::where('doctor_profile_id', $doctorId)
                ->where('override_date', $date->format('Y-m-d'))
                ->where('type', 'close')
                ->exists();
            if ($closed) return false;
        }

        return $this->querySchedules($dow, $doctorId, $specialtyId)->isNotEmpty();
    }

    public function findAlternatives(Appointment $appointment): \Illuminate\Support\Collection
    {
        $alternatives = [];
        $specialtyId = $appointment->specialty_id;
        $date = Carbon::parse($appointment->appointment_date);

        // Quét trong 3 ngày tới
        for ($i = 0; $i < 3; $i++) {
            $checkDate = clone $date;
            $checkDate->addDays($i);
            $dateString = $checkDate->format('Y-m-d');
            $dow = $this->toDow($checkDate);

            $schedules = $this->querySchedules($dow, null, $specialtyId);
            foreach ($schedules as $schedule) {
                // Ưu tiên bác sĩ khác nếu cùng ngày
                if ($i === 0 && $schedule->doctor_profile_id === $appointment->doctor_profile_id) {
                    continue;
                }

                $slots = $this->getAvailableSlots($schedule->doctor_profile_id, $dateString);
                $availableSlots = collect($slots)->filter(fn($s) => $s['available'])->pluck('time')->toArray();

                if (!empty($availableSlots)) {
                    $alternatives[] = (object) [
                        'id'               => $schedule->doctor_profile_id,
                        'full_title'       => $schedule->doctorProfile->full_title ?? ($schedule->doctorProfile->user->full_name ?? 'Bác sĩ'),
                        'alternative_date' => $dateString,
                    ];
                }

                if (count($alternatives) >= 3) {
                    return collect($alternatives);
                }
            }
        }

        return collect($alternatives);
    }

    private function querySchedules(int $dow, ?int $doctorId, ?int $specialtyId)
    {
        $query = WorkSchedule::with(['room', 'doctorProfile.user'])
            ->where('day_of_week', $dow)
            ->where('is_active', true);

        if ($doctorId) {
            $query->where('doctor_profile_id', $doctorId);
        } elseif ($specialtyId) {
            $ids = DoctorProfile::whereHas(
                'specialties',
                fn($q) => $q->where('specialties.id', $specialtyId)
            )->pluck('id');
            $query->whereIn('doctor_profile_id', $ids);
        }

        return $query->get();
    }
}