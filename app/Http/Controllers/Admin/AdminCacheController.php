<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAccessService;
use App\Services\ImageCleanupService;
use App\Support\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cache & performance page. Every action answers JSON to the page's script (and a redirect
 * with a flash message when posted without JavaScript).
 */
class AdminCacheController extends Controller
{
    /**
     * Cache keys the site really uses (label, what it holds, how to rebuild it).
     * 'warm' = a page whose first visit rebuilds the key, or 'settings'.
     */
    private const KEYS = [
        'site_global_settings_cache'      => ['সাইট সেটিংস', 'লোগো, যোগাযোগ, পেমেন্ট ও সব সেটিং', 'Settings', 'settings'],
        'home_page_shared_data_v3'        => ['হোমপেজের ডেটা', 'হোমপেজের বই, ক্যাটাগরি ও ব্যানার', 'Homepage', '/'],
        'site_header_categories_v3'       => ['হেডারের ক্যাটাগরি মেনু', 'সাইটের উপরের মেনুর ক্যাটাগরি', 'Menu', '/'],
        'site_header_authors_v3'          => ['হেডারের লেখক মেনু', 'মেনুতে দেখানো লেখক', 'Menu', '/'],
        'site_header_blog_cats_v3'        => ['হেডারের ব্লগ মেনু', 'মেনুতে দেখানো ব্লগ ক্যাটাগরি', 'Menu', '/'],
        'master_sitemap_xml_v2'           => ['সাইটম্যাপ', 'গুগলের জন্য sitemap.xml', 'SEO', '/sitemap.xml'],
        'active_currencies_list'          => ['মুদ্রার তালিকা', 'সক্রিয় মুদ্রা ও রেট', 'Currency', null],
        'schema_all_tables_map'           => ['ডাটাবেস টেবিল ম্যাপ', 'কোন টেবিল/কলাম আছে তার দ্রুত তালিকা', 'System', null],
        'admin_dash_pending_alerts'       => ['অ্যাডমিন অ্যালার্ট', 'বেলের পেন্ডিং অর্ডার, বই, ব্লগ', 'Admin', null],
        'admin_pending_registrations_count' => ['পেন্ডিং রেজিস্ট্রেশন', 'সাইডবারের রেজিস্ট্রেশন ব্যাজ', 'Admin', null],
        'admin_sidebar_pending_badges'    => ['সাইডবারের ব্যাজ', 'সাইডবারের সংখ্যাগুলো', 'Admin', null],
        'db_schema_auto_healed_v2'        => ['ডাটাবেস স্বয়ংক্রিয় মেরামত', 'একবার-চলা ডাটাবেস চেক', 'System', null],
    ];

    /** Words that mark a value as secret when a key is inspected. */
    private const SECRET_PATTERN = '/(pass(word)?|secret|token|api[_-]?key|app[_-]?key|private|credential|signature|otp|pin)$/i';

    public function __construct(private readonly ?AdminAccessService $accessService = null)
    {
    }

    // ── Page & live stats ───────────────────────────────────

    public function index(): View
    {
        try {
            ImageCleanupService::expireOld();
        } catch (\Throwable) {
        }

        return view('admin.cache-manage', [
            'stats'           => $this->gatherCacheMetrics(),
            'cachedKeys'      => $this->inspectKeyRegistry(),
            'artisanCommands' => $this->allowedCommands(),
            'consoleGroups'   => (array) config('cache_console.groups', []),
            'batches'         => ImageCleanupService::batches(),
            'retentionDays'   => ImageCleanupService::RETENTION_DAYS,
            'minAgeDays'      => ImageCleanupService::MIN_AGE_DAYS,
        ]);
    }

    public function statsJson(): JsonResponse
    {
        return response()->json([
            'success'    => true,
            'stats'      => $this->gatherCacheMetrics(),
            'cachedKeys' => $this->inspectKeyRegistry(),
            'timestamp'  => now()->format('h:i:s A'),
        ])->header('Cache-Control', 'no-store');
    }

    // ── Clearing ────────────────────────────────────────────

    /** Everything: data cache, compiled views, config/route/event caches, OPcache. */
    public function clearAll(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'clear_all_cache', function () {
            SiteSetting::clearCache();
            Cache::flush();
            foreach (['view:clear', 'config:clear', 'route:clear', 'event:clear'] as $cmd) {
                Artisan::call($cmd);
            }
            $this->resetOpcache();

            return 'সব ক্যাশ পরিষ্কার হয়েছে (ডেটা, ভিউ, কনফিগ, রুট, ইভেন্ট ও OPcache)। প্রথম কয়েকটি পেজ একটু ধীরে খুলবে, তারপর আবার দ্রুত হবে।';
        });
    }

    public function clearViews(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'clear_views_cache', function () {
            Artisan::call('view:clear');

            return 'কম্পাইল করা ভিউ (Blade) পরিষ্কার হয়েছে। ডিজাইন বদলালে এটি দরকার হয়।';
        });
    }

    public function clearApp(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'clear_app_cache', function () {
            SiteSetting::clearCache();
            Cache::flush();

            return 'অ্যাপের ডেটা ক্যাশ পরিষ্কার হয়েছে (সেটিংস, মেনু, হোমপেজ, সাইটম্যাপ ইত্যাদি)।';
        });
    }

    public function clearConfig(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'clear_config_cache', function () {
            Artisan::call('config:clear');

            return 'কনফিগ ক্যাশ পরিষ্কার হয়েছে। .env-এর পরিবর্তন এখন কার্যকর হবে।';
        });
    }

    public function clearRoutes(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'clear_routes_cache', function () {
            Artisan::call('route:clear');

            return 'রুট ক্যাশ পরিষ্কার হয়েছে।';
        });
    }

    public function clearEvents(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'clear_events_cache', function () {
            Artisan::call('event:clear');

            return 'ইভেন্ট ক্যাশ পরিষ্কার হয়েছে।';
        });
    }

    public function clearOpcache(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'clear_opcache', function () {
            return $this->resetOpcache()
                ? 'PHP OPcache রিসেট হয়েছে।'
                : 'এই সার্ভারে OPcache চালু নেই — রিসেট করার কিছু নেই।';
        });
    }

    // ── Speed up / warm up ──────────────────────────────────

    /**
     * Pre-compile routes, views and events. Config is deliberately NOT cached: parts of the site
     * (payments, SMS, admin login) read .env directly, and those values are empty once config is cached.
     */
    public function optimize(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'optimize_system', function () {
            Artisan::call('config:clear');
            foreach (['route:cache', 'view:cache', 'event:cache'] as $cmd) {
                Artisan::call($cmd);
            }

            return 'সাইট দ্রুত করা হয়েছে: রুট, ভিউ ও ইভেন্ট আগে থেকেই তৈরি রাখা হলো। (নিরাপত্তার জন্য কনফিগ ক্যাশ করা হয়নি — পেমেন্ট ও SMS-এর .env সেটিং ঠিক থাকবে।)';
        });
    }

    /** Load the real pages once so their caches are built before visitors arrive. */
    public function warmup(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'cache_warmup', function () {
            SiteSetting::all();
            $lines = [];
            foreach (['/' => 'হোমপেজ', '/books' => 'বই', '/sitemap.xml' => 'সাইটম্যাপ'] as $path => $label) {
                $lines[] = $label . ': ' . $this->warmUrl($path);
            }

            return 'ওয়ার্ম-আপ সম্পন্ন — ' . implode(' · ', $lines);
        });
    }

    public function warmKey(Request $request): JsonResponse
    {
        $key = (string) $request->input('key');
        $meta = self::KEYS[$key] ?? null;
        if (!$meta) {
            return response()->json(['success' => false, 'message' => 'এই কি-টি তালিকায় নেই।'], 422);
        }
        if ($meta[3] === null) {
            return response()->json(['success' => false, 'message' => 'এই কি-টি সাইট নিজেই দরকার হলে তৈরি করে — আলাদা করে লোড করা যায় না।'], 422);
        }

        try {
            if ($meta[3] === 'settings') {
                SiteSetting::clearCache();
                SiteSetting::all();
                $msg = 'সাইট সেটিংস নতুন করে লোড হয়েছে।';
            } else {
                Cache::forget($key);
                $msg = "{$meta[0]} নতুন করে তৈরি হয়েছে ({$this->warmUrl($meta[3])})।";
            }
            $this->logAction('cache_key_warmed', "Rebuilt cache key: {$key}");

            return response()->json(['success' => true, 'key' => $key, 'cached' => Cache::has($key), 'message' => $msg]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'লোড করা যায়নি: ' . $e->getMessage()], 500);
        }
    }

    // ── Keys ────────────────────────────────────────────────

    public function inspectKey(Request $request): JsonResponse
    {
        $key = (string) $request->input('key');
        if (!isset(self::KEYS[$key])) {
            return response()->json(['success' => false, 'message' => 'শুধু তালিকার কি দেখা যায়।'], 422);
        }

        $exists = Cache::has($key);
        $value = $exists ? Cache::get($key) : null;
        $size = $exists ? strlen(serialize($value)) : 0;
        $preview = null;
        if ($exists) {
            $masked = $this->maskSecrets($value);
            $preview = is_scalar($masked) || $masked === null
                ? (string) $masked
                : json_encode($masked, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
            if (strlen((string) $preview) > 60000) {
                $preview = substr((string) $preview, 0, 60000) . "\n… (বাকি অংশ দেখানো হয়নি)";
            }
        }

        return response()->json([
            'success'   => true,
            'key'       => $key,
            'label'     => self::KEYS[$key][0],
            'exists'    => $exists,
            'type'      => is_object($value) ? class_basename($value) : gettype($value),
            'size'      => $this->formatBytes($size),
            'sizeBytes' => $size,
            'preview'   => $preview,
        ]);
    }

    public function deleteKey(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'delete_cache_key', function () use ($request) {
            $key = (string) $request->input('key');
            if (!isset(self::KEYS[$key])) {
                throw new \InvalidArgumentException('শুধু তালিকার কি মোছা যায়।');
            }
            Cache::forget($key);
            if ($key === 'site_global_settings_cache') {
                SiteSetting::clearCache();
            }

            return self::KEYS[$key][0] . ' মুছে ফেলা হয়েছে। পরের বার দরকার হলে সাইট নিজেই নতুন করে তৈরি করবে।';
        });
    }

    public function bulkDeleteKeys(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'bulk_delete_cache_keys', function () use ($request) {
            $keys = array_values(array_filter((array) $request->input('keys', []), fn ($k) => is_string($k) && isset(self::KEYS[$k])));
            if ($keys === []) {
                throw new \InvalidArgumentException('কোনো কি নির্বাচন করা হয়নি।');
            }
            foreach ($keys as $k) {
                Cache::forget($k);
            }

            return count($keys) . 'টি কি মুছে ফেলা হয়েছে।';
        });
    }

    // ── Unused images (quarantine, never straight delete) ───

    public function scanImages(Request $request): JsonResponse
    {
        try {
            $items = ImageCleanupService::scan();

            return response()->json([
                'success' => true,
                'count'   => count($items),
                'bytes'   => (int) array_sum(array_column($items, 'size')),
                'size'    => $this->formatBytes((int) array_sum(array_column($items, 'size'))),
                'items'   => array_map(fn ($i) => [
                    'rel'      => $i['rel'],
                    'url'      => $i['url'],
                    'size'     => $this->formatBytes($i['size']),
                    'modified' => date('d M Y', $i['modified']),
                ], array_slice($items, 0, 300)),
            ]);
        } catch (\Throwable $e) {
            Log::error('Image scan failed', ['e' => $e]);

            return response()->json(['success' => false, 'message' => 'স্ক্যান করা যায়নি: ' . $e->getMessage()], 500);
        }
    }

    /** Old route name kept: now moves unused images to quarantine instead of deleting them. */
    public function clearImages(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'quarantine_images', function () {
            $r = ImageCleanupService::quarantine();
            if (!$r['count']) {
                return 'অব্যবহৃত কোনো ছবি পাওয়া যায়নি।';
            }

            return "{$r['count']}টি অব্যবহৃত ছবি কোয়ারেন্টাইনে সরানো হয়েছে ({$this->formatBytes($r['bytes'])})। "
                . ImageCleanupService::RETENTION_DAYS . ' দিন পর্যন্ত এক ক্লিকে ফেরত আনা যাবে।';
        });
    }

    public function restoreImages(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'restore_images', function () use ($request) {
            $n = ImageCleanupService::restore((string) $request->input('batch'));

            return "{$n}টি ছবি আগের জায়গায় ফেরত আনা হয়েছে।";
        });
    }

    public function deleteImageBatch(Request $request): JsonResponse|RedirectResponse
    {
        return $this->run($request, 'delete_image_batch', function () use ($request) {
            $n = ImageCleanupService::deleteBatch((string) $request->input('batch'));

            return "{$n}টি ছবি স্থায়ীভাবে মুছে ফেলা হয়েছে।";
        });
    }

    // ── Restricted console ──────────────────────────────────

    public function runArtisan(Request $request): JsonResponse
    {
        $command = (string) $request->validate(['command' => ['required', 'string', 'max:64']])['command'];
        $allowed = $this->allowedCommands();
        if (!isset($allowed[$command])) {
            return response()->json(['success' => false, 'message' => 'এই কমান্ড এখানে চালানো যায় না।'], 403);
        }

        try {
            $exitCode = Artisan::call($command, (array) ($allowed[$command]['args'] ?? []));
            $output = trim(Artisan::output());
            if (strlen($output) > 12000) {
                $output = substr($output, 0, 12000) . "\n[Output truncated]";
            }
            $this->logAction('artisan_command_run', "Executed: php artisan {$command}");

            return response()->json([
                'success'   => $exitCode === 0,
                'command'   => $command,
                'output'    => $output !== '' ? $output : "'{$command}' completed.",
                'timestamp' => now()->format('H:i:s'),
                'message'   => $exitCode === 0 ? "'{$command}' সম্পন্ন হয়েছে।" : "'{$command}' ব্যর্থ হয়েছে (exit {$exitCode})।",
            ], $exitCode === 0 ? 200 : 500);
        } catch (\Throwable $e) {
            Log::error('Admin Artisan command threw an exception.', ['command' => $command, 'exception' => $e]);

            return response()->json(['success' => false, 'command' => $command, 'message' => 'কমান্ড চালানো যায়নি। লগ দেখুন।'], 500);
        }
    }

    // ── internals ───────────────────────────────────────────

    /** Run an action and answer JSON (script) or redirect with a flash message. */
    private function run(Request $request, string $logKey, \Closure $action): JsonResponse|RedirectResponse
    {
        $wantsJson = $request->wantsJson() || $request->ajax();
        try {
            $msg = (string) $action();
            $this->logAction($logKey, $msg);

            return $wantsJson
                ? response()->json(['success' => true, 'message' => $msg, 'stats' => $this->gatherCacheMetrics(), 'batches' => ImageCleanupService::batches()])
                : back()->with('success', $msg);
        } catch (\InvalidArgumentException $e) {
            return $wantsJson ? response()->json(['success' => false, 'message' => $e->getMessage()], 422) : back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error("Cache page action failed: {$logKey}", ['exception' => $e]);
            $err = 'কাজটি সম্পন্ন হয়নি: ' . $e->getMessage();

            return $wantsJson ? response()->json(['success' => false, 'message' => $err], 500) : back()->with('error', $err);
        }
    }

    private function resetOpcache(): bool
    {
        return function_exists('opcache_reset') && $this->opcacheEnabled() && @opcache_reset();
    }

    private function opcacheEnabled(): bool
    {
        if (!function_exists('opcache_get_status')) {
            return false;
        }
        $s = @opcache_get_status(false);

        return is_array($s) && !empty($s['opcache_enabled']);
    }

    /** Request a page of this site once; returns "200 · 0.8s" or the error. */
    private function warmUrl(string $path): string
    {
        $t = microtime(true);
        try {
            $res = Http::timeout(20)->withoutVerifying()->withHeaders(['User-Agent' => 'IdeaabdCacheWarmer/1.0'])->get(url($path));

            return $res->status() . ' · ' . number_format(microtime(true) - $t, 1) . 's';
        } catch (\Throwable $e) {
            return 'পৌঁছানো যায়নি';
        }
    }

    /** Replace values of secret-looking fields with ••••. */
    private function maskSecrets(mixed $value, int $depth = 0): mixed
    {
        if ($depth > 12) {
            return '…';
        }
        if (is_object($value)) {
            $value = method_exists($value, 'toArray') ? $value->toArray() : (array) $value;
        }
        if (!is_array($value)) {
            return $value;
        }
        foreach ($value as $k => $v) {
            if (is_string($k) && preg_match(self::SECRET_PATTERN, $k) && (is_scalar($v) && $v !== '' && $v !== null)) {
                $value[$k] = '•••• (গোপন)';
            } else {
                $value[$k] = $this->maskSecrets($v, $depth + 1);
            }
        }

        return $value;
    }

    private function allowedCommands(): array
    {
        $order = (array) config('cache_console.risk_order', ['none', 'low', 'medium', 'high']);
        $ceiling = array_search(config('cache_console.max_risk', 'low'), $order, true);
        $ceiling = $ceiling === false ? 1 : $ceiling;

        return array_filter((array) config('cache_console.commands', []), function ($meta) use ($order, $ceiling) {
            $risk = array_search($meta['risk'] ?? 'high', $order, true);

            return $risk !== false && $risk <= $ceiling;
        });
    }

    private function gatherCacheMetrics(): array
    {
        $viewPath = storage_path('framework/views');
        $dataPath = storage_path('framework/cache/data');
        [$viewCount, $viewBytes] = $this->dirStats($viewPath);
        [$dataCount, $dataBytes] = $this->dirStats($dataPath);

        $op = ['enabled' => $this->opcacheEnabled(), 'used' => null, 'free' => null, 'percent' => 0, 'hit_rate' => null, 'scripts' => 0];
        if ($op['enabled']) {
            $s = @opcache_get_status(false) ?: [];
            $used = (int) ($s['memory_usage']['used_memory'] ?? 0);
            $free = (int) ($s['memory_usage']['free_memory'] ?? 0);
            $op['used'] = $this->formatBytes($used);
            $op['free'] = $this->formatBytes($free);
            $op['percent'] = ($used + $free) > 0 ? round($used / ($used + $free) * 100, 1) : 0;
            $op['hit_rate'] = isset($s['opcache_statistics']['opcache_hit_rate']) ? round((float) $s['opcache_statistics']['opcache_hit_rate'], 1) : null;
            $op['scripts'] = (int) ($s['opcache_statistics']['num_cached_scripts'] ?? 0);
        }

        $cached = fn (string $f) => File::exists(base_path('bootstrap/cache/' . $f));

        return [
            'view_files_count'       => $viewCount,
            'view_cache_size'        => $this->formatBytes($viewBytes),
            'data_files_count'       => $dataCount,
            'data_cache_size'        => $this->formatBytes($dataBytes),
            'is_config_cached'       => $cached('config.php'),
            'is_route_cached'        => $cached('routes-v7.php'),
            'is_events_cached'       => $cached('events.php'),
            'opcache_enabled'        => $op['enabled'],
            'opcache_memory_used'    => $op['used'],
            'opcache_memory_free'    => $op['free'],
            'opcache_memory_percent' => $op['percent'],
            'opcache_hit_rate'       => $op['hit_rate'],
            'opcache_scripts'        => $op['scripts'],
            'cache_driver'           => (string) config('cache.default', 'file'),
            'session_driver'         => (string) config('session.driver', 'file'),
            'php_version'            => PHP_VERSION,
            'laravel_version'        => app()->version(),
            'quarantine_size'        => $this->formatBytes($this->dirStats(ImageCleanupService::quarantineRoot())[1]),
        ];
    }

    private function inspectKeyRegistry(): array
    {
        $out = [];
        foreach (self::KEYS as $key => [$label, $desc, $type, $warm]) {
            $out[] = [
                'key'         => $key,
                'label'       => $label,
                'description' => $desc,
                'type'        => $type,
                'can_warm'    => $warm !== null,
                'is_cached'   => Cache::has($key),
            ];
        }

        return $out;
    }

    /** [file count, bytes] */
    private function dirStats(string $dir): array
    {
        if (!File::isDirectory($dir)) {
            return [0, 0];
        }
        $n = 0;
        $bytes = 0;
        try {
            foreach (File::allFiles($dir) as $f) {
                $n++;
                $bytes += $f->getSize();
            }
        } catch (\Throwable) {
        }

        return [$n, $bytes];
    }

    private function formatBytes(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = (int) min(floor(($bytes ? log($bytes) : 0) / log(1024)), count($units) - 1);

        return round($bytes / (1 << (10 * $pow)), $precision) . ' ' . $units[$pow];
    }

    private function logAction(string $action, string $details): void
    {
        try {
            $this->accessService?->log($action, $details);
        } catch (\Throwable) {
        }
    }
}
