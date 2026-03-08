<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\CalendarSubscriptionToken;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function edit(Request $request): Response
    {
        $token = CalendarSubscriptionToken::generateFor($request->user());
        $subscriptionUrl = route('calendar.subscribe', ['token' => $token->token], true);

        return Inertia::render('settings/Calendar', [
            'subscriptionUrl' => $subscriptionUrl,
        ]);
    }
}
