<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CaptchaService
{
    /**
     * Clear, high-contrast, unambiguous character pool (A-Z, 2-9).
     * Excludes ambiguous characters (0, O, 1, I, l) to eliminate user confusion.
     */
    private const CHARACTERS = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    /**
     * CAPTCHA expiration time in seconds (5 minutes).
     */
    public const EXPIRATION_SECONDS = 300;

    /**
     * Maximum failed attempts per IP before temporary throttling.
     */
    public const MAX_ATTEMPTS = 5;

    /**
     * Throttling duration in seconds (10 minutes).
     */
    public const THROTTLE_DURATION = 600;

    /**
     * Generate a new unique CAPTCHA challenge.
     *
     * @param string|null $clientIp
     * @param int|null $length
     * @return array{token: string, image: string, expires_in: int}
     */
    public function generate(?string $clientIp = null, ?int $length = null): array
    {
        // Optimal length between 4 and 6 characters (within 3-8 range)
        if ($length === null || $length < 3 || $length > 8) {
            $length = random_int(4, 6);
        }

        $code = $this->generateRandomCode($length, $clientIp);
        $token = (string) Str::uuid();

        // Store token in Cache for 5 minutes (300 seconds)
        $cacheKey = $this->getCacheKey($token);
        Cache::put($cacheKey, [
            'code'       => $code,
            'ip'         => $clientIp,
            'created_at' => now()->timestamp,
        ], now()->addSeconds(self::EXPIRATION_SECONDS));

        // Track last generated code per IP/Session to avoid consecutive identical codes
        if ($clientIp) {
            Cache::put('last_captcha_code_' . md5($clientIp), $code, now()->addMinutes(10));
        }

        $svgImage = $this->generateSvgImage($code);
        $base64Image = 'data:image/svg+xml;base64,' . base64_encode($svgImage);

        return [
            'token'      => $token,
            'image'      => $base64Image,
            'length'     => strlen($code),
            'expires_in' => self::EXPIRATION_SECONDS,
        ];
    }

    /**
     * Generate random code and ensure it is not identical to the immediately previous code.
     */
    private function generateRandomCode(int $length, ?string $clientIp = null): string
    {
        $lastCode = $clientIp ? Cache::get('last_captcha_code_' . md5($clientIp)) : null;
        $maxTries = 5;
        $code = '';

        while ($maxTries-- > 0) {
            $code = '';
            $poolLength = strlen(self::CHARACTERS);

            for ($i = 0; $i < $length; $i++) {
                $code .= self::CHARACTERS[random_int(0, $poolLength - 1)];
            }

            if ($code !== $lastCode) {
                break;
            }
        }

        return $code;
    }

    /**
     * Verify user submitted CAPTCHA code.
     *
     * @param string $token
     * @param string $userInputCode
     * @param string|null $clientIp
     * @return array{success: bool, message: string, proof_token?: string}
     */
    public function verify(string $token, string $userInputCode, ?string $clientIp = null): array
    {
        $ip = $clientIp ?: request()->ip();

        // 1. Check Rate Limiting on IP
        if ($this->isRateLimited($ip)) {
            return [
                'success' => false,
                'message' => 'Too many failed CAPTCHA attempts. Please wait a few minutes and try again.',
            ];
        }

        $cacheKey = $this->getCacheKey($token);
        $cachedData = Cache::get($cacheKey);

        // 2. Token must exist and not be expired
        if (!$cachedData || !isset($cachedData['code'])) {
            $this->recordFailedAttempt($ip);
            return [
                'success' => false,
                'message' => 'CAPTCHA has expired or is invalid. Please refresh and try again.',
            ];
        }

        $actualCode = (string) $cachedData['code'];

        // 3. Clean and normalize inputs (Case-insensitive comparison for superior UX & accessibility)
        $cleanUserInput = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', trim($userInputCode)));
        $cleanActualCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $actualCode));

        if ($cleanUserInput === '' || $cleanUserInput !== $cleanActualCode) {
            $this->recordFailedAttempt($ip);
            return [
                'success' => false,
                'message' => 'Invalid CAPTCHA characters. Please try again with the new image.',
            ];
        }

        // 4. Immediately consume / destroy the CAPTCHA token (Single-use)
        Cache::forget($cacheKey);

        // 5. Verification successful: reset failed attempt counter and issue single-use proof token
        $this->resetFailedAttempts($ip);
        $proofToken = (string) Str::uuid();
        Cache::put('captcha_proof_' . $proofToken, [
            'ip'         => $ip,
            'verified_at'=> now()->timestamp,
        ], now()->addMinutes(15));

        return [
            'success'     => true,
            'message'     => 'CAPTCHA verified successfully.',
            'proof_token' => $proofToken,
        ];
    }

    /**
     * Validate a registration proof token.
     */
    public function validateProofToken(?string $proofToken, ?string $clientIp = null): bool
    {
        if (empty($proofToken)) {
            return false;
        }

        $key = 'captcha_proof_' . $proofToken;
        $data = Cache::get($key);

        if (!$data) {
            return false;
        }

        // Optional IP check
        $ip = $clientIp ?: request()->ip();
        if (isset($data['ip']) && $data['ip'] !== $ip && $data['ip'] !== '127.0.0.1' && $ip !== '127.0.0.1') {
            return false;
        }

        // Consume proof token after successful registration usage
        Cache::forget($key);
        return true;
    }

    /**
     * Check if client IP is currently rate-limited.
     */
    private function isRateLimited(string $ip): bool
    {
        $throttleKey = 'captcha_throttle_' . md5($ip);
        return Cache::has($throttleKey);
    }

    /**
     * Record a failed attempt and throttle if threshold reached.
     */
    private function recordFailedAttempt(string $ip): void
    {
        $attemptsKey = 'captcha_attempts_' . md5($ip);
        $attempts = (int) Cache::get($attemptsKey, 0) + 1;
        Cache::put($attemptsKey, $attempts, now()->addSeconds(self::THROTTLE_DURATION));

        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::put('captcha_throttle_' . md5($ip), true, now()->addSeconds(self::THROTTLE_DURATION));
            Log::warning("CAPTCHA Rate limit triggered for IP: {$ip} after {$attempts} failed attempts.");
        }
    }

    /**
     * Reset failed attempts upon successful verification.
     */
    private function resetFailedAttempts(string $ip): void
    {
        Cache::forget('captcha_attempts_' . md5($ip));
        Cache::forget('captcha_throttle_' . md5($ip));
    }

    /**
     * Generate cache key for a token.
     */
    private function getCacheKey(string $token): string
    {
        return 'captcha_challenge_' . $token;
    }

    /**
     * Generate dynamic, highly-secure SVG image with noise, bezier curves,
     * character rotation, distortion, and variable background gradients.
     */
    public function generateSvgImage(string $code): string
    {
        $width = 240;
        $height = 76;
        $length = strlen($code);

        // Color palettes for backgrounds (soft / subtle modern pastel gradients)
        $bgGradients = [
            ['#f8fafc', '#e2e8f0', '#cbd5e1'],
            ['#fff7ed', '#ffedd5', '#fed7aa'],
            ['#f0fdf4', '#dcfce7', '#bbf7d0'],
            ['#f0f9ff', '#e0f2fe', '#bae6fd'],
            ['#faf5ff', '#f3e8ff', '#e9d5ff'],
            ['#fdf4ff', '#fae8ff', '#f5d0fe'],
        ];
        $selectedBg = $bgGradients[random_int(0, count($bgGradients) - 1)];

        // Character font colors (high contrast, distinct, professional)
        $charColors = [
            '#0f172a', '#1e293b', '#334155', '#1e3a8a', '#1d4ed8',
            '#0369a1', '#0f766e', '#15803d', '#b45309', '#c2410c',
            '#b91c1c', '#6d28d9', '#4338ca', '#be185d'
        ];

        // Random noise lines (bezier curves across the image)
        $curveLinesSvg = '';
        $numCurves = random_int(4, 7);
        for ($i = 0; $i < $numCurves; $i++) {
            $startX = random_int(-10, 20);
            $startY = random_int(10, $height - 10);
            $cp1X = random_int(40, 100);
            $cp1Y = random_int(0, $height);
            $cp2X = random_int(120, 180);
            $cp2Y = random_int(0, $height);
            $endX = random_int($width - 20, $width + 10);
            $endY = random_int(10, $height - 10);

            $strokeColor = $charColors[random_int(0, count($charColors) - 1)];
            $strokeWidth = random_int(15, 25) / 10.0; // 1.5 - 2.5px
            $opacity = random_int(30, 55) / 100.0;

            $curveLinesSvg .= "<path d=\"M {$startX} {$startY} C {$cp1X} {$cp1Y}, {$cp2X} {$cp2Y}, {$endX} {$endY}\" fill=\"none\" stroke=\"{$strokeColor}\" stroke-width=\"{$strokeWidth}\" stroke-opacity=\"{$opacity}\" stroke-linecap=\"round\" />\n";
        }

        // Random noise dots and short scratches
        $noiseSvg = '';
        $numDots = random_int(60, 100);
        for ($i = 0; $i < $numDots; $i++) {
            $cx = random_int(5, $width - 5);
            $cy = random_int(5, $height - 5);
            $r = random_int(8, 22) / 10.0; // 0.8 - 2.2px
            $color = $charColors[random_int(0, count($charColors) - 1)];
            $opacity = random_int(20, 60) / 100.0;
            $noiseSvg .= "<circle cx=\"{$cx}\" cy=\"{$cy}\" r=\"{$r}\" fill=\"{$color}\" fill-opacity=\"{$opacity}\" />\n";
        }

        // Additional geometric disturbance shapes
        for ($i = 0; $i < 6; $i++) {
            $rx = random_int(10, $width - 20);
            $ry = random_int(10, $height - 20);
            $size = random_int(3, 7);
            $color = $charColors[random_int(0, count($charColors) - 1)];
            $opacity = random_int(20, 45) / 100.0;
            $noiseSvg .= "<rect x=\"{$rx}\" y=\"{$ry}\" width=\"{$size}\" height=\"{$size}\" fill=\"{$color}\" fill-opacity=\"{$opacity}\" transform=\"rotate(" . random_int(0, 90) . " {$rx} {$ry})\" />\n";
        }

        // Available standard vector web fonts
        $fontFamilies = [
            "'Segoe UI', Roboto, Helvetica, Arial, sans-serif",
            "'Trebuchet MS', 'Lucida Sans Unicode', sans-serif",
            "'Courier New', Courier, monospace",
            "Georgia, 'Times New Roman', serif",
            "Verdana, Geneva, sans-serif",
        ];

        // Render each character with independent rotation, font size, color, baseline shift
        $charactersSvg = '';
        $charSpacing = ($width - 40) / max(1, $length);

        for ($i = 0; $i < $length; $i++) {
            $char = htmlspecialchars($code[$i], ENT_QUOTES, 'UTF-8');
            $x = 22 + ($i * $charSpacing) + random_int(-3, 3);
            $y = 48 + random_int(-5, 5);

            $angle = random_int(-25, 25);
            $fontSize = random_int(28, 34);
            $color = $charColors[random_int(0, count($charColors) - 1)];
            $fontFamily = $fontFamilies[random_int(0, count($fontFamilies) - 1)];
            $fontWeight = (random_int(0, 1) === 1) ? 'bold' : '800';

            // Add slight drop shadow filter or stroke on each letter for visual pop and bot confusion
            $charactersSvg .= "<text x=\"{$x}\" y=\"{$y}\" "
                . "font-family=\"{$fontFamily}\" "
                . "font-size=\"{$fontSize}px\" "
                . "font-weight=\"{$fontWeight}\" "
                . "fill=\"{$color}\" "
                . "transform=\"rotate({$angle}, {$x}, {$y})\" "
                . "letter-spacing=\"2\" "
                . "filter=\"url(#charGlow)\">{$char}</text>\n";
        }

        // SVG markup
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}" style="border-radius: 8px; user-select: none; -webkit-user-select: none;">
    <defs>
        <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="{$selectedBg[0]}" />
            <stop offset="50%" stop-color="{$selectedBg[1]}" />
            <stop offset="100%" stop-color="{$selectedBg[2]}" />
        </linearGradient>
        <pattern id="gridPattern" width="16" height="16" patternUnits="userSpaceOnUse">
            <path d="M 16 0 L 0 0 0 16" fill="none" stroke="#000000" stroke-width="0.5" stroke-opacity="0.04" />
        </pattern>
        <filter id="charGlow" x="-10%" y="-10%" width="120%" height="120%">
            <feDropShadow dx="1" dy="1" stdDeviation="0.8" flood-opacity="0.25" />
        </filter>
    </defs>
    
    <!-- Background -->
    <rect width="{$width}" height="{$height}" fill="url(#bgGrad)" rx="8" />
    <rect width="{$width}" height="{$height}" fill="url(#gridPattern)" rx="8" />
    
    <!-- Background Distortions -->
    {$noiseSvg}
    {$curveLinesSvg}
    
    <!-- CAPTCHA Characters -->
    {$charactersSvg}
    
    <!-- Outer Border -->
    <rect width="{$width}" height="{$height}" fill="none" stroke="#cbd5e1" stroke-width="1.5" rx="8" />
</svg>
SVG;

        return trim($svg);
    }
}
