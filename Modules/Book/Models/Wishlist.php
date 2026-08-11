<?php

declare(strict_types=1);

namespace Modules\Book\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\User\Models\User;

class Wishlist extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wishlists';

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'notes',
        'priority',
    ];

    /**
     * Attribute Type Casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'priority' => 'integer',
    ];

    /**
     * User Relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Book Relationship.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Scope to get active wishlists.
     */
    public function scopeActive($query)
    {
        return $query->whereHas('book', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Scope to order by priority.
     */
    public function scopeByPriority($query)
    {
        return $query->orderByDesc('priority')->latest();
    }

    /**
     * Check if book is in wishlist.
     */
    public static function isInWishlist($userId, $bookId): bool
    {
        return self::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->exists();
    }
}
