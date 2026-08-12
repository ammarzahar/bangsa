<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use HasUuids;
    use Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'email',
        'password',
        'full_name',
        'is_platform_owner',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_platform_owner' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function groupsOwned(): HasMany
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class);
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(MemberProfile::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'owner_user_id');
    }

    public function membershipRequests(): HasMany
    {
        return $this->hasMany(MembershipRequest::class);
    }

    public function reviewedRequests(): HasMany
    {
        return $this->hasMany(MembershipRequest::class, 'reviewed_by_user_id');
    }
}
