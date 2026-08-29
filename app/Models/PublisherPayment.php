<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Publisher\Models\Publisher;
use App\Models\User;

class PublisherPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'publisher_id',
        'vendor_name',
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

    public function getPartyNameAttribute(): string
    {
        if (!empty($this->vendor_name)) return $this->vendor_name;
        if ($this->purchase && !empty($this->purchase->party_name)) return $this->purchase->party_name;
        return $this->publisher->name ?? '—';
    }

    public function purchase()
    {
        return $this->belongsTo(PublisherPurchase::class, 'purchase_id');
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'publisher_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
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
            'other'    => 'অন্যান্য (Other)',
        ];
    }
}
