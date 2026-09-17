<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'tenant_id', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_GYM_ADMIN = 'gym_admin';

    public const ROLE_TRAINER = 'trainer';

    public const ROLE_MEMBER = 'member';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function belongsToTenant(): bool
    {
        return $this->tenant_id !== null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isGymAdmin(): bool
    {
        return $this->role === self::ROLE_GYM_ADMIN;
    }

    public function isTrainer(): bool
    {
        return $this->role === self::ROLE_TRAINER;
    }

    public function isMember(): bool
    {
        return $this->role === self::ROLE_MEMBER;
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [self::ROLE_GYM_ADMIN, self::ROLE_TRAINER], true);
    }

    public function isActiveAccount(): bool
    {
        return (bool) $this->status;
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_GYM_ADMIN => 'Gym Admin',
            self::ROLE_TRAINER => 'Trainer',
            self::ROLE_MEMBER => 'Member',
            default => ucfirst($role),
        };
    }
}
