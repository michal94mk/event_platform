<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
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
