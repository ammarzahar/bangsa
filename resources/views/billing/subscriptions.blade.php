@extends('layouts.app')

@section('content')
<div class="card">
    <h1>My Subscriptions</h1>
    @foreach($subscriptions as $subscription)
        <div class="card">
            <strong>{{ $subscription->plan->name }}</strong>
            <div>Status: {{ $subscription->status }}</div>
            <div>Provider: {{ $subscription->provider }}</div>
            <div>Group: {{ $subscription->group?->slug ?? 'Unassigned' }}</div>
            <div>Current period end: {{ optional($subscription->current_period_end)->toDateString() }}</div>
            <div>Grace end: {{ optional($subscription->grace_period_ends_at)->toDateString() }}</div>
        </div>
    @endforeach
</div>
@endsection