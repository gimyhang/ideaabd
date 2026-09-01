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
        'opened_by',
        'status',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'current_cash' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(PosSale::class, 'register_id');
    }
}
