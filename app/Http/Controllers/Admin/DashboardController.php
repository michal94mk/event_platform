<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $stats = [
            'usersCount' => User::count(),
            'eventsCount' => Event::count(),
            'registrationsCount' => Registration::count(),
            'organizersCount' => User::where('role', 'organizer')->count(),
            'publishedEventsCount' => Event::where('status', 'published')->count(),
        ];

        $recentEvents = Event::with('user:id,name')
            ->orderByDesc('created_at')
            ->take(5)
            ->get(['id', 'title', 'slug', 'status', 'start_date', 'user_id', 'created_at']);

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
            'recentEvents' => $recentEvents,
        ]);
    }
}
