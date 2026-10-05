<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'type',
        'category_id',
        'title',
        'slug',
        'sku',
        'brand',
        'model',
        'summary',
        'description',
        'specifications',
        'price',
        'discount_price',
        'stock',
        'stock_status',
        'cover_image',
        'gallery_images',
        'warranty',
        'badge',
        'rating',
        'reviews_count',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'specifications' => 'array',
        'gallery_images' => 'array',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'stock' => 'integer',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'final_price',
        'discount_percent',
        'image_url',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $baseSlug = Str::slug($product->title);
                $product->slug = $baseSlug ?: 'item-' . time();
            }
            if (empty($product->sku)) {
                $prefix = $product->type === 'electronics' ? 'ELC' : 'STN';
                $product->sku = $prefix . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function scopeElectronics($query)
    {
        return $query->where('type', 'electronics');
    }

    public function scopeStationery($query)
    {
        return $query->where('type', 'stationery');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getFinalPriceAttribute(): float
    {
        if ($this->discount_price && $this->discount_price > 0 && $this->discount_price < $this->price) {
            return (float) $this->discount_price;
        }
        return (float) $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if ($this->price > 0 && $this->discount_price && $this->discount_price < $this->price) {
            return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
        }
        return 0;
    }

    public function getImageUrlAttribute(): string
    {
        if (!empty($this->cover_image)) {
            if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
                return $this->cover_image;
            }
            if (str_starts_with($this->cover_image, 'storage/')) {
                return asset($this->cover_image);
            }
            if (str_starts_with($this->cover_image, 'assets/')) {
                return asset($this->cover_image);
            }
            return asset('storage/' . ltrim($this->cover_image, '/'));
        }

        // Elegant default placeholder depending on type
        return asset('assets/images/placeholder-product.svg');
    }

    public function getGalleryListAttribute(): array
    {
        $gallery = is_array($this->gallery_images) ? $this->gallery_images : [];
        $urls = [];
        foreach ($gallery as $img) {
            if (empty($img)) continue;
            if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, 'assets/')) {
                $urls[] = $img;
            } else {
                $urls[] = asset('storage/' . ltrim($img, '/'));
            }
        }
        return $urls;
    }
}
