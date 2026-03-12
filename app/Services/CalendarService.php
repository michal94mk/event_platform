<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event as IcalEvent;

class CalendarService
{
    public function generateSubscriptionIcs(User $user): string
    {
        $events = $this->getSubscriptionEvents($user);

        $calendar = Calendar::create('Event Platform – Moje wydarzenia')
            ->description('Wydarzenia, na które jesteś zapisany oraz wydarzenia, które organizujesz');

        foreach ($events as $eventData) {
            $event = $eventData['event'];
            $registration = $eventData['registration'] ?? null;

            $location = $this->buildLocation($event);
            $description = $this->buildDescription($event, $registration);
            $url = $registration
                ? route('registrations.show', ['registration' => $registration->id, 'token' => $registration->qr_code])
                : route('events.show', $event->slug);

            $uid = $registration
                ? "registration-{$registration->id}@".parse_url(config('app.url'), PHP_URL_HOST)
                : "event-{$event->id}@".parse_url(config('app.url'), PHP_URL_HOST);

            $icalEvent = IcalEvent::create($event->title)
                ->startsAt($event->start_date)
                ->endsAt($event->end_date ?? $event->start_date)
                ->address($location)
                ->description($description)
                ->url($url)
                ->uniqueIdentifier($uid);

            $calendar->event($icalEvent);
        }

        return $calendar->get();
    }

    private function getSubscriptionEvents(User $user): array
    {
        $result = [];
        $seenEventIds = [];

        $from = now()->subMonths(3);
        $to = now()->addYears(2);

        $registrations = Registration::with('event')
            ->where('user_id', $user->id)
            ->whereHas('event', fn ($q) => $q->whereBetween('start_date', [$from, $to]))
            ->orderByDesc('created_at')
            ->get();

        foreach ($registrations as $registration) {
            if (! in_array($registration->event_id, $seenEventIds, true)) {
                $seenEventIds[] = $registration->event_id;
                $result[] = ['event' => $registration->event, 'registration' => $registration];
            }
        }

        $ownEvents = Event::where('user_id', $user->id)
            ->whereBetween('start_date', [$from, $to])
            ->orderBy('start_date')
            ->get();

        foreach ($ownEvents as $event) {
            if (! in_array($event->id, $seenEventIds, true)) {
                $seenEventIds[] = $event->id;
                $result[] = ['event' => $event, 'registration' => null];
            }
        }

        usort($result, fn ($a, $b) => $a['event']->start_date->timestamp <=> $b['event']->start_date->timestamp);

        return $result;
    }

    public function generateIcs(Event $event, ?Registration $registration = null): string
    {
        $event->loadMissing('user');

        $location = $this->buildLocation($event);
        $description = $this->buildDescription($event, $registration);
        $url = $registration
            ? route('registrations.show', ['registration' => $registration->id, 'token' => $registration->qr_code])
            : route('events.show', $event->slug);

        $uid = $registration
            ? "registration-{$registration->id}@".parse_url(config('app.url'), PHP_URL_HOST)
            : "event-{$event->id}@".parse_url(config('app.url'), PHP_URL_HOST);

        $icalEvent = IcalEvent::create($event->title)
            ->startsAt($event->start_date)
            ->endsAt($event->end_date ?? $event->start_date)
            ->address($location)
            ->description($description)
            ->url($url)
            ->uniqueIdentifier($uid);

        return Calendar::create($event->title)
            ->event($icalEvent)
            ->get();
    }

    private function buildLocation(Event $event): string
    {
        $parts = array_filter([
            $event->venue_name,
            $event->venue_address,
            $event->venue_city,
            $event->venue_country,
        ]);

        return implode(', ', $parts) ?: '';
    }

    private function buildDescription(Event $event, ?Registration $registration): string
    {
        $lines = [];

        if ($event->description) {
            $lines[] = strip_tags($event->description);
        }

        if ($registration) {
            $lines[] = '';
            $lines[] = "Uczestnik: {$registration->first_name} {$registration->last_name}";
            $lines[] = "Biletów: {$registration->ticket_quantity}";
        }

        $lines[] = '';
        $lines[] = route('events.show', $event->slug);

        return implode("\n", $lines);
    }
}
