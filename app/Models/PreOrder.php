<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Book\Models\Book;

class PreOrder extends Model
{
    protected $fillable = [
        'book_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'quantity',
        'total_amount',
        'estimated_release_date',
        'status',
        'order_id',
    ];

    protected $casts = [
        'total_amount'           => 'decimal:2',
        'estimated_release_date' => 'date',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
