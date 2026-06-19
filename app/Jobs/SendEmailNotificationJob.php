<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationId;
    public function __construct($notificationId)
    {
        $this->notificationId = $notificationId;
    }

    public function handle(): void
    {
        $notification = Notification::with('user')->find($this->notificationId);
        if (!$notification || $notification->channel !== 'email' || $notification->is_sent) {
            return;
        }

        if (!$notification->user || empty($notification->user->email)) {
            return;
        }

        try {
            Mail::to($notification->user->email)->send(new \App\Mail\NotificationMail($notification));
            $notification->update(['is_sent' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to send email notification: ' . $e->getMessage(), [
                'notification_id' => $notification->id,
                'user_id' => $notification->user_id
            ]);
            throw $e;
        }
    }
}
