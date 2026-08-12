@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="bangsa-heading">Profile</h1>
    <p class="mt-1 text-sm text-slate-600">Update your account details and password.</p>
</div>

<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(280px,360px)]">
    <form method="POST" action="{{ route('profile.update') }}" class="bangsa-card space-y-5">
        @csrf
        @method('PATCH')

        <div>
            <label for="full_name" class="mb-2 block text-sm font-medium text-slate-700">Full Name</label>
            <input id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div class="border-t border-slate-200 pt-5">
            <h2 class="mb-4 text-base font-semibold text-slate-900">Change Password</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="current_password" class="mb-2 block text-sm font-medium text-slate-700">Current Password</label>
                    <input id="current_password" type="password" name="current_password" autocomplete="current-password" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-slate-700">New Password</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save Profile</button>
            <a href="{{ route('dashboard.home') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</a>
        </div>
    </form>

    <div class="bangsa-card h-fit">
        <p class="text-sm text-slate-500">Signed in as</p>
        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $user->full_name ?? 'Member' }}</p>
        <p class="mt-1 break-all text-sm text-slate-600">{{ $user->email }}</p>
    </div>
</div>
@endsection
