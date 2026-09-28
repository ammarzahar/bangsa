<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_search_active_public_communities(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();

        Group::query()->create([
            'slug' => 'usahawan-muda',
            'name' => 'Usahawan Muda',
            'description' => 'Business network for founders.',
            'visibility' => Group::VISIBILITY_PUBLIC,
            'status' => Group::STATUS_ACTIVE,
            'owner_id' => $owner->id,
        ]);

        Group::query()->create([
            'slug' => 'private-founders',
            'name' => 'Private Founders',
            'visibility' => Group::VISIBILITY_PRIVATE,
            'status' => Group::STATUS_ACTIVE,
            'owner_id' => $owner->id,
        ]);

        Group::query()->create([
            'slug' => 'inactive-network',
            'name' => 'Inactive Network',
            'visibility' => Group::VISIBILITY_PUBLIC,
            'status' => Group::STATUS_INACTIVE,
            'owner_id' => $owner->id,
        ]);

        $this->actingAs($user)
            ->get(route('communities.index', ['q' => 'usahawan']))
            ->assertOk()
            ->assertSee('Usahawan Muda')
            ->assertDontSee('Private Founders')
            ->assertDontSee('Inactive Network');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('communities.index'))
            ->assertRedirect(route('login'));
    }
}
