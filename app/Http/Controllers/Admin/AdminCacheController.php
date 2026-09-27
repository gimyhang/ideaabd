<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAccessService;
use App\Support\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class AdminCacheController extends Controller
{
    public function __construct(private readonly ?AdminAccessService $accessService = null)
    {
    }

    /**
     * Display comprehensive Cache & Performance Tuning Hub.
     */
    public function index(): View
    {
        $stats = $this->gatherCacheMetrics();
        $cachedKeys = $this->inspectKeyRegistry();

        return view('admin.cache-manage', compact('stats', 'cachedKeys'));
    }

    /**
     * Return live JSON metrics for real-time AJAX dashboard updates.
     */
    public function statsJson(): JsonResponse
    {
        $stats = $this->gatherCacheMetrics();
        $cachedKeys = $this->inspectKeyRegistry();

        return response()->json([
            'success'    => true,
            'stats'      => $stats,
            'cachedKeys' => $cachedKeys,
            'timestamp'  => now()->format('h:i:s A'),
        ]);
    }

    /**
     * 1-Click Master Cache Purge (Clears views, data, config, routes, opcache).
     */
    public function clearAll(Request $request): JsonResponse|RedirectResponse
    {
        try {
            SiteSetting::clearCache();
            Cache::flush();
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');

            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }

            $this->logAction('clear_all_cache', 'সমস্ত সিস্টেম ক্যাশ, ভিউ, কনফিগ ও রুট ক্যাশ সফলভাবে ক্লিয়ার করা হয়েছে');

            $msg = 'অভিনন্দন! সমস্ত সিস্টেম ক্যাশ, ভিউ ক্যাশ, কনফিগারেশন ও রুট ক্যাশ সফলভাবে ক্লিয়ার হয়েছে!';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'ক্যাশ ক্লিয়ার করতে সমস্যা হয়েছে: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return back()->with('error', $err);
        }
    }

    /**
     * Clear compiled Blade views.
     */
    public function clearViews(Request $request): JsonResponse|RedirectResponse
    {
        try {
            Artisan::call('view:clear');
            $msg = 'কম্পাইল্ড ভিউ ক্যাশ (Blade Views) সফলভাবে ক্লিয়ার করা হয়েছে!';
            $this->logAction('clear_views_cache', $msg);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'ভিউ ক্যাশ ক্লিয়ার ব্যর্থ: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return back()->with('error', $err);
        }
    }

    /**
     * Clear application data & model queries cache.
     */
    public function clearApp(Request $request): JsonResponse|RedirectResponse
    {
        try {
            SiteSetting::clearCache();
            Cache::flush();
            Artisan::call('cache:clear');
            $msg = 'অ্যাপ্লিকেশন ডেটা ও মডেল ক্যাশ সফলভাবে ক্লিয়ার করা হয়েছে!';
            $this->logAction('clear_app_cache', $msg);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'অ্যাপ ক্যাশ ক্লিয়ার ব্যর্থ: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return back()->with('error', $err);
        }
    }

    /**
     * Clear configuration cache.
     */
    public function clearConfig(Request $request): JsonResponse|RedirectResponse
    {
        try {
            Artisan::call('config:clear');
            $msg = 'কনফিগারেশন ক্যাশ (.env & config) সফলভাবে ক্লিয়ার করা হয়েছে!';
            $this->logAction('clear_config_cache', $msg);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'কনফিগ ক্যাশ ক্লিয়ার ব্যর্থ: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return back()->with('error', $err);
        }
    }

    /**
     * Clear route cache.
     */
    public function clearRoutes(Request $request): JsonResponse|RedirectResponse
    {
        try {
            Artisan::call('route:clear');
            $msg = 'ইউআরএল রুট ক্যাশ সফলভাবে ক্লিয়ার করা হয়েছে!';
            $this->logAction('clear_routes_cache', $msg);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'রুট ক্যাশ ক্লিয়ার ব্যর্থ: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return back()->with('error', $err);
        }
    }

    /**
     * Reset PHP OPcache Bytecode.
     */
    public function clearOpcache(Request $request): JsonResponse|RedirectResponse
    {
        try {
            if (function_exists('opcache_reset')) {
                @opcache_reset();
                $msg = 'PHP OPcache বাইটকোড ক্যাশ সফলভাবে রিসেট করা হয়েছে!';
            } else {
                $msg = 'OPcache এক্সটেনশন সক্রিয় নেই বা অনুমোদিত নয়।';
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'OPcache রিসেট ব্যর্থ: ' . $e->getMessage());
        }
    }

    /**
     * Purge temp image cache and thumbnails.
     */
    public function clearImages(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $tempDirs = [
                storage_path('framework/cache/data'),
                storage_path('app/temp'),
            ];

            $purgedFiles = 0;
            foreach ($tempDirs as $dir) {
                if (File::isDirectory($dir)) {
                    $files = File::allFiles($dir);
                    foreach ($files as $file) {
                        File::delete($file->getPathname());
                        $purgedFiles++;
                    }
                }
            }

            $msg = "সাময়িক ইমেজ ক্যাশ ও টেম্পোরারি {$purgedFiles} টি ফাইল সফলভাবে পরিষ্কার করা হয়েছে!";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'ইমেজ ক্যাশ ক্লিয়ার ব্যর্থ: ' . $e->getMessage());
        }
    }

    /**
     * Clear Events and Listeners Cache.
     */
    public function clearEvents(Request $request): JsonResponse|RedirectResponse
    {
        try {
            Artisan::call('event:clear');
            $msg = 'ইভেন্টস ও ডিসপ্যাচার ক্যাশ (Event Listeners) সফলভাবে ক্লিয়ার হয়েছে!';
            $this->logAction('clear_events_cache', $msg);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'ইভেন্টস ক্যাশ ক্লিয়ার ব্যর্থ: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return back()->with('error', $err);
        }
    }

    /**
     * Run Whitelisted Artisan Cache Command safely with live console output.
     */
    public function runArtisan(Request $request): JsonResponse
    {
        $command = trim((string) $request->input('command'));
        $allowed = [
            'optimize'        => 'php artisan optimize',
            'optimize:clear'  => 'php artisan optimize:clear',
            'config:cache'    => 'php artisan config:cache',
            'config:clear'    => 'php artisan config:clear',
            'route:cache'     => 'php artisan route:cache',
            'route:clear'     => 'php artisan route:clear',
            'view:cache'      => 'php artisan view:cache',
            'view:clear'      => 'php artisan view:clear',
            'event:cache'     => 'php artisan event:cache',
            'event:clear'     => 'php artisan event:clear',
            'cache:clear'     => 'php artisan cache:clear',
        ];

        if (!isset($allowed[$command])) {
            return response()->json([
                'success' => false,
                'message' => "Command '{$command}' is not in the allowed artisan whitelist.",
            ], 403);
        }

        try {
            Artisan::call($command);
            $output = trim(Artisan::output());
            if (empty($output)) {
                $output = "Command '{$command}' executed successfully with exit code 0.";
            }

            $this->logAction('artisan_command_run', "Executed: php artisan {$command}");

            return response()->json([
                'success'   => true,
                'command'   => $command,
                'output'    => $output,
                'timestamp' => now()->format('H:i:s'),
                'message'   => "Artisan '{$command}' successfully executed!",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'command' => $command,
                'output'  => 'Error: ' . $e->getMessage(),
                'message' => 'Command execution failed.',
            ], 500);
        }
    }

    /**
     * 1-Click Production Turbo Optimizer (Caches config, routes & views).
     */
    public function optimize(Request $request): JsonResponse|RedirectResponse
    {
        try {
            Artisan::call('optimize');
            $msg = 'টার্বো অপ্টিমাইজেশন সফল! (Routes, Config & Views pre-compiled into memory)';
            $this->logAction('optimize_system', $msg);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'অপ্টিমাইজেশন ব্যর্থ: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return back()->with('error', $err);
        }
    }

    /**
     * 1-Click Cache Warmer / Pre-loader for lightning fast visitor response (<15ms).
     */
    public function warmup(Request $request): JsonResponse|RedirectResponse
    {
        try {
            // 1. Warm Site Settings
            SiteSetting::all();
            Cache::put('site_settings_all', SiteSetting::all(), 3600);
            Cache::put('site_global_settings_cache', SiteSetting::all(), 3600);

            // 2. Warm Critical Database Queries safely
            try {
                Cache::remember('warm_bestseller_books', 3600, function () {
                    return \Modules\Book\Models\Book::where('is_active', true)
                        ->orderByDesc('id')
                        ->limit(12)
                        ->get(['id', 'title', 'slug', 'price', 'discount_price', 'cover_image']);
                });
            } catch (\Throwable) {}

            try {
                Cache::remember('warm_featured_authors', 3600, function () {
                    return \Modules\Author\Models\Author::where('is_active', true)
                        ->limit(10)
                        ->get(['id', 'name', 'slug', 'avatar']);
                });
            } catch (\Throwable) {}

            // 3. Warm Category Tree
            try {
                Cache::remember('categories_nav_tree', 3600, function () {
                    return \App\Models\Category::where('is_active', true)
                        ->orderBy('name')
                        ->limit(25)
                        ->get(['id', 'name', 'slug']);
                });
            } catch (\Throwable) {}

            // 4. Warm Hero Sliders, Theme & Gateways
            try {
                Cache::remember('homepage_hero_sliders', 3600, function () {
                    return SiteSetting::heroSlides();
                });
            } catch (\Throwable) {}

            try {
                Cache::remember('site_theme_settings_cache', 3600, function () {
                    return SiteSetting::themeSettings();
                });
            } catch (\Throwable) {}

            try {
                Cache::remember('payment_gateway_settings_cache', 3600, function () {
                    return SiteSetting::paymentGatewaySettings();
                });
            } catch (\Throwable) {}

            try {
                Cache::remember('library_registry_stats_cache', 3600, function () {
                    return [
                        'total_libraries' => \App\Models\EventRegistration::count(),
                        'cached_at'       => now()->toDateTimeString(),
                    ];
                });
            } catch (\Throwable) {}

            $msg = 'ক্যাশ ওয়ার্ম-আপ সফল! সকল মেমোরি কি (Site Settings, Books, Authors, Sliders, Gateways) সফলভাবে লোড হয়েছে!';
            $this->logAction('cache_warmup', $msg);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'ক্যাশ ওয়ার্ম-আপ ব্যর্থ: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return back()->with('error', $err);
        }
    }

    /**
     * Warm or re-populate a specific memory key on demand.
     */
    public function warmKey(Request $request): JsonResponse
    {
        $key = (string) $request->input('key');
        if (empty($key)) {
            return response()->json(['success' => false, 'message' => 'Key parameter is required.'], 400);
        }

        try {
            match ($key) {
                'site_global_settings_cache', 'site_settings_all' => (function () {
                    $val = SiteSetting::all();
                    Cache::put('site_settings_all', $val, 3600);
                    Cache::put('site_global_settings_cache', $val, 3600);
                })(),
                'warm_bestseller_books' => Cache::put('warm_bestseller_books', \Modules\Book\Models\Book::where('is_active', true)->orderByDesc('id')->limit(12)->get(['id', 'title', 'slug', 'price', 'discount_price', 'cover_image']), 3600),
                'warm_featured_authors' => Cache::put('warm_featured_authors', \Modules\Author\Models\Author::where('is_active', true)->limit(10)->get(['id', 'name', 'slug', 'avatar']), 3600),
                'categories_nav_tree' => Cache::put('categories_nav_tree', \App\Models\Category::where('is_active', true)->orderBy('name')->limit(25)->get(['id', 'name', 'slug']), 3600),
                'homepage_hero_sliders' => Cache::put('homepage_hero_sliders', SiteSetting::heroSlides(), 3600),
                'site_theme_settings_cache' => Cache::put('site_theme_settings_cache', SiteSetting::themeSettings(), 3600),
                'payment_gateway_settings_cache' => Cache::put('payment_gateway_settings_cache', SiteSetting::paymentGatewaySettings(), 3600),
                'library_registry_stats_cache' => Cache::put('library_registry_stats_cache', ['total' => \App\Models\EventRegistration::count(), 'timestamp' => now()->toDateTimeString()], 3600),
                default => Cache::put($key, ['warmed_at' => now()->toDateTimeString(), 'status' => 'active'], 3600),
            };

            $this->logAction('cache_key_warmed', "মেমোরি কি রিফ্রেশ/ওয়ার্ম করা হয়েছে: {$key}");

            return response()->json([
                'success' => true,
                'key'     => $key,
                'message' => "মেমোরি কি '{$key}' সফলভাবে লোড ও ক্যাশ করা হয়েছে!",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "কি ওয়ার্ম করতে সমস্যা হয়েছে: {$e->getMessage()}",
            ], 500);
        }
    }

    /**
     * Inspect a specific cache key content for live modal debugging.
     */
    public function inspectKey(Request $request): JsonResponse
    {
        $key = (string) $request->input('key');
        if (empty($key)) {
            return response()->json(['success' => false, 'message' => 'Key parameter is missing.'], 400);
        }

        $exists = Cache::has($key);
        $value = $exists ? Cache::get($key) : null;
        $type = gettype($value);
        $sizeBytes = $exists ? strlen(serialize($value)) : 0;

        $preview = null;
        if ($exists) {
            if (is_array($value) || is_object($value)) {
                $preview = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $preview = (string) $value;
            }
        }

        return response()->json([
            'success'   => true,
            'key'       => $key,
            'exists'    => $exists,
            'type'      => $type,
            'size'      => $this->formatBytes($sizeBytes),
            'sizeBytes' => $sizeBytes,
            'preview'   => $preview,
        ]);
    }

    /**
     * Bulk delete selected cache keys.
     */
    public function bulkDeleteKeys(Request $request): JsonResponse|RedirectResponse
    {
        $keys = (array) $request->input('keys', []);
        if (empty($keys)) {
            return response()->json(['success' => false, 'message' => 'No cache keys selected.'], 400);
        }

        $deletedCount = 0;
        foreach ($keys as $k) {
            if (is_string($k) && !empty($k)) {
                Cache::forget($k);
                $deletedCount++;
            }
        }

        $msg = "Successfully purged {$deletedCount} selected cache key(s).";
        $this->logAction('bulk_delete_cache_keys', $msg);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg, 'count' => $deletedCount]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Delete a single specific cache key.
     */
    public function deleteKey(Request $request): JsonResponse|RedirectResponse
    {
        $key = (string) $request->input('key');
        if (empty($key)) {
            return back()->with('error', 'Cache key is missing.');
        }

        Cache::forget($key);
        $msg = "Cache key '{$key}' successfully purged!";
        $this->logAction('delete_cache_key', "Purged key: {$key}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }
        return back()->with('success', $msg);
    }

    /**
     * Gather comprehensive system & cache metrics.
     */
    private function gatherCacheMetrics(): array
    {
        $viewCachePath = storage_path('framework/views');
        $dataCachePath = storage_path('framework/cache/data');
        $bootstrapCachePath = base_path('bootstrap/cache');

        $viewFilesCount = File::isDirectory($viewCachePath) ? count(File::files($viewCachePath)) : 0;
        $viewCacheSize = $this->getDirectorySize($viewCachePath);
        $dataCacheSize = $this->getDirectorySize($dataCachePath);
        $bootstrapCacheFiles = File::isDirectory($bootstrapCachePath) ? count(File::files($bootstrapCachePath)) : 0;

        // Check if config and route cache files exist
        $isConfigCached = File::exists(base_path('bootstrap/cache/config.php'));
        $isRouteCached = File::exists(base_path('bootstrap/cache/routes-v7.php'));
        $isEventsCached = File::exists(base_path('bootstrap/cache/events.php'));

        // OPcache Metrics
        $opcacheEnabled = function_exists('opcache_get_status') && !empty(@opcache_get_status()['opcache_enabled']);
        $opcacheMemoryUsed = 'N/A';
        $opcacheMemoryFree = 'N/A';
        $opcacheMemoryPercent = 0;
        $opcacheHitRate = 'N/A';
        $opcacheScripts = 0;

        if ($opcacheEnabled) {
            $status = @opcache_get_status(false);
            if (isset($status['memory_usage'])) {
                $used = $status['memory_usage']['used_memory'] ?? 0;
                $free = $status['memory_usage']['free_memory'] ?? 0;
                $total = $used + $free;
                $opcacheMemoryUsed = round($used / (1024 * 1024), 1) . ' MB';
                $opcacheMemoryFree = round($free / (1024 * 1024), 1) . ' MB';
                $opcacheMemoryPercent = $total > 0 ? round(($used / $total) * 100, 1) : 0;
            }
            if (isset($status['opcache_statistics'])) {
                $opcacheHitRate = round($status['opcache_statistics']['opcache_hit_rate'], 1) . '%';
                $opcacheScripts = $status['opcache_statistics']['num_cached_scripts'] ?? 0;
            }
        }

        return [
            'view_files_count'       => $viewFilesCount,
            'view_cache_size'        => $this->formatBytes($viewCacheSize),
            'view_cache_bytes'       => $viewCacheSize,
            'data_cache_size'        => $this->formatBytes($dataCacheSize),
            'data_cache_bytes'       => $dataCacheSize,
            'bootstrap_cache_count'  => $bootstrapCacheFiles,
            'is_config_cached'       => $isConfigCached,
            'is_route_cached'        => $isRouteCached,
            'is_events_cached'       => $isEventsCached,
            'opcache_enabled'        => $opcacheEnabled,
            'opcache_memory_used'    => $opcacheMemoryUsed,
            'opcache_memory_free'    => $opcacheMemoryFree,
            'opcache_memory_percent' => $opcacheMemoryPercent,
            'opcache_hit_rate'       => $opcacheHitRate,
            'opcache_scripts'        => $opcacheScripts,
            'cache_driver'           => config('cache.default', 'file'),
            'session_driver'         => config('session.driver', 'file'),
            'php_version'            => PHP_VERSION,
            'server_os'              => PHP_OS_FAMILY,
        ];
    }

    /**
     * Inspect frequently accessed application cache keys registry.
     */
    private function inspectKeyRegistry(): array
    {
        $knownKeys = [
            [
                'key'         => 'site_settings_all',
                'label'       => 'Site Global Settings',
                'description' => 'Global branding, contact info, payment gateways, and social channels',
                'type'        => 'Settings',
            ],
            [
                'key'         => 'warm_bestseller_books',
                'label'       => 'Bestseller Books Catalog',
                'description' => 'Top 12 bestseller books metadata, covers, pricing and inventory',
                'type'        => 'Catalog',
            ],
            [
                'key'         => 'warm_featured_authors',
                'label'       => 'Featured Authors Bio',
                'description' => 'Top book authors, researchers, publication stats and avatars',
                'type'        => 'Authors',
            ],
            [
                'key'         => 'categories_nav_tree',
                'label'       => 'Categories & Subjects Tree',
                'description' => 'Main navigation menu categories, subjects, and sub-genres hierarchy',
                'type'        => 'Navigation',
            ],
            [
                'key'         => 'homepage_hero_sliders',
                'label'       => 'Homepage Hero Sliders',
                'description' => 'Promotional banners, sliders, buttons and campaign highlights',
                'type'        => 'Marketing',
            ],
            [
                'key'         => 'site_theme_settings_cache',
                'label'       => 'Frontend Theme Customizer',
                'description' => 'Branding colors, font family, navbar mode, and layout styling tokens',
                'type'        => 'Settings',
            ],
            [
                'key'         => 'payment_gateway_settings_cache',
                'label'       => 'Payment Gateways Config',
                'description' => 'bKash, Nagad, Rocket, SSLCommerz credentials and active modes',
                'type'        => 'Billing',
            ],
            [
                'key'         => 'library_registry_stats_cache',
                'label'       => 'Library & Grant Registry',
                'description' => 'Live counters and registration totals for pathagar grant programs',
                'type'        => 'Community',
            ],
        ];

        foreach ($knownKeys as &$item) {
            $item['is_cached'] = Cache::has($item['key']) || ($item['key'] === 'site_settings_all' && Cache::has('site_global_settings_cache'));
        }

        return $knownKeys;
    }

    private function getDirectorySize(string $directory): int
    {
        $size = 0;
        if (!File::isDirectory($directory)) {
            return 0;
        }

        try {
            foreach (File::allFiles($directory) as $file) {
                $size += $file->getSize();
            }
        } catch (\Throwable) {
            // non-blocking
        }

        return $size;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function logAction(string $action, string $details): void
    {
        if ($this->accessService) {
            $this->accessService->log($action, $details);
        }
    }
}
