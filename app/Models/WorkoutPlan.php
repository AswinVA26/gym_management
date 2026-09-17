<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutPlan extends Model
{
    use TenantScoped;

    protected $fillable = [
        'member_id',
        'title',
        'focus',
        'level',
        'days_per_week',
        'plan',
        'generated_by',
        'ai_meta',
    ];

    protected function casts(): array
    {
        return [
            'days_per_week' => 'integer',
            'ai_meta' => 'array',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function getSummaryAttribute(): string
    {
        return mb_strimwidth(strip_tags((string) $this->plan), 0, 160, '...');
    }
}
