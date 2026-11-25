<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One movement of a raw material.
 *   opening — stock counted when the book was started (adds)
 *   in      — bought (from a raw-material purchase line; adds)
 *   out     — used for a book / print job (removes; valued at the average cost of that day)
 *   adjust  — count correction or wastage (signed quantity)
 */
class RawMaterialMove extends Model
{
    protected $fillable = [
        'raw_material_id', 'type', 'qty', 'unit_cost', 'amount', 'purchase_id', 'purchase_item_id',
        'book_id', 'job', 'note', 'moved_at', 'created_by',
    ];

    protected $casts = [
        'qty'       => 'decimal:3',
        'unit_cost' => 'decimal:4',
        'amount'    => 'decimal:2',
        'moved_at'  => 'date',
    ];

    public const TYPES = [
        'opening' => 'শুরুর মজুদ',
        'in'      => 'ক্রয়',
        'out'     => 'ব্যবহার',
        'adjust'  => 'সমন্বয়',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(PublisherPurchase::class, 'purchase_id')->withTrashed();
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(\Modules\Book\Models\Book::class, 'book_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Effect on the stock balance (+ adds, − removes). */
    public function signedQty(): float
    {
        $q = (float) $this->qty;

        return match ($this->type) {
            'out'   => -abs($q),
            'adjust'=> $q,
            default => abs($q),
        };
    }
}
