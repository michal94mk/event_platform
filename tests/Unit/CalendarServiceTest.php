<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Services\CalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    private CalendarService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CalendarService::class);
    }

    public function test_generate_ics_returns_valid_ics_for_event(): void
    {
        $event = Event::factory()->published()->create([
            'title' => 'Test Event',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(5)->addHours(2),
            'venue_name' => 'Test Venue',
            'venue_city' => 'Warszawa',
        ]);

        $ics = $this->service->generateIcs($event);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('END:VCALENDAR', $ics);
        $this->assertStringContainsString('BEGIN:VEVENT', $ics);
        $this->assertStringContainsString('END:VEVENT', $ics);
        $this->assertStringContainsString('Test Event', $ics);
        $this->assertStringContainsString('Test Venue', $ics);
        $this->assertStringContainsString('Warszawa', $ics);
    }

    public function test_generate_ics_includes_registration_details_when_provided(): void
    {
        $event = Event::factory()->published()->create(['title' => 'Event With Registration']);
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'ticket_quantity' => 2,
        ]);

        $ics = $this->service->generateIcs($event, $registration);

        $this->assertStringContainsString('Event With Registration', $ics);
        $this->assertStringContainsString('Jan', $ics);
        $this->assertStringContainsString('Kowalski', $ics);
    }

    public function test_generate_subscription_ics_includes_user_registrations(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->published()->create([
            'title' => 'My Registered Event',
            'start_date' => now()->addDays(10),
        ]);
        Registration::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $ics = $this->service->generateSubscriptionIcs($user);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('Event Platform', $ics);
        $this->assertStringContainsString('My Registered Event', $ics);
    }

    public function test_generate_subscription_ics_includes_own_events(): void
    {
        $organizer = User::factory()->organizer()->create();
        Event::factory()->published()->create([
            'user_id' => $organizer->id,
            'title' => 'My Organized Event',
            'start_date' => now()->addDays(15),
        ]);

        $ics = $this->service->generateSubscriptionIcs($organizer);

        $this->assertStringContainsString('My Organized Event', $ics);
    }

    public function test_generate_subscription_ics_returns_empty_calendar_when_no_events(): void
    {
        $user = User::factory()->create();

        $ics = $this->service->generateSubscriptionIcs($user);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('END:VCALENDAR', $ics);
        $this->assertStringNotContainsString('BEGIN:VEVENT', $ics);
    }
}
