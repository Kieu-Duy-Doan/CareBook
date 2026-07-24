<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Notification;
use App\Services\UserNotificationService;

class NotificationController extends Controller
{
    protected UserNotificationService $notificationService;

    public function __construct(UserNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Trang danh sách thông báo
     */
    public function page()
    {
        $notifications = $this->notificationService->getNotificationsPaginated(Auth::id(), 15);

        return view('doctor.notifications.index', compact('notifications'));
    }

    /**
     * Trang chi tiết thông báo
     */
    public function show($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);

        if (!$notification->is_read) {
            $notification->is_read = true;
            $notification->save();
        }

        $appointment = null;
        if ($notification->ref_type === 'appointment' && $notification->ref_id) {
            $appointment = Appointment::with(['doctorProfile', 'specialty', 'patientProfile'])->find($notification->ref_id);
        }

        return view('doctor.notifications.show', compact('notification', 'appointment'));
    }

    /**
     * API JSON lấy thông báo gần đây cho dropdown
     */
    public function index()
    {
        $userId = Auth::id();
        $notifications = $this->notificationService->getRecentNotifications($userId, 20);
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Đánh dấu đã đọc
     */
    public function markAsRead(Request $request)
    {
        $this->notificationService->markAsRead(Auth::id(), $request->input('id'));

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }

    /**
     * Xoá 1 thông báo
     */
    public function destroy($id)
    {
        $this->notificationService->deleteNotification(Auth::id(), $id);

        return redirect()->back()->with('success', 'Đã xoá thông báo!');
    }

    /**
     * Xoá tất cả thông báo đã đọc
     */
    public function destroyRead()
    {
        $this->notificationService->deleteReadNotifications(Auth::id());

        return redirect()->back()->with('success', 'Đã dọn dẹp các thông báo đã đọc!');
    }
}
