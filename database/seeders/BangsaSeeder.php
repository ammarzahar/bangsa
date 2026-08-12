<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\MemberProfile;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BangsaSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::query()->where('code', 'bangsa-starter-monthly')->firstOrFail();

        $owner = User::query()->updateOrCreate(
            ['email' => 'owner@bangsa.org'],
            [
                'full_name' => 'Platform Owner',
                'password' => Hash::make('Bangsa123!'),
                'account_type' => User::TYPE_ADMIN,
                'is_platform_owner' => true,
                'email_verified_at' => now(),
            ]
        );

        foreach (['prasassti' => 'Prasassti', 'usahawan' => 'Usahawan'] as $slug => $name) {
            $group = Group::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $slug === 'prasassti'
                        ? 'Private network for professionals and founders.'
                        : 'Networking community for entrepreneurs and business owners.',
                    'visibility' => Group::VISIBILITY_PUBLIC,
                    'status' => Group::STATUS_ACTIVE,
                    'owner_id' => $owner->id,
                ]
            );

            GroupMembership::query()->updateOrCreate(
                [
                    'group_id' => $group->id,
                    'user_id' => $owner->id,
                ],
                [
                    'role' => GroupMembership::ROLE_OWNER,
                    'status' => GroupMembership::STATUS_APPROVED,
                    'approved_by_user_id' => $owner->id,
                    'approved_at' => now(),
                ]
            );

            MemberProfile::query()->updateOrCreate(
                [
                    'group_id' => $group->id,
                    'user_id' => $owner->id,
                ],
                [
                    'username' => 'platform-owner',
                    'full_name' => 'Platform Owner',
                    'short_bio' => 'Owner and operator of Bangsa platform.',
                    'current_role' => 'Platform Administrator',
                    'business' => 'Bangsa',
                    'city' => 'Kuala Lumpur',
                    'country' => 'Malaysia',
                    'website_url' => 'https://bangsa.org',
                ]
            );

            Subscription::query()->updateOrCreate(
                ['group_id' => $group->id],
                [
                    'owner_user_id' => $owner->id,
                    'plan_id' => $plan->id,
                    'provider' => 'MANUAL',
                    'status' => Subscription::STATUS_ACTIVE,
                    'current_period_start' => now(),
                    'current_period_end' => now()->addDays(30),
                    'grace_period_ends_at' => now()->addDays(37),
                ]
            );
        }
    }
}
