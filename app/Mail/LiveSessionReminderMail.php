<?php

namespace App\Mail;

use App\Models\LiveSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LiveSessionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LiveSchedule $schedule
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel : Séance en direct bientôt !',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.live-reminder',
            with: [
                'schedule' => $this->schedule,
                'courseTitle' => $this->schedule->course->title,
                'scheduledAt' => $this->schedule->scheduled_at->format('d/m/Y à H:i'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}