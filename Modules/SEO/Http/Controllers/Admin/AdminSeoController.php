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
                $query->whereBetween('seo_score', [80, 99]);
            } elseif ($level === 'perfect') {
                $query->where('seo_score', '>=', 100);
            } elseif ($level === 'below') {
                $query->where('seo_score', '<', 100);
            }
        }

        $overview = $this->overview();

        // "Issue" filter: records failing one check (read from the stored audit).
        if ($request->filled('issue') && isset(self::ISSUES[$request->string('issue')->value()])) {
            $query->whereIn('id', $overview['issue_ids'][$request->string('issue')->value()] ?? [0]);
        }

        $items = $query->paginate(20)->withQueryString();
        unset($overview['issue_ids']);

        return view('seo::admin.index', ['items' => $items, 'overview' => $overview, 'issues' => self::ISSUES]);
    }

    /** Check key => short name shown on the dashboard. */
    private const ISSUES = [
        'title'         => 'Title length',
        'description'   => 'Description length',
        'keyword_title' => 'Keyword in title',
        'keyword_desc'  => 'Keyword in description',
        'image'         => 'Share image',
        'keywords'      => 'Keywords',
    ];

    /** Everything the dashboard shows above the table, from one pass over the records. */
    private function overview(): array
    {
        $types = ['Book' => 'Books', 'BlogPost' => 'Posts', 'Author' => 'Authors', 'Publisher' => 'Publishers', 'Ebook' => 'Ebooks', 'Webzine' => 'Webzines', 'ResearchPaper' => 'Research', 'Page' => 'Pages'];
        $o = [
            'total' => 0, 'avg' => 0, 'perfect' => 0, 'strong' => 0, 'fair' => 0, 'weak' => 0, 'missing_desc' => 0,
            'buckets' => ['100' => 0, '80–99' => 0, '50–79' => 0, '0–49' => 0],
            'types' => [], 'issues' => array_fill_keys(array_keys(self::ISSUES), 0), 'issue_ids' => [],
            'last_scan' => null, 'manual' => 0,
        ];
        $sum = 0;
        $byType = [];
        SeoMeta::select(['id', 'seoable_type', 'seo_score', 'seo_analysis', 'meta_description', 'last_scanned_at', 'is_auto_generated'])
            ->orderBy('id')->chunkById(1000, function ($rows) use (&$o, &$sum, &$byType) {
                foreach ($rows as $m) {
                    $s = (int) $m->seo_score;
                    $o['total']++;
                    $sum += $s;
                    $s >= 100 ? $o['perfect']++ : ($s >= 80 ? $o['strong']++ : ($s >= 50 ? $o['fair']++ : $o['weak']++));
                    $o['buckets'][$s >= 100 ? '100' : ($s >= 80 ? '80–99' : ($s >= 50 ? '50–79' : '0–49'))]++;
                    if (trim((string) $m->meta_description) === '') {
                        $o['missing_desc']++;
                    }
                    if (!$m->is_auto_generated && $m->seoable_type) {
                        $o['manual']++;
                    }
                    $t = $m->seoable_type ? class_basename($m->seoable_type) : 'Page';
                    $byType[$t][] = $s;
                    foreach ((array) ($m->seo_analysis['checks'] ?? []) as $c) {
                        if (($c['status'] ?? 'pass') !== 'pass' && isset($o['issues'][$c['key'] ?? ''])) {
                            $o['issues'][$c['key']]++;
                            $o['issue_ids'][$c['key']][] = $m->id;
                        }
                    }
                    if ($m->last_scanned_at && (!$o['last_scan'] || $m->last_scanned_at->gt($o['last_scan']))) {
                        $o['last_scan'] = $m->last_scanned_at;
                    }
                }
            });
        $o['avg'] = $o['total'] ? (int) floor($sum / $o['total']) : 0;
        $o['avg_exact'] = $o['total'] ? round($sum / $o['total'], 1) : 0;
        foreach ($byType as $t => $scores) {
            $o['types'][] = ['key' => $t, 'label' => $types[$t] ?? $t, 'count' => count($scores), 'avg' => (int) floor(array_sum($scores) / count($scores))];
        }
        usort($o['types'], fn ($a, $b) => $b['count'] <=> $a['count']);
        arsort($o['issues']);

        return $o;
    }

    /** One slice of a full scan (the page calls this until it reports no next step). */
    public function scanStep(Request $request): JsonResponse
    {
        $v = $request->validate([
            'kind'  => 'required|string|in:books,blog,authors,publishers,ebooks,webzines,research,pages,cleanup',
            'after' => 'nullable|integer|min:0',
            'force' => 'nullable|boolean',
        ]);
        if (function_exists('set_time_limit')) {
            @set_time_limit(60);
        }
        try {
            $step = $this->scannerService->scanStep($v['kind'], (int) ($v['after'] ?? 0), 8.0, (bool) ($v['force'] ?? false));
        } catch (\InvalidArgumentException) {
            // a kind whose module is not installed: go on with the next one
            return response()->json(['kind' => $v['kind'], 'after' => 0, 'scanned' => 0, 'done' => true, 'next' => $this->nextKind($v['kind'])]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Scan failed. Try again.'], 500);
        }
        if ($step['next'] === null) {
            $o = $this->overview();
            unset($o['issue_ids']);
            $step['overview'] = ['avg' => $o['avg'], 'total' => $o['total'], 'perfect' => $o['perfect']];
        }

        return response()->json($step);
    }

    /** Totals so the page can show progress for a full scan. */
    public function scanPlan(): JsonResponse
    {
        $totals = $this->scannerService->scanTotals();

        return response()->json(['steps' => array_merge(array_keys($totals), ['cleanup']), 'totals' => $totals, 'total' => array_sum($totals)]);
    }

    private function nextKind(string $kind): ?string
    {
        $order = array_merge(array_keys($this->scannerService->scanKinds()), ['pages', 'cleanup']);
        $i = array_search($kind, $order, true);

        return $i === false ? 'pages' : ($order[$i + 1] ?? null);
    }

    /** Regenerate one record's tags from its content (discards hand-written text for that record). */
    public function fix(int $id): JsonResponse
    {
        $seo = SeoMeta::findOrFail($id);
        try {
            if ($seo->seoable_type) {
                $model = $seo->seoable;
                if (!$model) {
                    $seo->delete();

                    return response()->json(['removed' => true, 'message' => 'The page no longer exists; its tags were removed.']);
                }
                $seo = $this->scannerService->scanAndSave($model, null, true);
            } else {
                $seo = $this->scannerService->scanAndSavePath($seo->url_path ?: '/', ['force' => true]);
            }
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not fix this page.'], 500);
        }

        return response()->json(['item' => self::modalData($seo), 'message' => 'Fixed — score ' . $seo->seo_score . '/100']);
    }

    /** Live score while editing (same rules as saving). */
    public function analyze(Request $request): JsonResponse
    {
        $v = $request->validate([
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1500',
            'meta_keywords'    => 'nullable|string|max:1000',
            'focus_keyword'    => 'nullable|string|max:150',
            'og_image'         => 'nullable|string|max:500',
        ]);

        return response()->json($this->scannerService->analyzeSeoQuality(
            (string) ($v['meta_title'] ?? ''), (string) ($v['meta_description'] ?? ''), (string) ($v['meta_keywords'] ?? ''), '', $v['og_image'] ?? null, $v['focus_keyword'] ?? null
        ));
    }

    /** Only what the editor needs (the full record carried the whole book row into the page). */
    public static function modalData(SeoMeta $m): array
    {
        return [
            'id'               => $m->id,
            'type'             => $m->seoable_type ? class_basename($m->seoable_type) : 'Page',
            'meta_title'       => (string) $m->meta_title,
            'meta_description' => (string) $m->meta_description,
            'meta_keywords'    => (string) $m->meta_keywords,
            'focus_keyword'    => (string) $m->focus_keyword,
            'canonical_url'    => (string) $m->canonical_url,
            'url_path'         => (string) $m->url_path,
            'robots'           => (string) ($m->robots ?: 'index, follow'),
            'og_image'         => (string) $m->og_image,
            'seo_score'        => (int) $m->seo_score,
            'checks'           => array_values((array) ($m->seo_analysis['checks'] ?? [])),
            'manual'           => !$m->is_auto_generated,
            'update_url'       => route('admin.seo.update', $m->id),
            'fix_url'          => route('admin.seo.fix', $m->id),
        ];
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
            report($e); // details go to the log, not to the browser

            return response()->json([
                'success' => false,
                'message' => 'Scan failed. Try again.',
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

    /** robots values the editor offers; anything else is refused. */
    private const ROBOTS = ['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'];

    /** A link field: a full http(s) URL or a site path — never javascript:, data: or other schemes. */
    private static function linkRule(): \Closure
    {
        return function (string $attr, $value, \Closure $fail) {
            $v = trim((string) $value);
            if ($v === '') {
                return;
            }
            if (str_starts_with($v, '/') && !str_starts_with($v, '//')) {
                return;
            }
            if (!preg_match('#^https?://[^\s<>"\']+$#i', $v)) {
                $fail('Use a full http(s) link or a path that starts with /.');
            }
        };
    }

    /**
     * Save / Update Static Page SEO Meta
     */
    public function updatePage(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'url_path'         => ['required', 'string', 'max:255', 'regex:#^/[^\s<>"\']*$#'],
            'meta_title'       => 'required|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords'    => 'nullable|string|max:1000',
            'focus_keyword'    => 'nullable|string|max:150',
            'canonical_url'    => ['nullable', 'string', 'max:500', self::linkRule()],
            'robots'           => ['required', 'string', \Illuminate\Validation\Rule::in(self::ROBOTS)],
            'og_image'         => ['nullable', 'string', 'max:500', self::linkRule()],
        ]);

        $seo = $this->scannerService->scanAndSavePath($v['url_path'], $v);

        return back()->with('success', 'Saved — score ' . $seo->seo_score . '/100');
    }

    /**
     * Edit / Update Single SeoMeta Record
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $seo = SeoMeta::findOrFail($id);

        // Only these fields can be changed here (the whole request used to be saved, so any column could be set).
        $v = $request->validate([
            'meta_title'       => 'required|string|max:255',
            'meta_description' => 'nullable|string|max:1500',
            'meta_keywords'    => 'nullable|string|max:1000',
            'focus_keyword'    => 'nullable|string|max:150',
            'canonical_url'    => ['nullable', 'string', 'max:500', self::linkRule()],
            'robots'           => ['required', 'string', \Illuminate\Validation\Rule::in(self::ROBOTS)],
            'og_image'         => ['nullable', 'string', 'max:500', self::linkRule()],
        ]);
        $v = array_map(fn ($x) => is_string($x) ? trim($x) : $x, $v);

        $analysis = $this->scannerService->analyzeSeoQuality(
            $v['meta_title'], $v['meta_description'] ?? '', $v['meta_keywords'] ?? '', '', $v['og_image'] ?? null, $v['focus_keyword'] ?? null
        );
        $analysis['by_admin'] = true;

        $seo->update($v + [
            // social cards follow the edited text
            'og_title'            => $v['meta_title'],
            'og_description'      => $v['meta_description'] ?? null,
            'twitter_title'       => $v['meta_title'],
            'twitter_description' => $v['meta_description'] ?? null,
            'twitter_image'       => $v['og_image'] ?? $seo->twitter_image,
            'seo_score'           => $analysis['score'],
            'seo_analysis'        => $analysis,
            'is_auto_generated'   => false,
            'last_scanned_at'     => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['item' => self::modalData($seo->fresh()), 'message' => 'Saved — score ' . $analysis['score'] . '/100']);
        }

        return back()->with('success', 'Saved — score ' . $analysis['score'] . '/100');
    }

    /**
     * Sitemap & Robots Manager
     */
    public function sitemap(): View
    {
        $sitemapUrl = url('/sitemap.xml');
        $robotsUrl = url('/robots.txt');
        $robotsContent = $this->sitemapService->generateRobotsTxt();
        $snapshot = $this->sitemapSnapshot();

        return view('seo::admin.sitemap', compact('sitemapUrl', 'robotsUrl', 'robotsContent', 'snapshot'));
    }

    /**
     * Live numbers for the sitemap page (also used by the AJAX refresh).
     */
    public function sitemapData(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->sitemapSnapshot());
    }

    /**
     * Rebuild every sitemap file without leaving the page.
     */
    public function regenerateSitemap(): \Illuminate\Http\JsonResponse
    {
        try {
            \App\Http\Controllers\SitemapController::regenerateStaticSitemap();
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Could not rebuild the sitemaps: ' . $e->getMessage()], 500);
        }

        $snapshot = $this->sitemapSnapshot();

        return response()->json([
            'success'  => true,
            'message'  => 'Sitemaps rebuilt — ' . number_format($snapshot['stats']['urls']) . ' URLs in ' . $snapshot['stats']['files'] . ' files.',
            'snapshot' => $snapshot,
        ]);
    }

    /**
     * Read every generated sitemap file: type, URL count, size, age, validity.
     *
     * @return array{files: array<int, array<string, mixed>>, stats: array<string, mixed>}
     */
    private function sitemapSnapshot(): array
    {
        $paths = array_merge([public_path('sitemap.xml')], glob(public_path('sitemaps/*.xml')) ?: []);
        $files = [];
        $urls = 0;
        $latest = null;
        $invalid = 0;

        foreach ($paths as $path) {
            if (! is_file($path)) {
                continue;
            }

            $prev = libxml_use_internal_errors(true);
            $xml = simplexml_load_file($path);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);

            $valid = $xml !== false;
            $isIndex = $valid && $xml->getName() === 'sitemapindex';
            $count = $valid ? ($isIndex ? count($xml->sitemap) : count($xml->url)) : 0;
            $modified = \Carbon\Carbon::createFromTimestamp(filemtime($path));
            $rel = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $path);
            $rel = str_replace('\\', '/', $rel);

            if (! $valid) {
                $invalid++;
            }
            if (! $isIndex) {
                $urls += $count;
            }
            if (! $latest || $modified->gt($latest)) {
                $latest = $modified;
            }

            $files[] = [
                'name'     => basename($path),
                'url'      => url('/' . $rel),
                'kind'     => $isIndex ? 'Index' : 'URLs',
                'count'    => $count,
                'size'     => round(filesize($path) / 1024, 1),
                'modified' => $modified->locale('en')->diffForHumans(),
                'valid'    => $valid,
            ];
        }

        // sitemap.xml may be a copy of the index: list it first, then the rest A–Z
        usort($files, fn ($a, $b) => [$a['name'] !== 'sitemap.xml', $a['name']] <=> [$b['name'] !== 'sitemap.xml', $b['name']]);

        return [
            'files' => $files,
            'stats' => [
                'files'    => count($files),
                'urls'     => $urls,
                'invalid'  => $invalid,
                'updated'  => $latest ? $latest->locale('en')->diffForHumans() : 'never',
                'in_robots' => str_contains($this->sitemapService->generateRobotsTxt(), url('/sitemap.xml')),
            ],
        ];
    }

    /**
     * Instant Search Engine Pinger (Google, Bing, IndexNow)
     */
    public function pingSearchEngines(Request $request, \Modules\SEO\Services\InstantIndexingService $indexingService)
    {
        $results = $indexingService->pingAllSearchEngines();

        // Report what the engine really answered, not a hard-coded "sent".
        foreach ($results as $engine => &$r) {
            if (! $r['success']) {
                $r['message'] = ucfirst($engine) . ' answered HTTP ' . $r['status'] . ($r['status'] === 404 || $r['status'] === 410 ? ' — this ping endpoint is retired' : '');
            }
        }
        unset($r);

        $successCount = count(array_filter($results, fn ($r) => $r['success']));
        $message = "{$successCount}/" . count($results) . ' services accepted the ping.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => $successCount > 0, 'message' => $message, 'results' => $results]);
        }

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
    public function brokenLinks(Request $request): View
    {
        $Link = \Modules\SEO\Models\SeoBrokenLink::class;
        $tab = $request->query('tab') === 'resolved' ? 'resolved' : 'open';
        $kind = (string) $request->query('kind', '');
        $sort = $request->query('sort') === 'hits' ? 'hits' : 'recent';
        $q = trim((string) $request->query('q', ''));

        // Files that were missing but exist now (e.g. a placeholder image that has been added) fix themselves.
        $Link::where('is_resolved', false)->where('url', 'like', '%.%')->latest('last_hit_at')->limit(300)->get()
            ->filter(fn ($l) => $l->existsNow())
            ->each(fn ($l) => $l->forceFill(['is_resolved' => true])->saveQuietly());

        // Plain LIKE on known file extensions: works on MySQL/MariaDB and SQLite (SQLite has no REGEXP).
        $extImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'ico', 'bmp'];
        $extAsset = ['css', 'js', 'map', 'json', 'xml', 'txt', 'pdf', 'epub', 'zip', 'woff', 'woff2', 'ttf', 'eot', 'otf', 'mp3', 'mp4', 'webm', 'csv', 'xlsx', 'docx', 'php'];
        $endsWith = function ($w, array $exts) {
            $w->where(function ($x) use ($exts) {
                foreach ($exts as $e) {
                    $x->orWhere('url', 'like', '%.' . $e);
                }
            });
        };
        $kindScope = function ($query, string $kind) use ($extImage, $extAsset, $endsWith) {
            if ($kind === 'image') {
                $endsWith($query, $extImage);
            } elseif ($kind === 'asset') {
                $endsWith($query, $extAsset);
            } elseif ($kind === 'page') {
                $query->whereNot(fn ($w) => $endsWith($w, array_merge($extImage, $extAsset)));
            }
        };

        $base = $Link::where('is_resolved', $tab === 'resolved');
        $brokenLinks = (clone $base)
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('url', 'like', '%' . $q . '%')
                ->orWhere('url', 'like', '%' . str_replace('%2F', '/', rawurlencode($q)) . '%')
                ->orWhere('referer', 'like', '%' . $q . '%')))
            ->when(isset($Link::KINDS[$kind]), fn ($w) => $kindScope($w, $kind))
            ->when($sort === 'hits', fn ($w) => $w->orderByDesc('hits_count'), fn ($w) => $w->orderByDesc('last_hit_at'))
            ->paginate(25)->withQueryString();

        $counts = ['open' => $Link::where('is_resolved', false)->count(), 'resolved' => $Link::where('is_resolved', true)->count()];
        foreach (array_keys($Link::KINDS) as $k) {
            $counts[$k] = $Link::query()->where('is_resolved', false)->where(fn ($w) => $kindScope($w, $k))->count();
        }
        $totalBroken = $counts['open'];
        $totalHits = (int) $Link::where('is_resolved', false)->sum('hits_count');

        // Common destinations for the "fix" box.
        $targets = collect(['/', '/books', '/ebooks', '/blog', '/webzines', '/authors', '/publishers', '/contact', '/track-order'])
            ->filter(fn ($p) => $Link::routeExists($p))->values();

        $kinds = $Link::KINDS;

        return view('seo::admin.broken-links', compact('brokenLinks', 'totalBroken', 'totalHits', 'counts', 'tab', 'kind', 'sort', 'q', 'targets', 'kinds'));
    }

    /**
     * Fix a broken link: 301 to the right address (default) or just dismiss it (action=ignore).
     */
    public function resolveBrokenLink(Request $request, int $id): RedirectResponse
    {
        $link = \Modules\SEO\Models\SeoBrokenLink::findOrFail($id);

        if ($request->input('action') === 'ignore') {
            $link->update(['is_resolved' => true]);

            return back()->with('success', 'লিংকটি তালিকা থেকে সরানো হয়েছে (রিডাইরেক্ট ছাড়া)। আবার কেউ এই লিংকে এলে এটি আবার তালিকায় আসবে।');
        }

        $target = $this->cleanRedirectTarget((string) $request->input('target_url'), $link->url);
        if (str_starts_with($target, '!')) {
            return back()->withInput()->with('error', substr($target, 1));
        }

        \Modules\SEO\Models\SeoRedirect::updateOrCreate(
            ['source_url' => $link->url],
            [
                'target_url'  => $target,
                'status_code' => 301,
                'is_active'   => true,
                'notes'       => 'Auto created from 404 broken link resolution',
            ]
        );
        $link->update(['is_resolved' => true]);

        return back()->with('success', '৩০১ রিডাইরেক্ট চালু হয়েছে: ' . $link->display_url . ' → ' . rawurldecode($target));
    }

    /** Bulk: ignore, delete or re-open the ticked links. */
    public function bulkBrokenLinks(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
            'action' => 'required|in:ignore,delete,reopen',
        ]);
        $query = \Modules\SEO\Models\SeoBrokenLink::whereIn('id', $data['ids']);
        $n = match ($data['action']) {
            'delete' => $query->delete(),
            'reopen' => $query->update(['is_resolved' => false]),
            default  => $query->update(['is_resolved' => true]),
        };
        $done = ['ignore' => 'তালিকা থেকে সরানো হয়েছে', 'delete' => 'মুছে ফেলা হয়েছে', 'reopen' => 'আবার খোলা হয়েছে'][$data['action']];

        return back()->with('success', "{$n}টি লিংক {$done}।");
    }

    /**
     * Normalise a redirect target. Returns the path, or "!message" when it cannot be used.
     * Only addresses on this site are accepted; a target equal to the broken address would loop.
     */
    private function cleanRedirectTarget(string $raw, string $source): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '!সঠিক লিংক লিখুন (যেমন /books)।';
        }
        if (preg_match('#^https?://#i', $raw)) {
            $strip = fn ($h) => preg_replace('/^www\./', '', strtolower((string) $h));
            $own = array_map($strip, [request()->getHost(), parse_url((string) config('app.url'), PHP_URL_HOST)]);
            if (!in_array($strip(parse_url($raw, PHP_URL_HOST)), $own, true)) {
                return '!শুধু এই সাইটের লিংক দিন। অন্য সাইটে পাঠাতে চাইলে রিডাইরেক্ট ম্যানেজার ব্যবহার করুন।';
            }
            $raw = (parse_url($raw, PHP_URL_PATH) ?: '/') . (($qs = parse_url($raw, PHP_URL_QUERY)) ? '?' . $qs : '');
        }
        $raw = '/' . ltrim($raw, '/');
        $same = fn ($p) => rtrim(rawurldecode((string) parse_url($p, PHP_URL_PATH)), '/');
        if ($same($raw) === $same($source)) {
            return '!লক্ষ্য লিংকটি ভাঙা লিংকের মতোই — এতে রিডাইরেক্ট লুপ হবে।';
        }

        return $raw;
    }
}

