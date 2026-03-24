<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventFeedbackRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Registration $registration)
    {
        $this->registration->loadMissing('event');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Jak minęło wydarzenie „'.$this->registration->event->title.'”?',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-feedback-request',
        );
    }
}
