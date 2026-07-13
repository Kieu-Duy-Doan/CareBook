<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Appointment $appointment;
    public string $actor;

    /**
     * Create a new message instance.
     */
    public function __construct(Appointment $appointment, string $actor = 'system')
    {
        $this->appointment = $appointment;
        $this->actor = $actor;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->actor === 'patient'
            ? 'Xác Nhận Đặt Lịch Khám Thành Công - CareBook'
            : 'Lịch Khám Đã Được Đặt Thành Công Bởi Phòng Khám - CareBook';

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
            view: 'emails.booking-confirmation',
        );
    }
}
