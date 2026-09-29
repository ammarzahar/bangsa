@extends('layouts.app')

@section('content')
<div class="mb-7 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-brand-600">Community Directory</p>
        <h1 class="text-3xl font-semibold tracking-tight text-slate-950 lg:text-4xl">Find your people</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Explore active public communities, meet their members, and request access to the groups that fit you.</p>
    </div>
    <span class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm">
        {{ $groups->total() }} {{ str('community')->plural($groups->total()) }}
    </span>
</div>

<form method="GET" action="{{ route('communities.index') }}" class="mb-7 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <label for="community-search" class="mb-2 block text-sm font-medium text-slate-700">Search communities</label>
    <div class="flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
            </svg>
            <input id="community-search" name="q" value="{{ $search }}" type="search" maxlength="100" placeholder="Search by name, URL, or description..." class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:ring-brand-500">
        </div>
        <button type="submit" class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">Search</button>
        @if($search !== '')
            <a href="{{ route('communities.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-medium text-slate-600 hover:bg-slate-50">Clear</a>
        @endif
    </div>
</form>

<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    @forelse($groups as $group)
        @php($membership = $group->memberships->first())
        <article class="group flex min-h-64 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
            <div class="h-2 bg-gradient-to-r from-brand-600 via-cyan-500 to-emerald-400"></div>
            <div class="flex flex-1 flex-col p-5">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        @if($group->logo_url)
                            <img src="{{ $group->logo_url }}" alt="{{ $group->name }} logo" class="h-12 w-12 shrink-0 rounded-xl border border-slate-100 object-cover">
                        @else
                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-lg font-bold text-brand-700">{{ str($group->name)->substr(0, 1)->upper() }}</span>
                        @endif
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-semibold text-slate-950">{{ $group->name }}</h2>
                            <p class="truncate text-xs font-medium text-slate-400">bangsa.org/{{ $group->slug }}</p>
                        </div>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $group->visibility === \App\Models\Group::VISIBILITY_PAID ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">{{ str($group->visibility)->headline() }}</span>
                </div>

                <p class="mb-5 line-clamp-3 flex-1 text-sm leading-6 text-slate-600">{{ $group->description ?: 'This community has not added a description yet.' }}</p>

                <div class="mb-4 flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-500">
                    <span>{{ $group->approved_members_count }} {{ str('member')->plural($group->approved_members_count) }}</span>
                    @if($membership)
                        <span class="rounded-full px-2.5 py-1 font-semibold {{ $membership->status === \App\Models\GroupMembership::STATUS_APPROVED ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ str($membership->status)->headline() }}
                        </span>
                    @else
                        <span>Open to requests</span>
                    @endif
                </div>

                <a href="{{ route('groups.show', [$group->slug]) }}" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition group-hover:bg-brand-600">
                    View Community
                </a>
            </div>
        </article>
    @empty
        <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
            </div>
            <h2 class="font-semibold text-slate-900">No communities found</h2>
            <p class="mt-1 text-sm text-slate-500">Try another keyword or clear your search.</p>
        </div>
    @endforelse
</div>

@if($groups->hasPages())
    <div class="mt-8">{{ $groups->links() }}</div>
@endif
@endsection
