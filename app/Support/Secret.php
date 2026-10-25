<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;

/**
 * Keeps API keys and SMTP passwords encrypted (with APP_KEY) inside admin_dashboard_settings.
 * Values saved before this existed are plain text; open() returns those unchanged, so nothing breaks.
 */
class Secret
{
    private const PREFIX = 'enc:';

    public static function seal(?string $value): string
    {
        $value = (string) $value;

        return $value === '' || str_starts_with($value, self::PREFIX) ? $value : self::PREFIX . Crypt::encryptString($value);
    }

    public static function open(?string $value): string
    {
        $value = (string) $value;
        if (!str_starts_with($value, self::PREFIX)) {
            return $value;
        }

        try {
            return Crypt::decryptString(substr($value, strlen(self::PREFIX)));
        } catch (\Throwable) {
            return ''; // APP_KEY changed: treat as not set so the admin is asked to type it again
        }
    }
}
