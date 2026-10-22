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
use Illuminate\Validation\ValidationException;
use Modules\Author\Models\Author;
use Modules\Book\Models\Category;
use Modules\Review\Models\Review;
use Modules\Tag\Models\Tag;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Vendor\Models\Vendor;
use Modules\SEO\Traits\HasSeo;

class Book extends Model
{
    use HasFactory, Moderatable, SoftDeletes, HasSeo;

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
        'idea_serial_no',
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
            $book->assertCatalogEntryIsUnique();

            // Auto-generate Idea Prokashon serial number (IP-XXX) if Idea book and empty
            if ($book->is_idea_prokashon && empty(trim((string) $book->idea_serial_no))) {
                $book->idea_serial_no = \App\Services\BarcodeService::generateNextIdeaSerial();
            }

            // Auto-generate General Catalog SKU (BK-XXXXX) if empty
            if (empty(trim((string) $book->sku))) {
                $book->sku = \App\Services\BarcodeService::generateNextGeneralSku();
            }

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

    public function assertCatalogEntryIsUnique(): void
    {
        if ($this->exists && ! $this->isDirty(['title', 'isbn', 'author_name', 'author_link_id', 'edition'])) {
            return;
        }

        $title = trim((string) $this->title);
        if ($title === '') {
            return;
        }

        $ignoreId = $this->exists ? $this->getKey() : null;
        $isbn = $this->normalizeCatalogIdentifier($this->isbn);

        if ($isbn !== '') {
            $duplicate = static::withTrashed()
                ->where('id', '!=', $ignoreId ?? 0)
                ->whereRaw("UPPER(REPLACE(REPLACE(REPLACE(TRIM(isbn), '-', ''), ' ', ''), '.', '')) = ?", [$isbn])
                ->first();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'isbn' => "এই ISBN-টি ইতিমধ্যে বই #{$duplicate->id} “{$duplicate->title}”-এ ব্যবহৃত হয়েছে।",
                ]);
            }
        }

        $normalizedTitle = mb_strtolower($title);
        $query = static::withTrashed()
            ->where('id', '!=', $ignoreId ?? 0)
            ->whereRaw('LOWER(TRIM(title)) = ?', [$normalizedTitle]);

        $authorId = $this->author_link_id;
        $authorName = trim((string) $this->author_name);
        if ($authorName === '' && $authorId) {
            $authorName = trim((string) \Modules\Author\Models\Author::whereKey($authorId)->value('name'));
        }

        if ($authorId || $authorName !== '') {
            $query->where(function ($authorQuery) use ($authorId, $authorName) {
                if ($authorId) {
                    $authorQuery->where('author_link_id', $authorId);
                    if ($authorName !== '') {
                        $authorQuery->orWhereRaw('LOWER(TRIM(author_name)) = ?', [mb_strtolower($authorName)]);
                    }
                } else {
                    $authorQuery->whereRaw('LOWER(TRIM(author_name)) = ?', [mb_strtolower($authorName)]);
                }
            });
        } else {
            $query->whereNull('author_link_id')
                ->where(function ($authorQuery) {
                    $authorQuery->whereNull('author_name')->orWhereRaw("TRIM(author_name) = ''");
                });
        }

        $edition = mb_strtolower(trim((string) $this->edition));
        if ($edition !== '') {
            $query->where(function ($editionQuery) use ($edition) {
                $editionQuery->whereNull('edition')
                    ->orWhereRaw("TRIM(edition) = ''")
                    ->orWhereRaw('LOWER(TRIM(edition)) = ?', [$edition]);
            });
        }

        $duplicate = $query->first();
        if ($duplicate) {
            throw ValidationException::withMessages([
                'title' => "এই শিরোনাম ও লেখকের বইটি ইতিমধ্যে ক্যাটালগে আছে (বই #{$duplicate->id})। বিদ্যমান বইটি সম্পাদনা করুন।",
            ]);
        }
    }

    private function normalizeCatalogIdentifier(?string $value): string
    {
        return strtoupper((string) preg_replace('/[\s.-]+/u', '', trim((string) $value)));
    }

    /**
     * Category Relationship (একটি বই একটি ক্যাটাগরিতে থাকে)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Category Display Name (fallback to 'বিবিধ' if missing)
     */
    public function getCategoryDisplayNameAttribute(): string
    {
        return $this->category?->name ?? 'বিবিধ';
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
     * Inventories Relationship (মাল্টি-লোকেশন স্টক রেকর্ড)
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(\Modules\Inventory\Models\Inventory::class, 'book_id');
    }

    /**
     * Locations where this book is stocked (Warehouse, Showroom, etc.)
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(\Modules\Inventory\Models\Location::class, 'inventories', 'book_id', 'location_id')
            ->withPivot(['quantity', 'min_alert_quantity', 'shelf_location'])
            ->withTimestamps();
    }

    /**
     * Get stock quantity at a specific location
     */
    public function stockAt(int $locationId): int
    {
        return (int) ($this->inventories()->where('location_id', $locationId)->value('quantity') ?? 0);
    }

    /**
     * Get Cover URL safely across all storage formats, converted WebP/AVIF files, and live environments.
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

        // Check if original file exists or if webp/avif replacement exists on disk
        $relPath = ltrim(str_replace(['storage/', '/storage/'], '', $cover), '/');
        
        if (!file_exists(storage_path('app/public/' . $relPath)) && !file_exists(public_path($cover))) {
            $baseNoExt = preg_replace('/\.[^.]+$/', '', $relPath);
            if (file_exists(storage_path('app/public/' . $baseNoExt . '.svg'))) {
                return asset('storage/' . $baseNoExt . '.svg');
            }
            if (file_exists(storage_path('app/public/' . $baseNoExt . '.webp'))) {
                return asset('storage/' . $baseNoExt . '.webp');
            }
            if (file_exists(storage_path('app/public/' . $baseNoExt . '.avif'))) {
                return asset('storage/' . $baseNoExt . '.avif');
            }
            if (file_exists(public_path($baseNoExt . '.svg'))) {
                return asset($baseNoExt . '.svg');
            }
            if (file_exists(public_path($baseNoExt . '.webp'))) {
                return asset($baseNoExt . '.webp');
            }
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

    /**
     * Check if this book belongs to Idea Prokashon (In-House)
     */
    public function getIsIdeaProkashonAttribute(): bool
    {
        return empty($this->publisher_id) || (int) $this->publisher_id === 2;
    }

    /**
     * Get vector Code 128 SVG Barcode (Uses Idea Serial if present, else SKU or ISBN)
     */
    public function getBarcodeSvgAttribute(): string
    {
        $code = !empty($this->idea_serial_no) ? $this->idea_serial_no : (!empty($this->sku) ? $this->sku : (!empty($this->isbn) ? $this->isbn : 'IDEA-' . $this->id));
        return \App\Services\BarcodeService::generateCode128Svg($code, 46, 1.8, true);
    }

    /**
     * Get Primary Product Identifier Code (Idea Serial for Idea books, SKU for others)
     */
    public function getProductCodeAttribute(): string
    {
        if ($this->is_idea_prokashon && !empty($this->idea_serial_no)) {
            return $this->idea_serial_no;
        }
        return $this->sku ?: ('BK-' . $this->id);
    }

    /**
     * Get vector QR Code SVG
     */
    public function getQrCodeSvgAttribute(): string
    {
        $url = url('/books/' . ($this->slug ?: $this->id));
        return \App\Services\BarcodeService::generateQrCodeSvg($url, 110);
    }

    /**
     * Get QR Code Payload String
     */
    public function getQrPayloadAttribute(): string
    {
        return url('/books/' . ($this->slug ?: $this->id));
    }
}