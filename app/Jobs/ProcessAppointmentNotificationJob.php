<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\NotificationService;
use App\Services\BookingService;
use App\Mail\BookingConfirmationMail;
use App\Mail\CancellationMail;
// ... other mails
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcessAppointmentNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [300, 300, 300];

    protected Appointment $appointment;
    protected string $type;

    /**
     * Create a new job instance.
     * $type can be: 'confirmation', 'cancellation', 'reminder_2h', 'reminder_30m'
     */
    public function __construct(Appointment $appointment, string $type)
    {
        $this->appointment = $appointment;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService, BookingService $bookingService): void
    {
        $patientEmail = $this->appointment->bookedByUser->email ?? null;

        switch ($this->type) {
            case 'confirmation':
                $notificationService->notifyBookingSuccess($this->appointment);
                if ($patientEmail) {
                    Mail::to($patientEmail)->send(new BookingConfirmationMail($this->appointment));
                }
                break;

            case 'cancellation':
                $notificationService->notifyCancellation($this->appointment);
                $alternatives = $bookingService->findAlternatives($this->appointment);
                if ($patientEmail) {
                    Mail::to($patientEmail)->send(new CancellationMail($this->appointment, $alternatives));
                }
                break;

            case 'reminder_2h':
                $notificationService->notifyReminder($this->appointment, '2 tiếng');
                // You can add email sending here if desired
                break;

            case 'reminder_30m':
                $notificationService->notifyReminder($this->appointment, '30 phút');
                // You can add email sending here if desired
                break;
        }
    }
}
