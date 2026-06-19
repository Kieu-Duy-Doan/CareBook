<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::selectRaw('
            title,
            content,
            type,
            created_at,
            MAX(scheduled_at) as scheduled_at,
            COUNT(DISTINCT user_id) as total_recipients,
            SUM(CASE WHEN channel = "email" THEN 1 ELSE 0 END) as total_email,
            SUM(CASE WHEN channel = "in_web" THEN 1 ELSE 0 END) as total_in_web,
            SUM(CASE WHEN channel = "email" AND is_sent = 1 THEN 1 ELSE 0 END) as sent_email_count,
            SUM(CASE WHEN channel = "in_web" AND is_read = 1 THEN 1 ELSE 0 END) as read_in_web_count,
            MAX(is_sent) as is_sent
        ')
            ->groupBy('title', 'content', 'type', 'created_at')
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'sent') {
                $query->havingRaw('MAX(is_sent) = 1');
            } elseif ($status === 'pending') {
                $query->havingRaw('MAX(is_sent) = 0');
            }
        }

        $campaigns = $query->paginate(20)->withQueryString();

        return view('admin.notifications.index', compact('campaigns'));
    }

    public function create()
    {
        $users = User::select('id', 'full_name', 'email', 'role')->get();
        return view('admin.notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:appointment,result,system,reminder',
            'channels' => 'required|array',
            'channels.*' => 'in:in_web,email',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
        ]);

        $now = now();
        $insertData = [];

        foreach ($request->user_ids as $userId) {
            foreach ($request->channels as $channel) {
                $insertData[] = [
                    'user_id' => $userId,
                    'title' => $request->title,
                    'content' => $request->content,
                    'type' => $request->type,
                    'channel' => $channel,
                    'scheduled_at' => $request->scheduled_at,
                    'is_sent' => false,
                    'is_read' => false,
                    'created_at' => $now,
                ];
            }
        }
        foreach (array_chunk($insertData, 500) as $chunk) {
            Notification::insert($chunk);
        }
        $emailNotifications = Notification::where('created_at', $now)
            ->where('title', $request->title)
            ->where('channel', 'email')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->get();

        foreach ($emailNotifications as $notification) {
            \App\Jobs\SendEmailNotificationJob::dispatch($notification->id);
        }

        return redirect()->route('admin.notifications.index')->with('success', 'Đã tạo và đưa vào hàng đợi thông báo thành công.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'created_at' => 'required|date',
        ]);

        Notification::where('title', $request->title)
            ->where('created_at', $request->created_at)
            ->delete();
        return back()->with('success', 'Đã xoá chiến dịch thông báo.');
    }

    public function resend(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'created_at' => 'required|date',
        ]);
        Notification::where('title', $request->title)
            ->where('created_at', $request->created_at)
            ->where('channel', 'email')
            ->update(['is_sent' => false]);

        return back()->with('success', 'Đã đặt lại trạng thái để gửi lại thông báo qua Email.');
    }
}
