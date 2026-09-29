<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\MemberProfile;
use App\Models\MembershipRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TautWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = (string) config('services.taut.webhook_secret');
        $signature = (string) $request->header('X-TAUT-Signature');

        abort_if($secret === '' || $signature === '', 401, 'Webhook signature is required.');
        abort_unless(hash_equals(hash_hmac('sha256', $request->getContent(), $secret), $signature), 401, 'Invalid webhook signature.');

        $validated = $request->validate([
            'event' => ['required', 'in:order.paid'],
            'order_id' => ['required', 'string', 'max:100'],
            'group_id' => ['required', 'uuid'],
            'user_id' => ['required', 'uuid'],
            'email' => ['required', 'email'],
            'payment_status' => ['required', 'in:paid'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $group = Group::query()
            ->whereKey($validated['group_id'])
            ->where('visibility', Group::VISIBILITY_PAID)
            ->firstOrFail();
        $user = User::query()->whereKey($validated['user_id'])->firstOrFail();

        abort_unless(hash_equals(strtolower($user->email), strtolower($validated['email'])), 422, 'Customer identity does not match.');

        DB::transaction(function () use ($validated, $group, $user): void {
            $membershipRequest = MembershipRequest::query()
                ->where('group_id', $group->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($membershipRequest->payment_status === MembershipRequest::PAYMENT_PAID) {
                abort_unless($membershipRequest->external_order_id === $validated['order_id'], 409, 'Membership already has a different paid order.');

                return;
            }

            $membershipRequest->update([
                'status' => MembershipRequest::STATUS_APPROVED,
                'payment_status' => MembershipRequest::PAYMENT_PAID,
                'external_order_id' => $validated['order_id'],
                'paid_at' => $validated['paid_at'] ?? now(),
                'reviewed_at' => now(),
            ]);

            GroupMembership::query()->updateOrCreate(
                ['group_id' => $group->id, 'user_id' => $user->id],
                [
                    'status' => GroupMembership::STATUS_APPROVED,
                    'role' => GroupMembership::ROLE_MEMBER,
                    'approved_at' => now(),
                ]
            );

            MemberProfile::query()->firstOrCreate(
                ['group_id' => $group->id, 'user_id' => $user->id],
                [
                    'username' => $this->uniqueUsername($group->id, $user->full_name ?: Str::before($user->email, '@')),
                    'full_name' => $user->full_name ?: $user->email,
                ]
            );

            AuditLog::query()->create([
                'target_group_id' => $group->id,
                'actor_user_id' => $user->id,
                'event_type' => 'PAID_MEMBERSHIP_ACTIVATED',
                'metadata' => ['taut_order_id' => $validated['order_id']],
            ]);
        });

        return response()->json(['received' => true]);
    }

    private function uniqueUsername(string $groupId, string $seed): string
    {
        $base = Str::of($seed)->lower()->slug('')->value() ?: 'member';
        $candidate = $base;
        $suffix = 1;

        while (MemberProfile::query()->where('group_id', $groupId)->where('username', $candidate)->exists()) {
            $candidate = $base.$suffix++;
        }

        return $candidate;
    }
}
