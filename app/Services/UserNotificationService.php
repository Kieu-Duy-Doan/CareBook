<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Appointment;

class UserNotificationService
{
    /**
     * Ghi thông báo In-Web cho người dùng liên quan đến lịch khám
     */
    public function logWebNotification(Appointment $appointment, string $title, string $content, string $type = 'appointment', array $data = [], ?int $userId = null): Notification
    {
        $notification = Notification::create([
            'user_id' => $userId ?? $appointment->booked_by_user_id,
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'channel' => 'in_web',
            'is_sent' => true,
            'is_read' => false,
            'ref_type' => 'appointment',
            'ref_id' => $appointment->id,
            'data' => empty($data) ? null : $data,
            'created_at' => now(),
        ]);

        return $notification;
    }

    public function notifyBookingSuccess(Appointment $appointment, string $actor = 'system'): Notification
    {
        $time = \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i');
        $date = $appointment->appointment_date->format('d/m/Y');
        $doctorName = $appointment->doctorProfile->full_title ?? 'Chưa xác định';

        $title = 'Đặt lịch khám thành công';
        $content = "Lịch hẹn khám lúc {$time} ngày {$date} với {$doctorName} đã được xác nhận. Mã lịch hẹn: {$appointment->appointment_code}.";

        if ($actor === 'patient') {
            $content = "Bạn đã đặt lịch khám thành công lúc {$time} ngày {$date} với {$doctorName}. Mã lịch hẹn: {$appointment->appointment_code}.";
        } else {
            $content = "Phòng khám đã đặt lịch khám thành công cho bạn lúc {$time} ngày {$date} với {$doctorName}. Mã lịch hẹn: {$appointment->appointment_code}.";
        }
        $type = $actor === 'patient' ? 'patient_booking' : 'system_booking';

        return $this->logWebNotification(
            $appointment,
            $title,
            $content,
            $type
        );
    }

    public function notifyReminder(Appointment $appointment, string $timeframeLabel): Notification
    {
        $time = \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i');
        $date = $appointment->appointment_date->format('d/m/Y');
        $doctorName = $appointment->doctorProfile->full_title ?? 'Chưa xác định';

        return $this->logWebNotification(
            $appointment,
            'Nhắc nhở lịch khám sắp tới',
            "Bạn có lịch hẹn lúc {$time} ngày {$date} với {$doctorName} sẽ diễn ra trong {$timeframeLabel} tới. Mã lịch hẹn: {$appointment->appointment_code}."
        );
    }

    public function notifyCancellation(Appointment $appointment, array $alternatives = [], string $actor = 'system'): Notification
    {
        $time = \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i');
        $date = $appointment->appointment_date->format('d/m/Y');
        $doctorName = $appointment->doctorProfile->full_title ?? 'Chưa xác định';

        $title = 'Lịch khám đã bị huỷ';
        $content = "Rất tiếc, lịch hẹn lúc {$time} ngày {$date} với {$doctorName} của bạn đã bị huỷ. Mã lịch hẹn: {$appointment->appointment_code}.";
        $type = 'system_cancellation';

        if ($actor === 'patient') {
            $title = 'Bạn đã huỷ lịch khám';
            $content = "Bạn đã huỷ thành công lịch hẹn khám lúc {$time} ngày {$date} với {$doctorName}. Mã lịch hẹn: {$appointment->appointment_code}.";
            $type = 'patient_cancellation';
        }

        return $this->logWebNotification(
            $appointment,
            $title,
            $content,
            $type,
            ['alternatives' => $alternatives]
        );
    }

    /**
     * Thông báo cho bác sĩ khi có lịch hẹn mới
     */
    public function notifyDoctorNewAppointment(Appointment $appointment): ?Notification
    {
        $doctorUserId = $appointment->doctorProfile->user_id ?? null;
        if (!$doctorUserId) {
            return null;
        }

        $time = \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i');
        $date = $appointment->appointment_date->format('d/m/Y');
        $patientName = $appointment->patientProfile->full_name ?? 'Chưa xác định';

        return $this->logWebNotification(
            $appointment,
            'Bạn có lịch hẹn mới',
            "Bệnh nhân {$patientName} đã đặt lịch khám lúc {$time} ngày {$date}. Mã lịch hẹn: {$appointment->appointment_code}.",
            'appointment',
            [],
            $doctorUserId
        );
    }

    /**
     * Thông báo cho bác sĩ khi lịch hẹn bị huỷ
     */
    public function notifyDoctorCancellation(Appointment $appointment, string $actor = 'system'): ?Notification
    {
        $doctorUserId = $appointment->doctorProfile->user_id ?? null;
        if (!$doctorUserId) {
            return null;
        }

        $time = \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i');
        $date = $appointment->appointment_date->format('d/m/Y');
        $patientName = $appointment->patientProfile->full_name ?? 'Chưa xác định';

        $title = 'Lịch hẹn đã bị huỷ';
        if ($actor === 'patient') {
            $content = "Bệnh nhân {$patientName} đã huỷ lịch hẹn lúc {$time} ngày {$date}. Mã lịch hẹn: {$appointment->appointment_code}.";
        } else {
            $content = "Lịch hẹn lúc {$time} ngày {$date} với bệnh nhân {$patientName} đã bị huỷ bởi hệ thống. Mã lịch hẹn: {$appointment->appointment_code}.";
        }

        return $this->logWebNotification(
            $appointment,
            $title,
            $content,
            'system_cancellation',
            [],
            $doctorUserId
        );
    }

    /**
     * Get paginated notifications for a user
     */
    public function getNotificationsPaginated(int $userId, int $perPage = 15)
    {
        return Notification::where('user_id', $userId)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get recent notifications for the dropdown
     */
    public function getRecentNotifications(int $userId, int $limit = 20)
    {
        return Notification::where('user_id', $userId)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark notifications as read
     */
    public function markAsRead(int $userId, ?int $notificationId = null): void
    {
        $query = Notification::where('user_id', $userId);

        if ($notificationId) {
            $query->where('id', $notificationId);
        }

        $query->update(['is_read' => true]);
    }

    /**
     * Delete a notification
     */
    public function deleteNotification(int $userId, int $notificationId): void
    {
        Notification::where('user_id', $userId)
            ->where('id', $notificationId)
            ->delete();
    }

    /**
     * Delete all read notifications for a user
     */
    public function deleteReadNotifications(int $userId): void
    {
        Notification::where('user_id', $userId)
            ->where('is_read', true)
            ->delete();
    }

    // Aliases for backward compatibility (Patient controllers still work)
    public function getPatientNotificationsPaginated(int $userId, int $perPage = 15)
    {
        return $this->getNotificationsPaginated($userId, $perPage);
    }

    public function getRecentPatientNotifications(int $userId, int $limit = 20)
    {
        return $this->getRecentNotifications($userId, $limit);
    }

    public function deletePatientNotification(int $userId, int $notificationId): void
    {
        $this->deleteNotification($userId, $notificationId);
    }

    public function deleteReadPatientNotifications(int $userId): void
    {
        $this->deleteReadNotifications($userId);
    }
}
