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
        'discount_percent',
        'total',
        'paid_cash',
        'paid_online',
        'tendered_amount',
        'change_amount',
        'payment_method',
        'trx_id',
        'items_json',
        'notes',
        'status',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'discount'         => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total'            => 'decimal:2',
        'paid_cash'        => 'decimal:2',
        'paid_online'      => 'decimal:2',
        'tendered_amount'  => 'decimal:2',
        'change_amount'    => 'decimal:2',
        'items_json'       => 'array',
        'voided_at'        => 'datetime',
    ];

    public function register(): BelongsTo
    {
        return $this->belongsTo(PosRegister::class, 'register_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function voidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function totalItemCount(): int
    {
        if (empty($this->items_json) || !is_array($this->items_json)) {
            return 0;
        }

        return array_sum(array_column($this->items_json, 'quantity'));
    }
}
