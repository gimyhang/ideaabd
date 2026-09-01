<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_bdt',
        'price_usd',
        'duration_days',
        'max_devices',
        'unlimited_ebooks',
        'unlimited_audiobooks',
        'unlimited_webzines',
        'features',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'price_bdt'            => 'decimal:2',
        'price_usd'            => 'decimal:2',
        'unlimited_ebooks'     => 'boolean',
        'unlimited_audiobooks' => 'boolean',
        'unlimited_webzines'   => 'boolean',
        'is_featured'          => 'boolean',
        'is_active'            => 'boolean',
        'features'             => 'array',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }
}
