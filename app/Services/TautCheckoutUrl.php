<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;
use RuntimeException;

class TautCheckoutUrl
{
    public function for(Group $group, User $user): string
    {
        $secret = (string) config('services.taut.webhook_secret');

        if ($secret === '' || ! $group->taut_checkout_url) {
            throw new RuntimeException('Paid community checkout is not configured.');
        }

        $path = (string) parse_url($group->taut_checkout_url, PHP_URL_PATH);
        if (preg_match('~/checkout/(\d+)/?$~', $path, $matches) !== 1) {
            throw new RuntimeException('Paid community checkout product is invalid.');
        }

        $context = $this->base64UrlEncode(json_encode([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'taut_product_id' => (int) $matches[1],
            'expires_at' => now()->addMinutes(30)->timestamp,
        ], JSON_THROW_ON_ERROR));

        $signature = hash_hmac('sha256', $context, $secret);
        $separator = str_contains($group->taut_checkout_url, '?') ? '&' : '?';

        return $group->taut_checkout_url.$separator.http_build_query([
            'bangsa_context' => $context,
            'bangsa_signature' => $signature,
        ]);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
