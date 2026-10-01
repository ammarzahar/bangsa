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

    public const TYPE_USER = 'USER';

    public const TYPE_ORGANISER = 'ORGANISER';

    public const TYPE_ORGANISER_PLUS = 'ORGANISER_PLUS';

    public const TYPE_ADMIN = 'ADMIN';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'email',
        'password',
        'full_name',
        'account_type',
        'is_platform_owner',
        'google_id',
        'avatar_url',
        'email_verified_at',
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

    public function isAdmin(): bool
    {
        return $this->is_platform_owner || $this->account_type === self::TYPE_ADMIN;
    }

    public function isOrganiser(): bool
    {
        return in_array($this->account_type, [self::TYPE_ORGANISER, self::TYPE_ORGANISER_PLUS, self::TYPE_ADMIN], true)
            || $this->is_platform_owner;
    }

    public function isOrganiserPlus(): bool
    {
        return in_array($this->account_type, [self::TYPE_ORGANISER_PLUS, self::TYPE_ADMIN], true)
            || $this->is_platform_owner;
    }

    public function accountTypeLabel(): string
    {
        return match ($this->account_type) {
            self::TYPE_ORGANISER => 'Organiser',
            self::TYPE_ORGANISER_PLUS => 'Organiser Plus',
            self::TYPE_ADMIN => 'Admin',
            default => 'User',
        };
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
