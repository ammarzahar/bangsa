@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="bangsa-heading">Group Dashboard: {{ $group->name }}</h1>
        <p class="mt-1 text-sm text-slate-600">Track approvals, featured profiles, and subscription status.</p>
    </div>
    @if($isManager)
        <div class="flex gap-2">
            <a href="{{ route('groups.membership.requests', [$group->slug]) }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Review Requests</a>
            <a href="{{ route('groups.settings', [$group->slug]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Group Settings</a>
        </div>
    @endif
</div>

<div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="bangsa-card"><p class="text-sm text-slate-500">Approved Members</p><p class="mt-2 text-3xl font-semibold text-slate-900">{{ $analytics['approved_members'] }}</p></div>
    <div class="bangsa-card"><p class="text-sm text-slate-500">Pending Members</p><p class="mt-2 text-3xl font-semibold text-slate-900">{{ $analytics['pending_members'] }}</p></div>
    <div class="bangsa-card"><p class="text-sm text-slate-500">Pending Requests</p><p class="mt-2 text-3xl font-semibold text-slate-900">{{ $analytics['pending_requests'] }}</p></div>
    <div class="bangsa-card"><p class="text-sm text-slate-500">Featured Profiles</p><p class="mt-2 text-3xl font-semibold text-slate-900">{{ $analytics['featured_profiles'] }}</p></div>
</div>

@unless($isManager)
    <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-700">
        Your membership status for this group is <strong>{{ $viewerMembership?->status ?? 'NONE' }}</strong>.
    </div>
@endunless

@if($subscription)
    <div class="bangsa-card">
        <h2 class="mb-3 text-lg font-semibold text-slate-900">Subscription</h2>
        <div class="grid gap-2 text-sm text-slate-600 md:grid-cols-3">
            <p><span class="font-medium text-slate-900">Status:</span> {{ $subscription->status }}</p>
            <p><span class="font-medium text-slate-900">Plan:</span> {{ $subscription->plan->name }}</p>
            <p><span class="font-medium text-slate-900">Current period end:</span> {{ optional($subscription->current_period_end)->toDateString() }}</p>
        </div>
    </div>
@endif
@endsection
