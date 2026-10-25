<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class SmsLog extends Model
{
    use Prunable;

    protected $fillable = [
        'batch', 'phone', 'purpose', 'source', 'status', 'reason', 'message', 'segments', 'cost',
        'provider', 'response_code', 'gateway_response', 'campaign', 'dedupe_key', 'user_id', 'ip',
    ];

    protected function casts(): array
    {
        return ['cost' => 'decimal:2'];
    }

    public function prunable()
    {
        $days = (int) config('sms.guard.log_retention_days', 365);

        return $days > 0 ? static::where('created_at', '<', now()->subDays($days)) : static::whereRaw('1 = 0');
    }
}
