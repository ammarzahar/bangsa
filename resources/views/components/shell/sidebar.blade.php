@php
    $canCreateGroup = auth()->check() && (
        auth()->user()->is_platform_owner ||
        \App\Models\GroupMembership::query()
            ->where('user_id', auth()->id())
            ->where('status', \App\Models\GroupMembership::STATUS_APPROVED)
            ->whereIn('role', [\App\Models\GroupMembership::ROLE_OWNER, \App\Models\GroupMembership::ROLE_ADMIN])
            ->exists()
    );

    $canManageBilling = auth()->check() && (
        auth()->user()->is_platform_owner ||
        \App\Models\GroupMembership::query()
            ->where('user_id', auth()->id())
            ->where('status', \App\Models\GroupMembership::STATUS_APPROVED)
            ->whereIn('role', [\App\Models\GroupMembership::ROLE_OWNER, \App\Models\GroupMembership::ROLE_ADMIN])
            ->exists()
    );
@endphp

<aside id="app-sidebar" class="fixed top-0 left-0 z-40 h-screen w-64 -translate-x-full border-r border-slate-200 bg-white pt-20 transition-transform lg:translate-x-0" aria-label="Sidebar">
    <div class="h-full overflow-y-auto px-3 pb-4">
        <ul class="space-y-1 text-sm font-medium">
            <li>
                <a href="{{ route('dashboard.home') }}" class="group flex items-center rounded-lg px-3 py-2 {{ request()->routeIs('dashboard.home') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-100' }}">Dashboard</a>
            </li>
            <li>
                <a href="{{ route('profile.edit') }}" class="group flex items-center rounded-lg px-3 py-2 {{ request()->routeIs('profile.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-100' }}">Profile</a>
            </li>
            <li>
                <a href="{{ route('landing') }}" class="group flex items-center rounded-lg px-3 py-2 {{ request()->routeIs('landing') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-100' }}">Home</a>
            </li>
            @if($canCreateGroup)
                <li>
                    <a href="{{ route('groups.create') }}" class="group flex items-center rounded-lg px-3 py-2 {{ request()->routeIs('groups.create') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-100' }}">Create Group</a>
                </li>
            @endif
            @if($canManageBilling)
                <li>
                    <a href="{{ route('billing.plans') }}" class="group flex items-center rounded-lg px-3 py-2 {{ request()->routeIs('billing.plans') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-100' }}">Plans</a>
                </li>
                <li>
                    <a href="{{ route('billing.subscriptions') }}" class="group flex items-center rounded-lg px-3 py-2 {{ request()->routeIs('billing.subscriptions') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-100' }}">Subscriptions</a>
                </li>
            @endif
            @if(auth()->user()?->is_platform_owner)
                <li>
                    <a href="{{ route('dashboard.platform') }}" class="group flex items-center rounded-lg px-3 py-2 {{ request()->routeIs('dashboard.platform') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-100' }}">Platform Dashboard</a>
                </li>
                <li>
                    <a href="{{ route('platform.groups') }}" class="group flex items-center rounded-lg px-3 py-2 {{ request()->routeIs('platform.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-100' }}">Manage Groups</a>
                </li>
            @endif
        </ul>
    </div>
</aside>
