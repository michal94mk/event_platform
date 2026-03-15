<?php

namespace App\Services;

use Twilio\Rest\Client as TwilioClient;

class TwilioService
{
    public function __construct(
        protected ?TwilioClient $client
    ) {}

    public static function make(): self
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.auth_token');

        return new self(
            ($sid && $token) ? new TwilioClient($sid, $token) : null
        );
    }

    public function sendSms(string $to, string $message): bool
    {
        if (! $this->client) {
            return false;
        }

        try {
            $this->client->messages->create($to, [
                'from' => config('services.twilio.phone_number'),
                'body' => $message,
            ]);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function sendTest(string $to): bool
    {
        return $this->sendSms($to, 'Test z Event Platform – wiadomość SMS działa poprawnie.');
    }

    public function isConfigured(): bool
    {
        return $this->client !== null
            && ! empty(config('services.twilio.phone_number'));
    }
}
