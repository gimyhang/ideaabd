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

    /** Query parameters that never change the page. */
    private const IGNORE_PARAMS = ['fbclid', 'gclid', 'msclkid', 'ref', 'v', '_'];

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->eligible($request)) {
            return $next($request);
        }

        $key = $this->key($request);
        $hit = Cache::get($key);
        if (is_array($hit) && isset($hit['html'])) {
            $html = str_replace(self::TOKEN, $request->session()->token(), $hit['html']);
            return response($html, 200, [
                'Content-Type' => $hit['type'] ?? 'text/html; charset=UTF-8',
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
            try {
                Cache::put($key, ['html' => $html, 'type' => $response->headers->get('Content-Type')], self::TTL);
            } catch (\Throwable) {
                // a full disk or a locked cache file must never break the page
            }
            $response->headers->set('X-Page-Cache', 'MISS');
        }

        return $response;
    }

    /** Clear every cached page (content changed). */
    public static function flush(): void
    {
        try {
            Cache::forever(self::VERSION_KEY, (int) Cache::get(self::VERSION_KEY, 1) + 1);
        } catch (\Throwable) {
        }
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

        return strlen((string) $request->getQueryString()) <= 300;
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

    private function key(Request $request): string
    {
        $query = $request->query();
        foreach (array_keys($query) as $k) {
            if (str_starts_with((string) $k, 'utm_') || in_array($k, self::IGNORE_PARAMS, true)) {
                unset($query[$k]);
            }
        }
        ksort($query);
        $url = $request->getSchemeAndHttpHost() . '/' . ltrim($request->path(), '/') . ($query ? '?' . http_build_query($query) : '');
        $version = (int) Cache::get(self::VERSION_KEY, 1);

        return 'gpc:' . $version . ':' . app()->getLocale() . ':' . sha1($url);
    }
}
