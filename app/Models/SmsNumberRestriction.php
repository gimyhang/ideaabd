<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsNumberRestriction extends Model
{
    protected $fillable = [
        'phone', 'restricted_until', 'blocked_at', 'count_after_id', 'violation_days', 'violations', 'reason', 'last_attempt_at', 'blocked_by',
    ];

    protected function casts(): array
    {
        return [
            'restricted_until' => 'datetime',
            'blocked_at'       => 'datetime',
            'last_attempt_at'  => 'datetime',
            'violation_days'   => 'array',
        ];
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    public function isRestricted(): bool
    {
        return $this->restricted_until !== null && $this->restricted_until->isFuture();
    }
}
