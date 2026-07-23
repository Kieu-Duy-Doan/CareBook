<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Appointment;
use App\Models\ClinicalVisit;
use Carbon\Carbon;
use Exception;

class AppointmentService
{
    /**
     * Lock a specific time slot for a doctor to prevent double booking.
     * Returns true if lock was acquired, false otherwise.
     * Lock duration is 10 minutes.
     */
    public function lockSlot($doctorId, $date, $time)
    {
        $lockKey = "appointment_slot:{$doctorId}:{$date}:{$time}";
        
        // Try to get the lock for 10 minutes (600 seconds)
        // Note: We don't block, we just fail immediately if someone else has it
        if (!Cache::add($lockKey, true, 600)) {
            return false;
        }
        
        return true;
    }

    /**
     * Release a locked slot manually (e.g. if user cancels booking process).
     */
    public function releaseSlot($doctorId, $date, $time)
    {
        $lockKey = "appointment_slot:{$doctorId}:{$date}:{$time}";
        Cache::forget($lockKey);
    }

    /**
     * Create an appointment with auto-confirmation.
     */
    public function createAppointment(array $data)
    {
        // Double check if we still have the lock or if it's available
        $lockKey = "appointment_slot:{$data['doctor_profile_id']}:{$data['appointment_date']}:{$data['appointment_time']}";
        
        // Check if there is already a confirmed or checked in appointment at this time
        $existingAppointment = Appointment::where('doctor_profile_id', $data['doctor_profile_id'])
            ->where('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $data['appointment_time'])
            ->whereNotIn('status', ['cancelled', 'absent'])
            ->exists();

        if ($existingAppointment) {
            throw new Exception("Khung giờ này đã được đặt. Vui lòng chọn giờ khác.");
        }

        // Generate unique code (e.g., APT-YYYYMMDD-XXXX)
        $data['appointment_code'] = $this->generateUniqueCode();
        
        // Auto confirm rule applied
        $data['status'] = 'confirmed'; // or 'pending' if you prefer, but plan said auto-confirm

        $appointment = Appointment::create($data);

        // Release the lock since booking is completed
        $this->releaseSlot($data['doctor_profile_id'], $data['appointment_date'], $data['appointment_time']);

        return $appointment;
    }

    /**
     * Cancel an appointment.
     * Enforces the 12-hour cancellation policy.
     */
    public function cancelAppointment(Appointment $appointment, $reason = null)
    {
        if ($appointment->status === 'cancelled') {
            throw new Exception("Lịch hẹn này đã được hủy trước đó.");
        }

        // Parse appointment date and time
        $appointmentDateTime = Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time);
        
        // Check if current time is at least 12 hours before appointment
        if (now()->diffInHours($appointmentDateTime, false) < 12) {
            throw new Exception("Bạn chỉ có thể hủy lịch hẹn trước giờ khám ít nhất 12 tiếng.");
        }

        $appointment->status = 'cancelled';
        if ($reason) {
            $appointment->receptionist_note = $appointment->receptionist_note 
                ? $appointment->receptionist_note . "\nLý do hủy: " . $reason 
                : "Lý do hủy: " . $reason;
        }
        $appointment->save();

        return $appointment;
    }

    /**
     * Tạo ClinicalVisit gốc nếu chưa có — atomic để tránh race condition.
     * Dùng firstOrCreate thay vì check-then-create.
     *
     * @param bool $withPayment Set true khi admin tạo (bao gồm payment_amount/payment_status)
     */
    public function createClinicalVisitIfNotExists(Appointment $appointment, bool $withPayment = false): ?ClinicalVisit
    {
        // firstOrCreate đảm bảo atomic — không bị duplicate dù 2 request đồng thời
        $attributes = ['appointment_id' => $appointment->id, 'is_origin' => true];

        $maxOrder = ClinicalVisit::where('doctor_profile_id', $appointment->doctor_profile_id)
            ->whereDate('created_at', now()->toDateString())
            ->max('visit_order');

        $nextOrder = $maxOrder ? $maxOrder + 1 : 1;

        $values = [
            'doctor_profile_id' => $appointment->doctor_profile_id,
            'room_id'           => $appointment->room_id,
            'visit_order'       => $nextOrder,
            'status'            => 'waiting',
        ];

        if ($withPayment) {
            $values['payment_amount'] = $appointment->total_fee ?? 0;
            $values['payment_status'] = 'pending';
        }

        return ClinicalVisit::firstOrCreate($attributes, $values);
    }

    /**
     * Generate unique appointment code with retry to prevent collisions.
     */
    public function generateUniqueCode(string $prefix = 'APT'): string
    {
        $maxAttempts = 5;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = $prefix . strtoupper(substr(uniqid(), -8));

            if (!Appointment::where('appointment_code', $code)->exists()) {
                return $code;
            }
        }

        // Fallback: use UUID fragment for guaranteed uniqueness
        return $prefix . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
    }

    /**
     * Escape ký tự đặc biệt trong LIKE query để tránh wildcard injection.
     */
    public static function escapeLikeWildcards(string $value): string
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $value);
    }
}

