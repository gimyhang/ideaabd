<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Whole-page cache for visitors who are not signed in.
 *
 * Building the home page or the book list takes 1.5–2.5 s on the live server; with several visitors at once the
 * requests queue and everyone waits 5–6 s. A guest sees the same page as every other guest, so the finished HTML is
 * kept for a few minutes and served in a few milliseconds.
 *
 * Safe by design:
 *  - signed-in users, non-GET requests, pages with a flash message / old form input, and private areas are never
 *    cached or served from cache;
 *  - each visitor's own CSRF token is put back into the cached HTML (forms keep working);
 *  - the language is part of the key; tracking parameters (utm_*, fbclid, …) are not;
 *  - saving a book, post, author, category, … clears every cached page (version bump, see AppServiceProvider),
 *    and the admin "Clear Cache" button empties it too.
 * Turn off with PAGE_CACHE=false in .env.
 */
class GuestPageCache
{
    public const TTL = 300;
    private const TOKEN = '%%GPC_CSRF%%';
    private const VERSION_KEY = 'gpc:version';

    /** Paths (prefixes) that are personal, transactional or admin. */
    private const SKIP = [
        'admin', 'author', 'publisher/', 'seller', 'subadmin', 'company-panel', 'my-account', 'user', 'wishlist',
        'cart', 'checkout', 'payment', 'orders', 'track-order', 'login', 'logout', 'register', 'password',
        'forgot-password', 'reset-password', 'pending-approval', 'auth', 'api', 'tts', 'search/suggest', 'invoices',
        'event-registration', 'event', 'rsu', 'card', 'coupon', 'v/', 'pathagar/', 'sitemap', 'feed', 'storage',
        'lang', 'blog/write', 'ideapatra/write', 'share',
    ];

    /** Query parameters a cached page may have (filters, sorting, paging). */
    private const CACHE_PARAMS = ['page', 'sort', 'category', 'author', 'publisher', 'format', 'type', 'tag', 'search', 'q', 'discount_min', 'per_page', 'view', 'lang', 'year', 'month', 'all_time', 'fy'];

    /** Query parameters that never change the page. */
    private const IGNORE_PARAMS = ['fbclid', 'gclid', 'msclkid', 'ref', 'v', '_'];

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->eligible($request)) {
            return $next($request);
        }

        $file = $this->file($request);
        $html = self::read($file);
        if ($html !== null) {
            return response(str_replace(self::TOKEN, $request->session()->token(), $html), 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Page-Cache' => 'HIT',
            ]);
        }

        $response = $next($request);

        if ($this->storable($request, $response)) {
            $html = (string) $response->getContent();
            $token = $request->session()->token();
            if ($token !== '' && $token !== null) {
                $html = str_replace($token, self::TOKEN, $html);
            }
            self::write($file, $html);
            // once: remove whole pages left in the general cache by the earlier version of this cache
            app()->terminating(static fn () => self::purgeLegacyEntries());
            $response->headers->set('X-Page-Cache', 'MISS');
        }

        return $response;
    }

    // ── storage ────────────────────────────────────────────────────────────────────────────────────────────────
    // Pages live in their own folder (storage/framework/page-cache/v<version>/), gzip-compressed (~85% smaller),
    // never more than MAX_FILES / MAX_BYTES, and never when the disk is low. A content change deletes the old
    // folder, so nothing piles up (the general file cache only deletes an expired entry when it is read again).

    public const MAX_FILES = 3000;
    public const MAX_BYTES = 50 * 1024 * 1024;
    private const MIN_FREE_DISK = 300 * 1024 * 1024;

    private static function root(): string
    {
        return storage_path('framework/page-cache');
    }

    private static function read(string $file): ?string
    {
        try {
            if (!is_file($file) || filemtime($file) < time() - self::TTL) {
                return null;
            }
            $gz = @file_get_contents($file);
            $html = $gz !== false ? @gzdecode($gz) : false;

            return is_string($html) && $html !== '' ? $html : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function write(string $file, string $html): void
    {
        try {
            $dir = dirname($file);
            if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
                return;
            }
            $free = @disk_free_space($dir);
            if ($free !== false && $free < self::MIN_FREE_DISK) {
                return;
            }
            // size budget for this version, counted in the cache (cheap) — re-counted when a folder starts
            $countKey = 'gpc:budget:' . basename($dir);
            $budget = Cache::get($countKey, ['files' => 0, 'bytes' => 0]);
            if (!is_file($file) && ($budget['files'] >= self::MAX_FILES || $budget['bytes'] >= self::MAX_BYTES)) {
                return;
            }
            $gz = gzencode($html, 6);
            $tmp = $file . '.' . getmypid() . '.tmp';
            if (@file_put_contents($tmp, $gz) !== false && @rename($tmp, $file)) {
                $budget['files']++;
                $budget['bytes'] += strlen($gz);
                Cache::put($countKey, $budget, 86400);
            } else {
                @unlink($tmp);
            }
        } catch (\Throwable) {
            // a full disk or a locked file must never break the page
        }
    }

    /** Clear every cached page (content changed): new folder now, old folders removed after the response. */
    public static function flush(): void
    {
        try {
            Cache::forever(self::VERSION_KEY, (int) Cache::get(self::VERSION_KEY, 1) + 1);
        } catch (\Throwable) {
            return;
        }
        static $queued = false;
        if ($queued) {
            return;
        }
        $queued = true;
        $clean = static fn () => self::removeOldVersions();
        try {
            app()->terminating($clean);
        } catch (\Throwable) {
            $clean();
        }
    }

    /** Delete page folders of older versions (and the pre-folder page entries left in the general cache, once). */
    public static function removeOldVersions(): int
    {
        $removed = 0;
        try {
            $current = 'v' . (int) Cache::get(self::VERSION_KEY, 1);
            foreach (glob(self::root() . '/v*', GLOB_ONLYDIR) ?: [] as $dir) {
                if (basename($dir) === $current) {
                    continue;
                }
                foreach (glob($dir . '/*') ?: [] as $f) {
                    if (@unlink($f)) $removed++;
                }
                @rmdir($dir);
            }
            $removed += self::purgeLegacyEntries();
        } catch (\Throwable) {
        }

        return $removed;
    }

    /**
     * Before the page folder existed, whole pages were kept in the general file cache, where expired entries stay
     * on disk until read again. Those files (value starts with the "html" key) are removed once.
     */
    public static function purgeLegacyEntries(bool $force = false): int
    {
        if (!$force) {
            try {
                if (!Cache::add('gpc:legacy-purged', 1, 86400 * 365)) {
                    return 0;
                }
            } catch (\Throwable) {
                // the cache cannot even be written (full disk): that is exactly when the purge is needed
            }
        }
        $removed = 0;
        $base = storage_path('framework/cache/data');
        if (!is_dir($base)) {
            return 0;
        }
        try {
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
            foreach ($it as $f) {
                if (!$f->isFile() || $f->getSize() < 400) {
                    continue;
                }
                $head = (string) @file_get_contents($f->getPathname(), false, null, 0, 40);
                // file cache format: 10-digit expiry + serialized value
                if (str_contains($head, 'a:2:{s:4:"html";')) {
                    if (@unlink($f->getPathname())) $removed++;
                }
            }
        } catch (\Throwable) {
        }

        return $removed;
    }

    private function eligible(Request $request): bool
    {
        if (!env('PAGE_CACHE', true) || !$request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return false;
        }
        if (!$request->hasSession() || $request->user()) {
            return false;
        }
        $session = $request->session();
        foreach (['success', 'error', 'status', 'warning', 'info', 'message', 'errors', '_old_input'] as $k) {
            if ($session->has($k)) {
                return false;
            }
        }
        $path = ltrim($request->path(), '/');
        foreach (self::SKIP as $prefix) {
            if ($path === rtrim($prefix, '/') || str_starts_with($path, rtrim($prefix, '/') . '/') || (str_ends_with($prefix, '/') && str_starts_with($path, $prefix))) {
                return false;
            }
        }
        // reading, downloading and printing are per person (licences, receipts)
        if (preg_match('#/(read|download|stream|preview|print|pdf|receipt|challan|slip)(/|$)#', '/' . $path)) {
            return false;
        }
        $route = $request->route()?->getName() ?? '';
        if ($route !== '' && preg_match('/^(event\.|cart|checkout|login|register|password|my-account|wishlist|admin\.|author\.|publisher\.|subadmin\.)/', $route)) {
            return false;
        }

        // only the filters pages really have; any other parameter (bots, typos) is served fresh and not stored
        foreach (self::cleanQuery($request) as $k => $v) {
            if (!in_array($k, self::CACHE_PARAMS, true) || is_array($v) || strlen((string) $v) > 80) {
                return false;
            }
        }

        return true;
    }

    private function storable(Request $request, Response $response): bool
    {
        if ($response->getStatusCode() !== 200 || $response->headers->has('X-No-Page-Cache')) {
            return false;
        }
        if (!str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return false;
        }
        if (method_exists($response, 'getContent') && $response->getContent() === false) {
            return false; // streamed / file responses
        }
        // signed in while the page was built (e.g. auto-login) or a message was flashed for the next page
        if ($request->user() || $request->session()->has('_flash.new') && !empty($request->session()->get('_flash.new'))) {
            return false;
        }

        return strlen((string) $response->getContent()) > 500;
    }

    /** The query without tracking parameters, sorted (same page = same file). */
    private static function cleanQuery(Request $request): array
    {
        $query = $request->query();
        foreach (array_keys($query) as $k) {
            if (str_starts_with((string) $k, 'utm_') || in_array($k, self::IGNORE_PARAMS, true)) {
                unset($query[$k]);
            }
        }
        ksort($query);

        return $query;
    }

    /** Cache file of this page: page-cache/v<version>/<locale>-<sha1 of the address>.gz */
    private function file(Request $request): string
    {
        $query = self::cleanQuery($request);
        $url = $request->getSchemeAndHttpHost() . '/' . ltrim($request->path(), '/') . ($query ? '?' . http_build_query($query) : '');
        $version = (int) Cache::get(self::VERSION_KEY, 1);

        return self::root() . '/v' . $version . '/' . preg_replace('/[^a-z_-]/i', '', app()->getLocale()) . '-' . sha1($url) . '.gz';
    }
}
