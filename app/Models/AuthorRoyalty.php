<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Author\Models\Author;
use Modules\Ebook\Models\Ebook;

class AuthorRoyalty extends Model
{
    use HasFactory;

    protected $table = 'author_royalties';

    protected $fillable = [
        'author_id',
        'user_id',
        'ebook_id',
        'order_id',
        'sale_price',
        'royalty_percentage',
        'royalty_amount',
        'platform_fee',
        'status',
    ];

    protected $casts = [
        'sale_price'         => 'decimal:2',
        'royalty_percentage' => 'decimal:2',
        'royalty_amount'     => 'decimal:2',
        'platform_fee'       => 'decimal:2',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ebook(): BelongsTo
    {
        return $this->belongsTo(Ebook::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
