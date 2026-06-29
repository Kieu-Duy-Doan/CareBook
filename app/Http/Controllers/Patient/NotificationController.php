<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * View the full notifications page
     */
    public function page()
    {
        $notifications = $this->notificationService->getPatientNotificationsPaginated(Auth::id(), 15);
            
        return view('patient.notifications.index', compact('notifications'));
    }

    /**
     * Get recent notifications for the header dropdown
     */
    public function index()
    {
        $userId = Auth::id();

        // Get 20 recent notifications (both read and unread)
        $notifications = $this->notificationService->getRecentPatientNotifications($userId, 20);
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        // Map and append appointment info for cancellation type
        $notificationsData = $notifications->map(function($notif) {
            $data = $notif->toArray();
            if ($notif->type === 'cancellation' && $notif->ref_type === 'appointment' && $notif->ref_id) {
                $appointment = Appointment::find($notif->ref_id);
                if ($appointment) {
                    $data['appointment_info'] = [
                        'patient_profile_id' => $appointment->patient_profile_id,
                        'specialty_id' => $appointment->specialty_id,
                        'doctor_profile_id' => $appointment->doctor_profile_id,
                        'reason' => $appointment->reason,
                        'booking_method' => $appointment->booking_method,
                    ];
                }
            }
            return $data;
        });

        return response()->json([
            'notifications' => $notificationsData,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request)
    {
        $this->notificationService->markAsRead(Auth::id(), $request->input('id'));

        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $this->notificationService->deletePatientNotification(Auth::id(), $id);

        return redirect()->back()->with('success', 'Đã xoá thông báo!');
    }
}
