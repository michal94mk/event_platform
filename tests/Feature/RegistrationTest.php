<?php

namespace Tests\Feature;

use App\Mail\OrganizerRegistrationNotification;
use App\Mail\RegistrationConfirmation;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Index
    // -----------------------------------------------------------------------

    public function test_guests_are_redirected_from_registrations_index(): void
    {
        $this->get(route('registrations.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_sees_only_their_own_registrations(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->published()->create();
        Registration::factory()->create(['user_id' => $user->id, 'event_id' => $event->id]);
        Registration::factory()->create(); // inna osoba

        $this->actingAs($user)
            ->get(route('registrations.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('registrations/MyRegistrations')
                ->has('registrations', 1)
            );
    }

    // -----------------------------------------------------------------------
    // Store
    // -----------------------------------------------------------------------

    public function test_authenticated_user_can_register_for_published_event(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $event = Event::factory()->published()->create();

        $this->actingAs($user)
            ->post(route('events.register', $event->slug), $this->registrationData())
            ->assertRedirect();

        $this->assertDatabaseHas('registrations', [
            'event_id'   => $event->id,
            'user_id'    => $user->id,
            'first_name' => 'Jan',
            'last_name'  => 'Kowalski',
        ]);
    }

    public function test_guest_can_register_without_account(): void
    {
        Mail::fake();

        $event = Event::factory()->published()->create();

        $this->post(route('events.register', $event->slug), $this->registrationData())
            ->assertRedirect();

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'user_id'  => null,
            'email'    => 'jan@example.com',
        ]);
    }

    public function test_confirmation_email_is_sent_to_attendee(): void
    {
        Mail::fake();

        $event = Event::factory()->published()->create();

        $this->post(route('events.register', $event->slug), $this->registrationData(['email' => 'test@example.com']));

        Mail::assertQueued(RegistrationConfirmation::class, fn ($mail) => $mail->hasTo('test@example.com'));
    }

    public function test_notification_email_is_sent_to_organizer(): void
    {
        Mail::fake();

        $organizer = User::factory()->organizer()->create(['email' => 'organizer@example.com']);
        $event = Event::factory()->published()->create(['user_id' => $organizer->id]);

        $this->post(route('events.register', $event->slug), $this->registrationData());

        Mail::assertQueued(OrganizerRegistrationNotification::class, fn ($mail) => $mail->hasTo('organizer@example.com'));
    }

    public function test_in_app_notification_created_for_organizer_on_registration(): void
    {
        Mail::fake();

        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->published()->create(['user_id' => $organizer->id]);

        $this->post(route('events.register', $event->slug), $this->registrationData());

        $this->assertDatabaseHas('notifications', [
            'user_id' => $organizer->id,
            'type'    => 'registration_created',
        ]);
    }

    public function test_free_event_registration_payment_status_is_paid(): void
    {
        Mail::fake();

        $event = Event::factory()->published()->create(['ticket_price' => null]);

        $this->post(route('events.register', $event->slug), $this->registrationData());

        $this->assertDatabaseHas('registrations', [
            'event_id'       => $event->id,
            'payment_status' => 'paid',
            'total_amount'   => '0.00',
        ]);
    }

    public function test_paid_event_registration_payment_status_is_pending(): void
    {
        Mail::fake();

        $event = Event::factory()->published()->paid(100)->create();

        $this->post(route('events.register', $event->slug), $this->registrationData(['ticket_quantity' => 1]));

        $this->assertDatabaseHas('registrations', [
            'event_id'       => $event->id,
            'payment_status' => 'pending',
            'total_amount'   => '100.00',
        ]);
    }

    public function test_cannot_register_for_draft_event(): void
    {
        $event = Event::factory()->draft()->create();

        $this->post(route('events.register', $event->slug), $this->registrationData())
            ->assertForbidden();
    }

    public function test_store_validates_required_fields(): void
    {
        $event = Event::factory()->published()->create();

        $this->post(route('events.register', $event->slug), [])
            ->assertInvalid(['first_name', 'last_name', 'email', 'ticket_quantity']);
    }

    // -----------------------------------------------------------------------
    // Show
    // -----------------------------------------------------------------------

    public function test_registration_accessible_with_valid_token(): void
    {
        $event = Event::factory()->published()->create();
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'qr_code'  => 'valid-token-12345678901234567',
        ]);

        $this->get(route('registrations.show', [
            'registration' => $registration->id,
            'token'        => 'valid-token-12345678901234567',
        ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('registrations/Show'));
    }

    public function test_registration_show_forbidden_with_invalid_token(): void
    {
        $event = Event::factory()->published()->create();
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'qr_code'  => 'correct-token-12345678901234',
        ]);

        $this->get(route('registrations.show', [
            'registration' => $registration->id,
            'token'        => 'wrong-token',
        ]))
            ->assertForbidden();
    }

    public function test_authenticated_owner_can_view_registration_without_token(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->published()->create();
        $registration = Registration::factory()->create([
            'user_id'  => $user->id,
            'event_id' => $event->id,
        ]);

        $this->actingAs($user)
            ->get(route('registrations.show', $registration->id))
            ->assertOk();
    }

    public function test_other_user_cannot_view_registration_without_token(): void
    {
        $event = Event::factory()->published()->create();
        $registration = Registration::factory()->create(['event_id' => $event->id]);
        $other = User::factory()->create();

        $this->actingAs($other)
            ->get(route('registrations.show', $registration->id))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // CheckIn page
    // -----------------------------------------------------------------------

    public function test_event_owner_can_access_check_in_page(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['user_id' => $organizer->id]);

        $this->actingAs($organizer)
            ->get(route('events.check-in.page', $event->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('events/CheckIn'));
    }

    public function test_admin_can_access_check_in_page_of_any_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)
            ->get(route('events.check-in.page', $event->slug))
            ->assertOk();
    }

    public function test_non_owner_cannot_access_check_in_page(): void
    {
        $event = Event::factory()->create();
        $other = User::factory()->organizer()->create();

        $this->actingAs($other)
            ->get(route('events.check-in.page', $event->slug))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // CheckIn – skanowanie QR
    // -----------------------------------------------------------------------

    public function test_organizer_can_check_in_attendee_with_valid_qr_code(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['user_id' => $organizer->id]);
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'qr_code'  => 'VALID-QR-CODE-123456789012345',
        ]);

        $this->actingAs($organizer)
            ->post(route('events.check-in', $event->slug), ['qr_code' => 'VALID-QR-CODE-123456789012345'])
            ->assertRedirect();

        $this->assertDatabaseHas('registrations', [
            'id'         => $registration->id,
            'checked_in' => true,
        ]);
    }

    public function test_check_in_returns_error_for_invalid_qr_code(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['user_id' => $organizer->id]);

        $this->actingAs($organizer)
            ->post(route('events.check-in', $event->slug), ['qr_code' => 'nieistniejacy-kod'])
            ->assertSessionHasErrors('qr_code');
    }

    public function test_check_in_returns_info_when_already_checked_in(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['user_id' => $organizer->id]);
        Registration::factory()->checkedIn()->create([
            'event_id' => $event->id,
            'qr_code'  => 'ALREADY-CHECKEDIN-QR-12345678',
        ]);

        $this->actingAs($organizer)
            ->post(route('events.check-in', $event->slug), ['qr_code' => 'ALREADY-CHECKEDIN-QR-12345678'])
            ->assertSessionHas('info');
    }

    public function test_non_owner_cannot_perform_check_in(): void
    {
        $event = Event::factory()->create();
        $other = User::factory()->organizer()->create();
        Registration::factory()->create(['event_id' => $event->id, 'qr_code' => 'SOME-QR']);

        $this->actingAs($other)
            ->post(route('events.check-in', $event->slug), ['qr_code' => 'SOME-QR'])
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // Destroy – anulowanie
    // -----------------------------------------------------------------------

    public function test_user_can_cancel_their_own_upcoming_registration(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->published()->create();
        $registration = Registration::factory()->create([
            'user_id'  => $user->id,
            'event_id' => $event->id,
        ]);

        $this->actingAs($user)
            ->delete(route('registrations.destroy', $registration->id))
            ->assertRedirect(route('registrations.index'));

        $this->assertDatabaseMissing('registrations', ['id' => $registration->id]);
    }

    public function test_cancellation_creates_notification_for_organizer(): void
    {
        $organizer = User::factory()->organizer()->create();
        $user = User::factory()->create();
        $event = Event::factory()->published()->create(['user_id' => $organizer->id]);
        $registration = Registration::factory()->create([
            'user_id'  => $user->id,
            'event_id' => $event->id,
        ]);

        $this->actingAs($user)
            ->delete(route('registrations.destroy', $registration->id));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $organizer->id,
            'type'    => 'registration_cancelled',
        ]);
    }

    public function test_user_cannot_cancel_checked_in_registration(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->published()->create();
        $registration = Registration::factory()->checkedIn()->create([
            'user_id'  => $user->id,
            'event_id' => $event->id,
        ]);

        $this->actingAs($user)
            ->delete(route('registrations.destroy', $registration->id))
            ->assertForbidden();

        $this->assertDatabaseHas('registrations', ['id' => $registration->id]);
    }

    public function test_user_cannot_cancel_registration_for_past_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->published()->past()->create();
        $registration = Registration::factory()->create([
            'user_id'  => $user->id,
            'event_id' => $event->id,
        ]);

        $this->actingAs($user)
            ->delete(route('registrations.destroy', $registration->id))
            ->assertForbidden();
    }

    public function test_user_cannot_cancel_another_users_registration(): void
    {
        $event = Event::factory()->published()->create();
        $registration = Registration::factory()->create(['event_id' => $event->id]);
        $other = User::factory()->create();

        $this->actingAs($other)
            ->delete(route('registrations.destroy', $registration->id))
            ->assertForbidden();

        $this->assertDatabaseHas('registrations', ['id' => $registration->id]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'first_name'      => 'Jan',
            'last_name'       => 'Kowalski',
            'email'           => 'jan@example.com',
            'ticket_quantity' => 1,
        ], $overrides);
    }
}
