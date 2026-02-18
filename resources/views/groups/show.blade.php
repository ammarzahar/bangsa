@extends('layouts.app')

@section('content')
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold text-slate-900">{{ $group->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">https://bangsa.org/{{ $group->slug }} · {{ $group->visibility }} · {{ $group->status }}</p>
            <p class="mt-4 max-w-3xl text-sm leading-6 text-slate-600">{{ $group->description ?: 'No description provided yet.' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('groups.members.directory', [$group->slug]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Directory</a>
            @auth
                <form method="POST" action="{{ route('groups.join', [$group->slug]) }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Request Join</button>
                </form>
                <a href="{{ route('dashboard.group', [$group->slug]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Group Dashboard</a>
                @can('manage', $group)
                    <a href="{{ route('groups.settings', [$group->slug]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Group Settings</a>
                @endcan
            @endauth
        </div>
    </div>
</div>

<div class="mb-6 bangsa-card">
    <h2 class="mb-4 text-xl font-semibold text-slate-900">Featured Members</h2>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($featuredMembers as $member)
            <a href="{{ route('groups.member.show', [$group->slug, $member->username]) }}" class="rounded-xl border border-amber-200 bg-amber-50 p-4 transition hover:shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $member->full_name }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $member->current_role }}{{ $member->business ? ' @ '.$member->business : '' }}</p>
                    </div>
                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">Featured</span>
                </div>
            </a>
        @empty
            <p class="text-sm text-slate-500">No featured members yet.</p>
        @endforelse
    </div>
</div>

<div class="bangsa-card">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-slate-900">All Members</h2>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ $approvedMembers->count() }} joined</span>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($approvedMembers as $member)
            <a href="{{ route('groups.member.show', [$group->slug, $member->username]) }}" class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-brand-300 hover:shadow-md">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <p class="font-semibold text-slate-900">{{ $member->full_name }}</p>
                    @if($member->is_featured)
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">Featured</span>
                    @endif
                </div>
                <p class="text-sm text-slate-600">{{ $member->current_role }}{{ $member->business ? ' @ '.$member->business : '' }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ $member->city }}{{ $member->country ? ', '.$member->country : '' }}</p>
            </a>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">No approved members yet.</div>
        @endforelse
    </div>
</div>
@endsection
