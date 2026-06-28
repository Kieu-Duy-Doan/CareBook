<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\NotificationService;
use App\Services\AlternativeDoctorService;
use App\Mail\CancellationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCancellationNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [300, 300, 300];

    protected Appointment $appointment;

    /**
     * Create a new job instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService, AlternativeDoctorService $alternativeDoctorService): void
    {
        // 1. Ghi thông báo web (ko cần kèm gợi ý bác sĩ vào web notification, email mới có)
        $notificationService->notifyCancellation($this->appointment);

        // 2. Lấy danh sách bác sĩ gợi ý
        $alternatives = $alternativeDoctorService->findAlternatives($this->appointment);

        // 3. Gửi email
        $patientEmail = $this->appointment->bookedByUser->email ?? null;
        
        if ($patientEmail) {
            Mail::to($patientEmail)->send(new CancellationMail($this->appointment, $alternatives));
        }
    }
}
