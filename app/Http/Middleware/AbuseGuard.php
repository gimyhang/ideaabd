<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * One place for robot and abuse control on the public site:
 *  - blocks vulnerability scanners and aggressive scrapers by User-Agent
 *  - counts probes of paths that do not exist here (/wp-login.php, /.env …) and blocks the IP after a few
 *  - rate-limits the whole site per IP, and sensitive forms much harder (OTP/SMS, login help, contact, orders…)
 *  - honeypot + "too fast" trap for forms that carry the <x-bot-trap /> fields
 * Search engines and normal visitors are not affected.
 */
class AbuseGuard
{
    /** route name → [bucket, what the limit is counted per] */
    private const SENSITIVE = [
        // codes sent by SMS / e-mail: also limited per phone or e-mail so one number cannot be spammed
        'register.send-otp'       => 'otp', 'register.send-email-otp' => 'otp', 'password.email' => 'otp', 'password.send-otp' => 'otp',
        'login.otp.send'          => 'otp', 'my-account.phone.send-otp' => 'otp',
        // guessing a code or a password
        'register.verify-otp'     => 'verify', 'register.verify-email-otp' => 'verify', 'password.verify-otp' => 'verify',
        'login.otp.verify-set-password' => 'verify', 'password.update-otp' => 'verify', 'password.update' => 'verify',
        'register.check-phone'    => 'lookup', 'cart.validate-coupon' => 'lookup',
        // anything that creates records or sends messages
        'contact.submit' => 'form', 'newsletter.subscribe' => 'form', 'book-requests.store' => 'form', 'reviews.store' => 'form',
        'blog.review.store' => 'form', 'author.honorarium.send' => 'form', 'blog.honorarium.send' => 'form',
        'ideapatra.honorarium.send' => 'form', 'author.store-registration' => 'form', 'publisher.store-registration' => 'form',
        'publisher.store-registration.alt' => 'form', 'register.submit' => 'form', 'register.quick-customer' => 'form',
        'register.complete' => 'form', 'pathagar.acknowledgment.submit' => 'form', 'event.submit' => 'form',
        'orders.store' => 'form', 'cart.checkout' => 'form',
    ];

    /** bucket → [per-IP per minute, per-IP per hour, per-target per day (0 = not used)] */
    private const LIMITS = [
        'otp'    => [4, 20, 6],
        'verify' => [10, 60, 30],
        'lookup' => [15, 120, 0],
        'form'   => [8, 60, 0],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!config('security.enabled', true) || (app()->runningUnitTests() && !config('security.test_mode'))) {
            return $next($request);
        }

        $ip = (string) $request->ip();

        // Edge checks run once, as a global middleware, so they also see requests for pages that do not exist.
        if (!$request->attributes->get('abuse.edge')) {
            $request->attributes->set('abuse.edge', true);

            if (Cache::has("abuse:block:$ip")) {
                return $this->deny($request, 403, 'Access denied.');
            }

            $agent = strtolower((string) $request->userAgent());
            foreach (array_merge(config('security.blocked_agents', []), config('security.blocked_scrapers', [])) as $bad) {
                if ($agent !== '' && str_contains($agent, $bad)) {
                    return $this->deny($request, 403, 'Access denied.');
                }
            }
            if ($agent === '' && !$request->isMethodSafe() && !$request->is('payment/*', 'api/payment/*')) {
                return $this->strike($request, $ip, 'empty user agent on a write request');
            }

            // Downloading a backup file (…/admin/backup/download/dump.sql) is not a probe, even though the name ends in .sql.
            // The route is behind the admin login, so nothing is exposed by letting it through to the router.
            if (!$request->is('admin/backup/download/*') && preg_match(config('security.probe_paths'), $request->path())) {
                return $this->strike($request, $ip, 'probe ' . $request->path());
            }

            $key = "abuse:global:$ip";
            if (RateLimiter::tooManyAttempts($key, config('security.global_per_minute', 240))) {
                return $this->deny($request, 429, 'Too many requests. Please slow down.', RateLimiter::availableIn($key));
            }
            RateLimiter::hit($key, 60);

            return $next($request);
        }

        // Form checks need the route, so they run inside the web group.
        if ($request->isMethod('POST') && $request->route()) {
            $bucket = self::SENSITIVE[$request->route()->getName() ?? ''] ?? ($request->is('login') ? 'verify' : null);

            if ($bucket !== null && ($response = $this->limitBucket($request, $bucket, $ip))) {
                return $response;
            }

            if ($response = $this->trap($request, $ip)) {
                return $response;
            }
        }

        return $next($request);
    }

    private function limitBucket(Request $request, string $bucket, string $ip): ?Response
    {
        [$perMin, $perHour, $perDay] = self::LIMITS[$bucket];

        foreach ([["m", $perMin, 60], ["h", $perHour, 3600]] as [$tag, $max, $decay]) {
            $key = "abuse:$bucket:$tag:$ip";
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return $this->deny($request, 429, 'Too many attempts. Please wait and try again.', RateLimiter::availableIn($key));
            }
            RateLimiter::hit($key, $decay);
        }

        // One phone number / e-mail address can only be sent so many codes per day, whoever asks.
        if ($perDay > 0) {
            $target = strtolower(trim((string) ($request->input('phone') ?: $request->input('email') ?: $request->input('identifier') ?: '')));
            $target = preg_replace('/[^a-z0-9@.+]/', '', $target) ?? '';
            if ($target !== '') {
                $key = "abuse:$bucket:target:" . sha1($target);
                if (RateLimiter::tooManyAttempts($key, $perDay)) {
                    return $this->deny($request, 429, 'Too many codes were requested for this number or e-mail. Try again tomorrow.', RateLimiter::availableIn($key));
                }
                RateLimiter::hit($key, 86400);
            }
        }

        return null;
    }

    private function trap(Request $request, string $ip): ?Response
    {
        $honey = $request->input('website_url');
        $stamp = $request->input('_ts');

        $filled = $honey !== null && trim((string) $honey) !== '';
        $tooFast = false;

        if ($stamp !== null && $stamp !== '') {
            try {
                $issued = (int) Crypt::decryptString((string) $stamp);
                $tooFast = $issued > 0 && (time() - $issued) < config('security.min_form_seconds', 2);
            } catch (\Throwable) {
                $tooFast = false; // a stale or missing stamp is not proof of a robot
            }
        }

        if (!$filled && !$tooFast) {
            return null;
        }

        Log::notice('AbuseGuard: robot form post dropped', ['ip' => $ip, 'path' => $request->path(), 'honeypot' => $filled, 'too_fast' => $tooFast]);
        Cache::put("abuse:robot:$ip", (int) Cache::get("abuse:robot:$ip", 0) + 1, now()->addHour());

        // Look successful to the robot so it does not adapt, but do nothing.
        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => 'OK'])
            : back()->with('success', 'ধন্যবাদ।');
    }

    private function strike(Request $request, string $ip, string $why): Response
    {
        $key = "abuse:strikes:$ip";
        $n = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $n, now()->addMinutes(config('security.strike_window_min', 10)));

        if ($n >= config('security.strikes_to_block', 4)) {
            Cache::put("abuse:block:$ip", true, now()->addMinutes(config('security.block_minutes', 60)));
            Log::warning('AbuseGuard: IP blocked', ['ip' => $ip, 'last' => $why, 'strikes' => $n]);
        }

        return $this->deny($request, 404, 'Not found.');
    }

    private function deny(Request $request, int $status, string $message, ?int $retryAfter = null): Response
    {
        $headers = $retryAfter ? ['Retry-After' => (string) $retryAfter] : [];

        return $request->expectsJson()
            ? response()->json(['success' => false, 'message' => $message], $status, $headers)
            : response($message, $status, $headers + ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
