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
