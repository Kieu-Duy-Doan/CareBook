<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\UserNotificationService;
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
    protected string $actor;

    /**
     * Create a new job instance.
     * $type can be: 'confirmation', 'admin_cancel', 'doctor_cancel', 'patient_cancel', 'reminder_2h', 'reminder_30m'
     */
    public function __construct(Appointment $appointment, string $type, string $actor = 'system')
    {
        $this->appointment = $appointment;
        $this->type = $type;
        $this->actor = $actor;
    }

    public function handle(UserNotificationService $notificationService, BookingService $bookingService): void
    {
        $patientEmail = $this->appointment->bookedByUser->email ?? null;

        switch ($this->type) {
            case 'confirmation':
            case 'patient_confirmation':
            case 'admin_confirmation':
                $notificationService->notifyBookingSuccess($this->appointment, $this->actor);
                $notificationService->notifyDoctorNewAppointment($this->appointment);
                if ($patientEmail) {
                    Mail::to($patientEmail)->send(new BookingConfirmationMail($this->appointment, $this->actor));
                }
                break;

            case 'admin_cancel':
            case 'doctor_cancel':
                $alternatives = $bookingService->findAlternatives($this->appointment);
                $notificationService->notifyCancellation($this->appointment, $alternatives->toArray(), $this->actor);
                $notificationService->notifyDoctorCancellation($this->appointment, $this->actor);
                if ($patientEmail) {
                    Mail::to($patientEmail)->send(new CancellationMail($this->appointment, $alternatives, $this->actor));
                }
                break;

            case 'patient_cancel':
                $notificationService->notifyCancellation($this->appointment, [], $this->actor);
                $notificationService->notifyDoctorCancellation($this->appointment, 'patient');
                if ($patientEmail) {
                    Mail::to($patientEmail)->send(new CancellationMail($this->appointment, collect([]), $this->actor));
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
