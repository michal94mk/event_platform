<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Models\Event;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $featuredEvents = Event::query()
        ->where('status', 'published')
        ->where('start_date', '>=', now())
        ->orderBy('start_date')
        ->take(6)
        ->with('user:id,name', 'categories')
        ->get();

    return Inertia::render('Welcome', [
        'featuredEvents' => $featuredEvents,
        'canCreate' => auth()->user() && (auth()->user()->isOrganizer() || auth()->user()->isAdmin()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    $user = auth()->user();

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
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('events', [EventController::class, 'index'])->name('events.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('events', [EventController::class, 'store'])->name('events.store');
});

Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
Route::post('events/{event}/register', [RegistrationController::class, 'store'])->name('events.register');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('events/{event}/check-in', [RegistrationController::class, 'checkInPage'])->name('events.check-in.page');
    Route::post('events/{event}/check-in', [RegistrationController::class, 'checkIn'])->name('events.check-in');
    Route::get('registrations', [RegistrationController::class, 'index'])->name('registrations.index');
});

Route::get('registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
