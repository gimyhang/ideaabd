<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    protected $fillable = [
        'user_id',
        'affiliate_code',
        'commission_rate',
        'balance',
        'total_earned',
        'total_paid',
        'payout_method',
        'payout_details',
        'status',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'balance'         => 'decimal:2',
        'total_earned'    => 'decimal:2',
        'total_paid'      => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(AffiliateReferral::class);
    }
}
