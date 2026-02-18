@extends('layouts.app')

@section('content')
<div class="mx-auto mt-8 w-full max-w-md">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="mb-1 text-2xl font-semibold text-slate-900">Welcome back</h1>
        <p class="mb-6 text-sm text-slate-600">Sign in to manage your Bangsa communities.</p>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                <input id="password" type="password" name="password" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Remember me
                </label>
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-700 hover:underline">Forgot password?</a>
            </div>

            <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Sign In</button>
        </form>
    </div>
</div>
@endsection