<?php

declare(strict_types=1);

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Book\Models\Book;

class StockTransferItem extends Model
{
    use HasFactory;

    protected $table = 'stock_transfer_items';

    protected $fillable = [
        'stock_transfer_id',
        'book_id',
        'quantity_sent',
        'quantity_received',
        'unit_cost',
        'remarks',
    ];

    protected $casts = [
        'quantity_sent' => 'integer',
        'quantity_received' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
