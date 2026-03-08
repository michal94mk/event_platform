<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('calendar/subscribe', [CalendarController::class, 'subscribe'])->name('calendar.subscribe');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('events', [EventController::class, 'index'])->name('events.index');
Route::get('events/calendar', [EventController::class, 'calendar'])->name('events.calendar.feed');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('events', [EventController::class, 'store'])->name('events.store');
});

Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('events/{event}/calendar.ics', [CalendarController::class, 'event'])->name('events.calendar');
Route::post('events/{event}/register', [RegistrationController::class, 'store'])->name('events.register');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('events/{event}/check-in', [RegistrationController::class, 'checkInPage'])->name('events.check-in.page');
    Route::post('events/{event}/check-in', [RegistrationController::class, 'checkIn'])->name('events.check-in');
    Route::get('registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::delete('registrations/{registration}', [RegistrationController::class, 'destroy'])->name('registrations.destroy');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

Route::get('registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
Route::get('registrations/{registration}/calendar.ics', [CalendarController::class, 'registration'])->name('registrations.calendar');

require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
