@extends('layouts.app')

@section('content')
<div class="bangsa-card">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="bangsa-heading">Manage Users</h1>
            <p class="mt-1 text-sm text-slate-600">View users and upgrade or downgrade account access.</p>
        </div>
    </div>

    <form id="bulkDeleteUsersForm" method="POST" action="{{ route('platform.users.bulk-delete') }}">
        @csrf
        @method('DELETE')
    </form>

    <div class="mb-3 flex justify-end">
        <button type="submit" form="bulkDeleteUsersForm" class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
            <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M10 11v6M14 11v6M9 7l1-3h4l1 3M8 7v12h8V7"/>
            </svg>
            Delete Selected
        </button>
    </div>

    <div class="relative overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-100 text-xs uppercase text-slate-700">
                <tr>
                    <th class="px-4 py-3">
                        <span class="sr-only">Select</span>
                    </th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Account Type</th>
                    <th class="px-4 py-3">Owned Groups</th>
                    <th class="px-4 py-3">Memberships</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="border-b border-slate-200 bg-white">
                        <td class="px-4 py-3">
                            @if($user->id !== auth()->id())
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" form="bulkDeleteUsersForm" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-900">{{ $user->full_name ?? 'Member' }}</p>
                            <p class="text-xs text-slate-500">{{ $user->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $user->accountTypeLabel() }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $user->groups_owned_count }}</td>
                        <td class="px-4 py-3">{{ $user->memberships_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('platform.users.update', [$user->id]) }}" class="flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="account_type" class="rounded-lg border-slate-300 py-2 text-xs focus:border-brand-500 focus:ring-brand-500">
                                        @foreach($accountTypes as $value => $label)
                                            <option value="{{ $value }}" @selected($user->account_type === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="rounded-lg bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700">Update</button>
                                </form>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('platform.users.delete', [$user->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-rose-600 text-white hover:bg-rose-700" aria-label="Delete {{ $user->email }}">
                                            <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M10 11v6M14 11v6M9 7l1-3h4l1 3M8 7v12h8V7"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
