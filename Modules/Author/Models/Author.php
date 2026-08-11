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

    /** Printed/both-format books, linked through the book_author pivot. */
    public function books()
    {
        return $this->belongsToMany(\Modules\Book\Models\Book::class, 'book_author', 'author_id', 'book_id');
    }

    public function ebooks()
    {
        return $this->belongsToMany(\Modules\Ebook\Models\Ebook::class, 'ebook_author', 'author_id', 'ebook_id');
    }

    public function submissions()
    {
        return $this->hasMany(AuthorSubmission::class, 'author_id');
    }
}
