<?php

namespace App\Mail;

use App\Models\MedicalRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FollowupReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public MedicalRecord $medicalRecord;

    public function __construct(MedicalRecord $medicalRecord)
    {
        $this->medicalRecord = $medicalRecord;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nhắc Nhở Tái Khám - CareBook',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.followup-reminder',
            with: [
                'medicalRecord' => $this->medicalRecord,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
