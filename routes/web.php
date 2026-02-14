<?php

use App\Models\Event;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Wydarzenia (publiczne)
Route::get('events', function () {
    return Inertia::render('events/Index', [
        'events' => Event::with('user:id,name', 'categories')
            ->where('status', 'published')
            ->orderBy('start_date')
            ->get(),
    ]);
})->name('events.index');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
