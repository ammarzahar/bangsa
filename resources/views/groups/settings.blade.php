@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <h1 class="bangsa-heading">Group Settings: {{ $group->name }}</h1>
        <p class="mt-1 text-sm text-slate-600">Update community details and access controls.</p>
    </div>

    <form method="POST" action="{{ route('groups.settings.update', [$group->slug]) }}" class="bangsa-card space-y-4">
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
            </select>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="logo_url" class="mb-2 block text-sm font-medium text-slate-700">Logo URL</label>
                <input id="logo_url" name="logo_url" value="{{ old('logo_url', $group->logo_url) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div>
                <label for="cover_image_url" class="mb-2 block text-sm font-medium text-slate-700">Cover Image URL</label>
                <input id="cover_image_url" name="cover_image_url" value="{{ old('cover_image_url', $group->cover_image_url) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
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
