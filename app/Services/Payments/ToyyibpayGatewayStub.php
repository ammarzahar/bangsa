<?php

namespace App\Services\Payments;

use App\Models\Plan;
use App\Models\User;

class ToyyibpayGatewayStub implements PaymentGatewayInterface
{
    public function provider(): string
    {
        return 'TOYYIBPAY';
    }

    public function createSubscriptionSession(User $user, Plan $plan): array
    {
        return [
            'provider' => $this->provider(),
            'status' => 'stubbed',
            'checkout_url' => null,
            'message' => 'Toyyibpay integration stub. Replace with bill creation + callback handling.',
            'plan_code' => $plan->code,
            'user_id' => $user->id,
        ];
    }
}