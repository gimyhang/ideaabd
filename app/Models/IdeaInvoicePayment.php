<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IdeaInvoicePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'customer_name',
        'customer_phone',
        'payment_no',
        'payment_date',
        'amount',
        'net_amount',
        'vat_deduction_rate',
        'vat_deduction_amount',
        'tax_deduction_rate',
        'tax_deduction_amount',
        'other_deduction_amount',
        'deduction_challan_no',
        'deduction_notes',
        'payment_method',
        'transaction_ref',
        'note',
        'recorded_by',
    ];

    protected $casts = [
        'payment_date'           => 'date',
        'amount'                 => 'decimal:2',
        'net_amount'             => 'decimal:2',
        'vat_deduction_rate'     => 'decimal:2',
        'vat_deduction_amount'   => 'decimal:2',
        'tax_deduction_rate'     => 'decimal:2',
        'tax_deduction_amount'   => 'decimal:2',
        'other_deduction_amount' => 'decimal:2',
    ];

    public function getTotalDeductionsAttribute(): float
    {
        return (float) ($this->vat_deduction_amount ?? 0)
            + (float) ($this->tax_deduction_amount ?? 0)
            + (float) ($this->other_deduction_amount ?? 0);
    }

    public function getHasDeductionsAttribute(): bool
    {
        return $this->total_deductions > 0.001;
    }

    public function getEffectiveNetAmountAttribute(): float
    {
        if ((float)($this->net_amount ?? 0) > 0) {
            return (float) $this->net_amount;
        }
        return max(0, (float)$this->amount - $this->total_deductions);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(IdeaInvoice::class, 'invoice_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getPartyNameAttribute(): string
    {
        if (!empty($this->customer_name)) {
            return $this->customer_name;
        }
        return $this->invoice?->customer_name ?? '—';
    }

    public function getPartyPhoneAttribute(): string
    {
        if (!empty($this->customer_phone)) {
            return $this->customer_phone;
        }
        return $this->invoice?->customer_phone ?? '—';
    }

    public static function paymentMethods(): array
    {
        return [
            'cash'     => 'নগদ (Cash)',
            'bank'     => 'ব্যাংক ট্রান্সফার (Bank Transfer)',
            'bkash'    => 'বিকাশ (bKash)',
            'nagad'    => 'নগদ (Nagad)',
            'rocket'   => 'রকেট (Rocket)',
            'cheque'   => 'চেক (Cheque)',
            'card'     => 'কার্ড (Card / POS)',
            'other'    => 'অন্যান্য (Other)',
        ];
    }

    public static function generatePaymentNo(): string
    {
        $dateStr = date('Ymd');
        $random = rand(1000, 9999);
        return 'RCP-' . $dateStr . '-' . $random;
    }
}
