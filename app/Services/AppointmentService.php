<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Appointment;
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
        $data['appointment_code'] = 'APT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        
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
}
