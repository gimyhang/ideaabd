<?php

namespace Modules\Webzine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Publisher\Models\Publisher;

class Webzine extends Model
{
    use HasFactory, SoftDeletes;

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
}
