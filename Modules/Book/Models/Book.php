<?php

declare(strict_types=1);

namespace Modules\Book\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Author\Models\Author;
use Modules\Book\Models\Category;
use Modules\Review\Models\Review;
use Modules\Vendor\Models\Vendor;

class Book extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'category_id',
        'title',
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
}