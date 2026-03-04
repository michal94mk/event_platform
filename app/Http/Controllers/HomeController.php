<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featuredEvents = Event::query()
            ->where('status', 'published')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(6)
            ->with('user:id,name', 'categories')
            ->get();

        /** @var User|null $user */
        $user = Auth::user();

        return Inertia::render('Welcome', [
            'featuredEvents' => $featuredEvents,
            'canCreate' => $user && ($user->isOrganizer() || $user->isAdmin()),
        ]);
    }
}
