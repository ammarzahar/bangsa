<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\MemberProfile;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    public function create()
    {
        abort_unless($this->canCreateGroup(auth()->user()), 403, 'Only admin/owner can create groups.');

        return view('groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->canCreateGroup($request->user()), 403, 'Only admin/owner can create groups.');

        $validated = $request->validate([
            'slug' => ['required', 'regex:/^[a-z0-9-]{3,50}$/', 'unique:groups,slug'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'visibility' => ['required', 'in:PUBLIC,PRIVATE'],
            'logo_url' => ['nullable', 'url'],
            'cover_image_url' => ['nullable', 'url'],
        ]);

        $subscription = Subscription::query()
            ->where('owner_user_id', $request->user()->id)
            ->whereNull('group_id')
            ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIALING])
            ->orderBy('created_at')
            ->first();

        if (!$subscription || !$subscription->isActiveWindow()) {
            return back()->withErrors(['subscription' => 'An active subscription is required to create a group.']);
        }

        DB::transaction(function () use ($validated, $subscription, $request): void {
            $group = Group::query()->create([
                'slug' => Str::of($validated['slug'])->lower()->toString(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'visibility' => $validated['visibility'],
                'logo_url' => $validated['logo_url'] ?? null,
                'cover_image_url' => $validated['cover_image_url'] ?? null,
                'status' => Group::STATUS_ACTIVE,
                'owner_id' => $request->user()->id,
            ]);

            $subscription->update(['group_id' => $group->id]);

            GroupMembership::query()->create([
                'group_id' => $group->id,
                'user_id' => $request->user()->id,
                'role' => GroupMembership::ROLE_OWNER,
                'status' => GroupMembership::STATUS_APPROVED,
                'approved_by_user_id' => $request->user()->id,
                'approved_at' => now(),
            ]);

            MemberProfile::query()->create([
                'group_id' => $group->id,
                'user_id' => $request->user()->id,
                'username' => $this->uniqueUsername(
                    $group->id,
                    $request->user()->full_name ?: Str::before($request->user()->email, '@')
                ),
                'full_name' => $request->user()->full_name ?: $request->user()->email,
            ]);

            AuditLog::query()->create([
                'target_group_id' => $group->id,
                'actor_user_id' => $request->user()->id,
                'event_type' => 'GROUP_CREATED',
                'metadata' => ['slug' => $group->slug],
            ]);
        });

        return redirect()->route('landing')->with('status', 'Group created successfully.');
    }

    public function show(Request $request, string $group_slug)
    {
        $group = $request->attributes->get('current_group');
        $this->authorize('view', $group);

        $featuredMembers = MemberProfile::query()
            ->where('group_id', $group->id)
            ->where('is_featured', true)
            ->orderBy('full_name')
            ->take(12)
            ->get();

        $approvedMembers = MemberProfile::query()
            ->where('group_id', $group->id)
            ->whereHas('user.memberships', function ($query) use ($group): void {
                $query->where('group_id', $group->id)
                    ->where('status', GroupMembership::STATUS_APPROVED);
            })
            ->orderByDesc('is_featured')
            ->orderBy('full_name')
            ->get();

        return view('groups.show', [
            'group' => $group,
            'featuredMembers' => $featuredMembers,
            'approvedMembers' => $approvedMembers,
        ]);
    }

    public function settings(Request $request, string $group_slug)
    {
        $group = $request->attributes->get('current_group');
        $this->authorize('manage', $group);

        return view('groups.settings', ['group' => $group]);
    }

    public function updateSettings(Request $request, string $group_slug): RedirectResponse
    {
        $group = $request->attributes->get('current_group');
        $this->authorize('manage', $group);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'visibility' => ['required', 'in:PUBLIC,PRIVATE'],
            'logo_url' => ['nullable', 'url'],
            'cover_image_url' => ['nullable', 'url'],
        ]);

        $group->update($validated);

        AuditLog::query()->create([
            'target_group_id' => $group->id,
            'actor_user_id' => $request->user()->id,
            'event_type' => 'GROUP_SETTINGS_UPDATED',
            'metadata' => ['fields' => array_keys($validated)],
        ]);

        return back()->with('status', 'Group settings updated.');
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

    private function canCreateGroup(?\App\Models\User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->is_platform_owner) {
            return true;
        }

        return GroupMembership::query()
            ->where('user_id', $user->id)
            ->where('status', GroupMembership::STATUS_APPROVED)
            ->whereIn('role', [GroupMembership::ROLE_OWNER, GroupMembership::ROLE_ADMIN])
            ->exists();
    }
}
