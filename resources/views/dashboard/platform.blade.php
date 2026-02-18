@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="bangsa-heading">Platform Dashboard</h1>
        <p class="mt-1 text-sm text-slate-600">Monitor total groups, membership growth, and recurring revenue.</p>
    </div>
    <a href="{{ route('platform.groups') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Manage Groups</a>
</div>

<div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="bangsa-card"><p class="text-sm text-slate-500">Total Groups</p><p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['total_groups'] }}</p></div>
    <div class="bangsa-card"><p class="text-sm text-slate-500">Total Members</p><p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['total_members'] }}</p></div>
    <div class="bangsa-card"><p class="text-sm text-slate-500">Active Subscriptions</p><p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['active_subscriptions'] }}</p></div>
    <div class="bangsa-card"><p class="text-sm text-slate-500">MRR</p><p class="mt-2 text-3xl font-semibold text-slate-900">${{ number_format($stats['mrr_cents'] / 100, 2) }}</p></div>
</div>

<div class="bangsa-card">
    <h2 class="mb-4 text-lg font-semibold text-slate-900">Recent Groups</h2>
    <div class="relative overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-100 text-xs uppercase text-slate-700">
                <tr>
                    <th class="px-4 py-3">Group</th>
                    <th class="px-4 py-3">Owner</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                    <tr class="border-b border-slate-200 bg-white">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $group->name }} <span class="text-xs text-slate-500">/{{ $group->slug }}</span></td>
                        <td class="px-4 py-3">{{ $group->owner->full_name ?? $group->owner->email }}</td>
                        <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $group->status }}</span></td>
                        <td class="px-4 py-3">{{ optional($group->created_at)->toDateString() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection