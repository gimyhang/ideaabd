<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetRequest extends Model
{
    protected $table = 'password_reset_requests';

    protected $fillable = [
        'user_id',
        'identity',
        'user_name',
        'user_ip',
        'reason_notes',
        'status',
        'otp_code',
        'otp_expires_at',
        'admin_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
        'resolved_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
