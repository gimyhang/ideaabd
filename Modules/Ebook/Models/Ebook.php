<?php

declare(strict_types=1);

namespace Modules\Ebook\Models;

use App\Models\Concerns\Moderatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Author\Models\Author;
use Modules\Ebook\Models\Category;
use Modules\Review\Models\Review;
use Modules\Tag\Models\Tag;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Vendor\Models\Vendor;

class Ebook extends Model
{
    use HasFactory, Moderatable, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ebooks';

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'category_id',
        'title',
        'subtitle',
        'author_name',
        'author_role',
        'slug',
        'isbn',
        'description',
        'price',
        'discount_price',
        'cover_image',
        'file_path',
        'file_type', // pdf, epub, mobi, etc.
        'file_size',
        'pages',
        'sales_count',
        'is_active',
    ];

    /**
     * Attribute Type Casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'file_size' => 'integer',
        'pages' => 'integer',
        'sales_count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Category Relationship
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Vendor Relationship
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Author Relationship
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'ebook_author', 'ebook_id', 'author_id');
    }

    /**
     * Primary author (the `author_id` column), alongside the many-to-many above.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(\Modules\Publisher\Models\Publisher::class, 'publisher_id');
    }

    /**
     * Reviews Relationship
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'ebook_id');
    }

    /**
     * Tags Relationship
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_id', 'tag_id', 'id', 'id');
    }

    /**
     * Scope to get active ebooks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentageAttribute(): float
    {
        if ($this->price == 0) return 0;
        return round((($this->price - $this->discount_price) / $this->price) * 100, 2);
    }
}
