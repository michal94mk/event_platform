<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Registration $registration,
        public string $variant
    ) {
        $this->registration->loadMissing('event');
    }

    public function envelope(): Envelope
    {
        $title = $this->registration->event->title;

        $subject = match ($this->variant) {
            '24h' => 'Przypomnienie: jutro – '.$title,
            '1h' => 'Za godzinę: '.$title,
            default => 'Przypomnienie: '.$title,
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-reminder',
        );
    }
}
