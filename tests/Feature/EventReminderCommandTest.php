<?php

namespace Tests\Feature;

use App\Mail\EventFeedbackRequestMail;
use App\Mail\EventReminderMail;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_24h_reminder_for_eligible_registration(): void
    {
        Mail::fake();

        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->published()->create([
            'user_id' => $organizer->id,
            'start_date' => now()->addHours(24),
            'end_date' => now()->addHours(26),
        ]);

        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'payment_status' => 'paid',
            'reminder_24h_sent_at' => null,
        ]);

        Artisan::call('event-reminders:send');

        Mail::assertSent(EventReminderMail::class, function (EventReminderMail $mail) use ($registration) {
            return $mail->registration->is($registration) && $mail->variant === '24h';
        });

        $this->assertNotNull($registration->fresh()->reminder_24h_sent_at);
    }

    public function test_sends_1h_reminder_in_window(): void
    {
        Mail::fake();

        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->published()->create([
            'user_id' => $organizer->id,
            'start_date' => now()->addMinutes(60),
            'end_date' => now()->addHours(3),
        ]);

        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'payment_status' => 'paid',
            'reminder_1h_sent_at' => null,
        ]);

        Artisan::call('event-reminders:send');

        Mail::assertSent(EventReminderMail::class, function (EventReminderMail $mail) use ($registration) {
            return $mail->registration->is($registration) && $mail->variant === '1h';
        });
    }

    public function test_does_not_send_24h_for_pending_payment_on_paid_event(): void
    {
        Mail::fake();

        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->published()->paid(50)->create([
            'user_id' => $organizer->id,
            'start_date' => now()->addHours(24),
            'end_date' => now()->addHours(26),
        ]);

        Registration::factory()->create([
            'event_id' => $event->id,
            'payment_status' => 'pending',
        ]);

        Artisan::call('event-reminders:send');

        Mail::assertNothingSent();
    }

    public function test_sends_feedback_after_event_ended_in_window(): void
    {
        Mail::fake();

        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->published()->create([
            'user_id' => $organizer->id,
            'start_date' => now()->subHours(48),
            'end_date' => now()->subHours(36),
        ]);

        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'payment_status' => 'paid',
            'feedback_reminder_sent_at' => null,
        ]);

        Artisan::call('event-reminders:send');

        Mail::assertSent(EventFeedbackRequestMail::class, function (EventFeedbackRequestMail $mail) use ($registration) {
            return $mail->registration->is($registration);
        });
    }
}
