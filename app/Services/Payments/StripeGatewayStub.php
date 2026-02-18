<?php

namespace App\Services\Payments;

use App\Models\Plan;
use App\Models\User;

class StripeGatewayStub implements PaymentGatewayInterface
{
    public function provider(): string
    {
        return 'STRIPE';
    }

    public function createSubscriptionSession(User $user, Plan $plan): array
    {
        return [
            'provider' => $this->provider(),
            'status' => 'stubbed',
            'checkout_url' => null,
            'message' => 'Stripe integration stub. Replace with Stripe Checkout session creation.',
            'plan_code' => $plan->code,
            'user_id' => $user->id,
        ];
    }
}