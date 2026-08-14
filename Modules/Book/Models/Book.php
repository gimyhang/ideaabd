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
        'title',
        'subtitle',
        'author_name',
        'author_role',
        'author_link_id',
        'slug',
        'isbn',
        'description',
        'price',
        'discount_price',
        'cover_image',
        'sample_pdf_path',
        'preview_pages',
        'stock_quantity',
        'sales_count',
        'format', // 'printed', 'ebook', 'both'
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
        'stock_quantity' => 'integer',
        'sales_count' => 'integer',
        'preview_pages' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::saved(function ($book) {
            // When saved from admin panel (ContentController), if author_link_id is provided, sync it to the pivot table
            // so frontend $book->authors logic works consistently.
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
     * Vendor Relationship (বইটি কোন প্রকাশনী/ভেন্ডরের)
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Author Relationship (Pivot Table: book_author)
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(\Modules\Publisher\Models\Publisher::class, 'publisher_id');
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
    }}