<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SecurityAuditService
{
    /**
     * Log a security event with sanitization of sensitive parameters.
     */
    public static function log(string $event, array $data = [], string $level = 'info'): void
    {
        $sanitizedData = self::sanitize($data);

        $payload = array_merge([
            'event'      => $event,
            'ip'         => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
            'timestamp'  => now()->toIso8601String(),
        ], $sanitizedData);

        Log::log($level, "AUTH_SECURITY_AUDIT: [{$event}]", $payload);
    }

    /**
     * Sanitize and strip sensitive authentication parameters.
     */
    private static function sanitize(array $data): array
    {
        $sensitiveKeys = [
            'password', 'password_confirmation', 'current_password', 'new_password',
            'token', 'otp', 'otp_code', 'secret', 'g-recaptcha-response',
        ];

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys, true)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = self::sanitize($value);
            }
        }

        return $data;
    }

    public static function loginSuccess(?int $userId, string $identifier): void
    {
        self::log('LOGIN_SUCCESS', [
            'user_id'    => $userId,
            'identifier' => $identifier,
        ], 'info');
    }

    public static function loginFailed(string $identifier, int $attemptCount, ?string $reason = null): void
    {
        self::log('LOGIN_FAILED', [
            'identifier'    => $identifier,
            'attempt_count' => $attemptCount,
            'reason'        => $reason ?: 'Invalid credentials',
        ], 'warning');
    }

    public static function cooldownTriggered(string $identifier, int $cooldownSeconds, int $attemptCount): void
    {
        self::log('PROGRESSIVE_COOLDOWN_TRIGGERED', [
            'identifier'       => $identifier,
            'cooldown_seconds' => $cooldownSeconds,
            'attempt_count'    => $attemptCount,
        ], 'warning');
    }

    public static function passwordChanged(?int $userId): void
    {
        self::log('PASSWORD_CHANGED', [
            'user_id' => $userId,
        ], 'info');
    }

    public static function passwordResetRequested(string $identifier, string $deliveryMethod): void
    {
        self::log('PASSWORD_RESET_REQUESTED', [
            'identifier'      => $identifier,
            'delivery_method' => $deliveryMethod,
        ], 'info');
    }

    public static function passwordResetCompleted(?int $userId): void
    {
        self::log('PASSWORD_RESET_COMPLETED', [
            'user_id' => $userId,
        ], 'info');
    }
}
