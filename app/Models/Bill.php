<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'bill_no', 'seller_id', 'customer_id',
        'customer_name', 'customer_phone', 'customer_email',
        'items', 'subtotal', 'discount', 'tax', 'total',
        'payment_method', 'payment_status', 'notes',
    ];

    protected $casts = ['items' => 'array'];

    public function seller()  { return $this->belongsTo(User::class, 'seller_id'); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }

    protected static function booted(): void
    {
        static::creating(function (self $bill) {
            if (empty($bill->bill_no)) {
                $bill->bill_no = 'BILL-' . strtoupper(uniqid());
            }
        });
    }
}
