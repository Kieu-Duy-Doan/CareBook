<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Jobs\SendEmailNotificationJob;

class ProcessScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quét và đẩy các thông báo email đã đến hẹn giờ (scheduled_at <= now) vào Queue';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $this->info('Starting to process scheduled notifications...');

        // Lấy tất cả thông báo qua email, chưa gửi, và đã tới giờ gửi
        $notifications = Notification::where('channel', 'email')
            ->where('is_sent', false)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->get();

        $count = $notifications->count();
        if ($count === 0) {
            $this->info('No scheduled notifications to process at this time.');
            return;
        }

        $this->info("Found {$count} notifications to process.");

        foreach ($notifications as $notification) {
            SendEmailNotificationJob::dispatch($notification->id);
            $this->line("Dispatched job for Notification ID: {$notification->id}");
        }

        $this->info('Finished processing scheduled notifications.');
    }
}
