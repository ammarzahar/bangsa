@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-950 lg:text-3xl">Dashboard Overview</h1>
        <p class="mt-1 text-sm text-slate-500">Manage your Bangsa account and discover communities.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('communities.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Browse Communities</a>
        @if(auth()->user()->isOrganiser())
            <a href="{{ route('billing.plans') }}" class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">Upgrade Plan</a>
        @endif
    </div>
</div>

<div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
    <strong>No group membership yet.</strong>
    Join an existing community or wait for an admin to approve your access.
</div>

<div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(380px,1.35fr)]">
    <div class="space-y-5">
        <div class="bangsa-card">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Account Progress</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-950">{{ auth()->user()->accountTypeLabel() }}</h2>
                </div>
                <span class="rounded-xl bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">Today</span>
            </div>

            <div class="mx-auto mb-5 flex h-40 w-40 items-center justify-center rounded-full bg-[conic-gradient(#2563eb_0_64%,#22d3ee_64%_78%,#e5e7eb_78%_100%)] p-4">
                <div class="flex h-full w-full flex-col items-center justify-center rounded-full bg-white">
                    <p class="text-3xl font-bold text-slate-950">64%</p>
                    <p class="text-xs text-slate-500">Setup</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">Profile</p>
                    <p class="mt-1 font-semibold text-slate-950">Ready</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">Memberships</p>
                    <p class="mt-1 font-semibold text-slate-950">0 active</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">Account</p>
                    <p class="mt-1 font-semibold text-slate-950">{{ auth()->user()->accountTypeLabel() }}</p>
                </div>
            </div>
        </div>

        <div class="bangsa-card">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-950">Next Actions</h2>
                <span class="rounded-xl bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">Priority</span>
            </div>
            <div class="space-y-3">
                <a href="{{ route('communities.index') }}" class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-4 hover:border-brand-200 hover:bg-brand-50">
                    <div>
                        <p class="font-medium text-slate-950">Find a community</p>
                        <p class="mt-1 text-sm text-slate-500">Open a public group and submit a join request.</p>
                    </div>
                    <span class="text-brand-700">-></span>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-4 hover:border-brand-200 hover:bg-brand-50">
                    <div>
                        <p class="font-medium text-slate-950">Update profile</p>
                        <p class="mt-1 text-sm text-slate-500">Keep your name, email, and password current.</p>
                    </div>
                    <span class="text-brand-700">-></span>
                </a>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="bangsa-card">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Available Communities</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $groups->count() }} public communities listed</p>
                </div>
                <span class="rounded-xl border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600">Public</span>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                @forelse($groups as $group)
                    <a href="{{ route('groups.show', [$group->slug]) }}" class="rounded-2xl border border-slate-100 bg-slate-50 p-4 transition hover:border-brand-200 hover:bg-white hover:shadow-sm">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="font-semibold text-slate-950">{{ $group->name }}</h3>
                            <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700">{{ $group->visibility }}</span>
                        </div>
                        <p class="line-clamp-3 text-sm leading-6 text-slate-600">{{ $group->description ?: 'No description provided.' }}</p>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">No active communities yet.</div>
                @endforelse
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div class="bangsa-card">
                <h2 class="mb-4 text-lg font-semibold text-slate-950">Activity</h2>
                <div class="grid grid-cols-7 gap-1">
                    @foreach(range(1, 35) as $i)
                        <div class="h-8 rounded-md {{ $i % 5 === 0 ? 'bg-brand-600' : ($i % 3 === 0 ? 'bg-cyan-400' : 'bg-slate-100') }}"></div>
                    @endforeach
                </div>
            </div>
            <div class="bangsa-card">
                <h2 class="mb-4 text-lg font-semibold text-slate-950">Account</h2>
                <p class="text-lg font-semibold text-slate-950">{{ auth()->user()->full_name ?? 'Member' }}</p>
                <p class="mt-1 break-all text-sm text-slate-500">{{ auth()->user()->email }}</p>
                <div class="mt-5 rounded-xl bg-slate-50 p-4">
                    <p class="text-sm text-slate-500">Role</p>
                    <p class="mt-1 font-semibold text-slate-950">{{ auth()->user()->accountTypeLabel() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
