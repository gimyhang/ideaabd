<?php

namespace Modules\Author\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Blog\Models\BlogPost;

class Author extends Model
{
    use HasFactory, SoftDeletes;

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
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    public function submissions()
    {
        return $this->hasMany(AuthorSubmission::class, 'author_id');
    }
}
