<?php

declare(strict_types=1);

namespace Modules\Book\Models;

use App\Models\Concerns\Moderatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Author\Models\Author;
use Modules\Book\Models\Category;
use Modules\Review\Models\Review;
use Modules\Tag\Models\Tag;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Vendor\Models\Vendor;

class Book extends Model
{
    use HasFactory, Moderatable, SoftDeletes;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'category_id',
        'sub_category_name',
        'ekushey_category',
        'genre_category',
        'audience_category',
        'publisher_id',
        'title',
        'title_en',
        'subtitle',
        'author_name',
        'author_role',
        'author_link_id',
        'translator_name',
        'editor_name',
        'rewriter_name',
        'cover_artist',
        'author_bio',
        'author_image',
        'slug',
        'isbn',
        'sku',
        'summary',
        'description',
        'published_at',
        'edition',
        'paper_type',
        'book_size',
        'book_height_cm',
        'book_width_cm',
        'weight',
        'stock_status',
        'pre_order_release_date',
        'pre_order_note',
        'stock_quantity',
        'format', // 'printed', 'ebook', 'both'
        'cover_type', // 'paperback', 'hardcover', 'both'
        'price', // Paperback regular price or default price
        'discount_price', // Paperback discount price
        'cost_price', // Cost price / wholesale cost
        'hardcover_price', // Hardcover regular price
        'hardcover_discount_price', // Hardcover discount price
        'page_count',
        'language',
        'country',
        'product_type',
        'cover_image',
        'sample_pdf_path',
        'look_inside_type',
        'look_inside_images',
        'preview_pages',
        'sales_count',
        'is_active',
        'mod_status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'submitted_by',
        'created_by',
        'owner_name',
        'owner_phone',
    ];

    /**
     * Attribute Type Casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'hardcover_price' => 'decimal:2',
        'hardcover_discount_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'page_count' => 'integer',
        'weight' => 'integer',
        'sales_count' => 'integer',
        'preview_pages' => 'integer',
        'is_active' => 'boolean',
        'published_at' => 'date',
        'pre_order_release_date' => 'date',
    ];

    protected static function booted()
    {
        static::saving(function ($book) {
            // Auto resolve or unify author if author_name is provided but author_link_id is missing
            if (empty($book->author_link_id) && !empty($book->author_name)) {
                $author = \Modules\Author\Models\Author::findOrCreateUnified([
                    'name'      => trim((string) $book->author_name),
                    'is_active' => true,
                ]);
                $book->author_link_id = $author->id;
                $book->author_name = $author->name;
            } elseif (!empty($book->author_link_id) && empty($book->author_name)) {
                $book->author_name = \Modules\Author\Models\Author::where('id', $book->author_link_id)->value('name');
            }
        });

        static::saved(function ($book) {
            // When saved anywhere, sync author_link_id to book_author pivot table
            if ($book->author_link_id && \Illuminate\Support\Facades\Schema::hasTable('book_author')) {
                $book->authors()->syncWithoutDetaching([$book->author_link_id]);
            }
        });
    }

    /**
     * Category Relationship (একটি বই একটি ক্যাটাগরিতে থাকে)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Publisher Relationship
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(\Modules\Publisher\Models\Publisher::class, 'publisher_id');
    }

    /**
     * Author Direct Link Relationship
     */
    public function authorLink(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_link_id');
    }

    /**
     * Authors Relationship
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_author', 'book_id', 'author_id');
    }

    /**
     * Reviews Relationship (বইয়ের রিভিউসমূহ)
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    /**
     * Tags Relationship (বইয়ের ট্যাগগুলো)
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_id', 'tag_id', 'id', 'id');
    }

    /**
     * Get Cover URL safely across all storage formats and live environments.
     */
    public function getCoverUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }
        $cover = trim($this->cover_image);
        if (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://')) {
            return $cover;
        }
        if (str_starts_with($cover, 'storage/')) {
            return asset($cover);
        }
        if (str_starts_with($cover, '/storage/')) {
            return asset(ltrim($cover, '/'));
        }
        if (str_starts_with($cover, 'images/')) {
            return asset($cover);
        }
        return asset('storage/' . ltrim($cover, '/'));
    }

    /**
     * Get Author Avatar URL (either from author link or custom uploaded author image)
     */
    public function getAuthorPhotoUrlAttribute(): ?string
    {
        if ($this->author_image) {
            $img = trim($this->author_image);
            if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                return $img;
            }
            return asset(str_starts_with($img, 'storage/') ? $img : 'storage/' . ltrim($img, '/'));
        }

        $linkedAuthor = $this->authorLink ?: $this->authors->first();
        if ($linkedAuthor && $linkedAuthor->avatar) {
            $av = trim($linkedAuthor->avatar);
            if (str_starts_with($av, 'http://') || str_starts_with($av, 'https://')) {
                return $av;
            }
            return asset(str_starts_with($av, 'storage/') ? $av : 'storage/' . ltrim($av, '/'));
        }

        return null;
    }

    /**
     * Get Author Bio Text (from authorLink, first author relation, or direct field)
     */
    public function getAuthorBioTextAttribute(): ?string
    {
        if (!empty($this->author_bio)) {
            return $this->author_bio;
        }
        $linkedAuthor = $this->authorLink ?: $this->authors->first();
        return $linkedAuthor?->bio;
    }

    /**
     * Human-readable stock status label in Bengali
     */
    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'স্টক শেষ (Out of Stock)',
            'pre_order'    => 'প্রি-অর্ডার চলছে (Pre-Order)',
            'upcoming'     => 'আসন্ন প্রকাশনা (Upcoming)',
            'backorder'    => 'মুদ্রণ সাপেক্ষে (Backorder)',
            default        => 'স্টকে আছে (In Stock)',
        };
    }

    /**
     * Check if book has separate hardcover pricing
     */
    public function getHasHardcoverAttribute(): bool
    {
        return in_array($this->cover_type, ['hardcover', 'both'], true)
            || ($this->hardcover_price && (float) $this->hardcover_price > 0)
            || $this->format === 'hardcover';
    }

    /**
     * Final effective paperback price
     */
    public function getEffectivePaperbackPriceAttribute(): float
    {
        if ($this->discount_price && $this->discount_price > 0 && $this->discount_price < $this->price) {
            return (float) $this->discount_price;
        }
        return (float) ($this->price ?? 0);
    }

    /**
     * Final effective hardcover price
     */
    public function getEffectiveHardcoverPriceAttribute(): float
    {
        if ($this->hardcover_discount_price && $this->hardcover_discount_price > 0 && $this->hardcover_discount_price < $this->hardcover_price) {
            return (float) $this->hardcover_discount_price;
        }
        return (float) ($this->hardcover_price ?? $this->effective_paperback_price);
    }

    /**
     * Active/Selected binding Regular Price (MRP)
     */
    public function getActiveRegularPriceAttribute(): float
    {
        if ($this->cover_type === 'hardcover') {
            return (float) ($this->hardcover_price ?: ($this->price ?: 0));
        }
        return (float) ($this->price ?: ($this->hardcover_price ?: 0));
    }

    /**
     * Active/Selected binding Selling Price (with discount applied if present)
     */
    public function getActiveSellingPriceAttribute(): float
    {
        if ($this->cover_type === 'hardcover') {
            $reg = (float) ($this->hardcover_price ?: $this->price);
            $disc = (float) ($this->hardcover_discount_price ?: $this->discount_price);
            return ($disc > 0 && $disc < $reg) ? $disc : $reg;
        }
        $reg = (float) ($this->price ?: $this->hardcover_price);
        $disc = (float) ($this->discount_price ?: $this->hardcover_discount_price);
        return ($disc > 0 && $disc < $reg) ? $disc : $reg;
    }

    /**
     * Active/Selected binding Discount Price
     */
    public function getActiveDiscountPriceAttribute(): ?float
    {
        if ($this->cover_type === 'hardcover') {
            return $this->hardcover_discount_price ? (float)$this->hardcover_discount_price : ($this->discount_price ? (float)$this->discount_price : null);
        }
        return $this->discount_price ? (float)$this->discount_price : ($this->hardcover_discount_price ? (float)$this->hardcover_discount_price : null);
    }
}