<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Integration;
use App\Services\GoogleCalendarService;
use App\Services\SendGridService;
use App\Services\StripeService;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IntegrationController extends Controller
{
    public function index(Request $request)
    {
        $integrations = Integration::where('user_id', $request->user()->id)
            ->orderBy('provider')
            ->get();

        return Inertia::render('integrations/Index', [
            'integrations' => $integrations,
            'stripeConfigured' => StripeService::make()->isConfigured(),
            'googleCalendarConfigured' => GoogleCalendarService::make()->isConfigured(),
            'sendGridConfigured' => SendGridService::make()->isConfigured(),
            'twilioConfigured' => TwilioService::make()->isConfigured(),
        ]);
    }

    public function googleCalendarConnect()
    {
        $url = GoogleCalendarService::make()->getAuthUrl();

        return redirect($url);
    }

    public function googleCalendarCallback(Request $request)
    {
        $code = $request->query('code');

        if (! $code) {
            return redirect()
                ->route('integrations.index')
                ->withErrors(['google' => 'Brak kodu autoryzacji.']);
        }

        try {
            $token = GoogleCalendarService::make()->handleCallback($code);
            GoogleCalendarService::make()->createIntegrationForUser($request->user()->id, $token);

            return redirect()
                ->route('integrations.index')
                ->with('success', 'Google Calendar został połączony.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('integrations.index')
                ->withErrors(['google' => $e->getMessage()]);
        }
    }

    public function disconnect(Request $request, Integration $integration)
    {
        if ($integration->user_id !== $request->user()->id) {
            abort(403);
        }

        $integration->delete();

        return back()->with('success', 'Integracja została rozłączona.');
    }

    public function test(Request $request, Integration $integration)
    {
        if ($integration->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($integration->isGoogleCalendar()) {
            try {
                $calendar = GoogleCalendarService::make()->getCalendarService($integration);

                return back()->with('success', 'Połączenie z Google Calendar działa poprawnie.');
            } catch (\Throwable $e) {
                return back()->withErrors(['test' => $e->getMessage()]);
            }
        }

        return back()->withErrors(['test' => 'Nieobsługiwany typ integracji.']);
    }

    public function syncCalendar(Request $request, Event $event)
    {
        if ($event->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $integration = Integration::where('user_id', $request->user()->id)
            ->where('provider', Integration::PROVIDER_GOOGLE_CALENDAR)
            ->where('is_active', true)
            ->first();

        if (! $integration) {
            return back()->withErrors(['sync' => 'Połącz najpierw konto Google Calendar w ustawieniach integracji.']);
        }

        try {
            $eventId = GoogleCalendarService::make()->syncEvent($integration, $event);
            $event->update(['google_calendar_event_id' => $eventId]);

            return back()->with('success', 'Wydarzenie zostało zsynchronizowane z Google Calendar.');
        } catch (\Throwable $e) {
            return back()->withErrors(['sync' => $e->getMessage()]);
        }
    }
}
