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

    public function storeByReceptionist(array $data, \App\Models\DoctorProfile $doctor, \App\Models\PatientProfile $patient, $userId)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $doctor, $patient, $userId) {
            $appointmentCode = $this->generateUniqueCode();

            $totalFee = 0;
            if ($doctor->level) {
                $fee = \App\Models\DoctorLevelFee::where('level', $doctor->level)->first();
                $totalFee = $fee ? $fee->specific_price : 0;
            }

            $checkedInAt = in_array($data['status'], ['checked_in', 'examining', 'completed']) ? now() : null;
            $completedAt = ($data['status'] === 'completed') ? now() : null;

            $appointment = Appointment::create(array_merge($data, [
                'appointment_code'   => $appointmentCode,
                'booked_by_user_id'  => $data['source'] === 'counter' ? $userId : ($patient->owner_id ?? $userId),
                'doctor_level'       => $doctor->level,
                'total_fee'          => $totalFee,
                'checked_in_at'      => $checkedInAt,
                'completed_at'       => $completedAt,
            ]));

            if (in_array($appointment->status, ['checked_in', 'examining', 'completed'])) {
                $this->createClinicalVisitIfNotExists($appointment, true);
            }

            \App\Models\AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'old_status'     => null,
                'new_status'     => $appointment->status,
                'action'         => \App\Models\AppointmentLog::ACTION_ADMIN_CREATE,
                'changed_by'     => $userId,
                'reason'         => 'Khởi tạo lịch hẹn bởi Quản trị/Lễ tân',
            ]);
            
            return $appointment;
        });
    }

    public function updateByReceptionist(Appointment $appointment, array $data, $userId, $doctor = null, $patient = null)
    {
        $oldStatus = $appointment->status;
        $newStatus = $data['status'];
        
        return \Illuminate\Support\Facades\DB::transaction(function () use ($appointment, $data, $oldStatus, $newStatus, $userId, $doctor, $patient) {
            if ($doctor && $patient) {
                // Lịch chưa khóa
                $data['booked_by_user_id'] = $data['source'] === 'counter' ? $userId : ($patient->owner_id ?? $userId);
                $data['doctor_level'] = $doctor->level;
                
                $totalFee = 0;
                if ($doctor->level) {
                    $fee = \App\Models\DoctorLevelFee::where('level', $doctor->level)->first();
                    $totalFee = $fee ? $fee->specific_price : 0;
                }
                $data['total_fee'] = $totalFee;
            }
            
            $appointment->fill($data);

            if (in_array($newStatus, ['checked_in', 'examining', 'completed']) && is_null($appointment->checked_in_at)) {
                $appointment->checked_in_at = now();
                
                // Tính toán đến muộn (>30 phút)
                $appointmentDatetime = \Carbon\Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time);
                if (now()->isAfter($appointmentDatetime->copy()->addMinutes(30))) {
                    $appointment->is_late = true;
                } else {
                    $appointment->is_late = false;
                }
            }
            if ($newStatus === 'completed' && is_null($appointment->completed_at)) {
                $appointment->completed_at = now();
            }

            $appointment->save();

            // Đồng bộ lượt khám lâm sàng
            if (in_array($newStatus, ['checked_in', 'examining', 'completed'])) {
                $this->createClinicalVisitIfNotExists($appointment, true);
            }

            // Tự động dọn dẹp lượt khám chưa khám nếu hủy lịch/đổi về chờ khám
            if (in_array($newStatus, ['cancelled', 'pending'])) {
                $visit = ClinicalVisit::where('appointment_id', $appointment->id)->first();
                if ($visit && $visit->status === 'waiting') {
                    $visit->delete();
                }
            }

            if ($oldStatus !== $newStatus) {
                \App\Models\AppointmentLog::create([
                    'appointment_id' => $appointment->id,
                    'old_status'     => $oldStatus,
                    'new_status'     => $newStatus,
                    'action'         => \App\Models\AppointmentLog::ACTION_RECEPTIONIST_UPDATE,
                    'changed_by'     => $userId,
                    'reason'         => 'Cập nhật lịch hẹn và trạng thái',
                ]);

                if ($newStatus === 'cancelled') {
                    \App\Jobs\ProcessAppointmentNotificationJob::dispatch($appointment, 'cancellation');
                }
            }
            
            return $appointment;
        });
    }

    public function destroyAppointment(Appointment $appointment)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($appointment) {
            // Xoá lượt khám lâm sàng liên quan trước
            ClinicalVisit::where('appointment_id', $appointment->id)->delete();
            // Xoá logs liên quan trước
            $appointment->logs()->delete();
            $appointment->delete();
        });
    }

    public function updateDoctorAppointmentStatus(Appointment $appointment, string $newStatus, ?string $reason, int $userId)
    {
        $oldStatus = $appointment->status;

        // Guard: kiểm tra điều kiện hoàn thành
        if ($newStatus === 'completed') {
            if (!$appointment->medicalRecord) {
                throw new Exception('Vui lòng ghi kết luận bệnh án trước khi hoàn thành.');
            }

            $pendingVisits = $appointment->clinicalVisits
                ->whereNotIn('status', ['completed', 'refused'])
                ->count();

            if ($pendingVisits > 0) {
                throw new Exception("Còn {$pendingVisits} phòng khám chưa hoàn thành. Vui lòng đợi kết quả từ tất cả phòng được chỉ định.");
            }
        }

        if ($oldStatus !== $newStatus) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($appointment, $oldStatus, $newStatus, $reason, $userId) {
                $appointment->status = $newStatus;

                if ($newStatus === 'checked_in' && is_null($appointment->checked_in_at)) {
                    $appointment->checked_in_at = now();
                }
                if ($newStatus === 'completed' && is_null($appointment->completed_at)) {
                    $appointment->completed_at = now();
                }

                $appointment->save();

                if (in_array($newStatus, ['checked_in', 'examining'])) {
                    $this->createClinicalVisitIfNotExists($appointment, true);
                }

                // Cập nhật started_at cho ClinicalVisit gốc khi bắt đầu khám
                if ($newStatus === 'examining') {
                    ClinicalVisit::where('appointment_id', $appointment->id)
                        ->where('is_origin', true)
                        ->whereNull('started_at')
                        ->update(['started_at' => now(), 'status' => 'in_progress']);
                }

                AppointmentLog::create([
                    'appointment_id' => $appointment->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'action' => AppointmentLog::ACTION_DOCTOR_STATUS_CHANGE,
                    'changed_by' => $userId,
                    'reason' => $reason,
                ]);
                
                if ($newStatus === 'cancelled') {
                    \App\Jobs\ProcessAppointmentNotificationJob::dispatch($appointment, 'cancellation');
                }
            });
        }

        return $appointment;
    }
}

