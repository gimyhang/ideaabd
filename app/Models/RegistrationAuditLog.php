<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'phone',
        'email',
        'category',
        'step',
        'is_success',
        'error_message',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'is_success' => 'boolean',
        'metadata'   => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to log registration events with IP and User Agent
     */
    public static function recordEvent(string $step, bool $isSuccess = true, array $data = []): ?self
    {
        try {
            return self::create([
                'user_id'       => $data['user_id'] ?? null,
                'ip_address'    => request()->ip(),
                'phone'         => $data['phone'] ?? null,
                'email'         => $data['email'] ?? null,
                'category'      => $data['category'] ?? 'buyer',
                'step'          => $step,
                'is_success'    => $isSuccess,
                'error_message' => $data['error_message'] ?? null,
                'user_agent'    => substr((string) request()->userAgent(), 0, 255),
                'metadata'      => $data['metadata'] ?? null,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('RegistrationAuditLog record failed: ' . $e->getMessage());
            return null;
        }
    }
}
