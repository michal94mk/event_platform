<?php

namespace App\Http\Controllers;

use App\Models\CalendarSubscriptionToken;
use App\Models\Event;
use App\Models\Registration;
use App\Services\CalendarService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CalendarController extends Controller
{
    public function __construct(
        private CalendarService $calendarService
    ) {}

    public function event(Event $event): StreamedResponse
    {
        if (! $event->isPublished()) {
            abort(404);
        }

        $ics = $this->calendarService->generateIcs($event);
        $filename = $this->sanitizeFilename($event->title).'.ics';

        return response()->streamDownload(
            fn () => print ($ics),
            $filename,
            [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]
        );
    }

    public function registration(Request $request, Registration $registration): StreamedResponse
    {
        $token = $request->query('token');
        $guestAccess = $token && $registration->qr_code && hash_equals($registration->qr_code, $token);

        if (! $guestAccess && $request->user()) {
            $this->authorize('view', $registration);
        } elseif (! $guestAccess) {
            abort(403);
        }

        $registration->load('event');
        $ics = $this->calendarService->generateIcs($registration->event, $registration);
        $filename = $this->sanitizeFilename($registration->event->title).'.ics';

        return response()->streamDownload(
            fn () => print ($ics),
            $filename,
            [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]
        );
    }

    public function subscribe(Request $request): StreamedResponse
    {
        $token = $request->query('token');
        if (! $token) {
            abort(404);
        }

        $subscription = CalendarSubscriptionToken::where('token', $token)->first();
        if (! $subscription || $subscription->isExpired()) {
            abort(404);
        }

        $subscription->load('user');
        $ics = $this->calendarService->generateSubscriptionIcs($subscription->user);

        return response()->streamDownload(
            fn () => print ($ics),
            'event-platform-subscription.ics',
            [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'inline; filename="event-platform-subscription.ics"',
                'Cache-Control' => 'public, max-age=900',
            ]
        );
    }

    private function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $name);
        $name = preg_replace('/\s+/', '-', trim($name));

        return ($name ?: 'event').'.ics';
    }
}
