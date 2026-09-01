<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'starts_at',
        'expires_at',
        'status',
        'payment_method',
        'transaction_id',
        'amount_paid',
        'currency',
        'auto_renew',
    ];

    protected $casts = [
        'starts_at'   => 'datetime',
        'expires_at'  => 'datetime',
        'amount_paid' => 'decimal:2',
        'auto_renew'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at && $this->expires_at->isFuture();
    }
}
