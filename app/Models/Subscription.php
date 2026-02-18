<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;
    use HasUuids;

    public const STATUS_TRIALING = 'TRIALING';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_PAST_DUE = 'PAST_DUE';
    public const STATUS_CANCELED = 'CANCELED';
    public const STATUS_INCOMPLETE = 'INCOMPLETE';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'owner_user_id',
        'group_id',
        'plan_id',
        'provider',
        'status',
        'provider_customer_id',
        'provider_subscription_id',
        'current_period_start',
        'current_period_end',
        'grace_period_ends_at',
        'canceled_at',
    ];

    protected function casts(): array
    {
        return [
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'grace_period_ends_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isActiveWindow(): bool
    {
        $now = now();

        if (!in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_TRIALING], true)) {
            return false;
        }

        if ($this->current_period_end === null) {
            return true;
        }

        if ($this->current_period_end->gte($now)) {
            return true;
        }

        return $this->grace_period_ends_at !== null && $this->grace_period_ends_at->gte($now);
    }
}