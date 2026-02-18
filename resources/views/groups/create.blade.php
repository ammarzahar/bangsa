@extends('layouts.app')

@section('content')
<div class="card">
    <h1>Create Group</h1>
    <form method="POST" action="{{ route('groups.store') }}">
        @csrf
        <label>Group Slug</label>
        <input name="slug" value="{{ old('slug') }}" required>

        <label>Group Name</label>
        <input name="name" value="{{ old('name') }}" required>

        <label>Description</label>
        <textarea name="description">{{ old('description') }}</textarea>

        <label>Visibility</label>
        <select name="visibility" required>
            <option value="PUBLIC">Public</option>
            <option value="PRIVATE">Private</option>
        </select>

        <label>Logo URL</label>
        <input name="logo_url" value="{{ old('logo_url') }}">

        <label>Cover Image URL</label>
        <input name="cover_image_url" value="{{ old('cover_image_url') }}">

        <button type="submit">Create Group</button>
    </form>
</div>
@endsection