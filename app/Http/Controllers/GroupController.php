<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\MemberProfile;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller
{
    public function create()
    {
        abort_unless($this->canCreateGroup(auth()->user()), 403, 'Only admins and organisers can create communities.');

        return view('groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->canCreateGroup($request->user()), 403, 'Only admins and organisers can create communities.');

        $validated = $request->validate([
            'slug' => ['required', 'regex:/^[a-z0-9-]{3,50}$/', 'unique:groups,slug'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'visibility' => ['required', 'in:PUBLIC,PRIVATE,PAID'],
            'taut_checkout_url' => ['nullable', 'required_if:visibility,PAID', 'url:http,https', 'max:2048'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=256,min_height=256,max_width=2000,max_height=2000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:min_width=1200,min_height=400,max_width=4000,max_height=2000'],
        ]);

        $this->validateTautCheckoutUrl(
            $validated['visibility'] === Group::VISIBILITY_PAID ? $validated['taut_checkout_url'] : null
        );

        $subscription = Subscription::query()
            ->where('owner_user_id', $request->user()->id)
            ->whereNull('group_id')
            ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIALING])
            ->orderBy('created_at')
            ->first();

        if ($request->user()->account_type === User::TYPE_ORGANISER_PLUS && (! $subscription || ! $subscription->isActiveWindow())) {
            return back()->withErrors(['subscription' => 'An active subscription is required for Organiser Plus features.']);
        }

        DB::transaction(function () use ($validated, $subscription, $request): void {
            $group = Group::query()->create([
                'slug' => Str::of($validated['slug'])->lower()->toString(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'visibility' => $validated['visibility'],
                'taut_checkout_url' => $validated['visibility'] === Group::VISIBILITY_PAID
                    ? $validated['taut_checkout_url']
                    : null,
                'status' => Group::STATUS_ACTIVE,
                'owner_id' => $request->user()->id,
                'invite_token' => Str::random(40),
            ]);

            $branding = [];
            if ($request->hasFile('logo')) {
                $branding['logo_url'] = Storage::disk('public')->url(
                    $request->file('logo')->store("communities/{$group->id}", 'public')
                );
            }
            if ($request->hasFile('cover_image')) {
                $branding['cover_image_url'] = Storage::disk('public')->url(
                    $request->file('cover_image')->store("communities/{$group->id}", 'public')
                );
            }
            if ($branding !== []) {
                $group->update($branding);
            }

            $subscription?->update(['group_id' => $group->id]);

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
                'photo_url' => $request->user()->avatar_url,
                'full_name' => $request->user()->full_name ?: $request->user()->email,
            ]);

            AuditLog::query()->create([
                'target_group_id' => $group->id,
                'actor_user_id' => $request->user()->id,
                'event_type' => 'GROUP_CREATED',
                'metadata' => ['slug' => $group->slug],
            ]);
        });

        return redirect()->route('dashboard.group', [$validated['slug']])->with('status', 'Group created successfully.');
    }

    public function show(Request $request, string $group_slug)
    {
        $group = $request->attributes->get('current_group');
        if (! $this->hasInviteAccess($request, $group)) {
            $this->authorize('view', $group);
        }

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

    public function invite(Request $request, string $group_slug, string $invite_token): RedirectResponse
    {
        $group = $request->attributes->get('current_group');

        abort_unless(hash_equals((string) $group->invite_token, $invite_token), 404);

        $request->session()->put("group_invites.{$group->id}", $invite_token);
        $request->session()->put('url.intended', route('groups.show', [$group->slug]));

        if (! $request->user()) {
            return redirect()->route('login')->with('status', 'Sign in or create an account to join this private community.');
        }

        return redirect()->route('groups.show', [$group->slug])->with('status', 'Private invitation accepted.');
    }

    public function settings(Request $request, string $group_slug)
    {
        $group = $request->attributes->get('current_group');
        $this->authorize('manage', $group);

        return view('groups.settings', [
            'group' => $group,
            'inviteUrl' => route('groups.invite', [$group->slug, $group->invite_token]),
        ]);
    }

    public function updateSettings(Request $request, string $group_slug): RedirectResponse
    {
        $group = $request->attributes->get('current_group');
        $this->authorize('manage', $group);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'visibility' => ['required', 'in:PUBLIC,PRIVATE,PAID'],
            'taut_checkout_url' => ['nullable', 'required_if:visibility,PAID', 'url:http,https', 'max:2048'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=256,min_height=256,max_width=2000,max_height=2000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:min_width=1200,min_height=400,max_width=4000,max_height=2000'],
        ]);

        $this->validateTautCheckoutUrl(
            $validated['visibility'] === Group::VISIBILITY_PAID ? $validated['taut_checkout_url'] : null
        );

        $validated['taut_checkout_url'] = $validated['visibility'] === Group::VISIBILITY_PAID
            ? $validated['taut_checkout_url']
            : null;

        unset($validated['logo'], $validated['cover_image']);

        $oldBranding = [];
        if ($request->hasFile('logo')) {
            $oldBranding[] = $this->publicStoragePath($group->logo_url);
            $validated['logo_url'] = Storage::disk('public')->url(
                $request->file('logo')->store("communities/{$group->id}", 'public')
            );
        }
        if ($request->hasFile('cover_image')) {
            $oldBranding[] = $this->publicStoragePath($group->cover_image_url);
            $validated['cover_image_url'] = Storage::disk('public')->url(
                $request->file('cover_image')->store("communities/{$group->id}", 'public')
            );
        }

        $group->update($validated);

        foreach (array_filter($oldBranding) as $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

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

    private function canCreateGroup(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isOrganiser()) {
            return false;
        }

        return Group::query()
            ->where('owner_id', $user->id)
            ->count() < 1;
    }

    private function validateTautCheckoutUrl(?string $url): void
    {
        if (! $url) {
            return;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);
        if (
            ($host !== 'taut.my' && ! str_ends_with($host, '.taut.my'))
            || preg_match('~/checkout/(\d+)/?$~', $path) !== 1
        ) {
            throw ValidationException::withMessages([
                'taut_checkout_url' => 'Use a TAUT single-product checkout URL ending with /checkout/{product_id}.',
            ]);
        }
    }

    private function publicStoragePath(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        if (! str_starts_with($path, '/storage/')) {
            return null;
        }

        return ltrim(Str::after($path, '/storage/'), '/');
    }

    private function hasInviteAccess(Request $request, Group $group): bool
    {
        if ($group->visibility !== Group::VISIBILITY_PRIVATE) {
            return false;
        }

        return hash_equals(
            (string) $group->invite_token,
            (string) $request->session()->get("group_invites.{$group->id}")
        );
    }
}
