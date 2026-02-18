<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GroupMembership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'Invalid credentials.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($request->user()->is_platform_owner) {
            return redirect()->intended(route('dashboard.platform'));
        }

        $membership = GroupMembership::query()
            ->with('group')
            ->where('user_id', $request->user()->id)
            ->orderByRaw("CASE status WHEN 'APPROVED' THEN 0 WHEN 'PENDING' THEN 1 ELSE 2 END")
            ->orderByRaw("CASE role WHEN 'OWNER' THEN 0 WHEN 'ADMIN' THEN 1 WHEN 'MEMBER' THEN 2 ELSE 3 END")
            ->orderBy('created_at')
            ->first();

        if ($membership && $membership->group) {
            return redirect()->intended(route('dashboard.group', [$membership->group->slug]));
        }

        return redirect()->intended(route('dashboard.home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
