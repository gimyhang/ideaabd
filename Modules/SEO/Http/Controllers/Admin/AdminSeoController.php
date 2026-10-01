<?php

namespace Modules\SEO\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\SEO\Models\SeoMeta;
use Modules\SEO\Services\AutoSeoScannerService;
use Modules\SEO\Services\SitemapService;

class AdminSeoController extends Controller
{
    public function __construct(
        protected AutoSeoScannerService $scannerService,
        protected SitemapService $sitemapService
    ) {}

    /**
     * SEO Dashboard & Overview
     */
    public function index(Request $request): View
    {
        $query = SeoMeta::with('seoable')->latest('updated_at');

        if ($request->filled('search')) {
            $s = $request->string('search')->trim()->value();
            $query->where(function ($q) use ($s) {
                $q->where('meta_title', 'like', "%{$s}%")
                  ->orWhere('meta_description', 'like', "%{$s}%")
                  ->orWhere('url_path', 'like', "%{$s}%")
                  ->orWhere('focus_keyword', 'like', "%{$s}%");
            });
        }

        if ($request->filled('filter_type')) {
            $type = $request->string('filter_type')->value();
            if ($type === 'pages') {
                $query->whereNull('seoable_type');
            } else {
                $query->where('seoable_type', 'like', "%{$type}%");
            }
        }

        if ($request->filled('score_level')) {
            $level = $request->string('score_level')->value();
            if ($level === 'poor') {
                $query->where('seo_score', '<', 50);
            } elseif ($level === 'fair') {
                $query->whereBetween('seo_score', [50, 79]);
            } elseif ($level === 'good') {
                $query->where('seo_score', '>=', 80);
            }
        }

        $items = $query->paginate(20)->withQueryString();

        // High Level Metrics
        $totalItems = SeoMeta::count();
        $avgScore = $totalItems > 0 ? (int) SeoMeta::avg('seo_score') : 0;
        $highScoreCount = SeoMeta::where('seo_score', '>=', 80)->count();
        $needsWorkCount = SeoMeta::where('seo_score', '<', 60)->count();
        $missingDescCount = SeoMeta::whereNull('meta_description')->orWhere('meta_description', '')->count();

        return view('seo::admin.index', compact(
            'items', 'totalItems', 'avgScore', 'highScoreCount', 'needsWorkCount', 'missingDescCount'
        ));
    }

    /**
     * Interactive Live SEO Scanner & Auto-Fix Tool
     */
    public function scanner(): View
    {
        return view('seo::admin.scanner');
    }

    /**
     * AJAX Live Scanner for Any Model or URL
     */
    public function scanAjax(Request $request): JsonResponse
    {
        $request->validate([
            'type'             => 'required|string|in:book,blog,author,publisher,ebook,webzine,url',
            'id'               => 'nullable',
            'url_path'         => 'nullable|string',
            'focus_keyword'    => 'nullable|string|max:150',
        ]);

        $type = $request->input('type');
        $id = $request->input('id');
        $focus = $request->input('focus_keyword');

        try {
            if ($type === 'url') {
                $path = $request->input('url_path', '/');
                $seo = $this->scannerService->scanAndSavePath($path, ['focus_keyword' => $focus]);
                return response()->json([
                    'success' => true,
                    'seo'     => $seo,
                    'message' => 'ইউআরএল এসইও অডিট ও মেটা সফলভাবে তৈরি হয়েছে!',
                ]);
            }

            $modelClass = match ($type) {
                'book'      => \Modules\Book\Models\Book::class,
                'blog'      => \Modules\Blog\Models\BlogPost::class,
                'author'    => \Modules\Author\Models\Author::class,
                'publisher' => \Modules\Publisher\Models\Publisher::class,
                'ebook'     => \Modules\Ebook\Models\Ebook::class,
                'webzine'   => \Modules\Webzine\Models\Webzine::class,
                default     => null,
            };

            if (!$modelClass || !class_exists($modelClass)) {
                return response()->json(['success' => false, 'message' => 'মডেল পাওয়া যায়নি।'], 404);
            }

            $model = $modelClass::find($id);
            if (!$model) {
                return response()->json(['success' => false, 'message' => 'নির্দিষ্ট রেকর্ড খুঁজে পাওয়া যায়নি।'], 404);
            }

            $seo = $this->scannerService->scanAndSave($model, $focus);

            return response()->json([
                'success' => true,
                'seo'     => $seo,
                'message' => 'সফলভাবে স্ক্যান সম্পন্ন হয়েছে! এসইও স্কোর: ' . $seo->seo_score . '/১০০',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'স্ক্যানে ত্রুটি: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 1-Click Bulk Batch Scanner across all database records
     */
    public function batchScan(Request $request): RedirectResponse
    {
        $summary = $this->scannerService->batchScanAll();

        return redirect()->route('admin.seo.index')->with(
            'success',
            "সফলভাবে মোট {$summary['total']}টি রেকর্ড (বই: {$summary['books']}, ব্লগ: {$summary['blog_posts']}, লেখক: {$summary['authors']}, পেজ: {$summary['pages']}) স্বয়ংক্রিয় স্ক্যান ও এসইও ট্যাগ অপ্টিমাইজ করা হয়েছে!"
        );
    }

    /**
     * Static Pages SEO Management
     */
    public function pages(): View
    {
        $pages = SeoMeta::whereNull('seoable_type')->orderBy('url_path')->get();
        return view('seo::admin.pages', compact('pages'));
    }

    /**
     * Save / Update Static Page SEO Meta
     */
    public function updatePage(Request $request): RedirectResponse
    {
        $request->validate([
            'url_path'         => 'required|string|max:255',
            'meta_title'       => 'required|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords'    => 'nullable|string|max:1000',
            'focus_keyword'    => 'nullable|string|max:150',
            'canonical_url'    => 'nullable|url|max:500',
            'robots'           => 'required|string|max:50',
            'og_image'         => 'nullable|string|max:500',
        ]);

        $this->scannerService->scanAndSavePath($request->input('url_path'), $request->all());

        return back()->with('success', 'পেজের এসইও কনফিগারেশন সফলভাবে সংরক্ষিত হয়েছে!');
    }

    /**
     * Edit / Update Single SeoMeta Record
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $seo = SeoMeta::findOrFail($id);

        $request->validate([
            'meta_title'          => 'required|string|max:255',
            'meta_description'    => 'nullable|string|max:1500',
            'meta_keywords'       => 'nullable|string|max:1000',
            'focus_keyword'       => 'nullable|string|max:150',
            'canonical_url'       => 'nullable|string|max:500',
            'robots'              => 'required|string|max:50',
            'og_title'            => 'nullable|string|max:255',
            'og_description'      => 'nullable|string|max:1500',
            'og_image'            => 'nullable|string|max:500',
            'twitter_card'        => 'nullable|string|max:50',
            'twitter_title'       => 'nullable|string|max:255',
            'twitter_description' => 'nullable|string|max:1500',
            'twitter_image'       => 'nullable|string|max:500',
        ]);

        $analysis = $this->scannerService->analyzeSeoQuality(
            $request->input('meta_title'),
            $request->input('meta_description') ?? '',
            $request->input('meta_keywords') ?? '',
            '',
            $request->input('og_image'),
            $request->input('focus_keyword')
        );

        $seo->update(array_merge($request->all(), [
            'seo_score'         => $analysis['score'],
            'seo_analysis'      => $analysis,
            'is_auto_generated' => false,
            'last_scanned_at'   => now(),
        ]));

        return back()->with('success', 'এসইও মেটা সফলভাবে আপডেট করা হয়েছে! নতুন স্কোর: ' . $analysis['score'] . '/১০০');
    }

    /**
     * Sitemap & Robots Manager
     */
    public function sitemap(): View
    {
        $sitemapUrl = url('/sitemap.xml');
        $robotsUrl = url('/robots.txt');
        $robotsContent = $this->sitemapService->generateRobotsTxt();

        return view('seo::admin.sitemap', compact('sitemapUrl', 'robotsUrl', 'robotsContent'));
    }

    /**
     * Instant Search Engine Pinger (Google, Bing, IndexNow)
     */
    public function pingSearchEngines(Request $request, \Modules\SEO\Services\InstantIndexingService $indexingService): RedirectResponse
    {
        $results = $indexingService->pingAllSearchEngines();

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $message = "গুগল, বিং ও IndexNow-এ ইনস্ট্যান্ট পিং পাঠানো হয়েছে! ({$successCount}/৩টি সার্ভিস সক্রিয় প্রতিক্রিয়া জানিয়েছে)";

        return back()->with('success', $message);
    }

    /**
     * 301 / 302 URL Redirects Management
     */
    public function redirects(): View
    {
        $redirects = \Modules\SEO\Models\SeoRedirect::latest('updated_at')->paginate(25);
        $totalRedirects = \Modules\SEO\Models\SeoRedirect::count();
        $totalHits = (int) \Modules\SEO\Models\SeoRedirect::sum('hits_count');

        return view('seo::admin.redirects', compact('redirects', 'totalRedirects', 'totalHits'));
    }

    /**
     * Store 301 / 302 URL Redirect
     */
    public function storeRedirect(Request $request): RedirectResponse
    {
        $request->validate([
            'source_url'  => 'required|string|max:500',
            'target_url'  => 'required|string|max:500',
            'status_code' => 'required|in:301,302',
            'notes'       => 'nullable|string|max:255',
        ]);

        $source = '/' . ltrim(parse_url($request->input('source_url'), PHP_URL_PATH) ?? $request->input('source_url'), '/');
        $target = $request->input('target_url');

        \Modules\SEO\Models\SeoRedirect::updateOrCreate(
            ['source_url' => $source],
            [
                'target_url'  => $target,
                'status_code' => (int) $request->input('status_code', 301),
                'is_active'   => true,
                'notes'       => $request->input('notes'),
            ]
        );

        return back()->with('success', "রিডাইরেক্ট সফলভাবে যোগ করা হয়েছে: {$source} → {$target}");
    }

    /**
     * Delete a Redirect rule
     */
    public function deleteRedirect(int $id): RedirectResponse
    {
        \Modules\SEO\Models\SeoRedirect::findOrFail($id)->delete();
        return back()->with('success', 'রিডাইরেক্ট নিয়ম মুছে ফেলা হয়েছে।');
    }

    /**
     * 404 Broken Links Monitoring
     */
    public function brokenLinks(): View
    {
        $brokenLinks = \Modules\SEO\Models\SeoBrokenLink::where('is_resolved', false)->latest('last_hit_at')->paginate(25);
        $totalBroken = \Modules\SEO\Models\SeoBrokenLink::where('is_resolved', false)->count();

        return view('seo::admin.broken-links', compact('brokenLinks', 'totalBroken'));
    }

    /**
     * Mark 404 broken link as resolved / create redirect
     */
    public function resolveBrokenLink(Request $request, int $id): RedirectResponse
    {
        $link = \Modules\SEO\Models\SeoBrokenLink::findOrFail($id);

        if ($request->filled('target_url')) {
            \Modules\SEO\Models\SeoRedirect::updateOrCreate(
                ['source_url' => $link->url],
                [
                    'target_url'  => $request->input('target_url'),
                    'status_code' => 301,
                    'is_active'   => true,
                    'notes'       => 'Auto created from 404 broken link resolution',
                ]
            );
        }

        $link->update(['is_resolved' => true]);

        return back()->with('success', 'ব্রোকেন লিংকটি সমাধান করা হয়েছে' . ($request->filled('target_url') ? ' এবং ৩০১ রিডাইরেক্ট সেট করা হয়েছে।' : '।'));
    }
}

