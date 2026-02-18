<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Any authenticated user can view the list (filtering by status is in controller).
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Public can view published events; only owner/admin can view draft/cancelled.
     */
    public function view(?User $user, Event $event): bool
    {
        if ($event->status === 'published' || $event->status === 'completed') {
            return true;
        }

        return $user && ($event->user_id === $user->id || $user->isAdmin());
    }

    /**
     * Only organizer or admin can create events.
     */
    public function create(User $user): bool
    {
        return $user->isOrganizer() || $user->isAdmin();
    }

    /**
     * Only owner or admin can update.
     */
    public function update(User $user, Event $event): bool
    {
        return $event->user_id === $user->id || $user->isAdmin();
    }

    /**
     * Only owner or admin can delete.
     */
    public function delete(User $user, Event $event): bool
    {
        return $event->user_id === $user->id || $user->isAdmin();
    }
}
