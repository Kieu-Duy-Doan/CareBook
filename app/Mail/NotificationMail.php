<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $notification;
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notification->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.system_notification',
            with: [
                'title' => $this->notification->title,
                'content' => $this->notification->content,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
