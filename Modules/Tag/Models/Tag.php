<?php

declare(strict_types=1);

namespace Modules\Tag\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tags';

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'color',
        'icon',
        'is_active',
        'usage_count',
    ];

    /**
     * Attribute Type Casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    /**
     * Polymorphic relation to taggable items.
     *
     * @return MorphToMany
     */
    public function taggables()
    {
        return $this->morphedByMany(
            null,
            'taggable',
            'taggables',
            'tag_id',
            'taggable_id',
            'id',
            'id'
        );
    }

    /**
     * Get books with this tag.
     *
     * @return MorphToMany
     */
    public function books()
    {
        return $this->morphedByMany(
            'Modules\Book\Models\Book',
            'taggable',
            'taggables'
        );
    }

    /**
     * Get ebooks with this tag.
     *
     * @return MorphToMany
     */
    public function ebooks()
    {
        return $this->morphedByMany(
            'Modules\Ebook\Models\Ebook',
            'taggable',
            'taggables'
        );
    }

    /**
     * Scope to get active tags.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category.
     *
     * @param $query
     * @param string $category
     * @return mixed
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Increment usage count.
     *
     * @return void
     */
    public function incrementUsageCount(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Decrement usage count.
     *
     * @return void
     */
    public function decrementUsageCount(): void
    {
        if ($this->usage_count > 0) {
            $this->decrement('usage_count');
        }
    }
}
