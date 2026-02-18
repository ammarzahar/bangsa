@extends('layouts.app')

@section('content')
<div class="card">
    <h1>Group Settings: {{ $group->name }}</h1>
    <form method="POST" action="{{ route('groups.settings.update', [$group->slug]) }}">
        @csrf
        @method('PATCH')

        <label>Name</label>
        <input name="name" value="{{ old('name', $group->name) }}" required>

        <label>Description</label>
        <textarea name="description">{{ old('description', $group->description) }}</textarea>

        <label>Visibility</label>
        <select name="visibility" required>
            <option value="PUBLIC" @selected(old('visibility', $group->visibility) === 'PUBLIC')>Public</option>
            <option value="PRIVATE" @selected(old('visibility', $group->visibility) === 'PRIVATE')>Private</option>
        </select>

        <label>Logo URL</label>
        <input name="logo_url" value="{{ old('logo_url', $group->logo_url) }}">

        <label>Cover Image URL</label>
        <input name="cover_image_url" value="{{ old('cover_image_url', $group->cover_image_url) }}">

        <button type="submit">Save Settings</button>
    </form>
</div>
@endsection