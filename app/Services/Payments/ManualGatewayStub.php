<?php

namespace App\Services\Payments;

use App\Models\Plan;
use App\Models\User;

class ManualGatewayStub implements PaymentGatewayInterface
{
    public function provider(): string
    {
        return 'MANUAL';
    }

    public function createSubscriptionSession(User $user, Plan $plan): array
    {
        return [
            'provider' => $this->provider(),
            'status' => 'stubbed',
            'checkout_url' => null,
            'message' => 'Manual payment flow stub. Mark payment after offline confirmation.',
            'plan_code' => $plan->code,
            'user_id' => $user->id,
        ];
    }
}