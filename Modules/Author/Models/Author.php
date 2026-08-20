<?php

namespace Modules\Author\Models;

use App\Models\Concerns\Moderatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Blog\Models\BlogPost;
use Illuminate\Support\Str;

class Author extends Model
{
    use HasFactory, SoftDeletes, Moderatable;

    protected $fillable = [
        'name',
        'slug',
        'bio',
        'avatar',
        'email',
        'phone',
        'website',
        'social_links',
        'is_verified',
        'is_active',
    ];

    protected $casts = [
        'social_links' => 'json',
        'is_verified'  => 'boolean',
        'is_active'    => 'boolean',
    ];

    protected $appends = [
        'avatar_url',
        'initials',
        'avatar_bg_color',
    ];

    /**
     * Dynamic Avatar URL with multiple fallbacks & protocol safety
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $avatar = $this->avatar ?? $this->photo ?? $this->image ?? null;
        if (empty($avatar)) {
            return null;
        }

        $avatar = trim((string) $avatar);

        // If it's inline data URI
        if (str_starts_with($avatar, 'data:image')) {
            return $avatar;
        }

        // If it's a URL (http/https), check if it points to local/site storage
        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            $parsed = parse_url($avatar);
            $host = $parsed['host'] ?? '';
            $path = $parsed['path'] ?? '';

            // If it belongs to local dev host (127.0.0.1/localhost) or contains /storage/, resolve dynamically
            if (in_array($host, ['127.0.0.1', 'localhost', 'ideaabd.com', 'www.ideaabd.com'], true) || str_contains($path, '/storage/')) {
                $cleanPath = ltrim($path, '/');
                if (str_starts_with($cleanPath, 'storage/')) {
                    return asset($cleanPath);
                }
                return asset('storage/' . $cleanPath);
            }

            // External third-party CDN / Gravatar / image link
            return $avatar;
        }

        // Clean leading slashes
        $cleanPath = ltrim($avatar, '/');

        // Check if path starts with storage/
        if (str_starts_with($cleanPath, 'storage/')) {
            return asset($cleanPath);
        }

        // Check if path starts with public/
        if (str_starts_with($cleanPath, 'public/')) {
            return asset('storage/' . substr($cleanPath, 7));
        }

        return asset('storage/' . $cleanPath);
    }

    /**
     * Unicode-safe Author initials (Bengali & English)
     */
    public function getInitialsAttribute(): string
    {
        $name = trim($this->name ?? '');
        if (empty($name)) {
            return 'লে';
        }

        $words = preg_split('/\s+/u', $name);
        if (count($words) >= 2) {
            return mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1);
        }

        return mb_substr($name, 0, 1);
    }

    /**
     * Deterministic pleasant gradient background for avatar based on ID / Name
     */
    public function getAvatarBgColorAttribute(): string
    {
        $colors = [
            'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)',
            'linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%)',
            'linear-gradient(135deg, #059669 0%, #10b981 100%)',
            'linear-gradient(135deg, #d97706 0%, #f59e0b 100%)',
            'linear-gradient(135deg, #db2777 0%, #ec4899 100%)',
            'linear-gradient(135deg, #7c3aed 0%, #a855f7 100%)',
            'linear-gradient(135deg, #0d9488 0%, #14b8a6 100%)',
            'linear-gradient(135deg, #e11d48 0%, #f43f5e 100%)',
            'linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)',
            'linear-gradient(135deg, #475569 0%, #64748b 100%)',
        ];

        $idx = abs(crc32((string) ($this->id ?? $this->name ?? '1'))) % count($colors);
        return $colors[$idx];
    }

    /**
     * Clean bio excerpt
     */
    public function getBioExcerptAttribute(): string
    {
        if (empty($this->bio)) {
            return '';
        }
        return Str::limit(strip_tags($this->bio), 120);
    }

    /**
     * Scopes for easy querying
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('slug', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('bio', 'like', "%{$term}%");
        });
    }

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    /** Printed/both-format books, linked through the book_author pivot. */
    public function books()
    {
        return $this->belongsToMany(\Modules\Book\Models\Book::class, 'book_author', 'author_id', 'book_id');
    }

    public function ebooks()
    {
        return $this->hasMany(\Modules\Ebook\Models\Ebook::class, 'author_id');
    }

    public function submissions()
    {
        return $this->hasMany(AuthorSubmission::class, 'author_id');
    }
}
