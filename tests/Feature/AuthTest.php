<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_is_signed_in(): void
    {
        $response = $this->post('/register', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.home'));
        $this->assertAuthenticated();

        $this->get('/dashboard')->assertRedirect(route('landing'));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'full_name' => 'Test User',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::query()->create([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.home'));
        $this->assertAuthenticated();

        $this->get('/dashboard')->assertRedirect(route('landing'));
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::query()->create([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
