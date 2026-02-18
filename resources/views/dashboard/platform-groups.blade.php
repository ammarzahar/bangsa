@extends('layouts.app')

@section('content')
<div class="bangsa-card">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h1 class="bangsa-heading">Manage Groups</h1>
            <p class="mt-1 text-sm text-slate-600">Suspend or activate any tenant group from the platform console.</p>
        </div>
    </div>

    <div class="relative overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-100 text-xs uppercase text-slate-700">
                <tr>
                    <th class="px-4 py-3">Group</th>
                    <th class="px-4 py-3">Owner</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                    <tr class="border-b border-slate-200 bg-white">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $group->name }} <span class="text-xs text-slate-500">/{{ $group->slug }}</span></td>
                        <td class="px-4 py-3">{{ $group->owner->full_name ?? $group->owner->email }}</td>
                        <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $group->status }}</span></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('platform.groups.suspend', [$group->id]) }}" class="flex gap-2">
                                    @csrf
                                    <input name="reason" placeholder="Reason" class="rounded-lg border-slate-300 px-2 py-1 text-xs focus:border-brand-500 focus:ring-brand-500">
                                    <button type="submit" class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">Suspend</button>
                                </form>
                                <form method="POST" action="{{ route('platform.groups.activate', [$group->id]) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Activate</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $groups->links() }}</div>
</div>
@endsection