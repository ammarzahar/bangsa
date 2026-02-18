<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GroupMembership;
use App\Models\MemberProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberProfileController extends Controller
{
    public function directory(Request $request, string $group_slug)
    {
        $group = $request->attributes->get('current_group');
        $this->authorize('view', $group);

        $query = MemberProfile::query()->where('group_id', $group->id);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('current_role', 'like', "%{$search}%")
                    ->orWhere('business', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->string('city')->toString().'%');
        }

        if ($request->filled('country')) {
            $query->where('country', 'like', '%'.$request->string('country')->toString().'%');
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $members = $query->orderByDesc('is_featured')->orderBy('full_name')->paginate(20)->withQueryString();

        return view('members.directory', [
            'group' => $group,
            'members' => $members,
        ]);
    }

    public function show(Request $request, string $group_slug, string $username)
    {
        $group = $request->attributes->get('current_group');
        $this->authorize('view', $group);

        $profile = MemberProfile::query()
            ->where('group_id', $group->id)
            ->where('username', $username)
            ->firstOrFail();

        return view('members.show', [
            'group' => $group,
            'profile' => $profile,
        ]);
    }

    public function edit(Request $request, string $group_slug, string $username)
    {
        $group = $request->attributes->get('current_group');
        $profile = MemberProfile::query()
            ->where('group_id', $group->id)
            ->where('username', $username)
            ->firstOrFail();

        $this->authorize('updateProfile', [GroupMembership::class, $group, $profile->user_id]);

        return view('members.edit', [
            'group' => $group,
            'profile' => $profile,
            'isAdmin' => $request->user()->is_platform_owner || GroupMembership::query()
                ->where('group_id', $group->id)
                ->where('user_id', $request->user()->id)
                ->where('status', GroupMembership::STATUS_APPROVED)
                ->whereIn('role', [GroupMembership::ROLE_OWNER, GroupMembership::ROLE_ADMIN])
                ->exists(),
        ]);
    }

    public function update(Request $request, string $group_slug, string $username): RedirectResponse
    {
        $group = $request->attributes->get('current_group');
        $profile = MemberProfile::query()
            ->where('group_id', $group->id)
            ->where('username', $username)
            ->firstOrFail();

        $this->authorize('updateProfile', [GroupMembership::class, $group, $profile->user_id]);

        $isAdmin = $request->user()->is_platform_owner || GroupMembership::query()
            ->where('group_id', $group->id)
            ->where('user_id', $request->user()->id)
            ->where('status', GroupMembership::STATUS_APPROVED)
            ->whereIn('role', [GroupMembership::ROLE_OWNER, GroupMembership::ROLE_ADMIN])
            ->exists();

        $rules = [
            'username' => [
                'required',
                'regex:/^[a-z0-9-]{3,40}$/',
                Rule::unique('member_profiles', 'username')->where(fn ($q) => $q->where('group_id', $group->id))->ignore($profile->id),
            ],
            'photo_url' => ['nullable', 'url'],
            'full_name' => ['required', 'string', 'max:120'],
            'short_bio' => ['nullable', 'string', 'max:500'],
            'current_role' => ['nullable', 'string', 'max:120'],
            'business' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:80'],
            'linkedin_url' => ['nullable', 'url'],
            'instagram_url' => ['nullable', 'url'],
            'facebook_url' => ['nullable', 'url'],
            'website_url' => ['nullable', 'url'],
        ];

        if ($isAdmin) {
            $rules['is_featured'] = ['nullable', 'boolean'];
        }

        $validated = $request->validate($rules);

        if (!$isAdmin) {
            unset($validated['is_featured']);
        }

        $profile->update($validated);

        AuditLog::query()->create([
            'target_group_id' => $group->id,
            'actor_user_id' => $request->user()->id,
            'event_type' => 'MEMBER_PROFILE_UPDATED',
            'metadata' => ['profile_user_id' => $profile->user_id],
        ]);

        return redirect()->route('groups.member.show', [$group->slug, $profile->username])->with('status', 'Profile updated.');
    }
}