@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="bangsa-heading">{{ $group->name }} Directory</h1>
        <p class="mt-1 text-sm text-slate-600">Discover members by role, location, and featured profile status.</p>
    </div>
</div>

<div class="mb-6 rounded-xl border border-slate-200 bg-white p-4">
    <form method="GET" action="{{ route('groups.members.directory', [$group->slug]) }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label for="search" class="mb-2 block text-sm font-medium text-slate-700">Search</label>
            <input id="search" name="search" value="{{ request('search') }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Name, role, business">
        </div>
        <div>
            <label for="city" class="mb-2 block text-sm font-medium text-slate-700">City</label>
            <input id="city" name="city" value="{{ request('city') }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="City">
        </div>
        <div>
            <label for="country" class="mb-2 block text-sm font-medium text-slate-700">Country</label>
            <input id="country" name="country" value="{{ request('country') }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Country">
        </div>
        <div class="flex items-center gap-3 lg:items-end">
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="featured" value="1" {{ request('featured') ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Featured only
            </label>
            <button type="submit" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Filter</button>
        </div>
    </form>
</div>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse($members as $member)
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-brand-300 hover:shadow-md">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <a href="{{ route('groups.member.show', [$group->slug, $member->username]) }}" class="text-lg font-semibold text-slate-900 hover:text-brand-700">{{ $member->full_name }}</a>
                    <p class="mt-1 text-sm text-slate-600">{{ $member->current_role }}{{ $member->business ? ' @ '.$member->business : '' }}</p>
                </div>
                @if($member->is_featured)
                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">Featured</span>
                @endif
            </div>
            <p class="mb-4 text-sm text-slate-500">{{ $member->city }}{{ $member->country ? ', '.$member->country : '' }}</p>
            <a href="{{ route('groups.member.show', [$group->slug, $member->username]) }}" class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100">View Profile</a>
        </article>
    @empty
        <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-sm text-slate-500">No members matched your filters.</div>
    @endforelse
</div>

<div class="mt-6">{{ $members->links() }}</div>
@endsection