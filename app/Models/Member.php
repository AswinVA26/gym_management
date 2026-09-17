<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Member extends Model
{
    use TenantScoped;

    protected $fillable = [
        'member_code',
        'name',
        'email',
        'phone',
        'address',
        'dob',
        'gender',
        'photo',
        'notes',
        'status',
        'trainer_id',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    public static function generateMemberCode(): string
    {
        return 'MB-'.strtoupper(Str::random(6));
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function workoutPlans(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function activeMembership(): ?Membership
    {
        return $this->memberships()->active()->latest('start_date')->first();
    }

    public function latestCheckIn(): ?Attendance
    {
        return $this->attendance()->whereNull('check_out')->latest('check_in')->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('member_code', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }
}
