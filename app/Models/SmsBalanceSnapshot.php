<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsBalanceSnapshot extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['balance', 'delta', 'type'];

    protected function casts(): array
    {
        return [
            'balance'    => 'float',
            'delta'      => 'float',
            'created_at' => 'datetime',
        ];
    }
}
