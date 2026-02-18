@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
    <h1 class="mb-6 text-2xl font-semibold text-slate-900">Edit Profile: {{ $profile->full_name }}</h1>

    <form method="POST" action="{{ route('groups.member.update', [$group->slug, $profile->username]) }}" class="grid gap-4 md:grid-cols-2">
        @csrf
        @method('PATCH')

        <div>
            <label for="username" class="mb-2 block text-sm font-medium text-slate-700">Username</label>
            <input id="username" name="username" value="{{ old('username', $profile->username) }}" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="photo_url" class="mb-2 block text-sm font-medium text-slate-700">Photo URL</label>
            <input id="photo_url" name="photo_url" value="{{ old('photo_url', $profile->photo_url) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="full_name" class="mb-2 block text-sm font-medium text-slate-700">Full Name</label>
            <input id="full_name" name="full_name" value="{{ old('full_name', $profile->full_name) }}" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="current_role" class="mb-2 block text-sm font-medium text-slate-700">Current Role</label>
            <input id="current_role" name="current_role" value="{{ old('current_role', $profile->current_role) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="business" class="mb-2 block text-sm font-medium text-slate-700">Business</label>
            <input id="business" name="business" value="{{ old('business', $profile->business) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="city" class="mb-2 block text-sm font-medium text-slate-700">City</label>
            <input id="city" name="city" value="{{ old('city', $profile->city) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="country" class="mb-2 block text-sm font-medium text-slate-700">Country</label>
            <input id="country" name="country" value="{{ old('country', $profile->country) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div class="md:col-span-2">
            <label for="short_bio" class="mb-2 block text-sm font-medium text-slate-700">Short Bio</label>
            <textarea id="short_bio" name="short_bio" rows="4" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('short_bio', $profile->short_bio) }}</textarea>
        </div>

        <div>
            <label for="linkedin_url" class="mb-2 block text-sm font-medium text-slate-700">LinkedIn URL</label>
            <input id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $profile->linkedin_url) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="instagram_url" class="mb-2 block text-sm font-medium text-slate-700">Instagram URL</label>
            <input id="instagram_url" name="instagram_url" value="{{ old('instagram_url', $profile->instagram_url) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="facebook_url" class="mb-2 block text-sm font-medium text-slate-700">Facebook URL</label>
            <input id="facebook_url" name="facebook_url" value="{{ old('facebook_url', $profile->facebook_url) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <div>
            <label for="website_url" class="mb-2 block text-sm font-medium text-slate-700">Website URL</label>
            <input id="website_url" name="website_url" value="{{ old('website_url', $profile->website_url) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        @if($isAdmin)
            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $profile->is_featured) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Featured member
                </label>
            </div>
        @endif

        <div class="md:col-span-2 flex gap-3">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save Profile</button>
            <a href="{{ route('groups.member.show', [$group->slug, $profile->username]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</a>
        </div>
    </form>
</div>
@endsection