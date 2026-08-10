<?php

namespace Modules\Research\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Author\Models\Author;

class ResearchPaper extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'abstract',
        'content',
        'pdf_file_path',
        'keywords',
        'category',
        'author_id',
        'publication_date',
        'doi',
        'citations_count',
        'view_count',
        'download_count',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'keywords' => 'json',
        'publication_date' => 'date',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function coAuthors()
    {
        return $this->belongsToMany(Author::class, 'research_co_authors', 'paper_id', 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->whereNotNull('published_at');
    }
}
