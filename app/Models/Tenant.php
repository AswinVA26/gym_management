<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'db_name',
        'db_host',
        'db_port',
        'db_username',
        'db_password',
        'settings',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'db_port' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
