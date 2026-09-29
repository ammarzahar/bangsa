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
            @foreach([1 => ['Basics', 'Name and purpose'], 2 => ['Access', 'Free, private or paid'], 3 => ['Review', 'Brand and confirm']] as $number => [$label, $description])
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

    <form method="POST" action="{{ route('groups.store') }}" enctype="multipart/form-data" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-community-form>
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
                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="organization" placeholder="e.g. Usahawan Malaysia" class="block min-h-[52px] w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-base shadow-sm focus:border-brand-500 focus:ring-brand-500">
                </div>

                <div>
                    <label for="slug" class="mb-2 block text-sm font-medium text-slate-700">Community URL</label>
                    <div class="flex min-h-[52px] overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                        <span class="hidden items-center border-r border-slate-200 bg-slate-50 px-4 text-sm text-slate-500 sm:flex">bangsa.org/</span>
                        <input id="slug" name="slug" value="{{ old('slug') }}" required minlength="3" maxlength="50" pattern="[a-z0-9-]+" placeholder="usahawan-malaysia" class="min-w-0 flex-1 rounded-xl border-0 bg-white px-4 py-3 text-base focus:ring-0 sm:rounded-l-none">
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Use lowercase letters, numbers, and hyphens only.</p>
                </div>

                <div>
                    <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                    <textarea id="description" name="description" rows="5" maxlength="2000" placeholder="Describe who this community is for and what members can expect." class="block min-h-40 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-base shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('description') }}</textarea>
                </div>
            </div>
        </section>

        <section data-wizard-panel="2" class="hidden p-6 sm:p-8">
            <div class="mb-6">
                <p class="text-sm font-semibold text-brand-600">Step 2 of 3</p>
                <h2 class="mt-1 text-xl font-semibold text-slate-950">Choose community access</h2>
                <p class="mt-1 text-sm text-slate-500">Control who can discover and view your community.</p>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
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
                <label class="cursor-pointer rounded-2xl border border-slate-200 p-5 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:ring-1 has-[:checked]:ring-brand-500">
                    <span class="flex items-start gap-3">
                        <input type="radio" name="visibility" value="PAID" required class="mt-1 border-slate-300 text-brand-600 focus:ring-brand-500" @checked(old('visibility') === 'PAID')>
                        <span>
                            <span class="block font-semibold text-slate-950">Paid community</span>
                            <span class="mt-1 block text-sm leading-6 text-slate-500">Members purchase access through TAUT before approval.</span>
                        </span>
                    </span>
                </label>
            </div>

            <div data-paid-checkout-fields class="mt-6 {{ old('visibility') === 'PAID' ? '' : 'hidden' }}">
                <label for="taut_checkout_url" class="mb-2 block text-sm font-medium text-slate-700">TAUT Checkout URL</label>
                <input id="taut_checkout_url" name="taut_checkout_url" type="url" value="{{ old('taut_checkout_url') }}" placeholder="https://your-store.taut.my/checkout/123" class="block min-h-[52px] w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-base shadow-sm focus:border-brand-500 focus:ring-brand-500">
                <p class="mt-2 text-xs text-slate-400">Create the membership product in TAUT, then paste its checkout URL here.</p>
            </div>

            <div class="mt-6 rounded-xl border border-cyan-200 bg-cyan-50 p-4 text-sm leading-6 text-cyan-900">
                Free communities use admin approval. Paid communities are activated automatically after TAUT confirms payment.
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
                    <label for="logo" class="mb-2 block text-sm font-medium text-slate-700">Community Logo <span class="font-normal text-slate-400">(optional)</span></label>
                    <label for="logo" class="flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-6 text-center transition hover:border-brand-400 hover:bg-brand-50">
                        <svg class="mb-2 h-7 w-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5V19h16v-2.5M12 4v11m-4-7 4-4 4 4"/></svg>
                        <span class="text-sm font-semibold text-slate-700">Upload logo</span>
                        <span class="mt-1 text-xs text-slate-400">JPG, PNG or WebP · max 2MB</span>
                        <span class="mt-1 text-xs text-slate-400">Minimum 256×256px, square recommended</span>
                    </label>
                    <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" data-image-input data-preview-target="logo-preview">
                </div>
                <div>
                    <label for="cover_image" class="mb-2 block text-sm font-medium text-slate-700">Cover Image <span class="font-normal text-slate-400">(optional)</span></label>
                    <label for="cover_image" class="flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-6 text-center transition hover:border-brand-400 hover:bg-brand-50">
                        <svg class="mb-2 h-7 w-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5V19h16v-2.5M12 4v11m-4-7 4-4 4 4"/></svg>
                        <span class="text-sm font-semibold text-slate-700">Upload cover image</span>
                        <span class="mt-1 text-xs text-slate-400">JPG, PNG or WebP · max 5MB</span>
                        <span class="mt-1 text-xs text-slate-400">Minimum 1200×400px</span>
                    </label>
                    <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" data-image-input data-preview-target="cover-preview">
                </div>
            </div>

            <div class="relative mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                <img id="cover-preview" alt="Cover preview" class="hidden h-32 w-full object-cover">
                <div class="p-5">
                <p class="mb-4 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Community Preview</p>
                <div class="flex items-start gap-4">
                    <span class="relative flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-600 text-lg font-bold text-white">
                        <span data-review-initial>C</span>
                        <img id="logo-preview" alt="Logo preview" class="absolute inset-0 hidden h-full w-full object-cover">
                    </span>
                    <div class="min-w-0">
                        <h3 class="truncate text-lg font-semibold text-slate-950" data-review-name>Your community</h3>
                        <p class="truncate text-sm font-medium text-brand-600">bangsa.org/<span data-review-slug>community-url</span></p>
                        <p class="mt-2 text-sm text-slate-500"><span data-review-visibility>Public</span> community</p>
                    </div>
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
