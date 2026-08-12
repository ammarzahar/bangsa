@extends('layouts.app')

@section('content')
<div class="bangsa-card">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="bangsa-heading">Manage Users</h1>
            <p class="mt-1 text-sm text-slate-600">View users and upgrade or downgrade account access.</p>
        </div>
    </div>

    <div class="relative overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-100 text-xs uppercase text-slate-700">
                <tr>
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
                            <p class="font-medium text-slate-900">{{ $user->full_name ?? 'Member' }}</p>
                            <p class="text-xs text-slate-500">{{ $user->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $user->accountTypeLabel() }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $user->groups_owned_count }}</td>
                        <td class="px-4 py-3">{{ $user->memberships_count }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('platform.users.update', [$user->id]) }}" class="flex justify-end gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="account_type" class="rounded-lg border-slate-300 py-2 text-xs focus:border-brand-500 focus:ring-brand-500">
                                    @foreach($accountTypes as $value => $label)
                                        <option value="{{ $value }}" @selected($user->account_type === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="rounded-lg bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700">Update</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
