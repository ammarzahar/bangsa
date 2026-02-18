<?php

namespace App\Providers;

use App\Models\Group;
use App\Models\GroupMembership;
use App\Policies\GroupMembershipPolicy;
use App\Policies\GroupPolicy;
use App\Support\CurrentGroup;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrentGroup::class, fn () => new CurrentGroup());
    }

    public function boot(): void
    {
        Gate::policy(Group::class, GroupPolicy::class);
        Gate::policy(GroupMembership::class, GroupMembershipPolicy::class);
    }
}