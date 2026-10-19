<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Book\Models\Book;

class BookProductionEvent extends Model
{
    public const STAGES = [
        'printed'  => 'Printed',
        'bound'    => 'Bound',
        'received' => 'Received',
    ];

    protected $fillable = ['book_id', 'stage', 'quantity', 'event_date', 'batch_no', 'vendor', 'notes', 'created_by'];

    protected $casts = [
        'quantity'   => 'integer',
        'event_date' => 'date',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
