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
        do {
            $candidate = 'RCP-' . date('Ymd') . '-' . random_int(100000, 999999);
        } while (static::withTrashed()->where('payment_no', $candidate)->exists());

        return $candidate;
    }

    /**
     * Record a payment against an invoice together with its income entry.
     * Income is booked at the cash actually received (net of VAT/tax withheld).
     */
    public static function record(IdeaInvoice $invoice, array $data, ?int $userId = null): self
    {
        $amount = round((float) $data['amount'], 2);
        $deductions = round(
            (float) ($data['vat_deduction_amount'] ?? 0)
            + (float) ($data['tax_deduction_amount'] ?? 0)
            + (float) ($data['other_deduction_amount'] ?? 0),
            2
        );
        $net = round(max(0, (float) ($data['net_amount'] ?? 0) > 0 ? (float) $data['net_amount'] : $amount - $deductions), 2);
        $payNo = static::generatePaymentNo();

        $payment = static::create(array_merge($data, [
            'invoice_id'     => $invoice->id,
            'customer_name'  => $invoice->customer_name,
            'customer_phone' => $invoice->customer_phone,
            'payment_no'     => $payNo,
            'amount'         => $amount,
            'net_amount'     => $net,
            'recorded_by'    => $userId,
        ]));

        $notes = trim(($data['note'] ?? '') . " Receipt #{$payNo}");
        if ($deductions > 0.001) {
            $notes .= " | Gross ৳" . number_format($amount, 2) . ", withheld ৳" . number_format($deductions, 2);
        }

        IdeaAccountingEntry::create([
            'entry_no'       => IdeaAccountingEntry::generateEntryNo('INC'),
            'type'           => 'income',
            'category'       => $invoice->incomeCategory(),
            'title'          => "Invoice #{$invoice->invoice_no} payment — {$invoice->customer_name}",
            'amount'         => $net,
            'entry_date'     => $data['payment_date'],
            'payment_method' => $data['payment_method'] ?? null,
            'party_name'     => $invoice->customer_name,
            'voucher_no'     => $payNo,
            'invoice_id'     => $invoice->id,
            'notes'          => $notes,
            'created_by'     => $userId,
        ]);

        return $payment;
    }

    /** Valid user id or null (avoids FK failures for stale sessions). */
    public static function actorId(): ?int
    {
        $id = auth()->id();

        return $id && \Illuminate\Support\Facades\DB::table('users')->where('id', $id)->exists() ? (int) $id : null;
    }
}
