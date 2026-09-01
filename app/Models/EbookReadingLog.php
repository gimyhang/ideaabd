<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Ebook\Models\Ebook;

class EbookReadingLog extends Model
{
    protected $fillable = [
        'user_id',
        'ebook_id',
        'pages_read',
        'session_duration_sec',
        'ip_address',
        'device_signature',
        'read_date',
    ];

    protected $casts = [
        'read_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ebook(): BelongsTo
    {
        return $this->belongsTo(Ebook::class, 'ebook_id');
    }
}
