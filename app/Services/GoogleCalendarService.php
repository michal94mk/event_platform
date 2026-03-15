<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Integration;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event as GoogleCalendarEvent;
use Google\Service\Calendar\EventDateTime;

class GoogleCalendarService
{
    public function __construct(
        protected Client $client
    ) {}

    public static function make(): self
    {
        $client = new Client;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->addScope(Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return new self($client);
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function handleCallback(string $code): array
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \RuntimeException($token['error_description'] ?? $token['error']);
        }

        return $token;
    }

    public function createIntegrationForUser(int $userId, array $token): Integration
    {
        $integration = Integration::updateOrCreate(
            [
                'user_id' => $userId,
                'provider' => Integration::PROVIDER_GOOGLE_CALENDAR,
            ],
            [
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'expires_at' => isset($token['expires_in'])
                    ? now()->addSeconds($token['expires_in'])
                    : null,
                'provider_user_id' => null,
                'is_active' => true,
            ]
        );

        return $integration;
    }

    public function getCalendarService(Integration $integration): Calendar
    {
        $expiresIn = $integration->expires_at
            ? max(0, (int) now()->diffInSeconds($integration->expires_at, false))
            : 0;

        $this->client->setAccessToken([
            'access_token' => $integration->access_token,
            'refresh_token' => $integration->refresh_token,
            'expires_in' => $expiresIn,
        ]);

        if ($this->client->isAccessTokenExpired() && $integration->refresh_token) {
            $newToken = $this->client->fetchAccessTokenWithRefreshToken($integration->refresh_token);
            $integration->update([
                'access_token' => $newToken['access_token'],
                'expires_at' => isset($newToken['expires_in'])
                    ? now()->addSeconds($newToken['expires_in'])
                    : null,
            ]);
        }

        return new Calendar($this->client);
    }

    public function createEvent(Integration $integration, Event $event): ?string
    {
        $calendar = $this->getCalendarService($integration);

        $googleEvent = new GoogleCalendarEvent([
            'summary' => $event->title,
            'description' => $event->description,
            'location' => $this->formatLocation($event),
            'start' => new EventDateTime([
                'dateTime' => $event->start_date->toRfc3339String(),
                'timeZone' => config('app.timezone', 'Europe/Warsaw'),
            ]),
            'end' => new EventDateTime([
                'dateTime' => $event->end_date->toRfc3339String(),
                'timeZone' => config('app.timezone', 'Europe/Warsaw'),
            ]),
        ]);

        $created = $calendar->events->insert('primary', $googleEvent);

        return $created->getId();
    }

    public function updateEvent(Integration $integration, Event $event): bool
    {
        if (empty($event->google_calendar_event_id)) {
            return false;
        }

        $calendar = $this->getCalendarService($integration);

        $googleEvent = $calendar->events->get('primary', $event->google_calendar_event_id);

        $googleEvent->setSummary($event->title);
        $googleEvent->setDescription($event->description);
        $googleEvent->setLocation($this->formatLocation($event));
        $googleEvent->setStart(new EventDateTime([
            'dateTime' => $event->start_date->toRfc3339String(),
            'timeZone' => config('app.timezone', 'Europe/Warsaw'),
        ]));
        $googleEvent->setEnd(new EventDateTime([
            'dateTime' => $event->end_date->toRfc3339String(),
            'timeZone' => config('app.timezone', 'Europe/Warsaw'),
        ]));

        $calendar->events->update('primary', $event->google_calendar_event_id, $googleEvent);

        return true;
    }

    public function deleteEvent(Integration $integration, Event $event): bool
    {
        if (empty($event->google_calendar_event_id)) {
            return false;
        }

        $calendar = $this->getCalendarService($integration);
        $calendar->events->delete('primary', $event->google_calendar_event_id);

        return true;
    }

    public function syncEvent(Integration $integration, Event $event): ?string
    {
        if ($event->google_calendar_event_id) {
            $this->updateEvent($integration, $event);

            return $event->google_calendar_event_id;
        }

        return $this->createEvent($integration, $event);
    }

    public function isConfigured(): bool
    {
        return ! empty(config('services.google.client_id'))
            && ! empty(config('services.google.client_secret'));
    }

    private function formatLocation(Event $event): string
    {
        $parts = array_filter([
            $event->venue_name,
            $event->venue_address,
            $event->venue_city,
            $event->venue_country,
        ]);

        return implode(', ', $parts);
    }
}
