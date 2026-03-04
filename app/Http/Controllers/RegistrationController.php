<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Mail\RegistrationConfirmation;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Registration::class);

        $registrations = Registration::with('event:id,title,slug,start_date,venue_name')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('registrations/MyRegistrations', [
            'registrations' => $registrations,
        ]);
    }

    public function store(StoreRegistrationRequest $request, Event $event)
    {
        $user = $request->user();
        $ticketQuantity = (int) $request->ticket_quantity;
        $price = $event->ticket_price ? (float) $event->ticket_price : 0;
        $totalAmount = $price * $ticketQuantity;
        $isFree = $totalAmount <= 0;

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $user?->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'ticket_quantity' => $ticketQuantity,
            'total_amount' => $totalAmount,
            'payment_status' => $isFree ? 'paid' : 'pending',
            'qr_code' => Str::random(32),
        ]);

        $url = route('registrations.show', ['registration' => $registration->id, 'token' => $registration->qr_code]);

        $registration->load('event');
        Mail::to($registration->email)->send(new RegistrationConfirmation($registration));

        return redirect()->to($url)->with('success', 'Rejestracja zakończona. Potwierdzenie wysłano na ' . $registration->email);
    }

    public function show(Request $request, Registration $registration)
    {
        $token = $request->query('token');
        $guestAccess = $token && $registration->qr_code && hash_equals($registration->qr_code, $token);

        if (! $guestAccess && $request->user()) {
            $this->authorize('view', $registration);
        } elseif (! $guestAccess) {
            abort(403);
        }

        $registration->load('event:id,title,slug,start_date,end_date,venue_name,venue_city');

        return Inertia::render('registrations/Show', [
            'registration' => $registration,
        ]);
    }

    public function checkInPage(Request $request, Event $event)
    {
        if ($event->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $registrations = $event->registrations()->orderBy('first_name')->orderBy('last_name')->get();

        return Inertia::render('events/CheckIn', [
            'event' => $event->only('id', 'title', 'slug'),
            'registrations' => $registrations,
        ]);
    }

    public function checkIn(Request $request, Event $event)
    {
        if ($event->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $request->validate(['qr_code' => 'required', 'string']);

        $registration = Registration::where('event_id', $event->id)
            ->where('qr_code', $request->qr_code)
            ->first();

        if (! $registration) {
            return back()->withErrors(['qr_code' => 'Nie znaleziono rejestracji o podanym kodzie.']);
        }

        if ($registration->checked_in) {
            return back()->with('info', 'Ta osoba jest już odhaczona.');
        }

        $registration->update([
            'checked_in' => true,
            'checked_in_at' => now(),
        ]);

        return back()->with('success', 'Check-in wykonany: '.$registration->first_name.' '.$registration->last_name);
    }
}
