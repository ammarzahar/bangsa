<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PlatformController extends Controller
{
    public function groups(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $groups = Group::query()->with(['owner', 'subscription.plan'])->orderByDesc('created_at')->paginate(20);

        return view('dashboard.platform-groups', ['groups' => $groups]);
    }

    public function suspend(Request $request, string $groupId): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

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
        abort_unless($request->user()->isAdmin(), 403);

        $group = Group::query()->findOrFail($groupId);
        $group->update(['status' => Group::STATUS_ACTIVE]);

        AuditLog::query()->create([
            'target_group_id' => $group->id,
            'actor_user_id' => $request->user()->id,
            'event_type' => 'GROUP_ACTIVATED',
        ]);

        return back()->with('status', 'Group activated.');
    }

    public function users(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $users = User::query()
            ->withCount(['groupsOwned', 'memberships'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.platform-users', [
            'users' => $users,
            'accountTypes' => [
                User::TYPE_USER => 'User',
                User::TYPE_ORGANISER => 'Organiser',
                User::TYPE_ORGANISER_PLUS => 'Organiser Plus',
                User::TYPE_ADMIN => 'Admin',
            ],
        ]);
    }

    public function updateUser(Request $request, string $userId): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'account_type' => ['required', Rule::in([
                User::TYPE_USER,
                User::TYPE_ORGANISER,
                User::TYPE_ORGANISER_PLUS,
                User::TYPE_ADMIN,
            ])],
        ]);

        $user = User::query()->findOrFail($userId);
        $user->account_type = $validated['account_type'];
        $user->is_platform_owner = $validated['account_type'] === User::TYPE_ADMIN;
        $user->save();

        AuditLog::query()->create([
            'actor_user_id' => $request->user()->id,
            'event_type' => 'USER_ACCOUNT_TYPE_UPDATED',
            'metadata' => [
                'target_user_id' => $user->id,
                'account_type' => $user->account_type,
            ],
        ]);

        return back()->with('status', 'User account type updated.');
    }

    public function deleteUser(Request $request, string $userId): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        if ($request->user()->id === $userId) {
            return back()->withErrors(['users' => 'You cannot delete your own account.']);
        }

        $deleted = $this->deleteUsers($request, collect([$userId]));

        return back()->with('status', "{$deleted} user deleted.");
    }

    public function bulkDeleteUsers(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['string', 'exists:users,id'],
        ]);

        $userIds = collect($validated['user_ids'])
            ->reject(fn (string $userId) => $userId === $request->user()->id)
            ->values();

        if ($userIds->isEmpty()) {
            return back()->withErrors(['users' => 'Select at least one user other than yourself.']);
        }

        $deleted = $this->deleteUsers($request, $userIds);

        return back()->with('status', "{$deleted} users deleted.");
    }

    private function deleteUsers(Request $request, Collection $userIds): int
    {
        return DB::transaction(function () use ($request, $userIds): int {
            Group::query()
                ->whereIn('owner_id', $userIds)
                ->delete();

            $deleted = User::query()
                ->whereIn('id', $userIds)
                ->whereKeyNot($request->user()->id)
                ->delete();

            AuditLog::query()->create([
                'actor_user_id' => $request->user()->id,
                'event_type' => 'USERS_DELETED',
                'metadata' => [
                    'user_ids' => $userIds->values()->all(),
                    'deleted_count' => $deleted,
                ],
            ]);

            return $deleted;
        });
    }
}
