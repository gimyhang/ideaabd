<?php

declare(strict_types=1);

namespace Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HotDeal extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hot_deals';

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'discount_percentage',
        'banner_image',
        'icon',
        'badge_color',
        'started_at',
        'ended_at',
        'is_active',
        'featured_position',
        'order_count',
    ];

    /**
     * Attribute Type Casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
        'featured_position' => 'integer',
        'order_count' => 'integer',
    ];

    /**
     * Books in this hot deal.
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(
            'Modules\Book\Models\Book',
            'hot_deal_book',
            'hot_deal_id',
            'book_id'
        );
    }

    /**
     * Scope for active hot deals.
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
     * Scope for featured hot deals.
     */
    public function scopeFeatured($query)
    {
        return $query->active()->orderBy('featured_position')->orderBy('order_count', 'desc');
    }

    /**
     * Check if deal is still active.
     */
    public function isActive(): bool
    {
        if (!$this->is_active) return false;
        if ($this->ended_at && $this->ended_at < now()) return false;
        return true;
    }

    /**
     * Get remaining time in hours.
     */
    public function getRemainingHours(): int
    {
        if (!$this->isActive() || !$this->ended_at) return 0;
        return (int) now()->diffInHours($this->ended_at);
    }

    /**
     * Increment order count.
     */
    public function incrementOrderCount(): void
    {
        $this->increment('order_count');
    }
}
