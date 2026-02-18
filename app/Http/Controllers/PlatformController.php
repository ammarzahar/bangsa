<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function groups(Request $request)
    {
        abort_unless($request->user()->is_platform_owner, 403);

        $groups = Group::query()->with(['owner', 'subscription.plan'])->orderByDesc('created_at')->paginate(20);

        return view('dashboard.platform-groups', ['groups' => $groups]);
    }

    public function suspend(Request $request, string $groupId): RedirectResponse
    {
        abort_unless($request->user()->is_platform_owner, 403);

        $group = Group::query()->findOrFail($groupId);
        $group->update(['status' => Group::STATUS_SUSPENDED]);

        AuditLog::query()->create([
            'target_group_id' => $group->id,
            'actor_user_id' => $request->user()->id,
            'event_type' => 'GROUP_SUSPENDED',
            'metadata' => ['reason' => $request->input('reason')],
        ]);

        return back()->with('status', 'Group suspended.');
    }

    public function activate(Request $request, string $groupId): RedirectResponse
    {
        abort_unless($request->user()->is_platform_owner, 403);

        $group = Group::query()->findOrFail($groupId);
        $group->update(['status' => Group::STATUS_ACTIVE]);

        AuditLog::query()->create([
            'target_group_id' => $group->id,
            'actor_user_id' => $request->user()->id,
            'event_type' => 'GROUP_ACTIVATED',
        ]);

        return back()->with('status', 'Group activated.');
    }
}