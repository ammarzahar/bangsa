@extends('layouts.app')

@section('content')
<div class="mx-auto mt-8 w-full max-w-md">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="mb-1 text-2xl font-semibold text-slate-900">Forgot password</h1>
        <p class="mb-6 text-sm text-slate-600">We will send a password reset link to your email.</p>

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Send Reset Link</button>
        </form>
    </div>
</div>
@endsection