<?php

declare(strict_types=1);

namespace Modules\Book\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Book\Models\Book;
use Modules\Book\Models\Category;
use Modules\Author\Models\Author;
use Modules\Publisher\Models\Publisher;

class BookController extends Controller
{
    /**
     * আইডিয়া প্রকাশন অ্যাডভান্সড ফিল্টারিং ও সার্চ ক্যাটালগ
     */
    public function index(Request $request): View
    {
        $canUseBooks = false;

        try {
            $canUseBooks = DB::getSchemaBuilder()->hasTable('books') && DB::getSchemaBuilder()->hasTable('categories');
        } catch (\Throwable) {
            $canUseBooks = false;
        }

        $books = collect();
        $categories = collect();
        $recentlySold = collect();
        $bestSellerEbooks = collect();
        $flashSales = collect();
        $recentlyViewedBooks = collect();
        $categoryBooks = [];
        $sidebarAuthors = collect();
        $sidebarPublishers = collect();
        $topSeller = null;
        $isSearchMode = $request->anyFilled(['search', 'category', 'author', 'publisher', 'in_stock', 'min_price', 'max_price', 'rating', 'format', 'discount_min', 'sort']);

        if ($canUseBooks) {
            $categories = Category::query()
                ->where('is_active', true)
                ->withCount('books')
                ->get(['id', 'name', 'slug']);

            $sidebarAuthors = Author::query()->where('is_active', true)->withCount('books')->orderByDesc('books_count')->take(15)->get(['id', 'name', 'slug']);
            $sidebarPublishers = Publisher::query()->where('is_active', true)->withCount('books')->orderByDesc('books_count')->take(15)->get(['id', 'name', 'slug']);
            $topSeller = Book::query()->with('authors')->where('is_active', true)->orderByDesc('sales_count')->first();

            if ($isSearchMode) {
                // --- Search / Filter Mode ---
                $books = Book::query()
                    ->with(['category', 'authors', 'publisher'])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->where('is_active', true)
                    ->when($request->filled('category'), fn ($q) =>
                        $q->whereHas('category', fn ($cat) => $cat->where('slug', $request->string('category')))
                    )
                    ->when($request->filled('author'), fn ($q) =>
                        $q->whereHas('authors', fn ($auth) => $auth->where('slug', $request->string('author')))
                    )
                    ->when($request->filled('min_price'), fn ($q) =>
                        $q->where('price', '>=', $request->float('min_price'))
                    )
                    ->when($request->filled('max_price'), fn ($q) =>
                        $q->where('price', '<=', $request->float('max_price'))
                    )
                    ->when($request->filled('rating'), function ($q) use ($request) {
                        $minRating = $request->float('rating');
                        $q->whereHas('reviews', function ($rq) use ($minRating) {
                            $rq->groupBy('book_id')->havingRaw('AVG(rating) >= ?', [$minRating]);
                        });
                    })
                    ->when($request->filled('format'), fn ($q) =>
                        $q->where('format', $request->string('format'))
                    )
                    ->when($request->filled('discount_min'), function ($q) use ($request) {
                        $minPercent = $request->integer('discount_min');
                        if ($minPercent > 0) {
                            $q->whereNotNull('discount_price')
                              ->whereRaw('((price - discount_price) * 100 / price) >= ?', [$minPercent]);
                        }
                    })
                    ->when($request->boolean('in_stock'), fn ($q) =>
                        $q->where('stock_quantity', '>', 0)
                    )
                    ->when($request->filled('publisher'), fn ($q) =>
                        $q->whereHas('publisher', fn ($pub) => $pub->where('slug', $request->string('publisher')))
                    )
                    ->when($request->filled('search'), function ($q) use ($request) {
                        $search = $request->string('search')->trim()->value();
                        $q->where(fn ($sub) =>
                            $sub->where('title', 'LIKE', "%{$search}%")
                                ->orWhere('isbn', 'LIKE', "%{$search}%")
                                ->orWhereHas('authors', fn ($a) => $a->where('name', 'LIKE', "%{$search}%"))
                                ->orWhereHas('vendor', fn ($v) => $v->where('shop_name', 'LIKE', "%{$search}%"))
                        );
                    })
                    ->when($request->filled('sort'), function ($q) use ($request) {
                        match ($request->string('sort')->value()) {
                            'price_low'   => $q->orderBy('price', 'asc'),
                            'price_high'  => $q->orderBy('price', 'desc'),
                            'discount_low' => $q->orderByRaw('(price - COALESCE(discount_price, price)) asc'),
                            'discount_high' => $q->orderByRaw('(price - COALESCE(discount_price, price)) desc'),
                            'avg_rating'  => $q->orderByDesc('reviews_avg_rating'),
                            'bestselling' => $q->orderByDesc('sales_count'),
                            'oldest'      => $q->oldest(),
                            default       => $q->latest(),
                        };
                    }, fn ($q) => $q->latest())
                    ->paginate(20)
                    ->withQueryString();
            } else {
                // --- Home Page Section Mode ---
                $recentlySold = Book::query()
                    ->with(['authors'])
                    ->where('is_active', true)
                    ->orderByDesc('sales_count')
                    ->take(10)
                    ->get();

                $bestSellerEbooks = Book::query()
                    ->with(['authors'])
                    ->where('is_active', true)
                    ->where('format', 'ebook')
                    ->orderByDesc('sales_count')
                    ->take(10)
                    ->get();

                $flashSales = Book::query()
                    ->with(['authors'])
                    ->where('is_active', true)
                    ->whereNotNull('discount_price')
                    ->whereColumn('discount_price', '<', 'price')
                    ->inRandomOrder()
                    ->take(10)
                    ->get();

                $recentlyViewedIds = session()->get('recently_viewed_books', []);
                if (!empty($recentlyViewedIds)) {
                    $recentlyViewedBooks = Book::query()
                        ->with(['authors'])
                        ->whereIn('id', $recentlyViewedIds)
                        ->where('is_active', true)
                        ->get()
                        ->sortBy(function($b) use ($recentlyViewedIds) {
                            return array_search($b->id, $recentlyViewedIds);
                        });
                }

                $dynamicCategories = Category::query()
                    ->where('is_active', true)
                    ->withCount('books')
                    ->orderByDesc('books_count')
                    ->take(15)
                    ->get(['id', 'name']);

                foreach ($dynamicCategories as $category) {
                    $booksForSection = Book::query()
                        ->with(['authors'])
                        ->where('is_active', true)
                        ->where('category_id', $category->id)
                        ->latest()
                        ->take(10)
                        ->get();
                    
                    if ($booksForSection->isNotEmpty()) {
                        $categoryBooks[$category->name] = $booksForSection;
                    }
                }
            }
        }

        return view('book::frontend.index', compact(
            'books', 'categories', 'isSearchMode', 'recentlySold', 'bestSellerEbooks', 'categoryBooks', 'sidebarAuthors', 'sidebarPublishers', 'flashSales', 'recentlyViewedBooks', 'topSeller'
        ));
    }

    /**
     * একক বইয়ের ডিটেইলস ও ক্রস-সেলিং (Frequently Bought Together)
     */
    public function show(string $slug): View
    {
        $decoded = urldecode($slug);
        $book = Book::query()
            ->with(['category', 'authors', 'publisher', 'reviews.user'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->where(function ($q) use ($slug, $decoded) {
                $q->where('slug', $slug)
                  ->orWhere('slug', $decoded)
                  ->orWhere('title', $decoded);
                if (is_numeric($slug)) {
                    $q->orWhere('id', (int) $slug);
                }
            })
            ->first();

        if (!$book) {
            $book = Book::query()
                ->with(['category', 'authors', 'publisher', 'reviews.user'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->firstOrFail();
        }

        // ১. একসাথে কেনা উপযোগী বই (Frequently Bought Together)
        $frequentlyBoughtTogether = Book::query()
            ->where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->take(2)
            ->get();

        $relatedBooks = Book::query()
            ->with(['authors'])
            ->where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->where('is_active', true)
            ->take(6)
            ->get();

        // ৩. Recently Viewed এ যোগ করা
        $recentlyViewed = session()->get('recently_viewed_books', []);
        if (!in_array($book->id, $recentlyViewed)) {
            array_unshift($recentlyViewed, $book->id);
            $recentlyViewed = array_slice($recentlyViewed, 0, 10);
            session()->put('recently_viewed_books', $recentlyViewed);
        }

        return view('book::frontend.show', compact('book', 'frequentlyBoughtTogether', 'relatedBooks'));
    }

    /**
     * একঝলক / বইয়ের অংশবিশেষ (স্যাম্পল চ্যাপ্টার / পৃষ্ঠা দেখার সুবিধা)
     */
    public function preview(string $slug): JsonResponse
    {
        $book = Book::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail(['id', 'title', 'sample_pdf_path', 'preview_pages']);

        return response()->json([
            'success' => true,
            'title'   => $book->title,
            'preview_url' => $book->sample_pdf_path ? asset('storage/' . $book->sample_pdf_path) : null,
            'pages'   => $book->preview_pages ?? 10,
        ]);
    }

    /**
     * Quick View Modal API (দ্রুত দেখার পপ-আপ)
     */
    public function quickView(int $id): JsonResponse
    {
        $book = Book::query()
            ->with(['category', 'authors', 'publisher'])
            ->withAvg('reviews', 'rating')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $book
        ]);
    }
}