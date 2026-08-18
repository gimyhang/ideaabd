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
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
