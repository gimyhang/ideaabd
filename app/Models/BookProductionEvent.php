<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Book\Models\Book;

class BookProductionEvent extends Model
{
    public const STAGES = [
        'printed'      => 'Printed',
        'bound'        => 'Bound',
        'received'     => 'Received',
        'press_waste'  => 'Press waste',
        'binder_waste' => 'Binding waste',
    ];

    /** Stages that move copies forward (the waste stages only take copies out). */
    public const FLOW_STAGES = ['printed', 'bound', 'received'];

    public const STAGES_BN = [
        'printed'      => 'ছাপা',
        'bound'        => 'বাঁধাই',
        'received'     => 'স্টোরে গ্রহণ',
        'press_waste'  => 'নষ্ট · প্রেসে',
        'binder_waste' => 'নষ্ট · বাঁধাইখানায়',
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
