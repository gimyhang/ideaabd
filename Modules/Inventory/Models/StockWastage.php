<?php

declare(strict_types=1);

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Book\Models\Book;

class StockWastage extends Model
{
    use HasFactory;

    protected $table = 'stock_wastages';

    protected $fillable = [
        'book_id',
        'location_id',
        'quantity',
        'reason', // production_defect, binding_defect, transit_damage, showroom_damage, water_damage, lost, other
        'reported_by',
        'action_taken', // disposed, returned_to_press, repaired, other
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
