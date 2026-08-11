<?php

declare(strict_types=1);

namespace Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'promotions';

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'title',
        'description',
        'discount_type', // 'percentage', 'fixed_amount'
        'discount_value',
        'max_discount_amount',
        'min_order_amount',
        'usage_limit',
        'usage_count',
        'started_at',
        'ended_at',
        'is_active',
        'is_featured',
        'banner_image',
    ];

    /**
     * Attribute Type Casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Books in this promotion.
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(
            'Modules\Book\Models\Book',
            'promotion_book',
            'promotion_id',
            'book_id'
        );
    }

    /**
     * Scope for active promotions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('started_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ended_at')
                    ->orWhere('ended_at', '>=', now());
            });
    }

    /**
     * Scope for featured promotions.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->active();
    }

    /**
     * Check if promotion is valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->ended_at && $this->ended_at < now()) return false;
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) return false;
        return true;
    }

    /**
     * Calculate discount amount.
     */
    public function calculateDiscount(float $amount): float
    {
        if (!$this->isValid()) return 0;
        if ($amount < $this->min_order_amount) return 0;

        $discount = $this->discount_type === 'percentage'
            ? ($amount * $this->discount_value) / 100
            : $this->discount_value;

        if ($this->max_discount_amount) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return $discount;
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
