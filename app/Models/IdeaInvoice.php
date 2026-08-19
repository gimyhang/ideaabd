<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IdeaInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_no',
        'type',
        'subject',
        'reference_no',
        'customer_name',
        'customer_phone',
        'customer_address',
        'invoice_date',
        'valid_until',
        'items',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_method',
        'payment_status',
        'notes',
        'terms_conditions',
        'created_by',
    ];

    protected $casts = [
        'items'        => 'array',
        'invoice_date' => 'date',
        'valid_until'  => 'date',
        'subtotal'     => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax'          => 'decimal:2',
        'grand_total'  => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'due_amount'   => 'decimal:2',
    ];

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'challan'   => 'ডেলিভারি চালান',
            'quotation' => 'কোটেশন / প্রফর্মা',
            'tender'    => 'দরপত্র / প্রস্তাবনা',
            default     => 'বিল / ক্যাশ মেমো',
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'challan'   => 'bg-info-subtle text-dark border-info',
            'quotation' => 'bg-warning-subtle text-dark border-warning',
            'tender'    => 'bg-purple-subtle text-purple border-purple',
            default     => 'bg-success-subtle text-success border-success',
        };
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accountingEntries(): HasMany
    {
        return $this->hasMany(IdeaAccountingEntry::class, 'invoice_id');
    }
}
