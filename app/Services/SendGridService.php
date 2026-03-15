<?php

namespace App\Services;

use SendGrid;
use SendGrid\Mail\Mail;

class SendGridService
{
    public function __construct(
        protected ?SendGrid $client
    ) {}

    public static function make(): self
    {
        $apiKey = config('services.sendgrid.api_key');

        return new self(
            $apiKey ? new SendGrid($apiKey) : null
        );
    }

    public function send(string $to, string $subject, string $htmlContent): bool
    {
        if (! $this->client) {
            return false;
        }

        try {
            $email = new Mail;
            $email->setFrom(
                config('services.sendgrid.from_email'),
                config('services.sendgrid.from_name')
            );
            $email->addTo($to);
            $email->setSubject($subject);
            $email->addContent('text/html', $htmlContent);

            $this->client->send($email);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function sendTest(string $to): bool
    {
        return $this->send(
            $to,
            'Test z Event Platform',
            '<p>To jest testowa wiadomość z platformy wydarzeń.</p>'
        );
    }

    public function isConfigured(): bool
    {
        return $this->client !== null;
    }
}
