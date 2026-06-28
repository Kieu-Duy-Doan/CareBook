<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\NotificationService;
use App\Mail\AppointmentReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [300, 300, 300];

    protected Appointment $appointment;
    protected string $timeframeLabel;

    /**
     * Create a new job instance.
     */
    public function __construct(Appointment $appointment, string $timeframeLabel)
    {
        $this->appointment = $appointment;
        $this->timeframeLabel = $timeframeLabel;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        // 1. Ghi thông báo web
        $notificationService->notifyReminder($this->appointment, $this->timeframeLabel);

        // 2. Gửi email
        $patientEmail = $this->appointment->bookedByUser->email ?? null;
        
        if ($patientEmail) {
            Mail::to($patientEmail)->send(new AppointmentReminderMail($this->appointment, $this->timeframeLabel));
        }
    }
}
