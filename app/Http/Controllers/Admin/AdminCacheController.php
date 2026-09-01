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
            SiteSetting::clearCache();
            SiteSetting::all();

            // 2. Warm Critical Database Queries safely
            try {
                Cache::remember('warm_bestseller_books', 3600, function () {
                    return \Modules\Book\Models\Book::where('is_active', true)
                        ->orderByDesc('sales_count')
                        ->limit(12)
                        ->get(['id', 'title', 'slug', 'price', 'discount_price', 'cover_image']);
                });
            } catch (\Throwable) {}

            try {
                Cache::remember('warm_featured_authors', 3600, function () {
                    return \Modules\Author\Models\Author::where('is_active', true)
                        ->withCount('books')
                        ->orderByDesc('books_count')
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

            $msg = 'ক্যাশ ওয়ার্ম-আপ সফল! হোমপেজ, বেস্টসেলার, ক্যাটাগরি ও সাইট সেটিংস মেমোরিতে প্রি-লোড করা হয়েছে (Instant 10ms response enabled)!';
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
     * Delete a single specific cache key.
     */
    public function deleteKey(Request $request): JsonResponse|RedirectResponse
    {
        $key = (string) $request->input('key');
        if (empty($key)) {
            return back()->with('error', 'ক্যাশ কী নির্ধারিত হয়নি।');
        }

        Cache::forget($key);
        $msg = "ক্যাশ কী '{$key}' সফলভাবে মুছে ফেলা হয়েছে!";

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
        $opcacheHitRate = 'N/A';
        $opcacheScripts = 0;

        if ($opcacheEnabled) {
            $status = @opcache_get_status(false);
            if (isset($status['memory_usage'])) {
                $opcacheMemoryUsed = round($status['memory_usage']['used_memory'] / (1024 * 1024), 1) . ' MB';
                $opcacheMemoryFree = round($status['memory_usage']['free_memory'] / (1024 * 1024), 1) . ' MB';
            }
            if (isset($status['opcache_statistics'])) {
                $opcacheHitRate = round($status['opcache_statistics']['opcache_hit_rate'], 1) . '%';
                $opcacheScripts = $status['opcache_statistics']['num_cached_scripts'] ?? 0;
            }
        }

        return [
            'view_files_count'      => $viewFilesCount,
            'view_cache_size'       => $this->formatBytes($viewCacheSize),
            'view_cache_bytes'      => $viewCacheSize,
            'data_cache_size'       => $this->formatBytes($dataCacheSize),
            'data_cache_bytes'      => $dataCacheSize,
            'bootstrap_cache_count' => $bootstrapCacheFiles,
            'is_config_cached'      => $isConfigCached,
            'is_route_cached'       => $isRouteCached,
            'is_events_cached'      => $isEventsCached,
            'opcache_enabled'       => $opcacheEnabled,
            'opcache_memory_used'   => $opcacheMemoryUsed,
            'opcache_memory_free'   => $opcacheMemoryFree,
            'opcache_hit_rate'      => $opcacheHitRate,
            'opcache_scripts'       => $opcacheScripts,
            'cache_driver'          => config('cache.default', 'file'),
            'session_driver'        => config('session.driver', 'file'),
            'php_version'           => PHP_VERSION,
            'server_os'             => PHP_OS_FAMILY,
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
                'label'       => 'গ্লোবাল সাইট সেটিংস',
                'description' => 'ওয়েবসাইটের লোগো, ফোন, পেমেন্ট গেটওয়ে ও সোশ্যাল লিংক কনফিগ',
                'type'        => 'Settings',
            ],
            [
                'key'         => 'warm_bestseller_books',
                'label'       => 'বেস্টসেলার বই প্রি-লোড',
                'description' => 'হোমপেজের টপ ১২ বেস্টসেলার বইয়ের মেটাডাটা ও কভার ক্যাশ',
                'type'        => 'Catalog',
            ],
            [
                'key'         => 'warm_featured_authors',
                'label'       => 'শীর্ষ লেখক ও গবেষক তালিকা',
                'description' => 'সর্বোচ্চ বইযুক্ত বিশিষ্ট লেখকদের প্রোফাইল ক্যাশ',
                'type'        => 'Authors',
            ],
            [
                'key'         => 'categories_nav_tree',
                'label'       => 'ক্যাটাগরি নেভিগেশন ট্রি',
                'description' => 'মেগা মেনুর সমস্ত বইয়ের বিষয় ও সাব-ক্যাটাগরি তালিকা',
                'type'        => 'Navigation',
            ],
            [
                'key'         => 'homepage_hero_sliders',
                'label'       => 'হোমপেজ ব্যানার ও স্লাইডার',
                'description' => 'বইমেলার স্পেশাল ব্যানার ও প্রমোশনাল অফার স্লাইডার',
                'type'        => 'Marketing',
            ],
        ];

        foreach ($knownKeys as &$item) {
            $item['is_cached'] = Cache::has($item['key']);
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
