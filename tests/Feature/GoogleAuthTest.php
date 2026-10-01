<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_pages_show_continue_with_google(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Continue with Google');
        $this->get(route('register'))->assertOk()->assertSee('Continue with Google');
    }

    public function test_new_google_user_completes_prefilled_registration(): void
    {
        $this->mockGoogleUser($this->googleUser());

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.google.complete'))
            ->assertSessionHas('google_registration.email', 'google@example.com');

        $this->get(route('auth.google.complete'))
            ->assertOk()
            ->assertSee('Google Person')
            ->assertSee('google@example.com')
            ->assertSee('https://images.example.com/google-person.jpg');

        $this->post(route('auth.google.store'), [
            'full_name' => 'Google Person',
            'account_type' => User::TYPE_ORGANISER,
        ])->assertRedirect(route('dashboard.home'));

        $user = User::query()->where('email', 'google@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-user-123', $user->google_id);
        $this->assertSame('https://images.example.com/google-person.jpg', $user->avatar_url);
        $this->assertSame(User::TYPE_ORGANISER, $user->account_type);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_existing_email_account_is_linked_and_logged_in(): void
    {
        $existing = User::factory()->create([
            'email' => 'google@example.com',
            'google_id' => null,
            'avatar_url' => null,
        ]);
        $this->mockGoogleUser($this->googleUser());

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard.home'));

        $this->assertAuthenticatedAs($existing);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'id' => $existing->id,
            'google_id' => 'google-user-123',
            'avatar_url' => 'https://images.example.com/google-person.jpg',
        ]);
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $this->mockGoogleUser($this->googleUser(false));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    private function mockGoogleUser(GoogleUser $googleUser): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
    }

    private function googleUser(bool $verified = true): GoogleUser
    {
        return (new GoogleUser)->setRaw([
            'sub' => 'google-user-123',
            'email_verified' => $verified,
        ])->map([
            'id' => 'google-user-123',
            'nickname' => null,
            'name' => 'Google Person',
            'email' => 'google@example.com',
            'avatar' => 'https://images.example.com/google-person.jpg',
        ]);
    }
}
