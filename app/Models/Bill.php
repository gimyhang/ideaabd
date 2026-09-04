<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'bill_no',
        'type',
        'subject',
        'reference_no',
        'seller_id',
        'customer_id',
        'customer_name',
        'customer_org',
        'customer_designation',
        'customer_phone',
        'customer_email',
        'customer_address',
        'items',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid_amount',
        'due_amount',
        'bill_date',
        'payment_method',
        'payment_status',
        'notes',
        'terms_conditions',
    ];

    protected $casts = [
        'items'       => 'array',
        'bill_date'   => 'date',
        'subtotal'    => 'decimal:2',
        'discount'    => 'decimal:2',
        'tax'         => 'decimal:2',
        'total'       => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount'  => 'decimal:2',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'challan'   => 'ডেলিভারি চালান',
            'quotation' => 'কোটেশন / প্রফর্মা',
            default     => 'ক্যাশ মেমো / ইনভয়েস',
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'challan'   => 'bg-info-subtle text-info border-info-subtle',
            'quotation' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
            default     => 'bg-primary-subtle text-primary border-primary-subtle',
        };
    }

    protected static function booted(): void
    {
        static::creating(function (self $bill) {
            if (empty($bill->bill_no)) {
                $prefix = match($bill->type) {
                    'challan'   => 'CH',
                    'quotation' => 'QUO',
                    default     => 'BILL',
                };
                $bill->bill_no = $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }
            if (empty($bill->bill_date)) {
                $bill->bill_date = now();
            }
        });
    }
}
