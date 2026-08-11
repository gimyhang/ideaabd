<?php

declare(strict_types=1);

namespace Modules\BulkOrder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Book\Models\Book;

class BulkOrderItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bulk_order_items';

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bulk_order_id',
        'book_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * Attribute Type Casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * BulkOrder Relationship.
     */
    public function bulkOrder(): BelongsTo
    {
        return $this->belongsTo(BulkOrder::class);
    }

    /**
     * Book Relationship.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
