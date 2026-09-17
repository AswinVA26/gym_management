<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use TenantScoped;

    public const METHODS = ['cash', 'card', 'upi', 'bank_transfer', 'other'];

    protected $fillable = [
        'member_id',
        'membership_id',
        'amount',
        'method',
        'transaction_id',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
