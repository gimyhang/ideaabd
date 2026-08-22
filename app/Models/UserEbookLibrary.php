<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Ebook\Models\Ebook;

class UserEbookLibrary extends Model
{
    use HasFactory;

    protected $table = 'user_ebook_library';

    protected $fillable = [
        'user_id',
        'ebook_id',
        'order_id',
        'access_type',
        'last_read_page',
        'progress_percent',
        'bookmarks_data',
        'is_active',
    ];

    protected $casts = [
        'last_read_page'   => 'integer',
        'progress_percent' => 'integer',
        'bookmarks_data'   => 'array',
        'is_active'        => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ebook(): BelongsTo
    {
        return $this->belongsTo(Ebook::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
