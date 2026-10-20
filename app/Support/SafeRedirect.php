<?php

namespace App\Support;

/**
 * Post-login redirects may only stay on this site. Anything else (another host, "//evil.test", "javascript:",
 * backslash tricks) falls back to a safe default, so a crafted login link cannot send people to a look-alike page.
 */
class SafeRedirect
{
    public static function sanitize(?string $url, string $fallback): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return $fallback;
        }

        $clean = !str_contains($url, '\\') && !preg_match('/[\x00-\x1F]/', $url);

        // Relative path on this site: one leading slash, never protocol-relative ("//host").
        if ($clean && str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }

        $parts = parse_url($url);
        $urlHost = strtolower((string) ($parts['host'] ?? ''));
        $scheme = (string) ($parts['scheme'] ?? '');
        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $requestHost = strtolower(request()->getHost());

        if ($clean && in_array($scheme, ['http', 'https'], true) && $urlHost !== '' && ($urlHost === $requestHost || $urlHost === $appHost)) {
            return $url;
        }

        return $fallback;
    }
}
