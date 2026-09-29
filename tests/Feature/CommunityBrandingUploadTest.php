<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommunityBrandingUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_organiser_can_upload_valid_community_branding(): void
    {
        Storage::fake('public');
        $organiser = User::factory()->create(['account_type' => User::TYPE_ORGANISER]);

        $this->actingAs($organiser)->post(route('groups.store'), [
            'slug' => 'brand-community',
            'name' => 'Brand Community',
            'visibility' => Group::VISIBILITY_PUBLIC,
            'logo' => UploadedFile::fake()->image('logo.jpg', 512, 512)->size(500),
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1600, 600)->size(1500),
        ])->assertRedirect(route('dashboard.group', ['brand-community']));

        $group = Group::query()->where('slug', 'brand-community')->firstOrFail();
        $logoPath = ltrim(str_replace('/storage/', '', parse_url($group->logo_url, PHP_URL_PATH)), '/');
        $coverPath = ltrim(str_replace('/storage/', '', parse_url($group->cover_image_url, PHP_URL_PATH)), '/');

        Storage::disk('public')->assertExists($logoPath);
        Storage::disk('public')->assertExists($coverPath);
    }

    public function test_branding_dimensions_are_validated(): void
    {
        Storage::fake('public');
        $organiser = User::factory()->create(['account_type' => User::TYPE_ORGANISER]);

        $this->actingAs($organiser)->post(route('groups.store'), [
            'slug' => 'small-branding',
            'name' => 'Small Branding',
            'visibility' => Group::VISIBILITY_PUBLIC,
            'logo' => UploadedFile::fake()->image('logo.jpg', 100, 100),
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 800, 200),
        ])->assertSessionHasErrors(['logo', 'cover_image']);

        $this->assertDatabaseMissing('groups', ['slug' => 'small-branding']);
    }
}
