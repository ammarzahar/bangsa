<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Bangsa' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50">
@php
    $canCreateGroup = auth()->check() && (
        auth()->user()->isAdmin() ||
        (auth()->user()->isOrganiser() && \App\Models\Group::query()->where('owner_id', auth()->id())->count() < 1)
    );

    $canManageBilling = auth()->check() && auth()->user()->isOrganiser();

    $showSidebar = auth()->check() && (
        request()->routeIs('dashboard.*') ||
        request()->routeIs('platform.*') ||
        request()->routeIs('groups.settings*') ||
        request()->routeIs('groups.membership.*') ||
        request()->routeIs('billing.*') ||
        request()->routeIs('groups.create')
    );
@endphp

<nav class="fixed top-0 z-50 w-full border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 lg:px-6">
        <div class="flex items-center gap-3">
            <button data-drawer-target="app-sidebar" data-drawer-toggle="app-sidebar" aria-controls="app-sidebar" type="button" class="inline-flex items-center rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                <span class="sr-only">Open sidebar</span>
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10zm0 5.25a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75z"></path></svg>
            </button>
            <a href="{{ route('landing') }}" class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-sm font-bold text-white">B</span>
                <span class="text-lg font-semibold tracking-tight text-slate-900">Bangsa</span>
            </a>
            <div class="hidden items-center gap-2 md:flex">
                @auth
                    @if($canCreateGroup)
                        <a href="{{ route('groups.create') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Create Group</a>
                    @endif
                    @if($canManageBilling)
                        <a href="{{ route('billing.plans') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Plans</a>
                    @endif
                @endauth
            </div>
        </div>

        <div class="flex items-center gap-3">
            @auth
                <x-nav.user-dropdown />
            @else
                <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Login</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">Get Started</a>
            @endauth
        </div>
    </div>
</nav>

@if($showSidebar)
    <x-shell.sidebar />
@endif

<main class="pt-20 {{ $showSidebar ? 'lg:ml-64' : '' }}">
    <div class="mx-auto w-full max-w-7xl px-4 pb-10 lg:px-6">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</main>
</body>
</html>
