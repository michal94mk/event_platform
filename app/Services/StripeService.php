<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeService
{
    public function __construct(
        protected StripeClient $stripe
    ) {}

    public static function make(): self
    {
        $secret = config('services.stripe.secret');

        if (empty($secret)) {
            throw new \RuntimeException('Stripe secret key is not configured.');
        }

        return new self(new StripeClient($secret));
    }

    /**
     * Create a Checkout Session for paid event registration.
     */
    public function createCheckoutSession(Registration $registration, string $successUrl, string $cancelUrl): Session
    {
        $event = $registration->event;
        $currency = strtolower($event->currency ?? config('services.stripe.currency', 'pln'));
        $unitAmount = (int) round((float) $event->ticket_price * 100);

        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $event->title,
                            'description' => $registration->ticket_quantity.' '.($registration->ticket_quantity === 1 ? 'bilet' : 'biletów'),
                        ],
                        'unit_amount' => $unitAmount,
                    ],
                    'quantity' => $registration->ticket_quantity,
                ],
            ],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'registration_id' => (string) $registration->id,
            ],
        ]);

        return $session;
    }

    /**
     * Refund a registration payment.
     */
    public function refund(Registration $registration): bool
    {
        if (empty($registration->payment_intent_id)) {
            return false;
        }

        try {
            $this->stripe->refunds->create([
                'payment_intent' => $registration->payment_intent_id,
            ]);

            return true;
        } catch (ApiErrorException) {
            return false;
        }
    }

    /**
     * Retrieve Payment Intent ID from a completed Checkout Session.
     */
    public function getPaymentIntentFromSession(string $sessionId): ?string
    {
        try {
            $session = $this->stripe->checkout->sessions->retrieve($sessionId);

            return $session->payment_intent;
        } catch (ApiErrorException) {
            return null;
        }
    }

    public function isConfigured(): bool
    {
        return ! empty(config('services.stripe.secret'));
    }
}
