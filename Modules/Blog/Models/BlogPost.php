<?php

namespace Modules\Blog\Models;

use App\Models\Concerns\Moderatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Blog\Models\BlogCategory;
use App\Models\User;
use Modules\Blog\Models\BlogTag;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes, Moderatable;

    protected $fillable = [
        'title',
        'subtitle',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'category_id',
        'author_id',
        'status',
        'published_at',
        'view_count',
        'is_featured',
        'mod_status',
        'submitted_by',
        'owner_name',
        'owner_phone',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'edit_request_status',
        'edit_request_data',
        'edit_requested_at',
        'edit_request_notes',
        'edit_request_reviewed_at',
        'edit_request_rejection_reason',
    ];

    protected $casts = [
        'published_at'              => 'datetime',
        'created_at'                => 'datetime',
        'updated_at'                => 'datetime',
        'edit_requested_at'         => 'datetime',
        'edit_request_reviewed_at'  => 'datetime',
        'edit_request_data'         => 'array',
    ];

    /**
     * Check if the post has a pending edit/correction request.
     */
    public function hasPendingEditRequest(): bool
    {
        return $this->edit_request_status === 'pending' && !empty($this->edit_request_data);
    }

    /**
     * Apply and replace the post content with the approved edit request data.
     */
    public function applyEditRequest(?int $reviewerId = null): void
    {
        if (empty($this->edit_request_data) || !is_array($this->edit_request_data)) {
            return;
        }

        $data = $this->edit_request_data;

        $fields = ['title', 'subtitle', 'content', 'excerpt', 'category_id', 'featured_image'];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $this->{$field} = $data[$field];
            }
        }

        if (!empty($data['slug'])) {
            $this->slug = $data['slug'];
        }

        $this->edit_request_status = 'approved';
        $this->edit_request_reviewed_at = now();
        $this->edit_request_rejection_reason = null;
        if ($reviewerId) {
            $this->reviewed_by = $reviewerId;
        }
        $this->save();
    }

    /**
     * Reject the pending edit request with an optional reason.
     */
    public function rejectEditRequest(?string $reason = null, ?int $reviewerId = null): void
    {
        $this->edit_request_status = 'rejected';
        $this->edit_request_rejection_reason = $reason;
        $this->edit_request_reviewed_at = now();
        if ($reviewerId) {
            $this->reviewed_by = $reviewerId;
        }
        $this->save();
    }

    /**
     * The "booted" method of the model.
     * Auto-heals missing subtitle column in SQLite/MySQL or safely omits it to prevent SQLSTATE crashes.
     */
    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            if (array_key_exists('subtitle', $post->attributes)) {
                try {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('blog_posts', 'subtitle')) {
                        $driver = \Illuminate\Support\Facades\DB::getDriverName();
                        if ($driver === 'sqlite') {
                            \Illuminate\Support\Facades\DB::statement('ALTER TABLE blog_posts ADD COLUMN subtitle VARCHAR(500) NULL');
                        } else {
                            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `blog_posts` ADD COLUMN `subtitle` VARCHAR(500) NULL AFTER `title`');
                        }
                    }
                } catch (\Throwable $e) {
                    // Safe fallback
                }

                // If column still does not exist, unset attribute so SQL INSERT/UPDATE never errors
                try {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('blog_posts', 'subtitle')) {
                        unset($post->attributes['subtitle']);
                    }
                } catch (\Throwable $e) {}
            }
        });

        static::saved(function () {
            try {
                \App\Http\Controllers\SitemapController::regenerateStaticSitemap();
            } catch (\Throwable $e) {}
        });

        static::deleted(function () {
            try {
                \App\Http\Controllers\SitemapController::regenerateStaticSitemap();
            } catch (\Throwable $e) {}
        });
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function tags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tags', 'blog_post_id', 'blog_tag_id');
    }

    public function reviews()
    {
        return $this->hasMany(\Modules\Review\Models\Review::class, 'blog_post_id')->where('is_approved', true)->latest();
    }

    public function allReviews()
    {
        return $this->hasMany(\Modules\Review\Models\Review::class, 'blog_post_id')->latest();
    }

    public function honorariums()
    {
        return $this->hasMany(\App\Models\AuthorHonorarium::class, 'blog_post_id')->where('payment_status', 'completed')->latest();
    }

    public function allHonorariums()
    {
        return $this->hasMany(\App\Models\AuthorHonorarium::class, 'blog_post_id')->latest();
    }

    /**
     * Resolve the corresponding Author model instance for this blog post.
     */
    public function resolveAuthorRecord(): ?\Modules\Author\Models\Author
    {
        // 1. If author_id is a User
        if ($this->author) {
            $authorRecord = $this->author->getAuthorRecord();
            if ($authorRecord) {
                return $authorRecord;
            }
        }

        // 2. If submitted_by is a User
        if ($this->submitter) {
            $authorRecord = $this->submitter->getAuthorRecord();
            if ($authorRecord) {
                return $authorRecord;
            }
        }

        // 3. Match by owner_name or author_id
        if (!empty($this->owner_name)) {
            $authorRecord = \Modules\Author\Models\Author::where('name', $this->owner_name)
                ->orWhere('name', 'like', "%{$this->owner_name}%")
                ->first();
            if ($authorRecord) {
                return $authorRecord;
            }
        }

        if ($this->author_id) {
            $authorRecord = \Modules\Author\Models\Author::find($this->author_id);
            if ($authorRecord) {
                return $authorRecord;
            }
        }

        return null;
    }

    public function scopePublished($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'published')
              ->orWhere('status', 'approved')
              ->orWhere('mod_status', 'approved')
              ->orWhereNull('status');
        })->where(function ($q) {
            $q->whereNull('mod_status')
              ->orWhere('mod_status', 'approved')
              ->orWhere('mod_status', '!=', 'rejected');
        });
    }

    public function getAuthorNameAttribute(): string
    {
        if (!empty($this->owner_name)) {
            return $this->owner_name;
        }
        if ($this->author) {
            return $this->author->name;
        }
        return 'সম্পাদকীয় বিভাগ';
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get fully-qualified cover / photocard URL for social media preview.
     */
    public function getCoverUrlAttribute(): ?string
    {
        $img = $this->featured_image;
        if (!$img) {
            return null;
        }
        $img = trim($img);
        if (str_starts_with($img, 'data:image') || str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
            return $img;
        }
        if (str_starts_with($img, 'storage/')) {
            return asset($img);
        }
        if (str_starts_with($img, '/storage/')) {
            return asset(ltrim($img, '/'));
        }
        return asset('storage/' . ltrim($img, '/'));
    }
}
