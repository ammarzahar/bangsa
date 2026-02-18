<?php

namespace App\Services\Payments;

use App\Models\Plan;
use App\Models\User;

interface PaymentGatewayInterface
{
    public function provider(): string;

    public function createSubscriptionSession(User $user, Plan $plan): array;
}