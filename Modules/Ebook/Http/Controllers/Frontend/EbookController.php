<?php

declare(strict_types=1);

namespace Modules\Ebook\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
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
     * ডিজিটাল ই-বুক ক্যাটালগ ও অ্যাডভান্সড ফিল্টারিং
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
                  ->orWhere('file_path', 'LIKE', '%.epub');
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
     * ই-বুক বিস্তারিত বিবরণ
     */
    public function show(string $slug): View
    {
        $ebook = Ebook::query()
            ->with(['author', 'publisher', 'category'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$ebook && is_numeric($slug)) {
            $ebook = Ebook::query()
                ->with(['author', 'publisher', 'category'])
                ->where('id', $slug)
                ->where('is_active', true)
                ->first();
        }

        if (!$ebook) {
            abort(404, 'অনুরোধকৃত ই-বুকটি পাওয়া যায়নি।');
        }

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

        return view('ebook::frontend.show', compact('ebook', 'relatedEbooks', 'authorOtherEbooks'));
    }

    /**
     * অনলাইন আধুনিক ই-বুক রিডার (EPUB ও PDF সাপোর্টেড)
     */
    public function read(string $slug): View
    {
        $ebook = Ebook::query()
            ->with(['author', 'publisher', 'category'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$ebook && is_numeric($slug)) {
            $ebook = Ebook::query()
                ->with(['author', 'publisher', 'category'])
                ->where('id', $slug)
                ->where('is_active', true)
                ->first();
        }

        if (!$ebook) {
            abort(404, 'ই-বুক পাওয়া যায়নি');
        }

        // Increment read count silently
        try {
            $ebook->increment('read_count');
        } catch (\Throwable) {}

        // Determine file URL and reader type
        $epubUrl = $ebook->epub_url;
        $pdfUrl = $ebook->file_url;
        $sampleUrl = $ebook->sample_url;

        // If no file uploaded, provide fallback preview
        $readerType = 'none';
        $fileUrl = null;

        if ($epubUrl) {
            $readerType = 'epub';
            $fileUrl = $epubUrl;
        } elseif ($pdfUrl && (str_ends_with(strtolower($pdfUrl), '.pdf') || $ebook->file_type === 'pdf')) {
            $readerType = 'pdf';
            $fileUrl = $pdfUrl;
        } elseif ($sampleUrl) {
            $readerType = str_ends_with(strtolower($sampleUrl), '.epub') ? 'epub' : 'pdf';
            $fileUrl = $sampleUrl;
        } elseif ($pdfUrl) {
            $readerType = 'pdf';
            $fileUrl = $pdfUrl;
        }

        return view('ebook::frontend.read', compact('ebook', 'readerType', 'fileUrl', 'epubUrl', 'pdfUrl', 'sampleUrl'));
    }

    /**
     * ফ্রি / স্যাম্পল ডাউনলোড হ্যান্ডলার
     */
    public function download(string $slug)
    {
        $ebook = Ebook::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $path = $ebook->file_path ?: $ebook->epub_file_path ?: $ebook->sample_file_path;

        if (!$path) {
            return back()->with('error', 'ডাউনলোড করার মত ফাইল সংযুক্ত নেই।');
        }

        try {
            $ebook->increment('download_count');
        } catch (\Throwable) {}

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path, $ebook->slug . '.' . pathinfo($path, PATHINFO_EXTENSION));
        }

        if (file_exists(public_path($path))) {
            return response()->download(public_path($path));
        }

        return redirect($ebook->file_url);
    }
}
