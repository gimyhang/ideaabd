<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Publisher\Models\Publisher;
use App\Models\User;

class PublisherPurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_no',
        'purchase_category',
        'publisher_memo_no',
        'publisher_id',
        'supplier_name',
        'vendor_name',
        'vendor_phone',
        'vendor_address',
        'purchase_date',
        'payment_type',
        'due_date',
        'installment_count',
        'installment_notes',
        'total_amount',
        'discount_amount',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_date'     => 'date',
        'due_date'          => 'date',
        'installment_count' => 'integer',
        'total_amount'      => 'decimal:2',
        'discount_amount'   => 'decimal:2',
        'grand_total'       => 'decimal:2',
        'paid_amount'       => 'decimal:2',
        'due_amount'        => 'decimal:2',
    ];

    public function getPartyNameAttribute(): string
    {
        if (!empty($this->vendor_name)) return $this->vendor_name;
        if (!empty($this->supplier_name)) return $this->supplier_name;
        return $this->publisher->name ?? '—';
    }

    public function getPartyPhoneAttribute(): ?string
    {
        if (!empty($this->vendor_phone)) return $this->vendor_phone;
        return $this->publisher->phone ?? null;
    }

    public function getPartyAddressAttribute(): ?string
    {
        if (!empty($this->vendor_address)) return $this->vendor_address;
        return $this->publisher->address ?? null;
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'publisher_id');
    }

    public function items()
    {
        return $this->hasMany(PublisherPurchaseItem::class, 'purchase_id');
    }

    public function payments()
    {
        return $this->hasMany(PublisherPayment::class, 'purchase_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate totals and payment status.
     */
    public function recalculate(): void
    {
        $this->total_amount = (float) $this->items()->sum('subtotal');
        $this->grand_total = max(0, $this->total_amount - (float) $this->discount_amount);
        $totalPaid = (float) $this->payments()->sum('amount');
        $this->paid_amount = $totalPaid;
        $this->due_amount = max(0, $this->grand_total - $this->paid_amount);

        if ($this->paid_amount >= $this->grand_total && $this->grand_total > 0) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'due';
        }

        $this->save();
    }
}
