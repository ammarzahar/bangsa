<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\MembershipRequest;
use App\Models\Subscription;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function home(Request $request)
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('dashboard.platform');
        }

        $membership = GroupMembership::query()
            ->with('group')
            ->where('user_id', $request->user()->id)
            ->orderByRaw("CASE status WHEN 'APPROVED' THEN 0 WHEN 'PENDING' THEN 1 ELSE 2 END")
            ->orderByRaw("CASE role WHEN 'OWNER' THEN 0 WHEN 'ADMIN' THEN 1 WHEN 'MEMBER' THEN 2 ELSE 3 END")
            ->orderBy('created_at')
            ->first();

        if ($membership && $membership->group) {
            return redirect()->route('dashboard.group', [$membership->group->slug]);
        }

        $groups = Group::query()
            ->where('status', Group::STATUS_ACTIVE)
            ->whereIn('visibility', [Group::VISIBILITY_PUBLIC, Group::VISIBILITY_PAID])
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        return view('dashboard.home', [
            'groups' => $groups,
        ]);
    }

    public function platform(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $activeSubscriptions = Subscription::query()
            ->with('plan')
            ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIALING])
            ->where(function ($q): void {
                $q->whereNull('current_period_end')
                    ->orWhere('current_period_end', '>=', now())
                    ->orWhere('grace_period_ends_at', '>=', now());
            })
            ->get();

        $mrrCents = $activeSubscriptions
            ->filter(fn ($subscription) => $subscription->group_id !== null)
            ->sum(fn ($subscription) => $subscription->plan->price_cents);

        $stats = [
            'total_groups' => Group::query()->count(),
            'total_members' => GroupMembership::query()->where('status', GroupMembership::STATUS_APPROVED)->count(),
            'active_subscriptions' => $activeSubscriptions->count(),
            'mrr_cents' => $mrrCents,
        ];

        $groups = Group::query()->with(['owner', 'subscription.plan'])->orderByDesc('created_at')->get();

        return view('dashboard.platform', [
            'stats' => $stats,
            'groups' => $groups,
        ]);
    }

    public function group(Request $request, string $group_slug)
    {
        $group = $request->attributes->get('current_group');
        $isManager = $request->user()->isAdmin() || GroupMembership::query()
            ->where('group_id', $group->id)
            ->where('user_id', $request->user()->id)
            ->where('status', GroupMembership::STATUS_APPROVED)
            ->whereIn('role', [GroupMembership::ROLE_OWNER, GroupMembership::ROLE_ADMIN])
            ->exists();

        $viewerMembership = GroupMembership::query()
            ->where('group_id', $group->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $isManager && ! $viewerMembership) {
            abort(403);
        }

        $analytics = [
            'approved_members' => GroupMembership::query()->where('group_id', $group->id)->where('status', GroupMembership::STATUS_APPROVED)->count(),
            'pending_members' => GroupMembership::query()->where('group_id', $group->id)->where('status', GroupMembership::STATUS_PENDING)->count(),
            'pending_requests' => MembershipRequest::query()->where('group_id', $group->id)->where('status', MembershipRequest::STATUS_PENDING)->count(),
            'featured_profiles' => $group->profiles()->where('is_featured', true)->count(),
        ];

        return view('dashboard.group', [
            'group' => $group,
            'analytics' => $analytics,
            'subscription' => $group->subscription()->with('plan')->first(),
            'isManager' => $isManager,
            'viewerMembership' => $viewerMembership,
        ]);
    }
}
