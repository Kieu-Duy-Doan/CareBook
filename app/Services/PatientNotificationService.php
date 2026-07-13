<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Appointment;

class PatientNotificationService
{
    /**
     * Ghi thông báo In-Web cho người dùng liên quan đến lịch khám
     */
    public function logWebNotification(Appointment $appointment, string $title, string $content, string $type = 'appointment', array $data = []): Notification
    {
        $notification = Notification::create([
            'user_id' => $appointment->booked_by_user_id,
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
            ['alternatives' => $alternatives] // Save suggested doctors
        );
    }

    /**
     * Get paginated notifications for a patient
     */
    public function getPatientNotificationsPaginated(int $userId, int $perPage = 15)
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get recent notifications for the dropdown
     */
    public function getRecentPatientNotifications(int $userId, int $limit = 20)
    {
        return Notification::where('user_id', $userId)
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
    public function deletePatientNotification(int $userId, int $notificationId): void
    {
        Notification::where('user_id', $userId)
            ->where('id', $notificationId)
            ->delete();
    }

    /**
     * Delete all read notifications for a patient
     */
    public function deleteReadPatientNotifications(int $userId): void
    {
        Notification::where('user_id', $userId)
            ->where('is_read', true)
            ->delete();
    }
}
