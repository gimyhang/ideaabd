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
use Modules\Book\Models\Category;
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
    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'category_id',
        'author_id',
        'publisher_id',
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
        'file_path',
        'epub_file_path',
        'sample_file_path',
        'file_type', // pdf, epub, mobi, etc.
        'file_size',
        'pages',
        'preview_pages',
        'format',
        'sales_count',
        'download_count',
        'read_count',
        'is_active',
        'mod_status',
        'owner_name',
        'owner_phone',
        'submitted_by',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * Attribute Type Casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'pages' => 'integer',
        'preview_pages' => 'integer',
        'sales_count' => 'integer',
        'download_count' => 'integer',
        'read_count' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($ebook) {
            // Auto resolve or unify author if author_name is provided but author_id/author_link_id is missing
            if (empty($ebook->author_id) && empty($ebook->author_link_id) && !empty($ebook->author_name)) {
                $author = \Modules\Author\Models\Author::findOrCreateUnified([
                    'name'      => trim((string) $ebook->author_name),
                    'is_active' => true,
                ]);
                $ebook->author_id = $author->id;
                $ebook->author_link_id = $author->id;
                $ebook->author_name = $author->name;
            } elseif (!empty($ebook->author_id) && empty($ebook->author_name)) {
                $ebook->author_name = \Modules\Author\Models\Author::where('id', $ebook->author_id)->value('name');
            }
        });
    }

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
     * Author Direct Link Relationship
     */
    public function authorLink(): BelongsTo
    {
        return $this->belongsTo(\Modules\Author\Models\Author::class, 'author_link_id');
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
        if ($this->price <= 0 || !$this->discount_price) return 0;
        return round((($this->price - $this->discount_price) / $this->price) * 100);
    }

    /**
     * Whether this ebook is free
     */
    public function getIsFreeAttribute(): bool
    {
        return (float) $this->price <= 0 || ((float) $this->discount_price === 0.0 && $this->discount_price !== null);
    }

    /**
     * Resolved Cover URL
     */
    public function getCoverUrlAttribute(): ?string
    {
        if (!$this->cover_image) return null;
        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }
        return asset('storage/' . ltrim($this->cover_image, '/'));
    }

    /**
     * Resolved Primary File URL
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) return null;
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }
        return asset('storage/' . ltrim($this->file_path, '/'));
    }

    /**
     * Resolved EPUB URL
     */
    public function getEpubUrlAttribute(): ?string
    {
        $path = $this->epub_file_path ?: ($this->file_type === 'epub' || str_ends_with(strtolower($this->file_path ?? ''), '.epub') ? $this->file_path : null);
        if (!$path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }
        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Resolved Sample URL
     */
    public function getSampleUrlAttribute(): ?string
    {
        $path = $this->sample_file_path ?: null;
        if (!$path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }
        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Formatted File Size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) return '';
        if (is_numeric($this->file_size)) {
            $bytes = (int) $this->file_size;
            if ($bytes >= 1048576) {
                return round($bytes / 1048576, 1) . ' MB';
            }
            return round($bytes / 1024) . ' KB';
        }
        return (string) $this->file_size;
    }

    /**
     * Display Format (PDF, EPUB, or BOTH)
     */
    public function getFormatBadgeAttribute(): string
    {
        $hasEpub = !empty($this->epub_file_path) || strtolower((string)$this->file_type) === 'epub' || str_ends_with(strtolower((string)$this->file_path), '.epub');
        $hasPdf = !empty($this->file_path) && (strtolower((string)$this->file_type) === 'pdf' || str_ends_with(strtolower((string)$this->file_path), '.pdf'));

        if ($hasEpub && $hasPdf) return 'EPUB + PDF';
        if ($hasEpub) return 'EPUB';
        return 'PDF';
    }
}
