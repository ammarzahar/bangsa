<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\MembershipRequest;
use App\Models\User;

class GroupMembershipPolicy
{
    public function moderate(User $user, Group $group): bool
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

    public function updateProfile(User $user, Group $group, string $targetUserId): bool
    {
        if ($user->isAdmin() || $user->id === $targetUserId) {
            return true;
        }

        return $this->moderate($user, $group);
    }

    public function review(User $user, Group $group, MembershipRequest $request): bool
    {
        if ($request->group_id !== $group->id) {
            return false;
        }

        return $this->moderate($user, $group);
    }
}
