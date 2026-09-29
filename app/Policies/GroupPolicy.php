<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\User;

class GroupPolicy
{
    public function view(?User $user, Group $group): bool
    {
        if (in_array($group->visibility, [Group::VISIBILITY_PUBLIC, Group::VISIBILITY_PAID], true)) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return GroupMembership::query()
            ->where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('status', GroupMembership::STATUS_APPROVED)
            ->exists();
    }

    public function manage(User $user, Group $group): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return GroupMembership::query()
            ->where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('status', GroupMembership::STATUS_APPROVED)
            ->whereIn('role', [GroupMembership::ROLE_OWNER, GroupMembership::ROLE_ADMIN])
            ->exists();
    }

    public function join(User $user, Group $group): bool
    {
        if ($group->status !== Group::STATUS_ACTIVE) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return ! GroupMembership::query()
            ->where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('status', GroupMembership::STATUS_APPROVED)
            ->exists();
    }
}
