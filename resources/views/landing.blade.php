@extends('layouts.app')

@section('content')
@php
    $canCreateGroup = auth()->check() && (
        auth()->user()->isAdmin() ||
        (auth()->user()->isOrganiser() && \App\Models\Group::query()->where('owner_id', auth()->id())->count() < 1)
    );

    $canManageBilling = auth()->check() && auth()->user()->isOrganiser();
@endphp

<section class="rounded-2xl bg-gradient-to-br from-slate-900 via-brand-900 to-slate-800 p-8 text-white lg:p-12">
    <div class="max-w-3xl">
        <p class="mb-4 inline-flex rounded-full border border-white/30 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">Bangsa SaaS</p>
        <h1 class="mb-4 text-3xl font-bold leading-tight lg:text-5xl">Build high-trust networking communities at scale.</h1>
        <p class="mb-8 text-base text-slate-200 lg:text-lg">Launch private or public groups with approvals, profile-rich directories, and subscription-based operations from day one.</p>
        <div class="flex flex-wrap gap-3">
            @auth
                @if($canCreateGroup)
                    <a href="{{ route('groups.create') }}" class="rounded-lg bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-100">Create Your Group</a>
                @else
                    <a href="{{ route('dashboard.home') }}" class="rounded-lg bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-100">Go To Dashboard</a>
                @endif
            @else
                <a href="{{ route('register') }}" class="rounded-lg bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-100">Start Free Setup</a>
                <a href="{{ route('login') }}" class="rounded-lg border border-white/40 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10">Sign In</a>
            @endauth
        </div>
    </div>
</section>

<section class="mt-8 grid gap-4 md:grid-cols-3">
    <div class="bangsa-card">
        <h3 class="mb-2 text-lg font-semibold">Verified Communities</h3>
        <p class="text-sm text-slate-600">Admin approval workflow ensures member quality and trusted networking.</p>
    </div>
    <div class="bangsa-card">
        <h3 class="mb-2 text-lg font-semibold">Member Profiles</h3>
        <p class="text-sm text-slate-600">SEO-ready profile URLs and social links for discoverability and collaboration.</p>
    </div>
    <div class="bangsa-card">
        <h3 class="mb-2 text-lg font-semibold">Subscription Ready</h3>
        <p class="text-sm text-slate-600">Built-in gating and billing models to monetize each group with control.</p>
    </div>
</section>

<section class="mt-8 bangsa-card">
    <div class="mb-5 flex items-center justify-between">
        <h2 class="text-xl font-semibold">Active Communities</h2>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ $groups->count() }} listed</span>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($groups as $group)
            <a href="{{ route('groups.show', [$group->slug]) }}" class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-brand-300 hover:shadow-md">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">{{ $group->name }}</h3>
                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700">{{ $group->visibility }}</span>
                </div>
                <p class="mb-4 line-clamp-3 text-sm text-slate-600">{{ $group->description ?: 'No description provided.' }}</p>
                <p class="text-xs font-medium text-slate-500">https://bangsa.org/{{ $group->slug }}</p>
            </a>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">No active groups yet.</div>
        @endforelse
    </div>
</section>

<section class="mt-8 grid gap-4 lg:grid-cols-2">
    <div class="bangsa-card">
        <h2 class="mb-4 text-xl font-semibold">Starter Plan</h2>
        <div class="mb-3 text-3xl font-bold">$99<span class="text-base font-medium text-slate-500">/month</span></div>
        <ul class="mb-6 space-y-2 text-sm text-slate-600">
            <li>One paid group workspace</li>
            <li>Admin approvals and profile moderation</li>
            <li>Directory search and featured members</li>
        </ul>
        @if(auth()->check() && $canManageBilling)
            <a href="{{ route('billing.plans') }}" class="inline-flex rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Choose Plan</a>
        @elseif(auth()->check())
            <a href="{{ route('dashboard.home') }}" class="inline-flex rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Go To Dashboard</a>
        @else
            <a href="{{ route('register') }}" class="inline-flex rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Choose Plan</a>
        @endif
    </div>
    <div class="bangsa-card bg-slate-900 text-white">
        <h2 class="mb-3 text-xl font-semibold">Ready to build your network brand?</h2>
        <p class="mb-6 text-sm text-slate-300">Launch your community URL, onboard members with review flow, and manage growth in one dashboard.</p>
        @if(auth()->check() && $canCreateGroup)
            <a href="{{ route('groups.create') }}" class="inline-flex rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-100">Launch Bangsa Group</a>
        @elseif(auth()->check())
            <a href="{{ route('dashboard.home') }}" class="inline-flex rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-100">Open Dashboard</a>
        @else
            <a href="{{ route('register') }}" class="inline-flex rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-100">Launch Bangsa Group</a>
        @endif
    </div>
</section>
@endsection
