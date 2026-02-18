@extends('layouts.app')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
    <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
        <div class="flex items-start gap-4">
            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-brand-100 text-2xl font-bold text-brand-700">
                {{ strtoupper(substr($profile->full_name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $profile->full_name }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ $profile->current_role }}{{ $profile->business ? ' @ '.$profile->business : '' }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $profile->city }}{{ $profile->country ? ', '.$profile->country : '' }}</p>
                @if($profile->is_featured)
                    <span class="mt-2 inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">Featured Member</span>
                @endif
            </div>
        </div>
        @auth
            <a href="{{ route('groups.member.edit', [$group->slug, $profile->username]) }}" class="inline-flex rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Edit Profile</a>
        @endauth
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <h2 class="mb-2 text-lg font-semibold text-slate-900">About</h2>
            <p class="text-sm leading-6 text-slate-600">{{ $profile->short_bio ?: 'No bio provided yet.' }}</p>
        </div>

        <div>
            <h2 class="mb-3 text-lg font-semibold text-slate-900">Social Links</h2>
            <ul class="space-y-2 text-sm">
                @if($profile->linkedin_url)
                    <li><a href="{{ $profile->linkedin_url }}" target="_blank" class="text-brand-700 hover:underline">LinkedIn</a></li>
                @endif
                @if($profile->instagram_url)
                    <li><a href="{{ $profile->instagram_url }}" target="_blank" class="text-brand-700 hover:underline">Instagram</a></li>
                @endif
                @if($profile->facebook_url)
                    <li><a href="{{ $profile->facebook_url }}" target="_blank" class="text-brand-700 hover:underline">Facebook</a></li>
                @endif
                @if($profile->website_url)
                    <li><a href="{{ $profile->website_url }}" target="_blank" class="text-brand-700 hover:underline">Website</a></li>
                @endif
                @if(!$profile->linkedin_url && !$profile->instagram_url && !$profile->facebook_url && !$profile->website_url)
                    <li class="text-slate-500">No social links yet.</li>
                @endif
            </ul>
        </div>
    </div>
</div>
@endsection