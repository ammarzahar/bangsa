@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-6">
        <h1 class="bangsa-heading">Create Community</h1>
        <p class="mt-1 text-sm text-slate-600">Organisers can create one community and choose whether it is public or private.</p>
    </div>

    <form method="POST" action="{{ route('groups.store') }}" class="bangsa-card space-y-4">
        @csrf
        <div>
            <label for="slug" class="mb-2 block text-sm font-medium text-slate-700">Community Slug</label>
            <input id="slug" name="slug" value="{{ old('slug') }}" required placeholder="my-community" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Community Name</label>
            <input id="name" name="name" value="{{ old('name') }}" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Description</label>
            <textarea id="description" name="description" rows="4" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="visibility" class="mb-2 block text-sm font-medium text-slate-700">Visibility</label>
            <select id="visibility" name="visibility" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="PUBLIC">Public</option>
                <option value="PRIVATE">Private</option>
            </select>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="logo_url" class="mb-2 block text-sm font-medium text-slate-700">Logo URL</label>
                <input id="logo_url" name="logo_url" value="{{ old('logo_url') }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div>
                <label for="cover_image_url" class="mb-2 block text-sm font-medium text-slate-700">Cover Image URL</label>
                <input id="cover_image_url" name="cover_image_url" value="{{ old('cover_image_url') }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
        </div>

        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Create Community</button>
    </form>
</div>
@endsection
