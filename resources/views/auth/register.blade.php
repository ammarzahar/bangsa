@extends('layouts.app')

@section('content')
<div class="mx-auto mt-8 w-full max-w-md">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="mb-1 text-2xl font-semibold text-slate-900">Create account</h1>
        <p class="mb-6 text-sm text-slate-600">Start your networking SaaS journey on Bangsa.</p>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label for="full_name" class="mb-2 block text-sm font-medium text-slate-700">Full Name</label>
                <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                <input id="password" type="password" name="password" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>

            <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Create Account</button>
        </form>
    </div>
</div>
@endsection