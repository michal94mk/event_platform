<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Mail\OrganizerRegistrationNotification;
use App\Mail\RegistrationConfirmation;
use App\Models\Event;
use App\Models\Registration;
use App\Models\UserNotification;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        if (! $isFree) {
            if (! StripeService::make()->isConfigured()) {
                return back()->withErrors(['payment' => 'Płatności online nie są obecnie dostępne. Skontaktuj się z organizatorem.']);
            }
            $successUrl = $url;
            $cancelUrl = route('events.show', $event->slug);

            $session = StripeService::make()->createCheckoutSession($registration, $successUrl, $cancelUrl);

            return redirect($session->url);
        }

        $registration->load('event.user');

        $participantMail = new RegistrationConfirmation($registration);
        $organizerMail = $registration->event->user && $registration->event->user->email
            ? new OrganizerRegistrationNotification($registration)
            : null;

        if (config('queue.default') === 'sync') {
            Mail::to($registration->email)->send($participantMail);

            if ($organizerMail) {
                Mail::to($registration->event->user->email)->send($organizerMail);
            }
        } else {
            Mail::to($registration->email)->queue($participantMail);

            if ($organizerMail) {
                Mail::to($registration->event->user->email)->queue($organizerMail);
            }
        }

        if ($registration->event->user_id) {
            UserNotification::create([
                'user_id' => $registration->event->user_id,
                'type' => 'registration_created',
                'title' => 'Nowa rejestracja',
                'message' => $registration->first_name.' '.$registration->last_name.' zapisał się na wydarzenie „'.$registration->event->title.'" ('.$registration->ticket_quantity.' '.($registration->ticket_quantity === 1 ? 'bilet' : 'biletów').').',
                'data' => [
                    'event_id' => $registration->event->id,
                    'event_slug' => $registration->event->slug,
                    'registration_id' => $registration->id,
                ],
            ]);
        }

        return redirect()->to($url)->with('success', 'Rejestracja zakończona. Potwierdzenie wysłano na '.$registration->email);
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

        $calendarUrl = $token
            ? route('registrations.calendar', ['registration' => $registration->id, 'token' => $token])
            : route('registrations.calendar', $registration->id);

        return Inertia::render('registrations/Show', [
            'registration' => $registration,
            'calendarUrl' => $calendarUrl,
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

        $request->validate([
            'qr_code' => ['required', 'string'],
        ]);

        $registration = Registration::where('event_id', $event->id)
            ->where('qr_code', trim($request->qr_code))
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

    public function exportCsv(Request $request, Event $event): StreamedResponse
    {
        if ($event->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $registrations = $event->registrations()->orderBy('first_name')->orderBy('last_name')->get();

        $filename = Str::slug($event->title).'-uczestnicy-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($registrations) {
            $handle = fopen('php://output', 'w');

            $bom = "\xEF\xBB\xBF";
            fwrite($handle, $bom);

            fputcsv($handle, [
                'Imię',
                'Nazwisko',
                'Email',
                'Telefon',
                'Liczba biletów',
                'Status',
                'Data check-in',
            ], ';');

            foreach ($registrations as $r) {
                fputcsv($handle, [
                    $r->first_name,
                    $r->last_name,
                    $r->email,
                    $r->phone ?? '',
                    $r->ticket_quantity,
                    $r->checked_in ? 'Odhaczono' : 'Oczekuje',
                    $r->checked_in_at ? $r->checked_in_at->format('Y-m-d H:i') : '',
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function destroy(Request $request, Registration $registration)
    {
        $this->authorize('delete', $registration);

        $registration->load('event');
        $organizerId = $registration->event->user_id;
        $eventTitle = $registration->event->title;
        $eventSlug = $registration->event->slug;
        $eventId = $registration->event->id;
        $participantName = $registration->first_name.' '.$registration->last_name;

        $registration->delete();

        if ($organizerId) {
            UserNotification::create([
                'user_id' => $organizerId,
                'type' => 'registration_cancelled',
                'title' => 'Anulowanie rejestracji',
                'message' => $participantName.' anulował rejestrację na wydarzenie „'.$eventTitle.'".',
                'data' => [
                    'event_id' => $eventId,
                    'event_slug' => $eventSlug,
                ],
            ]);
        }

        return redirect()
            ->route('registrations.index')
            ->with('success', 'Rejestracja została anulowana.');
    }

    public function refund(Request $request, Registration $registration)
    {
        if ($registration->event->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        if ($registration->payment_status !== 'paid' || empty($registration->payment_intent_id)) {
            return back()->withErrors(['refund' => 'Nie można zwrócić płatności dla tej rejestracji.']);
        }

        if (! StripeService::make()->isConfigured()) {
            return back()->withErrors(['refund' => 'Stripe nie jest skonfigurowany.']);
        }

        if (! StripeService::make()->refund($registration)) {
            return back()->withErrors(['refund' => 'Nie udało się zrealizować zwrotu.']);
        }

        $registration->update(['payment_status' => 'refunded']);

        return back()->with('success', 'Zwrot płatności został zrealizowany.');
    }
}
