<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use TenantScoped;

    protected $fillable = [
        'member_id',
        'check_in',
        'check_out',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'datetime',
            'check_out' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function durationMinutes(): ?int
    {
        if ($this->check_in === null || $this->check_out === null) {
            return null;
        }

        return (int) $this->check_in->diffInMinutes($this->check_out);
    }
}
