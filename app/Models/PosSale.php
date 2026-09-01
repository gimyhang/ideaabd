<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSale extends Model
{
    protected $fillable = [
        'register_id',
        'receipt_no',
        'cashier_id',
        'customer_name',
        'customer_phone',
        'subtotal',
        'discount',
        'total',
        'paid_cash',
        'paid_online',
        'payment_method',
        'items_json',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'discount'    => 'decimal:2',
        'total'       => 'decimal:2',
        'paid_cash'   => 'decimal:2',
        'paid_online' => 'decimal:2',
        'items_json'  => 'array',
    ];

    public function register(): BelongsTo
    {
        return $this->belongsTo(PosRegister::class, 'register_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
