<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateReferral extends Model
{
    protected $fillable = [
        'affiliate_id',
        'order_id',
        'order_amount',
        'commission_amount',
        'visitor_ip',
        'status',
    ];

    protected $casts = [
        'order_amount'      => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
