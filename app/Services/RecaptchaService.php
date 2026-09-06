<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Get the configured site key.
     */
    public static function getSiteKey(): string
    {
        return (string) config('services.recaptcha.site_key', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
    }

    /**
     * Get the configured secret key.
     */
    public static function getSecretKey(): string
    {
        return (string) config('services.recaptcha.secret_key', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
    }

    /**
     * Check if reCAPTCHA verification is enabled.
     */
    public static function isEnabled(): bool
    {
        return (bool) config('services.recaptcha.enabled', true);
    }

    /**
     * Get threshold for failed attempts before requiring captcha.
     */
    public static function getThreshold(): int
    {
        return (int) config('services.recaptcha.threshold', 3);
    }

    /**
     * Verify the g-recaptcha-response token against Google's API.
     */
    public static function verify(?string $token, ?string $ip = null): bool
    {
        if (!self::isEnabled()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        $secret = self::getSecretKey();
        if (empty($secret)) {
            Log::warning('Google reCAPTCHA secret key is not configured.');
            return true;
        }

        try {
            $payload = [
                'secret'   => $secret,
                'response' => $token,
            ];

            if ($ip) {
                $payload['remoteip'] = $ip;
            }

            $response = Http::asForm()
                ->timeout(10)
                ->post(self::VERIFY_URL, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return (bool) ($data['success'] ?? false);
            }

            Log::error('reCAPTCHA verification HTTP request failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification exception: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return false;
        }
    }
}
