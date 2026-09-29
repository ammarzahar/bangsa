<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\MembershipRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaidCommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_community_join_redirects_to_signed_taut_checkout(): void
    {
        config(['services.taut.webhook_secret' => 'test-secret']);
        [$group, $member] = $this->paidCommunity();

        $response = $this->actingAs($member)->post(route('groups.join', [$group->slug]));

        $response->assertRedirectContains('https://demo.taut.my/checkout/123?');
        $location = (string) $response->headers->get('Location');
        $this->assertStringContainsString('bangsa_context=', $location);
        $this->assertStringContainsString('bangsa_signature=', $location);
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $context = strtr((string) $query['bangsa_context'], '-_', '+/');
        $context .= str_repeat('=', (4 - strlen($context) % 4) % 4);
        $payload = json_decode((string) base64_decode($context, true), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(123, $payload['taut_product_id']);
        $this->assertDatabaseHas('membership_requests', [
            'group_id' => $group->id,
            'user_id' => $member->id,
            'status' => MembershipRequest::STATUS_PENDING,
            'payment_status' => MembershipRequest::PAYMENT_AWAITING,
        ]);
    }

    public function test_valid_taut_paid_webhook_activates_membership_idempotently(): void
    {
        config(['services.taut.webhook_secret' => 'test-secret']);
        [$group, $member] = $this->paidCommunity();
        $this->actingAs($member)->post(route('groups.join', [$group->slug]));

        $payload = [
            'event' => 'order.paid',
            'order_id' => '4321',
            'group_id' => $group->id,
            'user_id' => $member->id,
            'email' => $member->email,
            'payment_status' => 'paid',
            'paid_at' => now()->toIso8601String(),
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $body, 'test-secret');

        $this->call('POST', route('integrations.taut.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TAUT_SIGNATURE' => $signature,
        ], $body)->assertOk()->assertJson(['received' => true]);

        $this->call('POST', route('integrations.taut.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TAUT_SIGNATURE' => $signature,
        ], $body)->assertOk();

        $this->assertDatabaseHas('membership_requests', [
            'group_id' => $group->id,
            'user_id' => $member->id,
            'status' => MembershipRequest::STATUS_APPROVED,
            'payment_status' => MembershipRequest::PAYMENT_PAID,
            'external_order_id' => '4321',
        ]);
        $this->assertDatabaseHas('group_memberships', [
            'group_id' => $group->id,
            'user_id' => $member->id,
            'status' => GroupMembership::STATUS_APPROVED,
        ]);
        $this->assertDatabaseHas('member_profiles', [
            'group_id' => $group->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_taut_webhook_rejects_invalid_signature(): void
    {
        config(['services.taut.webhook_secret' => 'test-secret']);

        $this->postJson(route('integrations.taut.webhook'), [
            'event' => 'order.paid',
        ], ['X-TAUT-Signature' => 'invalid'])->assertUnauthorized();
    }

    private function paidCommunity(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $group = Group::query()->create([
            'slug' => 'paid-network',
            'name' => 'Paid Network',
            'visibility' => Group::VISIBILITY_PAID,
            'taut_checkout_url' => 'https://demo.taut.my/checkout/123',
            'status' => Group::STATUS_ACTIVE,
            'owner_id' => $owner->id,
        ]);

        return [$group, $member];
    }
}
