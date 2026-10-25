<?php

/**
 * SMS cost tracking and abuse protection used by App\Services\SmsGuard.
 * Every value can be changed in .env without touching code.
 */
return [
    // Rough price of one SMS part. Only used for the cost estimates in the admin dashboard.
    'cost_per_sms' => (float) env('SMS_COST_PER_SMS', 0.35),

    'guard' => [
        'enabled' => env('SMS_GUARD', true),

        // OTP: one number can get this many per rolling 24 hours.
        'daily_limit' => (int) env('SMS_DAILY_LIMIT', 2),

        // otp = the daily limit and lock apply to OTP messages only; all = to every automatic message (welcome, order notices …).
        'limit_scope' => env('SMS_LIMIT_SCOPE', 'otp'),

        // Non-OTP automatic messages (when scope is otp): a looser cap per number per 24 hours, no lock.
        'notification_limit' => (int) env('SMS_NOTIFICATION_LIMIT', 5),

        // The attempt after the limit locks the number for this many hours.
        'restrict_hours' => (int) env('SMS_RESTRICT_HOURS', 24),

        // Hitting the limit again on this many different days (inside the window) blocks OTP to the number until an admin releases it.
        'block_after_days'      => (int) env('SMS_BLOCK_AFTER_DAYS', 3),
        'violation_window_days' => 7,

        // Numbers that skip every check (your own test phones). Comma separated, any format: 01712345678,+8801912345678
        'allowed_numbers' => array_values(array_filter(array_map('trim', explode(',', (string) env('SMS_ALLOWED_NUMBERS', ''))))),

        // The transaction history keeps this many days of rows (0 = keep forever). Run `php artisan model:prune` daily.
        'log_retention_days' => (int) env('SMS_LOG_RETENTION_DAYS', 365),
    ],
];
