@extends('layouts.app')

@section('content')
<div class="card">
    <h1>Verify Email</h1>
    <p>Please verify your email before continuing.</p>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">Resend verification link</button>
    </form>
</div>
@endsection