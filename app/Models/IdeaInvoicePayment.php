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
        'payment_method',
        'transaction_ref',
        'note',
        'recorded_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

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
