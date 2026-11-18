<?php

namespace Modules\SEO\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SeoBrokenLink extends Model
{
    protected $table = 'seo_broken_links';

    protected $fillable = [
        'url',
        'referer',
        'user_agent',
        'ip_address',
        'hits_count',
        'is_resolved',
        'last_hit_at',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'hits_count'  => 'integer',
        'last_hit_at' => 'datetime',
    ];

    /** Paths only attack scanners ask for (WordPress, PHP shells, secrets). Not worth a row in the monitor. */
    public const PROBE_PATTERN = '#(^/(wp-|wordpress|xmlrpc|cgi-bin|phpmyadmin|pma|\.git|\.env|\.aws|vendor/phpunit|boaform|actuator|_ignition|server-status))|(\.(php\d?|asp|aspx|jsp|cgi|env|ini|bak|sql|log|old|swp)$)#i';

    public const KINDS = [
        'page'  => 'পেজ',
        'image' => 'ছবি',
        'asset' => 'ফাইল',
    ];

    /**
     * Record a 404 hit. Scanner probes are skipped; a link that was marked fixed but is
     * failing again is re-opened so it shows up in the list.
     */
    public static function logHit(string $url, ?string $referer = null, ?string $userAgent = null, ?string $ip = null): ?self
    {
        $cleanUrl = '/' . ltrim((string) (parse_url($url, PHP_URL_PATH) ?? $url), '/');
        if (preg_match(self::PROBE_PATTERN, $cleanUrl)) {
            return null;
        }
        // Columns are 500 / 45 characters: longer values used to make the save fail silently.
        $cleanUrl = Str::limit($cleanUrl, 495, '');

        $record = static::firstOrNew(['url' => $cleanUrl]);
        $record->referer = $referer ? Str::limit($referer, 495, '') : $record->referer;
        $record->user_agent = $userAgent ? Str::limit($userAgent, 495, '') : $record->user_agent;
        $record->ip_address = $ip ? Str::limit($ip, 45, '') : $record->ip_address;
        $record->hits_count = ($record->hits_count ?? 0) + 1;
        $record->last_hit_at = now();
        $record->is_resolved = false;
        $record->save();

        return $record;
    }

    /** The address as people read it (Bangla instead of %E0%A6…). */
    public function getDisplayUrlAttribute(): string
    {
        return rawurldecode((string) $this->url);
    }

    /** page / image / asset */
    public function getKindAttribute(): string
    {
        $ext = strtolower(pathinfo((string) parse_url($this->url, PHP_URL_PATH), PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'ico', 'bmp'], true)) {
            return 'image';
        }
        if ($ext !== '' && !in_array($ext, ['html', 'htm'], true)) {
            return 'asset';
        }

        return 'page';
    }

    /** A file under public/ that exists now (e.g. a missing placeholder that has since been added). */
    public function existsNow(): bool
    {
        if ($this->kind === 'page') {
            return false;
        }
        $rel = ltrim(rawurldecode((string) parse_url($this->url, PHP_URL_PATH)), '/');
        if ($rel === '' || str_contains($rel, '..')) {
            return false;
        }

        return is_file(public_path($rel))
            || (str_starts_with($rel, 'storage/') && is_file(storage_path('app/public/' . substr($rel, 8))));
    }

    /**
     * A likely correct address for this broken one, or null. Only addresses that resolve to a
     * route on this site are suggested.
     */
    public function suggestion(): ?string
    {
        $path = rawurldecode((string) parse_url($this->url, PHP_URL_PATH));
        $candidates = [];

        // Links that leaked Laravel's /public folder or index.php into the address.
        foreach (['/public', '/index.php'] as $prefix) {
            if (str_starts_with($path, $prefix . '/')) {
                $candidates[] = substr($path, strlen($prefix));
            }
        }
        // Old WooCommerce-style shop addresses.
        if (preg_match('#^/product/([^/]+)#u', $path, $m)) {
            $slug = $m[1];
            if (class_exists(\Modules\Book\Models\Book::class) && ($b = \Modules\Book\Models\Book::where('slug', $slug)->where('is_active', true)->first(['slug']))) {
                $candidates[] = '/books/' . $b->slug;
            }
            $candidates[] = '/books?search=' . str_replace('-', ' ', $slug);
        }
        if (preg_match('#^/product-category/([^/]+)#u', $path, $m)) {
            $candidates[] = '/books/category/' . $m[1];
            $candidates[] = '/books';
        }
        if (preg_match('#^/(?:public/)?blog/category/([^/]+)#u', $path, $m)) {
            $candidates[] = '/blog/category/' . $m[1];
            $candidates[] = '/blog';
        }
        // Same address without a trailing slash or in lower case.
        if (strlen($path) > 1 && str_ends_with($path, '/')) {
            $candidates[] = rtrim($path, '/');
        }
        if ($path !== mb_strtolower($path)) {
            $candidates[] = mb_strtolower($path);
        }

        foreach (array_unique($candidates) as $c) {
            if ($c !== $path && self::routeExists($c)) {
                return $c;
            }
        }

        return null;
    }

    /** Does this site have a (non-catch-all) GET route for the path? */
    public static function routeExists(string $path): bool
    {
        try {
            $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create(strtok($path, '?') ?: '/', 'GET'));

            return $route && !$route->isFallback && !in_array($route->getName(), ['storage.file'], true);
        } catch (\Throwable) {
            return false;
        }
    }
}
