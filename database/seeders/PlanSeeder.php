<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::query()->updateOrCreate(
            ['code' => 'bangsa-starter-monthly'],
            [
                'name' => 'Bangsa Starter',
                'description' => 'Monthly subscription to create and run one group.',
                'price_cents' => 9900,
                'currency' => 'USD',
                'billing_interval' => 'MONTHLY',
                'is_active' => true,
            ]
        );
    }
}