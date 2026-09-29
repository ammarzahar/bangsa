@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <h1 class="bangsa-heading">Group Settings: {{ $group->name }}</h1>
        <p class="mt-1 text-sm text-slate-600">Update community details and access controls.</p>
    </div>

    <form method="POST" action="{{ route('groups.settings.update', [$group->slug]) }}" enctype="multipart/form-data" class="bangsa-card space-y-4">
        @csrf
        @method('PATCH')

        <div>
            <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Name</label>
            <input id="name" name="name" value="{{ old('name', $group->name) }}" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Description</label>
            <textarea id="description" name="description" rows="4" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('description', $group->description) }}</textarea>
        </div>

        <div>
            <label for="visibility" class="mb-2 block text-sm font-medium text-slate-700">Visibility</label>
            <select id="visibility" name="visibility" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="PUBLIC" @selected(old('visibility', $group->visibility) === 'PUBLIC')>Public</option>
                <option value="PRIVATE" @selected(old('visibility', $group->visibility) === 'PRIVATE')>Private</option>
                <option value="PAID" @selected(old('visibility', $group->visibility) === 'PAID')>Paid via TAUT</option>
            </select>
        </div>

        <div>
            <label for="taut_checkout_url" class="mb-2 block text-sm font-medium text-slate-700">TAUT Checkout URL <span class="font-normal text-slate-400">(required for paid communities)</span></label>
            <input id="taut_checkout_url" name="taut_checkout_url" type="url" value="{{ old('taut_checkout_url', $group->taut_checkout_url) }}" placeholder="https://your-store.taut.my/checkout/123" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="logo" class="mb-2 block text-sm font-medium text-slate-700">Community Logo</label>
                @if($group->logo_url)
                    <img src="{{ $group->logo_url }}" alt="Current community logo" class="mb-3 h-20 w-20 rounded-xl border border-slate-200 object-cover">
                @endif
                <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-lg border border-slate-300 bg-white text-sm file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-3 file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                <p class="mt-2 text-xs text-slate-400">JPG, PNG or WebP · max 2MB · minimum 256×256px.</p>
            </div>

            <div>
                <label for="cover_image" class="mb-2 block text-sm font-medium text-slate-700">Cover Image</label>
                @if($group->cover_image_url)
                    <img src="{{ $group->cover_image_url }}" alt="Current community cover" class="mb-3 h-20 w-full rounded-xl border border-slate-200 object-cover">
                @endif
                <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-lg border border-slate-300 bg-white text-sm file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-3 file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                <p class="mt-2 text-xs text-slate-400">JPG, PNG or WebP · max 5MB · minimum 1200×400px.</p>
            </div>
        </div>

        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save Settings</button>
    </form>

    <div class="bangsa-card">
        <h2 class="mb-2 text-lg font-semibold text-slate-900">Private Invitation URL</h2>
        <p class="mb-4 text-sm text-slate-600">Share this URL with people who should be allowed to view and request access to a private community.</p>
        <input readonly value="{{ $inviteUrl }}" class="block w-full rounded-lg border-slate-300 bg-slate-50 text-sm text-slate-700 focus:border-brand-500 focus:ring-brand-500">
    </div>
</div>
@endsection
