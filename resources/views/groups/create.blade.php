@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl" data-community-wizard>
    <div class="mb-7">
        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-brand-600">Community Setup</p>
        <h1 class="text-3xl font-semibold tracking-tight text-slate-950">Create Community</h1>
        <p class="mt-2 text-sm text-slate-500">Set up your community in three simple steps. You can update these details later.</p>
    </div>

    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <ol class="grid grid-cols-3 gap-2" aria-label="Community creation progress">
            @foreach([1 => ['Basics', 'Name and purpose'], 2 => ['Access', 'Public or private'], 3 => ['Review', 'Brand and confirm']] as $number => [$label, $description])
                <li>
                    <button type="button" data-wizard-step-button="{{ $number }}" class="flex w-full items-center gap-3 rounded-xl p-2 text-left transition sm:p-3" aria-current="{{ $number === 1 ? 'step' : 'false' }}">
                        <span data-wizard-step-circle class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border text-sm font-bold">{{ $number }}</span>
                        <span class="hidden min-w-0 sm:block">
                            <span data-wizard-step-label class="block text-sm font-semibold">{{ $label }}</span>
                            <span class="mt-0.5 block truncate text-xs text-slate-400">{{ $description }}</span>
                        </span>
                    </button>
                </li>
            @endforeach
        </ol>
    </div>

    <form method="POST" action="{{ route('groups.store') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-community-form>
        @csrf

        <section data-wizard-panel="1" class="p-6 sm:p-8">
            <div class="mb-6">
                <p class="text-sm font-semibold text-brand-600">Step 1 of 3</p>
                <h2 class="mt-1 text-xl font-semibold text-slate-950">Community basics</h2>
                <p class="mt-1 text-sm text-slate-500">Choose a clear name and tell people what this community is about.</p>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Community Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="organization" placeholder="e.g. Usahawan Malaysia" class="block w-full rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>

                <div>
                    <label for="slug" class="mb-2 block text-sm font-medium text-slate-700">Community URL</label>
                    <div class="flex rounded-xl border border-slate-300 bg-white focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                        <span class="hidden items-center border-r border-slate-200 bg-slate-50 px-3 text-sm text-slate-500 sm:flex">bangsa.org/</span>
                        <input id="slug" name="slug" value="{{ old('slug') }}" required minlength="3" maxlength="50" pattern="[a-z0-9-]+" placeholder="usahawan-malaysia" class="min-w-0 flex-1 rounded-xl border-0 text-sm focus:ring-0 sm:rounded-l-none">
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Use lowercase letters, numbers, and hyphens only.</p>
                </div>

                <div>
                    <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                    <textarea id="description" name="description" rows="5" maxlength="2000" placeholder="Describe who this community is for and what members can expect." class="block w-full rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('description') }}</textarea>
                </div>
            </div>
        </section>

        <section data-wizard-panel="2" class="hidden p-6 sm:p-8">
            <div class="mb-6">
                <p class="text-sm font-semibold text-brand-600">Step 2 of 3</p>
                <h2 class="mt-1 text-xl font-semibold text-slate-950">Choose community access</h2>
                <p class="mt-1 text-sm text-slate-500">Control who can discover and view your community.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="cursor-pointer rounded-2xl border border-slate-200 p-5 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:ring-1 has-[:checked]:ring-brand-500">
                    <span class="flex items-start gap-3">
                        <input type="radio" name="visibility" value="PUBLIC" required class="mt-1 border-slate-300 text-brand-600 focus:ring-brand-500" @checked(old('visibility', 'PUBLIC') === 'PUBLIC')>
                        <span>
                            <span class="block font-semibold text-slate-950">Public community</span>
                            <span class="mt-1 block text-sm leading-6 text-slate-500">Anyone can discover the community and browse its public directory.</span>
                        </span>
                    </span>
                </label>
                <label class="cursor-pointer rounded-2xl border border-slate-200 p-5 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:ring-1 has-[:checked]:ring-brand-500">
                    <span class="flex items-start gap-3">
                        <input type="radio" name="visibility" value="PRIVATE" required class="mt-1 border-slate-300 text-brand-600 focus:ring-brand-500" @checked(old('visibility') === 'PRIVATE')>
                        <span>
                            <span class="block font-semibold text-slate-950">Private community</span>
                            <span class="mt-1 block text-sm leading-6 text-slate-500">Only invited and approved members can access the community.</span>
                        </span>
                    </span>
                </label>
            </div>

            <div class="mt-6 rounded-xl border border-cyan-200 bg-cyan-50 p-4 text-sm leading-6 text-cyan-900">
                All join requests still require admin approval, regardless of visibility.
            </div>
        </section>

        <section data-wizard-panel="3" class="hidden p-6 sm:p-8">
            <div class="mb-6">
                <p class="text-sm font-semibold text-brand-600">Step 3 of 3</p>
                <h2 class="mt-1 text-xl font-semibold text-slate-950">Brand and review</h2>
                <p class="mt-1 text-sm text-slate-500">Add optional images, then review your community before creating it.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="logo_url" class="mb-2 block text-sm font-medium text-slate-700">Logo URL <span class="font-normal text-slate-400">(optional)</span></label>
                    <input id="logo_url" name="logo_url" type="url" value="{{ old('logo_url') }}" placeholder="https://example.com/logo.png" class="block w-full rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label for="cover_image_url" class="mb-2 block text-sm font-medium text-slate-700">Cover Image URL <span class="font-normal text-slate-400">(optional)</span></label>
                    <input id="cover_image_url" name="cover_image_url" type="url" value="{{ old('cover_image_url') }}" placeholder="https://example.com/cover.jpg" class="block w-full rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>

            <div class="mt-7 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <p class="mb-4 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Community Preview</p>
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-lg font-bold text-white" data-review-initial>C</span>
                    <div class="min-w-0">
                        <h3 class="truncate text-lg font-semibold text-slate-950" data-review-name>Your community</h3>
                        <p class="truncate text-sm font-medium text-brand-600">bangsa.org/<span data-review-slug>community-url</span></p>
                        <p class="mt-2 text-sm text-slate-500"><span data-review-visibility>Public</span> community</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-4 sm:px-8">
            <button type="button" data-wizard-back class="invisible rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back</button>
            <button type="button" data-wizard-next class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">Continue</button>
            <button type="submit" data-wizard-submit class="hidden rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">Create Community</button>
        </div>
    </form>
</div>
@endsection
