<?php

namespace Modules\Webzine\Models;

use App\Models\Concerns\Moderatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Publisher\Models\Publisher;

class Webzine extends Model
{
    use HasFactory, SoftDeletes, Moderatable;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'epub_file_path',
        'cover_image',
        'issue_number',
        'publication_date',
        'publisher_id',
        'category',
        'view_count',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'publication_date' => 'date',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'publisher_id');
    }

    public function articles()
    {
        return $this->hasMany(WebzineArticle::class, 'webzine_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->whereNotNull('published_at');
    }

    /**
     * Resolved Cover URL
     */
    public function getCoverUrlAttribute(): ?string
    {
        if (!$this->cover_image) return null;
        $cover = trim($this->cover_image);
        if (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://')) {
            return $cover;
        }
        if (str_starts_with($cover, 'storage/')) {
            return asset($cover);
        }
        return asset('storage/' . ltrim($cover, '/'));
    }

    /**
     * Resolved EPUB URL
     */
    public function getEpubUrlAttribute(): ?string
    {
        if (!$this->epub_file_path) return null;
        $path = trim($this->epub_file_path);
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }
        return asset('storage/' . ltrim($path, '/'));
    }
}
