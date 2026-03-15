<?php

namespace App\Http\Controllers;

use App\Mail\OrganizerRegistrationNotification;
use App\Mail\RegistrationConfirmation;
use App\Models\Registration;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (empty($webhookSecret)) {
            Log::warning('Stripe webhook secret not configured');

            return response('Webhook secret not configured', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload', ['error' => $e->getMessage()]);

            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutCompleted($event->data->object);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $this->handlePaymentIntentSucceeded($event->data->object);
        }

        return response('OK', 200);
    }

    protected function handleCheckoutCompleted(object $session): void
    {
        $registrationId = $session->metadata->registration_id ?? null;

        if (! $registrationId) {
            return;
        }

        $registration = Registration::find($registrationId);

        if (! $registration || $registration->payment_status === 'paid') {
            return;
        }

        $paymentIntentId = is_object($session->payment_intent)
            ? ($session->payment_intent->id ?? null)
            : $session->payment_intent;

        $registration->update([
            'payment_status' => 'paid',
            'payment_intent_id' => $paymentIntentId,
        ]);

        $this->sendConfirmationEmails($registration);
        $this->notifyOrganizer($registration);
    }

    protected function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        $registration = Registration::where('payment_intent_id', $paymentIntent->id)->first();

        if (! $registration || $registration->payment_status === 'paid') {
            return;
        }

        $registration->update(['payment_status' => 'paid']);
        $this->sendConfirmationEmails($registration);
        $this->notifyOrganizer($registration);
    }

    protected function sendConfirmationEmails(Registration $registration): void
    {
        $registration->load('event.user');

        $participantMail = new RegistrationConfirmation($registration);

        if (config('queue.default') === 'sync') {
            Mail::to($registration->email)->send($participantMail);
        } else {
            Mail::to($registration->email)->queue($participantMail);
        }
    }

    protected function notifyOrganizer(Registration $registration): void
    {
        if (! $registration->event->user_id) {
            return;
        }

        $organizerMail = new OrganizerRegistrationNotification($registration);

        if (config('queue.default') === 'sync') {
            Mail::to($registration->event->user->email)->send($organizerMail);
        } else {
            Mail::to($registration->event->user->email)->queue($organizerMail);
        }

        UserNotification::create([
            'user_id' => $registration->event->user_id,
            'type' => 'registration_created',
            'title' => 'Nowa rejestracja (opłacona)',
            'message' => $registration->first_name.' '.$registration->last_name.' zapisał się na wydarzenie „'.$registration->event->title.'" ('.$registration->ticket_quantity.' '.($registration->ticket_quantity === 1 ? 'bilet' : 'biletów').').',
            'data' => [
                'event_id' => $registration->event->id,
                'event_slug' => $registration->event->slug,
                'registration_id' => $registration->id,
            ],
        ]);
    }
}
