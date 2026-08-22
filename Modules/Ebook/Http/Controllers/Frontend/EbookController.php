<?php

declare(strict_types=1);

namespace Modules\Ebook\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\UserEbookLibrary;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Ebook\Models\Ebook;
use Modules\Book\Models\Category;
use Modules\Author\Models\Author;
use Modules\Publisher\Models\Publisher;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EbookController extends Controller
{
    /**
     * ডিজিটাল ই-বুক ক্যাটালগ ও ফিল্টারিং
     */
    public function index(Request $request): View
    {
        $canUseEbooks = false;

        try {
            $canUseEbooks = DB::getSchemaBuilder()->hasTable('ebooks');
        } catch (\Throwable) {
            $canUseEbooks = false;
        }

        $ebooks = collect();
        $categories = collect();
        $sidebarAuthors = collect();
        $sidebarPublishers = collect();
        $featuredEbooks = collect();
        $stats = [
            'total' => 0,
            'free' => 0,
            'epub' => 0,
            'pdf' => 0,
        ];

        $isSearchMode = $request->anyFilled([
            'search', 'category', 'author', 'publisher', 'format', 
            'min_price', 'max_price', 'free_only', 'sort'
        ]);

        if ($canUseEbooks) {
            // Stats
            $stats['total'] = Ebook::query()->where('is_active', true)->count();
            $stats['free'] = Ebook::query()->where('is_active', true)->where(function ($q) {
                $q->where('price', '<=', 0)->orWhere('discount_price', '=', 0);
            })->count();
            $stats['epub'] = Ebook::query()->where('is_active', true)->where(function ($q) {
                $q->where('file_type', 'epub')
                  ->orWhere('file_path', 'LIKE', '%.epub')
                  ->orWhereNotNull('epub_file_path');
            })->count();
            $stats['pdf'] = Ebook::query()->where('is_active', true)->where(function ($q) {
                $q->where('file_type', 'pdf')
                  ->orWhere('file_path', 'LIKE', '%.pdf')
                  ->orWhereNull('file_type');
            })->count();

            // Categories
            try {
                if (DB::getSchemaBuilder()->hasTable('categories')) {
                    $categories = Category::query()
                        ->where('is_active', true)
                        ->withCount(['ebooks' => fn ($q) => $q->where('is_active', true)])
                        ->orderByDesc('ebooks_count')
                        ->orderBy('sort_order')
                        ->get(['id', 'name', 'slug', 'ebooks_count']);
                }
            } catch (\Throwable) {}

            // Authors
            try {
                if (DB::getSchemaBuilder()->hasTable('authors')) {
                    $sidebarAuthors = Author::query()
                        ->where('is_active', true)
                        ->withCount(['ebooks' => fn ($q) => $q->where('is_active', true)])
                        ->orderByDesc('ebooks_count')
                        ->orderBy('name')
                        ->take(25)
                        ->get(['id', 'name', 'slug', 'ebooks_count']);
                }
            } catch (\Throwable) {}

            // Publishers
            try {
                if (DB::getSchemaBuilder()->hasTable('publishers')) {
                    $sidebarPublishers = Publisher::query()
                        ->where('is_active', true)
                        ->withCount(['ebooks' => fn ($q) => $q->where('is_active', true)])
                        ->orderByDesc('ebooks_count')
                        ->orderBy('name')
                        ->take(25)
                        ->get(['id', 'name', 'slug', 'ebooks_count']);
                }
            } catch (\Throwable) {}

            // Featured Ebooks for Hero
            $featuredEbooks = Ebook::query()
                ->with(['author', 'publisher', 'category'])
                ->where('is_active', true)
                ->orderByDesc('sales_count')
                ->take(4)
                ->get();

            // Main Query
            $query = Ebook::query()
                ->with(['author', 'publisher', 'category'])
                ->where('is_active', true);

            // Filter: Category
            if ($request->filled('category')) {
                $query->whereHas('category', fn ($c) => $c->where('slug', $request->string('category')));
            }

            // Filter: Author
            if ($request->filled('author')) {
                $query->where(function ($q) use ($request) {
                    $authorSlug = $request->string('author');
                    $q->whereHas('author', fn ($a) => $a->where('slug', $authorSlug))
                      ->orWhereHas('authors', fn ($a) => $a->where('slug', $authorSlug));
                });
            }

            // Filter: Publisher
            if ($request->filled('publisher')) {
                $query->whereHas('publisher', fn ($p) => $p->where('slug', $request->string('publisher')));
            }

            // Filter: Format
            if ($request->filled('format')) {
                $fmt = strtolower($request->string('format')->value());
                if ($fmt === 'epub') {
                    $query->where(fn ($q) => 
                        $q->where('file_type', 'epub')
                          ->orWhere('file_path', 'LIKE', '%.epub')
                          ->orWhereNotNull('epub_file_path')
                    );
                } elseif ($fmt === 'pdf') {
                    $query->where(fn ($q) => 
                        $q->where('file_type', 'pdf')
                          ->orWhere('file_path', 'LIKE', '%.pdf')
                    );
                } elseif ($fmt === 'free') {
                    $query->where(fn ($q) => 
                        $q->where('price', '<=', 0)
                          ->orWhere('discount_price', '=', 0)
                    );
                } elseif ($fmt === 'paid') {
                    $query->where('price', '>', 0);
                }
            }

            // Filter: Free Only switch
            if ($request->boolean('free_only')) {
                $query->where(fn ($q) => 
                    $q->where('price', '<=', 0)
                      ->orWhere('discount_price', '=', 0)
                );
            }

            // Filter: Price Range
            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->float('min_price'));
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->float('max_price'));
            }

            // Filter: Search Keyword
            if ($request->filled('search')) {
                $search = $request->string('search')->trim()->value();
                $query->where(function ($sub) use ($search) {
                    $sub->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('subtitle', 'LIKE', "%{$search}%")
                        ->orWhere('author_name', 'LIKE', "%{$search}%")
                        ->orWhere('isbn', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhereHas('author', fn ($a) => $a->where('name', 'LIKE', "%{$search}%"))
                        ->orWhereHas('publisher', fn ($p) => $p->where('name', 'LIKE', "%{$search}%"))
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'LIKE', "%{$search}%"));
                });
            }

            // Sorting
            if ($request->filled('sort')) {
                match ($request->string('sort')->value()) {
                    'price_low'   => $query->orderBy('price', 'asc'),
                    'price_high'  => $query->orderBy('price', 'desc'),
                    'discount_high'=> $query->orderByRaw('(price - COALESCE(discount_price, price)) desc'),
                    'bestselling', 'popular' => $query->orderByDesc('sales_count')->orderByDesc('read_count'),
                    'oldest'      => $query->oldest(),
                    default       => $query->latest(),
                };
            } else {
                $query->latest();
            }

            $ebooks = $query->paginate(16)->withQueryString();
        }

        return view('ebook::frontend.index', compact(
            'ebooks',
            'categories',
            'sidebarAuthors',
            'sidebarPublishers',
            'featuredEbooks',
            'stats',
            'isSearchMode'
        ));
    }

    /**
     * ই-বুক বিস্তারিত বিবরণ ও এক্সেস স্টেটাস
     */
    public function show(string $slug): View
    {
        $ebook = $this->resolveEbook($slug);

        if (!$ebook || (!$ebook->is_active && !auth()->user()?->isAdmin() && auth()->id() !== $ebook->author_user_id)) {
            abort(404, 'অনুরোধকৃত ই-বুকটি পাওয়া যায়নি।');
        }

        // Check current user's legitimate library access (admin, author, paid order, or claimed free)
        $user = auth()->user();
        $isOwnerOrAdmin = $user && ($user->isAdmin() || $user->isSubAdmin() || $ebook->author_user_id === $user->id);
        $hasAccess = $this->checkUserEbookAccess($user, $ebook);
        $libraryEntry = $hasAccess && $user ? UserEbookLibrary::where('user_id', $user->id)->where('ebook_id', $ebook->id)->first() : null;

        $relatedEbooks = Ebook::query()
            ->where('id', '!=', $ebook->id)
            ->where('is_active', true)
            ->when($ebook->category_id, fn ($q) => $q->where('category_id', $ebook->category_id))
            ->inRandomOrder()
            ->take(6)
            ->get();

        if ($relatedEbooks->isEmpty()) {
            $relatedEbooks = Ebook::query()
                ->where('id', '!=', $ebook->id)
                ->where('is_active', true)
                ->latest()
                ->take(6)
                ->get();
        }

        $authorOtherEbooks = collect();
        if ($ebook->author_id) {
            $authorOtherEbooks = Ebook::query()
                ->where('id', '!=', $ebook->id)
                ->where('author_id', $ebook->author_id)
                ->where('is_active', true)
                ->take(4)
                ->get();
        }

        return view('ebook::frontend.show', compact(
            'ebook',
            'relatedEbooks',
            'authorOtherEbooks',
            'hasAccess',
            'libraryEntry',
            'isOwnerOrAdmin'
        ));
    }

    /**
     * অনলাইন সুরক্ষিত ই-বুক রিডার (EPUB ও PDF সাপোর্টেড)
     */
    public function read(string $slug): View|RedirectResponse
    {
        $ebook = $this->resolveEbook($slug);

        if (!$ebook || (!$ebook->is_active && !auth()->user()?->isAdmin() && auth()->id() !== $ebook->author_user_id)) {
            abort(404, 'অনুরোধকৃত ই-বুক পাওয়া যায়নি।');
        }

        $user = auth()->user();
        $hasAccess = false;
        $libraryEntry = null;

        $user = auth()->user();
        $libraryEntry = null;

        // Free e-books can be read online by anyone
        if ($ebook->is_free) {
            $hasAccess = true;
            if ($user) {
                $libraryEntry = UserEbookLibrary::where('user_id', $user->id)->where('ebook_id', $ebook->id)->first();
            }
        } else {
            // For Paid E-Books: verify legitimate purchase or admin/author access
            $hasAccess = $this->checkUserEbookAccess($user, $ebook);
            if ($user && $hasAccess) {
                $libraryEntry = UserEbookLibrary::where('user_id', $user->id)->where('ebook_id', $ebook->id)->first();
            }
        }

        if (!$hasAccess) {
            return redirect()->route('ebook.show', $ebook->slug)
                ->with('info', 'সম্পূর্ণ ই-বুকটি পড়ার জন্য অনুগ্রহ করে বইটি ক্রয় করুন অথবা ফ্রি প্রিভিউ পড়ুন।');
        }

        // Increment read count silently
        try {
            $ebook->increment('read_count');
        } catch (\Throwable) {}

        // Determine Reader Type and Stream URL
        $readerType = 'epub';
        if (empty($ebook->epub_file_path) && !empty($ebook->file_path) && str_ends_with(strtolower((string)$ebook->file_path), '.pdf')) {
            $readerType = 'pdf';
        } elseif (strtolower((string)$ebook->file_type) === 'pdf') {
            $readerType = 'pdf';
        }

        $streamUrl = route('ebook.stream', $ebook->id);

        // Anti-Piracy Watermark Text
        $watermarkText = ($user ? ($user->name . ' (' . ($user->phone ?: $user->email) . ')') : 'আইডিয়া প্রকাশন')
            . ' • ' . ($libraryEntry ? ('Order #' . ($libraryEntry->order_id ?: 'Claimed')) : 'Licensed Reader')
            . ' • ' . date('d-m-Y');

        $bookmarks = $libraryEntry?->bookmarks_data ?? [];
        $lastReadPage = $libraryEntry?->last_read_page ?? 1;

        return view('ebook::frontend.read', compact(
            'ebook',
            'readerType',
            'streamUrl',
            'watermarkText',
            'libraryEntry',
            'bookmarks',
            'lastReadPage'
        ));
    }

    /**
     * ফ্রি স্যাম্পল প্রিভিউ রিডার
     */
    public function preview(string $slug): View
    {
        $ebook = $this->resolveEbook($slug);

        if (!$ebook || (!$ebook->is_active && !auth()->user()?->isAdmin() && auth()->id() !== $ebook->author_user_id)) {
            abort(404, 'অনুরোধকৃত ই-বুক পাওয়া যায়নি।');
        }

        $readerType = 'epub';
        if (!empty($ebook->sample_file_path) && str_ends_with(strtolower($ebook->sample_file_path), '.pdf')) {
            $readerType = 'pdf';
        } elseif (empty($ebook->epub_file_path) && !empty($ebook->file_path) && str_ends_with(strtolower($ebook->file_path), '.pdf')) {
            $readerType = 'pdf';
        }

        $streamUrl = route('ebook.stream', ['id' => $ebook->id, 'sample' => 1]);
        $watermarkText = 'ফ্রি নমুনা অংশ (Sample Preview) • আইডিয়া প্রকাশন • সর্বস্বত্ব সংরক্ষিত';
        $bookmarks = [];
        $lastReadPage = 1;
        $libraryEntry = null;

        return view('ebook::frontend.read', compact(
            'ebook',
            'readerType',
            'streamUrl',
            'watermarkText',
            'libraryEntry',
            'bookmarks',
            'lastReadPage'
        ));
    }

    /**
     * সিকিউর ফাইল স্ট্রিম এন্ডপয়েন্ট (CORS, সঠিক MIME Type ও DRM ভ্যালিডেশনসহ)
     */
    public function stream(int|string $id, Request $request): BinaryFileResponse|\Illuminate\Http\Response
    {
        $ebook = Ebook::findOrFail($id);
        $user = auth()->user();
        $isSample = $request->query('sample') == '1';

        // Access check for full reading (sample and free books are readable)
        if (!$isSample && !$ebook->is_free) {
            $hasAccess = $this->checkUserEbookAccess($user, $ebook);

            if (!$hasAccess) {
                abort(403, 'অননুমোদিত ই-বুক এক্সেস। দয়া করে বইটি ক্রয় করুন।');
            }
        }

        // Determine file path: prefer EPUB, fallback to sample or primary file
        $filePath = null;
        if ($isSample && $ebook->sample_file_path) {
            $filePath = $ebook->sample_file_path;
        } elseif ($ebook->epub_file_path) {
            $filePath = $ebook->epub_file_path;
        } else {
            $filePath = $ebook->file_path ?: $ebook->sample_file_path;
        }

        if (!$filePath) {
            abort(404, 'ই-বুক ফাইল সার্ভারে পাওয়া যায়নি।');
        }

        // Resolve absolute file path (handling clean relative paths, storage prefixes and URLs)
        $cleanPath = preg_replace('#^https?://[^/]+/storage/#', '', (string)$filePath);
        $cleanPath = preg_replace('#^/storage/#', '', $cleanPath);
        $cleanPath = ltrim($cleanPath, '/');

        $fullPath = null;
        $candidates = [
            storage_path('app/public/' . $cleanPath),
            storage_path('app/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            public_path($cleanPath),
            storage_path('app/secure/ebooks/' . basename($cleanPath)),
            storage_path('app/public/' . ltrim($filePath, '/')),
            storage_path('app/' . ltrim($filePath, '/')),
            public_path(ltrim($filePath, '/')),
        ];

        foreach ($candidates as $cand) {
            if (file_exists($cand) && is_file($cand)) {
                $fullPath = $cand;
                break;
            }
        }

        if (!$fullPath) {
            abort(404, 'ই-বুক ফাইল স্টোরেজে পাওয়া যায়নি।');
        }

        // Detect correct MIME type
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $contentType = match ($ext) {
            'epub'  => 'application/epub+zip',
            'pdf'   => 'application/pdf',
            'mobi'  => 'application/x-mobipocket-ebook',
            default => 'application/octet-stream',
        };

        $headers = [
            'Content-Type'                   => $contentType,
            'Content-Disposition'            => 'inline; filename="' . basename($fullPath) . '"',
            'Access-Control-Allow-Origin'    => '*',
            'Access-Control-Allow-Methods'   => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers'   => 'Range, Content-Type, Authorization, X-Requested-With',
            'Access-Control-Expose-Headers'  => 'Content-Length, Content-Range, Accept-Ranges',
            'Accept-Ranges'                  => 'bytes',
            'Cache-Control'                  => 'private, max-age=86400, must-revalidate',
            'X-Content-Type-Options'         => 'nosniff',
        ];

        if ($request->isMethod('OPTIONS')) {
            return response('', 200, $headers);
        }

        return response()->file($fullPath, $headers);
    }

    /**
     * ফ্রি ই-বুক এক-ক্লিকে সংগ্রহ / ক্লেম হ্যান্ডলার
     */
    public function claim(Request $request, string $slug): RedirectResponse
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->guest(route('login'))
                ->with('info', 'ফ্রি বইটি আপনার লাইব্রেরিতে যুক্ত করতে অনুগ্রহ করে প্রথমে লগইন করুন।');
        }

        $ebook = $this->resolveEbook($slug);
        if (!$ebook || !$ebook->is_active) {
            abort(404, 'ই-বুক পাওয়া যায়নি।');
        }

        if (!$ebook->is_free) {
            return redirect()->route('ebook.show', $ebook->slug)
                ->with('error', 'এই বইটি পেইড সংস্করণ। সংগ্রহ করতে অনুগ্রহ করে ক্রয় সম্পন্ন করুন।');
        }

        UserEbookLibrary::updateOrCreate(
            ['user_id' => $user->id, 'ebook_id' => $ebook->id],
            ['access_type' => 'free', 'is_active' => true]
        );

        return redirect()->route('ebook.show', $ebook->slug)
            ->with('success', 'অভিনন্দন! বইটি সফলভাবে আপনার ব্যক্তিগত ই-বুক লাইব্রেরিতে যুক্ত হয়েছে এবং সম্পূর্ণ ডাউনলোড সক্রিয় করা হয়েছে।');
    }

    /**
     * সম্পূর্ণ ই-বুক ডাউনলোড হ্যান্ডলার (EPUB Only, Strict DRM Access Control)
     */
    public function download(string $slug): BinaryFileResponse|RedirectResponse
    {
        $ebook = $this->resolveEbook($slug);
        if (!$ebook || !$ebook->is_active) {
            abort(404, 'ই-বুক পাওয়া যায়নি।');
        }

        $user = auth()->user();
        if (!$user) {
            return redirect()->guest(route('login'))
                ->with('info', 'ই-বুক ডাউনলোড করতে অনুগ্রহ করে প্রথমে লগইন করুন।');
        }

        // 1. Strictly verify legitimate user access
        $hasAccess = $this->checkUserEbookAccess($user, $ebook);

        if (!$hasAccess) {
            if ($ebook->is_free) {
                return redirect()->route('ebook.show', $ebook->slug)
                    ->with('error', 'ফ্রি ই-বুকটি ডাউনলোড করতে অনুগ্রহ করে প্রথমে "বিনামূল্যে সংগ্রহ করুন (Claim)" বাটনে ক্লিক করে আপনার লাইব্রেরিতে যুক্ত করুন।');
            }
            return redirect()->route('ebook.show', $ebook->slug)
                ->with('error', 'সম্পূর্ণ ই-বুক ডাউনলোড করতে প্রথমে বইটি ক্রয় সম্পন্ন করুন।');
        }

        // 2. Format & Copyright Restriction: PDF Download is STRICTLY DISABLED
        // Only EPUB format is allowed for download
        $epubPath = $ebook->epub_file_path;
        if (!$epubPath && (strtolower((string)$ebook->file_type) === 'epub' || str_ends_with(strtolower((string)$ebook->file_path), '.epub'))) {
            $epubPath = $ebook->file_path;
        }

        if (!$epubPath) {
            return redirect()->route('ebook.show', $ebook->slug)
                ->with('error', 'কপিরাইট ও ডিজিটাল রাইটস সুরক্ষার কারণে PDF ফরম্যাট সরাসরি ডাউনলোড বন্ধ রয়েছে। আপনি অনলাইনে সুরক্ষিত রিডারে বইটি অনায়াসে পড়তে পারেন।');
        }

        // 3. Resolve physical file
        $cleanEpubPath = preg_replace('#^https?://[^/]+/storage/#', '', (string)$epubPath);
        $cleanEpubPath = preg_replace('#^/storage/#', '', $cleanEpubPath);
        $cleanEpubPath = ltrim($cleanEpubPath, '/');

        $candidates = [
            storage_path('app/public/' . $cleanEpubPath),
            storage_path('app/' . $cleanEpubPath),
            public_path('storage/' . $cleanEpubPath),
            public_path($cleanEpubPath),
            storage_path('app/secure/ebooks/' . basename($cleanEpubPath)),
            storage_path('app/public/' . ltrim($epubPath, '/')),
            storage_path('app/' . ltrim($epubPath, '/')),
            public_path(ltrim($epubPath, '/')),
        ];

        $fullPath = null;
        foreach ($candidates as $cand) {
            if (file_exists($cand) && is_file($cand)) {
                $fullPath = $cand;
                break;
            }
        }

        if (!$fullPath) {
            return redirect()->route('ebook.show', $ebook->slug)
                ->with('error', 'ডাউনলোড করার মত EPUB ফাইল সার্ভারে পাওয়া যায়নি।');
        }

        // 4. Increment download count
        try {
            $ebook->increment('download_count');
        } catch (\Throwable) {}

        $downloadFilename = ($ebook->slug ?: 'idea_ebook_' . $ebook->id) . '.epub';

        return response()->download($fullPath, $downloadFilename, [
            'Content-Type' => 'application/epub+zip',
        ]);
    }

    /**
     * পড়ার অগ্রগতি ও বুকমার্ক সংরক্ষণ (AJAX Endpoint)
     */
    public function saveProgress(Request $request, int|string $id): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'last_read_page'   => 'nullable|integer|min:1',
            'progress_percent' => 'nullable|integer|min:0|max:100',
            'cfi'              => 'nullable|string|max:500',
            'bookmark_title'   => 'nullable|string|max:255',
        ]);

        $ebook = Ebook::find($id);
        $hasAccess = $ebook ? $this->checkUserEbookAccess($user, $ebook) : false;

        $entry = UserEbookLibrary::where('user_id', $user->id)
            ->where('ebook_id', (int)$id)
            ->first();

        if (!$entry) {
            $accessType = ($ebook && $ebook->is_free) ? 'free' : ($hasAccess ? 'purchased' : 'reading');
            $entry = UserEbookLibrary::create([
                'user_id'     => $user->id,
                'ebook_id'    => (int)$id,
                'access_type' => $accessType,
                'is_active'   => $hasAccess || ($ebook && $ebook->is_free),
            ]);
        }

        $bookmarks = $entry->bookmarks_data ?? [];

        if (!empty($validated['bookmark_title']) || !empty($validated['cfi'])) {
            $newBookmark = [
                'id'         => uniqid('bm_'),
                'page'       => $validated['last_read_page'] ?? 1,
                'cfi'        => $validated['cfi'] ?? null,
                'title'      => $validated['bookmark_title'] ?? ('পৃষ্ঠা #' . ($validated['last_read_page'] ?? 1)),
                'time'       => now()->toIso8601String(),
                'created_at' => now()->format('d M, Y h:i A'),
            ];
            $bookmarks[] = $newBookmark;
        }

        $updateData = [
            'progress_percent' => $validated['progress_percent'] ?? $entry->progress_percent,
            'bookmarks_data'   => $bookmarks,
        ];

        if (!empty($validated['last_read_page'])) {
            $updateData['last_read_page'] = $validated['last_read_page'];
        }

        $entry->update($updateData);

        return response()->json([
            'success'   => true,
            'bookmarks' => $bookmarks,
            'message'   => 'অগ্রগতি ও বুকমার্ক সংরক্ষিত হয়েছে',
        ]);
    }

    /**
     * Check whether a user has legitimate access to an ebook (purchased, claimed free, or admin/author).
     */
    private function checkUserEbookAccess(?\App\Models\User $user, ?Ebook $ebook): bool
    {
        if (!$user || !$ebook) {
            return false;
        }

        // Admin, SubAdmin, or Author of this specific book
        if ($user->isAdmin() || $user->isSubAdmin() || $ebook->author_user_id === $user->id) {
            return true;
        }

        // Free E-books: Access granted if user claimed it in their library
        if ($ebook->is_free) {
            return UserEbookLibrary::where('user_id', $user->id)
                ->where('ebook_id', $ebook->id)
                ->where('is_active', true)
                ->exists();
        }

        // Paid E-books: MUST have access_type = 'purchased' and is_active = true
        $hasLibraryPurchase = UserEbookLibrary::where('user_id', $user->id)
            ->where('ebook_id', $ebook->id)
            ->where('access_type', 'purchased')
            ->where('is_active', true)
            ->exists();

        if ($hasLibraryPurchase) {
            return true;
        }

        // Also check if user has a verified paid/completed order for this ebook
        $hasPaidOrder = \App\Models\Order::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhereIn('status', ['completed', 'delivered', 'processing']);
            })
            ->where(function ($q) use ($ebook) {
                $q->where('book_id', $ebook->id)
                  ->orWhereHas('items', fn ($iq) => $iq->where('ebook_id', $ebook->id));
            })
            ->exists();

        if ($hasPaidOrder) {
            // Auto-grant access in UserEbookLibrary
            UserEbookLibrary::updateOrCreate(
                ['user_id' => $user->id, 'ebook_id' => $ebook->id],
                ['access_type' => 'purchased', 'is_active' => true]
            );
            return true;
        }

        return false;
    }

    /**
     * Resolve E-Book by slug, decoded slug, translated slug, or numeric ID
     */
    private function resolveEbook(string|int $slug): ?Ebook
    {
        $raw = trim((string) $slug);
        $decoded = urldecode($raw);
        $rawDecoded = rawurldecode($raw);
        $slugified = \Illuminate\Support\Str::slug($decoded);
        $dashedToSpace = str_replace('-', ' ', $decoded);
        $spaceToDash = str_replace(' ', '-', $decoded);

        $query = Ebook::query()->with(['author', 'publisher', 'category']);

        // 1. Check if numeric ID
        if (is_numeric($raw)) {
            $found = (clone $query)->where('id', (int)$raw)->first();
            if ($found) return $found;
        }

        // 2. Try exact match on slug candidates
        $candidates = array_unique(array_filter([
            $raw,
            $decoded,
            $rawDecoded,
            $slugified,
            $dashedToSpace,
            $spaceToDash,
        ]));

        $found = (clone $query)->whereIn('slug', $candidates)->first();
        if ($found) return $found;

        // 3. Try matching title directly
        $found = (clone $query)->where(function ($q) use ($candidates) {
            foreach ($candidates as $cand) {
                $q->orWhere('title', $cand)
                  ->orWhere('title', 'like', '%' . $cand . '%');
            }
        })->first();

        return $found;
    }
}
