<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    use TenantScoped;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'member_id',
        'plan_id',
        'start_date',
        'end_date',
        'price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'plan_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isExpired(): bool
    {
        return $this->end_date !== null && $this->end_date->isPast() && $this->status === self::STATUS_ACTIVE;
    }

    public function refreshStatus(): static
    {
        if ($this->status === self::STATUS_ACTIVE && $this->end_date !== null && $this->end_date->isPast()) {
            $this->status = self::STATUS_EXPIRED;
            $this->save();
        }

        return $this;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeExpiringIn(Builder $query, int $days = 30): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('end_date', '<=', now()->addDays($days)->toDateString())
            ->where('end_date', '>=', now()->toDateString());
    }
}
