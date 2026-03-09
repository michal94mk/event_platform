<?php

namespace Tests\Feature;

use App\Models\CalendarSubscriptionToken;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_calendar_ics_returns_200_for_published_event(): void
    {
        $event = Event::factory()->published()->create(['title' => 'Public Event']);

        $response = $this->get(route('events.calendar', $event->slug));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Public-Event', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->streamedContent());
        $this->assertStringContainsString('Public Event', $response->streamedContent());
    }

    public function test_event_calendar_ics_returns_404_for_draft_event(): void
    {
        $event = Event::factory()->draft()->create();

        $this->get(route('events.calendar', $event->slug))
            ->assertNotFound();
    }

    public function test_registration_calendar_ics_accessible_with_valid_token(): void
    {
        $event = Event::factory()->published()->create(['title' => 'Event For Ticket']);
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'qr_code' => 'valid-token-123',
        ]);

        $response = $this->get(route('registrations.calendar', [
            'registration' => $registration->id,
            'token' => 'valid-token-123',
        ]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('Event For Ticket', $response->streamedContent());
    }

    public function test_registration_calendar_ics_forbidden_with_invalid_token(): void
    {
        $registration = Registration::factory()->create(['qr_code' => 'correct-token']);

        $this->get(route('registrations.calendar', [
            'registration' => $registration->id,
            'token' => 'wrong-token',
        ]))
            ->assertForbidden();
    }

    public function test_authenticated_owner_can_access_registration_calendar_without_token(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->published()->create();
        $registration = Registration::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $this->actingAs($user)
            ->get(route('registrations.calendar', $registration->id))
            ->assertOk();
    }

    public function test_subscribe_returns_404_without_token(): void
    {
        $this->get(route('calendar.subscribe'))
            ->assertNotFound();
    }

    public function test_subscribe_returns_404_with_invalid_token(): void
    {
        $this->get(route('calendar.subscribe', ['token' => 'invalid-token-xyz']))
            ->assertNotFound();
    }

    public function test_subscribe_returns_ics_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = CalendarSubscriptionToken::generateFor($user);

        $response = $this->get(route('calendar.subscribe', ['token' => $token->token]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('max-age=900', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->streamedContent());
    }

    public function test_subscribe_returns_404_for_expired_token(): void
    {
        $user = User::factory()->create();
        $token = CalendarSubscriptionToken::create([
            'user_id' => $user->id,
            'token' => 'expired-token',
            'expires_at' => now()->subDay(),
        ]);

        $this->get(route('calendar.subscribe', ['token' => $token->token]))
            ->assertNotFound();
    }

    public function test_events_calendar_feed_returns_json(): void
    {
        Event::factory()->published()->create([
            'title' => 'Feed Event',
            'start_date' => now()->addDays(5),
        ]);

        $response = $this->get(route('events.calendar.feed'));

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'Feed Event']);
    }

    public function test_events_calendar_feed_respects_date_range(): void
    {
        Event::factory()->published()->create([
            'title' => 'In Range',
            'start_date' => now()->addDays(10),
        ]);
        Event::factory()->published()->create([
            'title' => 'Out Of Range',
            'start_date' => now()->addYears(3),
        ]);

        $response = $this->get(route('events.calendar.feed'), [
            'start' => now()->format('Y-m-d'),
            'end' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertOk();
        $data = $response->json();
        $titles = array_column($data, 'title');
        $this->assertContains('In Range', $titles);
        $this->assertNotContains('Out Of Range', $titles);
    }

    public function test_events_calendar_feed_excludes_draft_for_guests(): void
    {
        Event::factory()->published()->create(['title' => 'Published']);
        Event::factory()->draft()->create(['title' => 'Draft']);

        $response = $this->get(route('events.calendar.feed'));

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'Published']);
    }
}
