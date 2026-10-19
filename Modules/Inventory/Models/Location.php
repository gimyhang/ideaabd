<?php

declare(strict_types=1);

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Book\Models\Book;

class Location extends Model
{
    use HasFactory;

    protected $table = 'locations';

    protected $fillable = [
        'name',
        'code',
        'type', // 'warehouse', 'showroom'
        'is_central',
        'contact_person',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_central' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Stock records belonging to this location.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'location_id');
    }

    /**
     * Books stocked at this location.
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'inventories', 'location_id', 'book_id')
            ->withPivot(['quantity', 'min_alert_quantity', 'shelf_location'])
            ->withTimestamps();
    }

    /**
     * Outgoing transfers dispatched from this location.
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_location_id');
    }

    /**
     * Incoming transfers received at this location.
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_location_id');
    }

    /**
     * Wastage/damaged logs recorded at this location.
     */
    public function wastages(): HasMany
    {
        return $this->hasMany(StockWastage::class, 'location_id');
    }

    /**
     * Movement logs for this location.
     */
    public function movementLogs(): HasMany
    {
        return $this->hasMany(InventoryMovementLog::class, 'location_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWarehouses($query)
    {
        return $query->where('type', 'warehouse');
    }

    public function scopeShowrooms($query)
    {
        return $query->where('type', 'showroom');
    }

    public function scopeCentral($query)
    {
        return $query->where('is_central', true);
    }

    public function isWarehouse(): bool
    {
        return $this->type === 'warehouse';
    }

    public function isShowroom(): bool
    {
        return $this->type === 'showroom';
    }
}
