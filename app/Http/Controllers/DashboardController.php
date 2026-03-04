<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $upcomingRegistrations = $user->registrations()
            ->with('event:id,title,slug,start_date,venue_name,venue_city')
            ->whereHas('event', fn ($q) => $q->where('start_date', '>=', now()))
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $organizerStats = null;
        if ($user->isOrganizer() || $user->isAdmin()) {
            $organizerStats = [
                'eventsCount' => $user->events()->count(),
                'registrationsCount' => $user->events()->withCount('registrations')->get()->sum('registrations_count'),
            ];
        }

        return Inertia::render('Dashboard', [
            'upcomingRegistrations' => $upcomingRegistrations,
            'organizerStats' => $organizerStats,
            'canCreate' => $user->isOrganizer() || $user->isAdmin(),
        ]);
    }
}
