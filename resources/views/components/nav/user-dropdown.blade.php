@php
    $user = auth()->user();
    $initial = strtoupper(substr($user->full_name ?? $user->email, 0, 1));
    $canCreateGroup = $user->is_platform_owner || \App\Models\GroupMembership::query()
        ->where('user_id', $user->id)
        ->where('status', \App\Models\GroupMembership::STATUS_APPROVED)
        ->whereIn('role', [\App\Models\GroupMembership::ROLE_OWNER, \App\Models\GroupMembership::ROLE_ADMIN])
        ->exists();
    $canManageBilling = $user->is_platform_owner || \App\Models\GroupMembership::query()
        ->where('user_id', $user->id)
        ->where('status', \App\Models\GroupMembership::STATUS_APPROVED)
        ->whereIn('role', [\App\Models\GroupMembership::ROLE_OWNER, \App\Models\GroupMembership::ROLE_ADMIN])
        ->exists();
@endphp

<button id="userDropdownButton" data-dropdown-toggle="userDropdown" data-dropdown-placement="bottom-end" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100" type="button">
    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-600 text-xs font-semibold text-white">{{ $initial }}</span>
    <span class="hidden sm:block">{{ $user->full_name ?? $user->email }}</span>
    <svg class="h-3 w-3" aria-hidden="true" fill="none" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
        <path d="m1 1 4 4 4-4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
    </svg>
</button>

<div id="userDropdown" class="z-50 hidden w-56 divide-y divide-slate-100 rounded-lg border border-slate-200 bg-white shadow">
    <div class="px-4 py-3 text-sm text-slate-900">
        <div class="font-medium">{{ $user->full_name ?? 'Member' }}</div>
        <div class="truncate text-slate-500">{{ $user->email }}</div>
    </div>
    <ul class="py-2 text-sm text-slate-700">
        <li><a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-slate-100">Profile</a></li>
        @if($canManageBilling)
            <li><a href="{{ route('billing.subscriptions') }}" class="block px-4 py-2 hover:bg-slate-100">My Subscriptions</a></li>
        @endif
        @if($canCreateGroup)
            <li><a href="{{ route('groups.create') }}" class="block px-4 py-2 hover:bg-slate-100">Create Group</a></li>
        @endif
        @if($user->is_platform_owner)
            <li><a href="{{ route('dashboard.platform') }}" class="block px-4 py-2 hover:bg-slate-100">Platform Dashboard</a></li>
        @endif
    </ul>
    <div class="p-2">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full rounded-lg px-4 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">Logout</button>
        </form>
    </div>
</div>
