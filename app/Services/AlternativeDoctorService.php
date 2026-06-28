<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\WorkSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AlternativeDoctorService
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Find alternative doctors for a cancelled appointment.
     * Tầng 1: Same day.
     * Tầng 2: Next 3 days.
     *
     * @param Appointment $appointment
     * @return Collection
     */
    public function findAlternatives(Appointment $appointment): Collection
    {
        if (!$appointment->specialty_id) {
            return collect();
        }

        $specialtyId = $appointment->specialty_id;
        $originalDoctorId = $appointment->doctor_profile_id;
        $cancelledDate = $appointment->appointment_date;
        $alternatives = collect();

        // Tầng 1: Cùng ngày bị huỷ
        $alternatives = $this->searchDoctorsOnDate($cancelledDate, $specialtyId, $originalDoctorId);

        if ($alternatives->isNotEmpty()) {
            return $alternatives->take(3);
        }

        // Tầng 2: Tìm trong 3 ngày tiếp theo
        for ($i = 1; $i <= 3; $i++) {
            $nextDate = $cancelledDate->copy()->addDays($i);
            $doctors = $this->searchDoctorsOnDate($nextDate, $specialtyId, $originalDoctorId);
            $alternatives = $alternatives->merge($doctors);

            if ($alternatives->count() >= 3) {
                break;
            }
        }

        return $alternatives->unique('id')->take(3);
    }

    private function searchDoctorsOnDate(Carbon $date, int $specialtyId, ?int $excludeDoctorId = null): Collection
    {
        // Sử dụng BookingService để lấy ds bác sĩ khả dụng cho ngày này
        // BookingService chưa có hàm getAvailableDoctorsOnDate, ta sẽ tìm các bác sĩ có schedule
        $dow = $date->dayOfWeek === 0 ? 1 : ($date->dayOfWeek + 1); // 1 = CN, 2 = T2...

        // Find work schedules for this specialty on this day
        $schedules = WorkSchedule::with('doctorProfile')
            ->where('is_active', true)
            ->where('day_of_week', $dow)
            ->whereHas('doctorProfile', function ($q) use ($specialtyId, $excludeDoctorId) {
                $q->whereHas('specialties', function ($sq) use ($specialtyId) {
                    $sq->where('specialties.id', $specialtyId);
                });
                if ($excludeDoctorId) {
                    $q->where('id', '!=', $excludeDoctorId);
                }
            })
            ->get();

        $doctors = collect();

        foreach ($schedules as $schedule) {
            $doctor = $schedule->doctorProfile;
            if (!$doctor) continue;

            // Kiểm tra xem bác sĩ này có bị override close không
            $hasSlots = false;
            $dates = $this->bookingService->getAvailableDates($doctor->id, null);
            foreach ($dates as $d) {
                if ($d['date'] === $date->format('Y-m-d')) {
                    // Cần lấy slots để chắc chắn còn trống
                    $slots = $this->bookingService->getSlots($doctor->id, null, $date->format('Y-m-d'));
                    if (count($slots) > 0) {
                        $hasSlots = true;
                    }
                    break;
                }
            }

            if ($hasSlots && !$doctors->contains('id', $doctor->id)) {
                // Attach the alternative date so the email template knows which date to pre-fill
                $doctor->alternative_date = $date->format('Y-m-d');
                $doctors->push($doctor);
            }
        }

        return $doctors;
    }
}
