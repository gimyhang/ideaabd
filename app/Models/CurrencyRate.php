<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate_to_bdt',
        'is_default',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'exchange_rate_to_bdt' => 'decimal:4',
        'is_default'           => 'boolean',
        'is_active'            => 'boolean',
        'last_synced_at'       => 'datetime',
    ];
}
