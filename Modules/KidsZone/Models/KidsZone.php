<?php

declare(strict_types=1);

namespace Modules\KidsZone\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Book\Models\Book;

class KidsZone extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kids_zones';

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'age_group',
        'icon',
        'color',
        'banner_image',
        'featured_position',
        'is_active',
    ];

    /**
     * Attribute Type Casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'featured_position' => 'integer',
    ];

    /**
     * Books in this zone.
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'kids_zone_book', 'zone_id', 'book_id');
    }

    /**
     * Featured books for this zone.
     */
    public function featuredBooks(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'kids_zone_book', 'zone_id', 'book_id')
            ->wherePivot('is_featured', true)
            ->limit(6);
    }

    /**
     * Scope for active zones.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get age group label.
     */
    public function getAgeGroupLabelAttribute(): string
    {
        return match ($this->age_group) {
            '0-3' => 'For Babies (0-3 years)',
            '3-6' => 'For Toddlers (3-6 years)',
            '6-9' => 'For Children (6-9 years)',
            '9-12' => 'For Young Readers (9-12 years)',
            '12-16' => 'For Teenagers (12-16 years)',
            default => 'All Ages',
        };
    }
}
