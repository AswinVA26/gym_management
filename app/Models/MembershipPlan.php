<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    use TenantScoped;

    protected $fillable = [
        'plan_name',
        'duration_months',
        'price',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_months' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'plan_id');
    }

    public function isActive(): bool
    {
        return (bool) $this->status;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
