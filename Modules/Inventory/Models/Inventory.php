<?php

declare(strict_types=1);

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Book\Models\Book;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
        'book_id',
        'location_id',
        'quantity',
        'min_alert_quantity',
        'shelf_location',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_alert_quantity' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Scope for items that have reached or dropped below low stock threshold.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_alert_quantity');
    }

    /**
     * Scope by location.
     */
    public function scopeForLocation($query, int $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_alert_quantity;
    }
}
