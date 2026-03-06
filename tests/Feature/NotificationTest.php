<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Index
    // -----------------------------------------------------------------------

    public function test_guests_are_redirected_from_notifications_index(): void
    {
        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_sees_only_their_own_notifications(): void
    {
        $user = User::factory()->create();
        $this->createNotification($user, 'Powiadomienie 1');
        $this->createNotification($user, 'Powiadomienie 2');
        $this->createNotification(User::factory()->create(), 'Cudze powiadomienie');

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('notifications/Index')
                ->where('notifications.total', 2)
            );
    }

    public function test_notifications_are_paginated_with_15_per_page(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 20; $i++) {
            $this->createNotification($user, "Powiadomienie {$i}");
        }

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('notifications.per_page', 15)
                ->where('notifications.total', 20)
            );
    }

    // -----------------------------------------------------------------------
    // Mark as read
    // -----------------------------------------------------------------------

    public function test_user_can_mark_their_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = $this->createNotification($user, 'Do przeczytania');

        $this->assertNull($notification->read_at);

        $this->actingAs($user)
            ->patch(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $notification = $this->createNotification($other, 'Cudze powiadomienie');

        $this->actingAs($user)
            ->patch(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_marking_already_read_notification_does_not_fail(): void
    {
        $user = User::factory()->create();
        $notification = $this->createNotification($user, 'Przeczytane', ['read_at' => now()->subHour()]);

        $this->actingAs($user)
            ->patch(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function createNotification(User $user, string $title, array $overrides = []): UserNotification
    {
        return UserNotification::create(array_merge([
            'user_id' => $user->id,
            'type'    => 'registration_created',
            'title'   => $title,
            'message' => 'Treść powiadomienia.',
            'data'    => [],
        ], $overrides));
    }
}
