<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;

class RegistrationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** User can view own registration; organizer can view registrations for their events. */
    public function view(User $user, Registration $registration): bool
    {
        if ($registration->user_id === $user->id) {
            return true;
        }

        return $registration->event->user_id === $user->id || $user->isAdmin();
    }

    /** Any authenticated user can register for events (guest flow handled in controller). */
    public function create(User $user): bool
    {
        return true;
    }

    /** User can cancel (delete) only own upcoming registration that is not checked in. */
    public function delete(User $user, Registration $registration): bool
    {
        if ($registration->user_id !== $user->id) {
            return false;
        }

        if ($registration->checked_in) {
            return false;
        }

        return $registration->event
            && $registration->event->start_date
            && $registration->event->start_date->isFuture();
    }
}
