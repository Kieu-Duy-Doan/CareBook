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

    /**
     * Create a new message instance.
     */
    public function __construct(Appointment $appointment, Collection $alternativeDoctors)
    {
        $this->appointment = $appointment;
        $this->alternativeDoctors = $alternativeDoctors;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thông Báo Huỷ Lịch Khám - CareBook',
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
