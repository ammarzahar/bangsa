<?php

namespace App\Http\Controllers;

use App\Models\GroupMembership;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function plans(Request $request)
    {
        abort_unless($this->canManageBilling($request->user()), 403, 'Only admin/owner can access billing.');

        $plans = Plan::query()->where('is_active', true)->orderBy('price_cents')->get();

        return view('billing.plans', ['plans' => $plans]);
    }

    public function index(Request $request)
    {
        abort_unless($this->canManageBilling($request->user()), 403, 'Only admin/owner can access billing.');

        $subscriptions = Subscription::query()
            ->with(['plan', 'group'])
            ->where('owner_user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return view('billing.subscriptions', ['subscriptions' => $subscriptions]);
    }

    public function store(Request $request, PaymentGatewayFactory $factory): RedirectResponse
    {
        abort_unless($this->canManageBilling($request->user()), 403, 'Only admin/owner can access billing.');

        $validated = $request->validate([
            'plan_code' => ['required', 'string', 'exists:plans,code'],
            'provider' => ['required', 'in:STRIPE,TOYYIBPAY,MANUAL'],
        ]);

        $plan = Plan::query()->where('code', $validated['plan_code'])->where('is_active', true)->firstOrFail();

        $gateway = $factory->make($validated['provider']);
        $session = $gateway->createSubscriptionSession($request->user(), $plan);

        Subscription::query()->create([
            'owner_user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'provider' => $validated['provider'],
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
            'grace_period_ends_at' => now()->addDays(37),
        ]);

        return redirect()->route('billing.subscriptions')->with('status', 'Subscription created. '.$session['message']);
    }

    private function canManageBilling(?\App\Models\User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->is_platform_owner) {
            return true;
        }

        return GroupMembership::query()
            ->where('user_id', $user->id)
            ->where('status', GroupMembership::STATUS_APPROVED)
            ->whereIn('role', [GroupMembership::ROLE_OWNER, GroupMembership::ROLE_ADMIN])
            ->exists();
    }
}
