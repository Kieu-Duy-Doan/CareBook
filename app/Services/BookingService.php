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
     * Tính toán Fee và tìm Room cho Bước 4
     */
    public function calculateBookingSummary(array $booking): array
    {
        $doctor = isset($booking['doctor_id']) ? DoctorProfile::with('user')->find($booking['doctor_id']) : null;
        $specialty = isset($booking['specialty_id']) ? \App\Models\Specialty::find($booking['specialty_id']) : null;
        
        $totalFee = 0;
        $level = null;
        if (($booking['booking_method'] ?? '') === 'specialty' || ($booking['booking_method'] ?? '') === 'suggested') {
            $level = $booking['level'] ?? ($doctor ? $doctor->level : null);
            $fee = \App\Models\DoctorLevelFee::where('level', $level)->first();
            $totalFee = $fee ? $fee->base_price : 0;
        } elseif (($booking['booking_method'] ?? '') === 'doctor') {
            $level = $doctor ? $doctor->level : null;
            $fee = \App\Models\DoctorLevelFee::where('level', $level)->first();
            $totalFee = $fee ? $fee->specific_price : 0;
        }

        $roomName = 'Được sắp xếp sau';
        if (!empty($booking['doctor_id']) && !empty($booking['date'])) {
            $date = \Carbon\Carbon::parse($booking['date']);
            $dbDayOfWeek = $date->dayOfWeek === 0 ? 1 : $date->dayOfWeek + 1;
            
            $schedule = \App\Models\WorkSchedule::with('room')
                ->where('doctor_profile_id', $booking['doctor_id'])
                ->where('day_of_week', $dbDayOfWeek)
                ->where('is_active', true)
                ->first();
                
            if ($schedule && $schedule->room) {
                $roomName = $schedule->room->name;
            }
        }

        return [
            'totalFee' => $totalFee,
            'roomName' => $roomName,
            'doctor' => $doctor,
            'specialty' => $specialty
        ];
    }

    /**
     * 14 ngày tới có lịch của bác sĩ hoặc chuyên khoa.
     */
    public function getAvailableDates(?int $doctorId, ?int $specialtyId, ?string $level = null): array
    {
        $dates = [];
        $today = Carbon::today();

        for ($i = 0; $i < self::DAYS_AHEAD; $i++) {
            $date = $today->copy()->addDays($i);
            $dow  = $this->toDow($date);

            if ($this->hasActiveSchedule($date, $dow, $doctorId, $specialtyId, $level)) {
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
    public function getSlots(?int $doctorId, ?int $specialtyId, string $dateStr, ?string $level = null, ?string $draftId = null): array
    {
        $date = Carbon::parse($dateStr);
        $dayOfWeek = $date->dayOfWeek === 0 ? 1 : $date->dayOfWeek + 1;

        $query = \App\Models\WorkSchedule::with('room')
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true);

        if ($doctorId) {
            $query->where('doctor_profile_id', $doctorId);
        } elseif ($specialtyId) {
            $query->whereHas('doctorProfile', function ($q) use ($specialtyId, $level) {
                $q->where('doctor_type', 'clinical');
                $q->whereHas('specialties', function ($sq) use ($specialtyId) {
                    $sq->where('specialties.id', $specialtyId);
                });
                if ($level) {
                    $q->where('level', $level);
                }
            });
        }

        $schedules = $query->get();

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

            $bookedTimes = \App\Models\Appointment::where('appointment_date', $dateStr)
                ->where('doctor_profile_id', $schedule->doctor_profile_id)
                ->whereNotIn('status', ['cancelled', 'absent'])
                ->pluck('appointment_time')
                ->map(fn($t) => substr($t, 0, 5))
                ->toArray();

            $current = $start->copy();
            while ($current->lt($end) && $count < $maxSlots) {
                $timeStr = $current->format('H:i');
                $isPast  = $date->isToday() && $current->lte(Carbon::now());
                
                $lockKey = "slot_lock_{$schedule->doctor_profile_id}_{$dateStr}_{$timeStr}";
                $currentLock = \Illuminate\Support\Facades\Cache::get($lockKey);
                $isLocked = $currentLock && $currentLock !== $draftId;

                $slots[] = [
                    'time'      => $timeStr,
                    'available' => !$isPast && !in_array($timeStr, $bookedTimes) && !$isLocked,
                    'room_name' => $schedule->room?->name ?? null,
                    'doctor_id' => $schedule->doctor_profile_id,
                ];

                $current->addMinutes($duration);
                $count++;
            }
        }

        // Group by time and randomly pick 1 doctor per time slot
        $grouped = [];
        foreach ($slots as $slot) {
            $grouped[$slot['time']][] = $slot;
        }

        $finalSlots = [];
        foreach ($grouped as $time => $timeSlots) {
            $available = array_filter($timeSlots, fn($s) => $s['available']);
            if (count($available) > 0) {
                // Randomly pick one of the available slots
                $finalSlots[] = $available[array_rand($available)];
            } else {
                // If none available, just pick the first one (will show as disabled)
                $finalSlots[] = $timeSlots[0];
            }
        }

        usort($finalSlots, fn($a, $b) => strcmp($a['time'], $b['time']));

        return $finalSlots;
    }

    // ── Legacy (giữ tương thích) ──────────────────────────────────────────

    /**
     * Lấy danh sách slot available cho bác sĩ theo ngày
     */
    public function getAvailableSlots(int $doctorProfileId, string $dateStr): array
    {
        $date = Carbon::parse($dateStr)->format('Y-m-d');
        $dow = $this->toDow(Carbon::parse($date));

        // Kiểm tra override
        $override = ScheduleOverride::where('doctor_profile_id', $doctorProfileId)
            ->whereDate('override_date', $date)
            ->first();

        if ($override && $override->type === 'close') {
            return [];
        }

        // Lấy work_schedule
        $schedule = WorkSchedule::where('doctor_profile_id', $doctorProfileId)
            ->where('day_of_week', $dow)
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
        return DB::transaction(function () use ($data, $bookedBy) {
            // Lấy room_id từ work_schedule và lock row để tránh race condition
            $dbDayOfWeek = Carbon::parse($data['appointment_date'])->dayOfWeek === 0
                ? 1
                : Carbon::parse($data['appointment_date'])->dayOfWeek + 1;

            $schedule = WorkSchedule::where('doctor_profile_id', $data['doctor_profile_id'])
                ->where('day_of_week', $dbDayOfWeek)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            // Lock hồ sơ bệnh nhân để tránh race condition khi check lịch active
            $patientProfile = PatientProfile::where('id', $data['patient_profile_id'])->lockForUpdate()->first();
            if (!$patientProfile) {
                throw new \Exception('Hồ sơ bệnh nhân không tồn tại.');
            }

            // Kiểm tra 1 hồ sơ chỉ có 1 lịch active
            $hasActiveAppointment = Appointment::where('patient_profile_id', $data['patient_profile_id'])
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($hasActiveAppointment) {
                throw new \Exception('Hồ sơ bệnh nhân này đang có 1 lịch hẹn chưa hoàn thành. Vui lòng hoàn thành hoặc hủy lịch cũ trước khi đặt lịch mới.');
            }

            // Lấy thông tin bác sĩ và kiểm tra chuyên khoa hợp lệ
            $doc = DoctorProfile::with('specialties')->find($data['doctor_profile_id']);
            if (!$doc) {
                throw new \Exception('Không tìm thấy thông tin bác sĩ.');
            }

            if (empty($data['specialty_id'])) {
                $data['specialty_id'] = $doc->primary_specialty_id ?? ($doc->specialties->first()->id ?? null);
            }

            if (empty($data['specialty_id']) || !$doc->specialties->contains('id', $data['specialty_id'])) {
                throw new \Exception('Bác sĩ này không thuộc chuyên khoa bạn đã chọn hoặc bác sĩ chưa có chuyên khoa. Vui lòng thử lại.');
            }

            // Double-check slot còn trống (bên trong transaction sau khi có lock)
            $slots = $this->getAvailableSlots($data['doctor_profile_id'], $data['appointment_date']);
            $availableSlot = collect($slots)->firstWhere('time', substr($data['appointment_time'], 0, 5));

            if (!$availableSlot || !$availableSlot['available']) {
                throw new \Exception('Slot giờ này đã hết. Vui lòng chọn giờ khác.');
            }

            $appointmentDateTime = Carbon::parse($data['appointment_date'] . ' ' . $data['appointment_time']);
            $isWithin2Hours = $appointmentDateTime->diffInMinutes(now()) <= 120 && $appointmentDateTime->isFuture();
            $isWithin30Mins = $appointmentDateTime->diffInMinutes(now()) <= 30 && $appointmentDateTime->isFuture();

            // Tính phí
            $totalFee = null;
            if ($doc->level) {
                $levelFee = \App\Models\DoctorLevelFee::where('level', $doc->level)->first();
                if ($levelFee) {
                    $totalFee = ($data['booking_method'] ?? 'doctor') === 'specialty' ? $levelFee->base_price : $levelFee->specific_price;
                }
            }

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
                'total_fee'          => $totalFee,
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

    private function hasActiveSchedule(Carbon $date, int $dow, ?int $doctorId, ?int $specialtyId, ?string $level = null): bool
    {
        // Nếu bác sĩ bị override close thì bỏ qua
        if ($doctorId) {
            $closed = ScheduleOverride::where('doctor_profile_id', $doctorId)
                ->where('override_date', $date->format('Y-m-d'))
                ->where('type', 'close')
                ->exists();
            if ($closed) return false;
        }

        return $this->querySchedules($dow, $doctorId, $specialtyId, $level)->isNotEmpty();
    }

    public function findAlternatives(Appointment $appointment): \Illuminate\Support\Collection
    {
        $alternatives = [];
        $specialtyId = $appointment->specialty_id;
        $dateString = Carbon::parse($appointment->appointment_date)->format('Y-m-d');
        $timeStr = substr($appointment->appointment_time, 0, 5);
        $doctorId = $appointment->doctor_profile_id;
        $doctorLevel = $appointment->doctorProfile->level ?? null;

        $date = Carbon::parse($dateString);
        $dow = $this->toDow($date);

        // Lấy danh sách schedule cùng ngày, cùng chuyên khoa, cùng level
        $schedules = $this->querySchedules($dow, null, $specialtyId, $doctorLevel);

        foreach ($schedules as $schedule) {
            if ($schedule->doctor_profile_id === $doctorId) continue;

            $slots = $this->getAvailableSlots($schedule->doctor_profile_id, $dateString);

            $hasSameSlot = collect($slots)->contains(function ($s) use ($timeStr) {
                return $s['time'] === $timeStr && $s['available'];
            });

            $hasAnySlot = collect($slots)->contains('available', true);

            if ($hasSameSlot || $hasAnySlot) {
                $alternatives[] = (object) [
                    'id'               => $schedule->doctor_profile_id,
                    'full_title'       => $schedule->doctorProfile->full_title ?? ($schedule->doctorProfile->user->full_name ?? 'Bác sĩ'),
                    'alternative_date' => $dateString,
                    'experience_years' => $schedule->doctorProfile->experience_years ?? 0,
                    'expertise'        => $schedule->doctorProfile->expertise ?? '',
                    'avatar_url'       => $schedule->doctorProfile->user->avatar_url ?? null,
                    'level'            => $schedule->doctorProfile->level ?? null,
                    'has_same_slot'    => $hasSameSlot,
                ];
            }
        }

        $collection = collect($alternatives)->unique('id');

        // Nếu có bác sĩ rảnh đúng giờ, chỉ trả về những người đó
        $withSameSlot = $collection->filter(fn($a) => $a->has_same_slot);
        if ($withSameSlot->isNotEmpty()) {
            return $withSameSlot->values();
        }

        // Nếu không ai rảnh đúng giờ, trả về những người rảnh giờ khác
        return $collection;
    }

    private function querySchedules(int $dow, ?int $doctorId, ?int $specialtyId, ?string $level = null)
    {
        $query = WorkSchedule::with(['room', 'doctorProfile.user'])
            ->where('day_of_week', $dow)
            ->where('is_active', true);

        if ($doctorId) {
            $query->where('doctor_profile_id', $doctorId);
        } elseif ($specialtyId) {
            $ids = DoctorProfile::where('doctor_type', 'clinical')
                ->whereHas(
                    'specialties',
                    fn($q) => $q->where('specialties.id', $specialtyId)
                );
            if ($level) {
                $ids->where('level', $level);
            }
            $query->whereIn('doctor_profile_id', $ids->pluck('id'));
        }

        return $query->get();
    }
}
