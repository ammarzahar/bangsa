@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="bangsa-heading">Dashboard</h1>
        <p class="mt-1 text-sm text-slate-600">Manage your Bangsa account and community access.</p>
    </div>
    <a href="{{ route('landing') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Browse Communities</a>
</div>

<div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
    <strong>No group membership yet.</strong>
    Join an existing community or wait for an admin to approve your access.
</div>

<div class="mb-6 grid gap-4 md:grid-cols-3">
    <div class="bangsa-card">
        <p class="text-sm text-slate-500">Account</p>
        <p class="mt-2 text-lg font-semibold text-slate-900">{{ auth()->user()->full_name ?? 'Member' }}</p>
        <p class="mt-1 break-all text-sm text-slate-600">{{ auth()->user()->email }}</p>
    </div>
    <div class="bangsa-card">
        <p class="text-sm text-slate-500">Memberships</p>
        <p class="mt-2 text-3xl font-semibold text-slate-900">0</p>
        <p class="mt-1 text-sm text-slate-600">No active group membership found.</p>
    </div>
    <div class="bangsa-card">
        <p class="text-sm text-slate-500">Next Step</p>
        <p class="mt-2 text-lg font-semibold text-slate-900">Find a community</p>
        <p class="mt-1 text-sm text-slate-600">Open a public group and submit a join request.</p>
    </div>
</div>

<div class="bangsa-card">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-slate-900">Available Communities</h2>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ $groups->count() }} listed</span>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($groups as $group)
            <a href="{{ route('groups.show', [$group->slug]) }}" class="rounded-lg border border-slate-200 bg-white p-4 transition hover:border-brand-300 hover:shadow-sm">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <h3 class="font-semibold text-slate-900">{{ $group->name }}</h3>
                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700">{{ $group->visibility }}</span>
                </div>
                <p class="line-clamp-3 text-sm text-slate-600">{{ $group->description ?: 'No description provided.' }}</p>
            </a>
        @empty
            <div class="col-span-full rounded-lg border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">No active communities yet.</div>
        @endforelse
    </div>
</div>
@endsection
