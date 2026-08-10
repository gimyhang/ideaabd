<?php

namespace Modules\Webzine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Author\Models\Author;

class WebzineArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'webzine_id',
        'title',
        'content',
        'author_id',
        'page_number',
        'featured_image',
        'order',
    ];

    public function webzine()
    {
        return $this->belongsTo(Webzine::class, 'webzine_id');
    }

    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }
}
