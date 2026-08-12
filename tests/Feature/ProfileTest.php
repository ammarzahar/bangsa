<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile_page(): void
    {
        $user = User::query()->create([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Profile')
            ->assertSee('test@example.com');
    }

    public function test_user_can_update_profile_details(): void
    {
        $user = User::query()->create([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->patch('/profile', [
                'full_name' => 'Updated User',
                'email' => 'updated@example.com',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'full_name' => 'Updated User',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_can_update_password_with_current_password(): void
    {
        $user = User::query()->create([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->patch('/profile', [
                'full_name' => 'Test User',
                'email' => 'test@example.com',
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_current_password_is_required_to_update_password(): void
    {
        $user = User::query()->create([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'full_name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
