<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventCampaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'event_campaigns';

    protected $fillable = [
        'title',
        'slug',
        'type',
        'badge_text',
        'short_description',
        'description',
        'banner_image',
        'theme_color',
        'has_fee_or_donation',
        'fee_amount',
        'is_donation_flexible',
        'min_donation',
        'payment_methods',
        'payment_instructions',
        'custom_fields',
        'form_settings',
        'table_settings',
        'starts_at',
        'ends_at',
        'max_participants',
        'is_active',
        'success_message',
        'redirect_url',
        'contact_phone',
        'contact_email',
    ];

    protected $casts = [
        'has_fee_or_donation'  => 'boolean',
        'is_donation_flexible' => 'boolean',
        'fee_amount'           => 'decimal:2',
        'min_donation'         => 'decimal:2',
        'custom_fields'        => 'array',
        'form_settings'        => 'array',
        'table_settings'       => 'array',
        'is_active'            => 'boolean',
        'starts_at'            => 'datetime',
        'ends_at'              => 'datetime',
        'max_participants'     => 'integer',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class, 'event_campaign_id');
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/' . ltrim($this->slug, '/'));
    }

    public function isExpired(): bool
    {
        if ($this->ends_at && now()->isAfter($this->ends_at)) {
            return true;
        }
        return false;
    }

    public function isFull(): bool
    {
        if ($this->max_participants && $this->registrations()->count() >= $this->max_participants) {
            return true;
        }
        return false;
    }

    public function canAcceptRegistrations(): bool
    {
        return $this->is_active && !$this->isExpired() && !$this->isFull();
    }
}
