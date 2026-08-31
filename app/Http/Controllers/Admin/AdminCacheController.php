<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAccessService;
use App\Support\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class AdminCacheController extends Controller
{
    public function __construct(private readonly ?AdminAccessService $accessService = null)
    {
    }

    /**
     * Display cache management dashboard with real-time statistics.
     */
    public function index(): View
    {
        $viewCachePath = storage_path('framework/views');
        $dataCachePath = storage_path('framework/cache/data');
        $bootstrapCachePath = base_path('bootstrap/cache');

        $viewFilesCount = File::isDirectory($viewCachePath) ? count(File::files($viewCachePath)) : 0;
        $viewCacheSize = $this->getDirectorySize($viewCachePath);
        $dataCacheSize = $this->getDirectorySize($dataCachePath);
        $bootstrapCacheFiles = File::isDirectory($bootstrapCachePath) ? count(File::files($bootstrapCachePath)) : 0;

        $opcacheEnabled = function_exists('opcache_get_status') && !empty(@opcache_get_status()['opcache_enabled']);
        $opcacheMemory = null;
        if ($opcacheEnabled) {
            $status = @opcache_get_status(false);
            if (isset($status['memory_usage']['used_memory'])) {
                $opcacheMemory = round($status['memory_usage']['used_memory'] / (1024 * 1024), 2) . ' MB';
            }
        }

        $stats = [
            'view_files_count'      => $viewFilesCount,
            'view_cache_size'       => $this->formatBytes($viewCacheSize),
            'data_cache_size'       => $this->formatBytes($dataCacheSize),
            'bootstrap_cache_count' => $bootstrapCacheFiles,
            'opcache_enabled'       => $opcacheEnabled,
            'opcache_memory'        => $opcacheMemory ?? 'N/A',
            'cache_driver'          => config('cache.default', 'file'),
            'session_driver'        => config('session.driver', 'file'),
            'php_version'           => PHP_VERSION,
        ];

        return view('admin.cache-manage', compact('stats'));
    }

    /**
     * Clear all application, view, config, and route caches.
     */
    public function clearAll(Request $request): RedirectResponse
    {
        try {
            SiteSetting::clearCache();
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');

            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }

            if ($this->accessService) {
                $this->accessService->log('clear_all_cache', 'অ্যাডমিন প্যানেল থেকে সমস্ত সিস্টেম ক্যাশ ক্লিয়ার করা হয়েছে');
            }

            return back()->with('success', 'অভিনন্দন! সমস্ত সিস্টেম ক্যাশ, ভিউ ক্যাশ, কনফিগারেশন ও রুট ক্যাশ সফলভাবে ক্লিয়ার হয়েছে!');
        } catch (\Throwable $e) {
            return back()->with('error', 'ক্যাশ ক্লিয়ার করতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Clear compiled Blade views.
     */
    public function clearViews(): RedirectResponse
    {
        try {
            Artisan::call('view:clear');
            return back()->with('success', 'কম্পাইল্ড ভিউ ক্যাশ (Blade Views) সফলভাবে ক্লিয়ার করা হয়েছে!');
        } catch (\Throwable $e) {
            return back()->with('error', 'ভিউ ক্যাশ ক্লিয়ার ব্যর্থ: ' . $e->getMessage());
        }
    }

    /**
     * Clear application data cache.
     */
    public function clearApp(): RedirectResponse
    {
        try {
            SiteSetting::clearCache();
            Artisan::call('cache:clear');
            return back()->with('success', 'অ্যাপ্লিকেশন ডেটা ক্যাশ সফলভাবে ক্লিয়ার করা হয়েছে!');
        } catch (\Throwable $e) {
            return back()->with('error', 'অ্যাপ ক্যাশ ক্লিয়ার ব্যর্থ: ' . $e->getMessage());
        }
    }

    /**
     * Clear config cache.
     */
    public function clearConfig(): RedirectResponse
    {
        try {
            Artisan::call('config:clear');
            return back()->with('success', 'কনফিগারেশন ক্যাশ সফলভাবে ক্লিয়ার করা হয়েছে!');
        } catch (\Throwable $e) {
            return back()->with('error', 'কনফিগ ক্যাশ ক্লিয়ার ব্যর্থ: ' . $e->getMessage());
        }
    }

    /**
     * Clear route cache.
     */
    public function clearRoutes(): RedirectResponse
    {
        try {
            Artisan::call('route:clear');
            return back()->with('success', 'রুট ক্যাশ সফলভাবে ক্লিয়ার করা হয়েছে!');
        } catch (\Throwable $e) {
            return back()->with('error', 'রুট ক্যাশ ক্লিয়ার ব্যর্থ: ' . $e->getMessage());
        }
    }

    /**
     * Optimize application for production.
     */
    public function optimize(): RedirectResponse
    {
        try {
            Artisan::call('optimize');
            return back()->with('success', 'সিস্টেম সফলভাবে অপ্টিমাইজ করা হয়েছে (Config & Route Cached)!');
        } catch (\Throwable $e) {
            return back()->with('error', 'অপ্টিমাইজেশন ব্যর্থ: ' . $e->getMessage());
        }
    }

    /**
     * Get directory size helper.
     */
    private function getDirectorySize(string $directory): int
    {
        $size = 0;
        if (!File::isDirectory($directory)) {
            return 0;
        }

        foreach (File::allFiles($directory) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
