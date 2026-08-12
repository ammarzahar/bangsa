<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\MembershipRequest;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_can_browse_public_communities_but_cannot_create_one(): void
    {
        $owner = $this->user('owner@example.com', User::TYPE_ORGANISER);
        $publicGroup = $this->group($owner, [
            'slug' => 'public-group',
            'name' => 'Public Group',
            'visibility' => Group::VISIBILITY_PUBLIC,
        ]);
        $this->group($owner, [
            'slug' => 'private-group',
            'name' => 'Private Group',
            'visibility' => Group::VISIBILITY_PRIVATE,
        ]);
        $user = $this->user('member@example.com', User::TYPE_USER);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee($publicGroup->name)
            ->assertDontSee('Private Group');

        $this->actingAs($user)
            ->post('/groups', [
                'slug' => 'member-group',
                'name' => 'Member Group',
                'visibility' => Group::VISIBILITY_PUBLIC,
            ])
            ->assertForbidden();
    }

    public function test_organiser_can_create_one_community(): void
    {
        $organiser = $this->user('organiser@example.com', User::TYPE_ORGANISER);

        $this->actingAs($organiser)
            ->post('/groups', [
                'slug' => 'organiser-group',
                'name' => 'Organiser Group',
                'visibility' => Group::VISIBILITY_PRIVATE,
            ])
            ->assertRedirect(route('dashboard.group', ['organiser-group']));

        $this->assertDatabaseHas('groups', [
            'slug' => 'organiser-group',
            'visibility' => Group::VISIBILITY_PRIVATE,
            'owner_id' => $organiser->id,
        ]);

        $this->actingAs($organiser)
            ->post('/groups', [
                'slug' => 'second-group',
                'name' => 'Second Group',
                'visibility' => Group::VISIBILITY_PUBLIC,
            ])
            ->assertForbidden();
    }

    public function test_private_community_can_be_joined_through_invitation_url(): void
    {
        $owner = $this->user('owner@example.com', User::TYPE_ORGANISER);
        $group = $this->group($owner, [
            'slug' => 'invite-only',
            'visibility' => Group::VISIBILITY_PRIVATE,
        ]);
        $user = $this->user('member@example.com', User::TYPE_USER);

        $this->actingAs($user)
            ->get(route('groups.show', [$group->slug]))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('groups.invite', [$group->slug, $group->invite_token]))
            ->assertRedirect(route('groups.show', [$group->slug]));

        $this->actingAs($user)
            ->get(route('groups.show', [$group->slug]))
            ->assertOk()
            ->assertSee($group->name);

        $this->actingAs($user)
            ->post(route('groups.join', [$group->slug]))
            ->assertRedirect();

        $this->assertDatabaseHas('membership_requests', [
            'group_id' => $group->id,
            'user_id' => $user->id,
            'status' => MembershipRequest::STATUS_PENDING,
        ]);
    }

    public function test_admin_can_update_user_account_type(): void
    {
        $admin = $this->user('admin@example.com', User::TYPE_ADMIN, true);
        $user = $this->user('member@example.com', User::TYPE_USER);

        $this->actingAs($admin)
            ->get(route('platform.users'))
            ->assertOk()
            ->assertSee('Manage Users')
            ->assertSee('member@example.com');

        $this->actingAs($admin)
            ->patch(route('platform.users.update', [$user->id]), [
                'account_type' => User::TYPE_ORGANISER_PLUS,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'account_type' => User::TYPE_ORGANISER_PLUS,
            'is_platform_owner' => false,
        ]);
    }

    public function test_admin_can_delete_user_from_manage_users(): void
    {
        $admin = $this->user('admin@example.com', User::TYPE_ADMIN, true);
        $user = $this->user('member@example.com', User::TYPE_USER);
        $group = $this->group($user, [
            'slug' => 'member-owned',
            'name' => 'Member Owned',
        ]);

        $this->actingAs($admin)
            ->delete(route('platform.users.delete', [$user->id]))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    public function test_admin_can_bulk_delete_checked_users(): void
    {
        $admin = $this->user('admin@example.com', User::TYPE_ADMIN, true);
        $first = $this->user('first@example.com', User::TYPE_USER);
        $second = $this->user('second@example.com', User::TYPE_ORGANISER);
        $untouched = $this->user('untouched@example.com', User::TYPE_USER);

        $this->actingAs($admin)
            ->delete(route('platform.users.bulk-delete'), [
                'user_ids' => [$first->id, $second->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $first->id]);
        $this->assertDatabaseMissing('users', ['id' => $second->id]);
        $this->assertDatabaseHas('users', ['id' => $untouched->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->user('admin@example.com', User::TYPE_ADMIN, true);

        $this->actingAs($admin)
            ->from(route('platform.users'))
            ->delete(route('platform.users.delete', [$admin->id]))
            ->assertRedirect(route('platform.users'))
            ->assertSessionHasErrors('users');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_organiser_subscription_upgrades_account_to_plus(): void
    {
        $organiser = $this->user('organiser@example.com', User::TYPE_ORGANISER);
        Plan::query()->create([
            'code' => 'plus-monthly',
            'name' => 'Organiser Plus Monthly',
            'description' => 'Monthly organiser plus plan.',
            'price_cents' => 9900,
            'currency' => 'USD',
            'billing_interval' => 'MONTHLY',
            'is_active' => true,
        ]);

        $this->actingAs($organiser)
            ->post(route('billing.subscriptions.store'), [
                'plan_code' => 'plus-monthly',
                'provider' => 'MANUAL',
            ])
            ->assertRedirect(route('billing.subscriptions'));

        $this->assertDatabaseHas('users', [
            'id' => $organiser->id,
            'account_type' => User::TYPE_ORGANISER_PLUS,
        ]);
    }

    private function user(string $email, string $accountType, bool $isPlatformOwner = false): User
    {
        return User::query()->create([
            'full_name' => Str::of($email)->before('@')->headline()->toString(),
            'email' => $email,
            'account_type' => $accountType,
            'is_platform_owner' => $isPlatformOwner,
            'password' => Hash::make('password'),
        ]);
    }

    private function group(User $owner, array $attributes = []): Group
    {
        $group = Group::query()->create(array_merge([
            'slug' => 'test-group',
            'name' => 'Test Group',
            'visibility' => Group::VISIBILITY_PUBLIC,
            'status' => Group::STATUS_ACTIVE,
            'owner_id' => $owner->id,
            'invite_token' => Str::random(40),
        ], $attributes));

        GroupMembership::query()->create([
            'group_id' => $group->id,
            'user_id' => $owner->id,
            'role' => GroupMembership::ROLE_OWNER,
            'status' => GroupMembership::STATUS_APPROVED,
            'approved_by_user_id' => $owner->id,
            'approved_at' => now(),
        ]);

        return $group;
    }
}
