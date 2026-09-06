<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosRegister extends Model
{
    protected $fillable = [
        'name',
        'location',
        'opening_cash',
        'current_cash',
        'closing_cash',
        'opened_by',
        'closed_by',
        'closed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'current_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'closed_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(PosSale::class, 'register_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
