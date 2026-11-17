<?php

namespace Modules\Book\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Every address a book has ever had. When a slug changes (the one-time "450-book-name" numbering, or an
 * admin editing it later) the old address is kept here, so printed QR codes, Google results and shared
 * links keep working: BookController::show sends them to the book's current address with a 301.
 */
class BookSlugRedirect extends Model
{
    protected $table = 'book_slug_redirects';

    protected $fillable = ['book_id', 'old_slug'];

    public static function remember(int $bookId, ?string $oldSlug): void
    {
        $oldSlug = trim((string) $oldSlug);
        if ($oldSlug === '') {
            return;
        }

        static::updateOrCreate(['old_slug' => $oldSlug], ['book_id' => $bookId]);
    }
}
