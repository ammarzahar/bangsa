@extends('layouts.app')

@section('content')
<div class="mx-auto mt-8 w-full max-w-lg">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-gradient-to-br from-brand-50 to-cyan-50 p-6 sm:p-8">
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-brand-700">One last step</p>
            <h1 class="text-2xl font-semibold text-slate-950">Complete your Bangsa account</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Your verified Google details are ready. Choose how you plan to use Bangsa.</p>
        </div>

        <form method="POST" action="{{ route('auth.google.store') }}" class="space-y-6 p-6 sm:p-8">
            @csrf

            <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                @if($registration['avatar_url'])
                    <img src="{{ $registration['avatar_url'] }}" alt="Google profile" referrerpolicy="no-referrer" class="h-14 w-14 rounded-full object-cover ring-4 ring-white">
                @else
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-brand-600 text-lg font-semibold text-white">{{ strtoupper(substr($registration['full_name'] ?: $registration['email'], 0, 1)) }}</span>
                @endif
                <div class="min-w-0">
                    <p class="truncate font-semibold text-slate-900">{{ $registration['full_name'] ?: 'Google user' }}</p>
                    <p class="truncate text-sm text-slate-500">{{ $registration['email'] }}</p>
                    <p class="mt-1 text-xs font-medium text-emerald-700">Verified by Google</p>
                </div>
            </div>

            <div>
                <label for="full_name" class="mb-2 block text-sm font-medium text-slate-700">Full Name</label>
                <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $registration['full_name']) }}" required autocomplete="name" class="block min-h-12 w-full rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div>
                <label for="google_email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                <input id="google_email" type="email" value="{{ $registration['email'] }}" readonly class="block min-h-12 w-full cursor-not-allowed rounded-xl border-slate-200 bg-slate-100 text-sm text-slate-500">
            </div>

            <fieldset>
                <legend class="mb-3 text-sm font-medium text-slate-700">I want to use Bangsa as</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="cursor-pointer rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:ring-1 has-[:checked]:ring-brand-500">
                        <span class="flex items-start gap-3">
                            <input type="radio" name="account_type" value="{{ \App\Models\User::TYPE_USER }}" required class="mt-1 border-slate-300 text-brand-600 focus:ring-brand-500" @checked(old('account_type', \App\Models\User::TYPE_USER) === \App\Models\User::TYPE_USER)>
                            <span>
                                <span class="block font-semibold text-slate-900">Member</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">Discover and join communities.</span>
                            </span>
                        </span>
                    </label>
                    <label class="cursor-pointer rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:ring-1 has-[:checked]:ring-brand-500">
                        <span class="flex items-start gap-3">
                            <input type="radio" name="account_type" value="{{ \App\Models\User::TYPE_ORGANISER }}" required class="mt-1 border-slate-300 text-brand-600 focus:ring-brand-500" @checked(old('account_type') === \App\Models\User::TYPE_ORGANISER)>
                            <span>
                                <span class="block font-semibold text-slate-900">Organiser</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">Create and manage a community.</span>
                            </span>
                        </span>
                    </label>
                </div>
            </fieldset>

            <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-200">Complete Account</button>
        </form>
    </div>
</div>
@endsection
