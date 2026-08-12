@php
    $canCreateGroup = auth()->check() && (
        auth()->user()->isAdmin() ||
        (auth()->user()->isOrganiser() && \App\Models\Group::query()->where('owner_id', auth()->id())->count() < 1)
    );

    $canManageBilling = auth()->check() && auth()->user()->isOrganiser();

    $navItem = function (string $route, string $label, string $activePattern, string $iconPath) {
        $active = request()->routeIs($activePattern);
        return '<a href="'.e(route($route)).'" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition '.($active ? 'bg-brand-50 text-brand-700 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900').'"><svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">'.$iconPath.'</svg><span>'.e($label).'</span></a>';
    };
@endphp

<aside id="app-sidebar" class="fixed left-3 top-3 z-40 h-[calc(100vh-1.5rem)] w-64 -translate-x-[calc(100%+1rem)] rounded-2xl border border-slate-200 bg-white shadow-sm transition-transform lg:left-5 lg:top-5 lg:h-[calc(100vh-2.5rem)] lg:translate-x-0" aria-label="Sidebar">
    <div class="flex h-full flex-col overflow-hidden p-3">
        <a href="{{ route('dashboard.home') }}" class="mb-5 flex items-center gap-3 rounded-xl px-2 py-2">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-sm font-bold text-white">B</span>
            <span class="text-lg font-semibold tracking-tight text-slate-900">Bangsa</span>
        </a>

        <div class="min-h-0 flex-1 overflow-y-auto">
            <p class="mb-2 px-3 text-xs font-semibold uppercase text-slate-400">Menu</p>
            <ul class="space-y-1">
                <li>{!! $navItem('dashboard.home', 'Dashboard', 'dashboard.home', '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5h6v6H4zM14 5h6v6h-6zM4 15h6v4H4zM14 15h6v4h-6z"/>') !!}</li>
                <li>{!! $navItem('profile.edit', 'Profile', 'profile.*', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 20a8 8 0 0 1 16 0"/>') !!}</li>
                <li>{!! $navItem('landing', 'Browse Communities', 'landing', '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>') !!}</li>
                @if($canCreateGroup)
                    <li>{!! $navItem('groups.create', 'Create Group', 'groups.create', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>') !!}</li>
                @endif
            </ul>

            @if($canManageBilling)
                <p class="mb-2 mt-6 px-3 text-xs font-semibold uppercase text-slate-400">Billing</p>
                <ul class="space-y-1">
                    <li>{!! $navItem('billing.plans', 'Plans', 'billing.plans', '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M6 7v12h12V7M8 11h8M8 15h5"/>') !!}</li>
                    <li>{!! $navItem('billing.subscriptions', 'Subscriptions', 'billing.subscriptions', '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10v10H7zM4 4h16v16H4z"/>') !!}</li>
                </ul>
            @endif

            @if(auth()->user()?->isAdmin())
                <p class="mb-2 mt-6 px-3 text-xs font-semibold uppercase text-slate-400">Admin</p>
                <ul class="space-y-1">
                    <li>{!! $navItem('dashboard.platform', 'Platform Overview', 'dashboard.platform', '<path stroke-linecap="round" stroke-linejoin="round" d="M4 19V9M10 19V5M16 19v-7M22 19H2"/>') !!}</li>
                    <li>{!! $navItem('platform.users', 'Manage Users', 'platform.users*', '<path stroke-linecap="round" stroke-linejoin="round" d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM2 20a6 6 0 0 1 12 0M17 8h4M19 6v4M16 20h6"/>') !!}</li>
                    <li>{!! $navItem('platform.groups', 'Manage Groups', 'platform.groups*', '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h7v7H4zM13 7h7v7h-7zM4 16h16"/>') !!}</li>
                </ul>
            @endif
        </div>

        <div class="mt-4 border-t border-slate-100 pt-3">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8M8 12h8M5 4h14v16H5z"/></svg>
                Settings
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-rose-600 hover:bg-rose-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6v12h4M14 8l4 4-4 4M8 12h10"/></svg>
                    Log out
                </button>
            </form>
        </div>
    </div>
</aside>
