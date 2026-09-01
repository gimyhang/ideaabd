<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Book\Models\Book;

class BundleItem extends Model
{
    protected $fillable = [
        'bundle_id',
        'book_id',
    ];

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(BookBundle::class, 'bundle_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
