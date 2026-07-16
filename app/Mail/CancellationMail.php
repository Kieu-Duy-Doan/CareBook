<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CancellationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Appointment $appointment;
    public Collection $alternativeDoctors;
    public string $actor;

    /**
     * Create a new message instance.
     */
    public function __construct(Appointment $appointment, Collection $alternativeDoctors, string $actor = 'system')
    {
        $this->appointment = $appointment;
        $this->alternativeDoctors = $alternativeDoctors;
        $this->actor = $actor;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->actor === 'patient' 
            ? 'Bạn đã huỷ lịch khám thành công - CareBook'
            : 'Thông Báo Huỷ Lịch Khám - CareBook';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.cancellation',
        );
    }
}
