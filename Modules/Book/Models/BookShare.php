<?php

declare(strict_types=1);

namespace Modules\Book\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookShare extends Model
{
    public const UPDATED_AT = null;

    /** Channels the share sheet can report; anything else is rejected. */
    public const CHANNELS = ['native', 'copy', 'whatsapp', 'facebook', 'messenger', 'x', 'telegram', 'linkedin', 'email', 'sms', 'qr'];

    protected $fillable = ['book_id', 'channel', 'user_id', 'visitor_hash'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
