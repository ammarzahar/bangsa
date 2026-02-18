@extends('layouts.app')

@section('content')
<div class="card">
    <h1>Plans</h1>
    @foreach($plans as $plan)
        <div class="card">
            <strong>{{ $plan->name }}</strong>
            <p>{{ $plan->description }}</p>
            <p>${{ number_format($plan->price_cents / 100, 2) }} / month</p>

            <form method="POST" action="{{ route('billing.subscriptions.store') }}">
                @csrf
                <input type="hidden" name="plan_code" value="{{ $plan->code }}">
                <label>Provider</label>
                <select name="provider">
                    <option value="STRIPE">Stripe</option>
                    <option value="TOYYIBPAY">Toyyibpay</option>
                    <option value="MANUAL">Manual</option>
                </select>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    @endforeach
</div>
@endsection