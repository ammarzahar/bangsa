@extends('layouts.app')

@section('content')
<div class="bangsa-card">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h1 class="bangsa-heading">Membership Requests</h1>
            <p class="text-sm text-slate-600">Pending approvals for {{ $group->name }}</p>
        </div>
    </div>

    <div class="relative overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-100 text-xs uppercase text-slate-700">
                <tr>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Note</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $membershipRequest)
                    <tr class="border-b border-slate-200 bg-white">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $membershipRequest->user->full_name ?? $membershipRequest->user->email }}</td>
                        <td class="px-4 py-3">{{ $membershipRequest->note ?: 'No note provided.' }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">{{ $membershipRequest->status }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('groups.membership.approve', [$group->slug, $membershipRequest->id]) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('groups.membership.reject', [$group->slug, $membershipRequest->id]) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">No pending requests.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection