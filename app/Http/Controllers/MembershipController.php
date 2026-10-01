<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\MemberProfile;
use App\Models\MembershipRequest;
use App\Services\TautCheckoutUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MembershipController extends Controller
{
    public function join(Request $request, string $group_slug, TautCheckoutUrl $checkoutUrl): RedirectResponse
    {
        $group = $request->attributes->get('current_group');

        $existingMembership = GroupMembership::query()
            ->where('group_id', $group->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingMembership?->status === GroupMembership::STATUS_APPROVED) {
            return back()->with('status', 'You are already an approved member of this group.');
        }

        if ($group->visibility === Group::VISIBILITY_PAID) {
            abort_unless($this->canJoin($request, $group), 403);

            DB::transaction(function () use ($request, $group): void {
                GroupMembership::query()->updateOrCreate(
                    ['group_id' => $group->id, 'user_id' => $request->user()->id],
                    [
                        'role' => GroupMembership::ROLE_MEMBER,
                        'status' => GroupMembership::STATUS_PENDING,
                        'approved_by_user_id' => null,
                        'approved_at' => null,
                    ]
                );

                MembershipRequest::query()->updateOrCreate(
                    ['group_id' => $group->id, 'user_id' => $request->user()->id],
                    [
                        'status' => MembershipRequest::STATUS_PENDING,
                        'payment_status' => MembershipRequest::PAYMENT_AWAITING,
                        'external_order_id' => null,
                        'paid_at' => null,
                        'reviewed_by_user_id' => null,
                        'reviewed_at' => null,
                    ]
                );
            });

            return redirect()->away($checkoutUrl->for($group, $request->user()));
        }

        if ($existingMembership?->status === GroupMembership::STATUS_PENDING) {
            return back()->with('status', 'Your join request is already pending approval.');
        }

        abort_unless($this->canJoin($request, $group), 403);

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $group, $validated): void {
            GroupMembership::query()->updateOrCreate(
                [
                    'group_id' => $group->id,
                    'user_id' => $request->user()->id,
                ],
                [
                    'role' => GroupMembership::ROLE_MEMBER,
                    'status' => GroupMembership::STATUS_PENDING,
                    'approved_by_user_id' => null,
                    'approved_at' => null,
                ]
            );

            MembershipRequest::query()->updateOrCreate(
                [
                    'group_id' => $group->id,
                    'user_id' => $request->user()->id,
                ],
                [
                    'status' => MembershipRequest::STATUS_PENDING,
                    'note' => $validated['note'] ?? null,
                    'reviewed_by_user_id' => null,
                    'reviewed_at' => null,
                ]
            );

            AuditLog::query()->create([
                'target_group_id' => $group->id,
                'actor_user_id' => $request->user()->id,
                'event_type' => 'MEMBERSHIP_REQUEST_SUBMITTED',
            ]);
        });

        return back()->with('status', 'Membership request submitted.');
    }

    private function canJoin(Request $request, Group $group): bool
    {
        if ($group->status !== Group::STATUS_ACTIVE) {
            return false;
        }

        if ($request->user()->isAdmin()) {
            return true;
        }

        if (in_array($group->visibility, [Group::VISIBILITY_PUBLIC, Group::VISIBILITY_PAID], true)) {
            return true;
        }

        return hash_equals(
            (string) $group->invite_token,
            (string) $request->session()->get("group_invites.{$group->id}")
        );
    }

    public function requests(Request $request, string $group_slug)
    {
        $group = $request->attributes->get('current_group');
        $this->authorize('moderate', [GroupMembership::class, $group]);

        $requests = MembershipRequest::query()
            ->with('user')
            ->where('group_id', $group->id)
            ->where('status', MembershipRequest::STATUS_PENDING)
            ->orderBy('created_at')
            ->get();

        return view('members.requests', [
            'group' => $group,
            'requests' => $requests,
        ]);
    }

    public function approve(Request $request, string $group_slug, string $requestId): RedirectResponse
    {
        $group = $request->attributes->get('current_group');
        $membershipRequest = MembershipRequest::query()->where('id', $requestId)->firstOrFail();

        $this->authorize('review', [GroupMembership::class, $group, $membershipRequest]);

        if ($membershipRequest->status !== MembershipRequest::STATUS_PENDING) {
            return back()->withErrors(['request' => 'Membership request is already processed.']);
        }

        if (
            $group->visibility === Group::VISIBILITY_PAID
            && $membershipRequest->payment_status !== MembershipRequest::PAYMENT_PAID
        ) {
            return back()->withErrors(['request' => 'Payment must be confirmed by TAUT before approval.']);
        }

        DB::transaction(function () use ($request, $group, $membershipRequest): void {
            $membershipRequest->update([
                'status' => MembershipRequest::STATUS_APPROVED,
                'reviewed_by_user_id' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            GroupMembership::query()->updateOrCreate(
                [
                    'group_id' => $group->id,
                    'user_id' => $membershipRequest->user_id,
                ],
                [
                    'status' => GroupMembership::STATUS_APPROVED,
                    'role' => GroupMembership::ROLE_MEMBER,
                    'approved_by_user_id' => $request->user()->id,
                    'approved_at' => now(),
                ]
            );

            MemberProfile::query()->firstOrCreate(
                [
                    'group_id' => $group->id,
                    'user_id' => $membershipRequest->user_id,
                ],
                [
                    'username' => $this->uniqueUsername(
                        $group->id,
                        $membershipRequest->user->full_name ?: Str::before($membershipRequest->user->email, '@')
                    ),
                    'photo_url' => $membershipRequest->user->avatar_url,
                    'full_name' => $membershipRequest->user->full_name ?: $membershipRequest->user->email,
                ]
            );

            AuditLog::query()->create([
                'target_group_id' => $group->id,
                'actor_user_id' => $request->user()->id,
                'event_type' => 'MEMBERSHIP_REQUEST_APPROVED',
                'metadata' => ['request_id' => $membershipRequest->id],
            ]);
        });

        return back()->with('status', 'Membership approved.');
    }

    public function reject(Request $request, string $group_slug, string $requestId): RedirectResponse
    {
        $group = $request->attributes->get('current_group');
        $membershipRequest = MembershipRequest::query()->where('id', $requestId)->firstOrFail();

        $this->authorize('review', [GroupMembership::class, $group, $membershipRequest]);

        if ($membershipRequest->status !== MembershipRequest::STATUS_PENDING) {
            return back()->withErrors(['request' => 'Membership request is already processed.']);
        }

        DB::transaction(function () use ($request, $group, $membershipRequest): void {
            $membershipRequest->update([
                'status' => MembershipRequest::STATUS_REJECTED,
                'reviewed_by_user_id' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            GroupMembership::query()->updateOrCreate(
                [
                    'group_id' => $group->id,
                    'user_id' => $membershipRequest->user_id,
                ],
                [
                    'status' => GroupMembership::STATUS_REJECTED,
                    'role' => GroupMembership::ROLE_MEMBER,
                    'approved_by_user_id' => null,
                    'approved_at' => null,
                ]
            );

            AuditLog::query()->create([
                'target_group_id' => $group->id,
                'actor_user_id' => $request->user()->id,
                'event_type' => 'MEMBERSHIP_REQUEST_REJECTED',
                'metadata' => ['request_id' => $membershipRequest->id],
            ]);
        });

        return back()->with('status', 'Membership rejected.');
    }

    private function uniqueUsername(string $groupId, string $seed): string
    {
        $base = Str::of($seed)->lower()->slug('')->value();
        $base = $base !== '' ? $base : 'member';
        $candidate = $base;
        $suffix = 1;

        while (
            MemberProfile::query()
                ->where('group_id', $groupId)
                ->where('username', $candidate)
                ->exists()
        ) {
            $candidate = $base.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
