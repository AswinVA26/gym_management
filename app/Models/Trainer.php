<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trainer extends Model
{
    use TenantScoped;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'specialization',
        'salary',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'salary' => 'decimal:2',
            'status' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function isActive(): bool
    {
        return (bool) $this->status;
    }
}
