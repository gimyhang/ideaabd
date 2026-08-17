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
        'customer_name',
        'customer_phone',
        'customer_address',
        'invoice_date',
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
        'created_by',
    ];

    protected $casts = [
        'items'        => 'array',
        'invoice_date' => 'date',
        'subtotal'     => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax'          => 'decimal:2',
        'grand_total'  => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'due_amount'   => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accountingEntries(): HasMany
    {
        return $this->hasMany(IdeaAccountingEntry::class, 'invoice_id');
    }
}
