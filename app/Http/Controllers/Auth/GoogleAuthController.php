<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    private const SESSION_KEY = 'google_registration';

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'google' => 'Google sign-in could not be completed. Please try again.',
            ]);
        }

        $googleId = trim((string) $googleUser->getId());
        $email = Str::lower(trim((string) $googleUser->getEmail()));
        $emailVerified = filter_var(
            data_get($googleUser->user, 'email_verified', data_get($googleUser->user, 'verified_email', false)),
            FILTER_VALIDATE_BOOL
        );

        if ($googleId === '' || $email === '' || ! $emailVerified) {
            return redirect()->route('login')->withErrors([
                'google' => 'A verified Google email address is required.',
            ]);
        }

        $avatarUrl = $this->validAvatarUrl($googleUser->getAvatar());
        $user = User::query()->where('google_id', $googleId)->first()
            ?? User::query()->where('email', $email)->first();

        if ($user) {
            if ($user->google_id && ! hash_equals($user->google_id, $googleId)) {
                return redirect()->route('login')->withErrors([
                    'google' => 'This email is already linked to another Google account.',
                ]);
            }

            $user->forceFill([
                'google_id' => $googleId,
                'avatar_url' => $user->avatar_url ?: $avatarUrl,
                'email_verified_at' => $user->email_verified_at ?: now(),
            ])->save();

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard.home'));
        }

        $request->session()->put(self::SESSION_KEY, [
            'google_id' => $googleId,
            'email' => $email,
            'full_name' => trim((string) $googleUser->getName()),
            'avatar_url' => $avatarUrl,
            'expires_at' => now()->addMinutes(15)->timestamp,
        ]);

        return redirect()->route('auth.google.complete');
    }

    public function complete(Request $request): View|RedirectResponse
    {
        $registration = $this->pendingRegistration($request);
        if (! $registration) {
            return redirect()->route('register')->withErrors([
                'google' => 'Your Google registration session expired. Please continue with Google again.',
            ]);
        }

        return view('auth.google-complete', compact('registration'));
    }

    public function store(Request $request): RedirectResponse
    {
        $registration = $this->pendingRegistration($request);
        if (! $registration) {
            return redirect()->route('register')->withErrors([
                'google' => 'Your Google registration session expired. Please continue with Google again.',
            ]);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'account_type' => ['required', Rule::in([User::TYPE_USER, User::TYPE_ORGANISER])],
        ]);

        $existingUser = User::query()->where('email', $registration['email'])->first();
        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' => 'An account already exists for this email. Sign in and connect Google again.',
            ]);
        }

        $user = User::query()->create([
            'google_id' => $registration['google_id'],
            'avatar_url' => $registration['avatar_url'],
            'full_name' => $validated['full_name'],
            'email' => $registration['email'],
            'email_verified_at' => now(),
            'account_type' => $validated['account_type'],
            'password' => Hash::make(Str::random(64)),
        ]);

        $request->session()->forget(self::SESSION_KEY);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard.home'))->with('status', 'Google account connected. Welcome to Bangsa.');
    }

    private function pendingRegistration(Request $request): ?array
    {
        $registration = $request->session()->get(self::SESSION_KEY);

        if (
            ! is_array($registration)
            || empty($registration['google_id'])
            || empty($registration['email'])
            || (int) ($registration['expires_at'] ?? 0) < now()->timestamp
        ) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        return $registration;
    }

    private function validAvatarUrl(?string $avatarUrl): ?string
    {
        if (! $avatarUrl || strlen($avatarUrl) > 2048 || ! filter_var($avatarUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        return $avatarUrl;
    }
}
