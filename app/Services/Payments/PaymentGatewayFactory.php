<?php

namespace App\Services\Payments;

use InvalidArgumentException;

class PaymentGatewayFactory
{
    public function make(string $provider): PaymentGatewayInterface
    {
        return match (strtoupper($provider)) {
            'STRIPE' => new StripeGatewayStub(),
            'TOYYIBPAY' => new ToyyibpayGatewayStub(),
            'MANUAL' => new ManualGatewayStub(),
            default => throw new InvalidArgumentException('Unsupported provider.'),
        };
    }
}