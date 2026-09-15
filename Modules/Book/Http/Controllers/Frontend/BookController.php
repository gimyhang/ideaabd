<?php

declare(strict_types=1);

namespace Modules\Book\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\Book\Models\Book;
use Modules\Book\Models\Category;
use Modules\Author\Models\Author;
use Modules\Publisher\Models\Publisher;

class BookController extends Controller
{
    /**
     * আইডিয়া প্রকাশন অ্যাডভান্সড ফিল্টারিং ও সার্চ ক্যাটালগ
     */
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        if ($request->has('suggest') || $request->wantsJson() || ($request->ajax() && !$request->has('page'))) {
            return $this->suggest($request);
        }

        $canUseBooks = false;

        try {
            $canUseBooks = DB::getSchemaBuilder()->hasTable('books') && DB::getSchemaBuilder()->hasTable('categories');
        } catch (\Throwable) {
            $canUseBooks = false;
        }

        $books = collect();
        $categories = collect();
        $recentlySold = collect();
        $newArrivals = collect();
        $bestSellerEbooks = collect();
        $flashSales = collect();
        $recentlyViewedBooks = collect();
        $categoryBooks = [];
        $sidebarAuthors = collect();
        $sidebarPublishers = collect();
        $topSeller = null;
        $rawSearch = trim((string)($request->input('search') ?: $request->input('q') ?: ''));
        $isSearchMode = $request->anyFilled(['search', 'q', 'category', 'author', 'publisher', 'in_stock', 'min_price', 'max_price', 'rating', 'format', 'discount_min', 'sort']) || ($request->has('page') && (int)$request->get('page') > 1);

        $activeFilterTitle = null;

        if ($canUseBooks) {
            $categories = Category::query()
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->with(['children' => fn($q) => $q->where('is_active', true)->withCount(['books' => fn($bq) => $bq->where('is_active', true)])])
                ->withCount(['books' => fn($q) => $q->where('is_active', true)])
                ->orderBy('sort_order')
                ->orderByDesc('books_count')
                ->get();

            // If no parent categories found, fallback to flat active categories list
            if ($categories->isEmpty()) {
                $categories = Category::query()
                    ->where('is_active', true)
                    ->withCount(['books' => fn($q) => $q->where('is_active', true)])
                    ->orderByDesc('books_count')
                    ->orderBy('name')
                    ->get();
            }

            $sidebarAuthors = Author::query()
                ->where('is_active', true)
                ->withCount(['books' => fn($q) => $q->where('is_active', true)])
                ->orderByDesc('books_count')
                ->orderBy('name')
                ->take(50)
                ->get(['id', 'name', 'slug']);

            $sidebarPublishers = Publisher::query()
                ->where('is_active', true)
                ->withCount(['books' => fn($q) => $q->where('is_active', true)])
                ->orderByDesc('books_count')
                ->orderBy('name')
                ->take(50)
                ->get(['id', 'name', 'slug']);

            $topSeller = Book::query()->with('authors')->where('is_active', true)->orderByDesc('sales_count')->first();

            // Dynamic Categories with active books
            $dynamicCategories = Category::query()
                ->where('is_active', true)
                ->whereHas('books', fn($q) => $q->where('is_active', true))
                ->withCount(['books' => fn($q) => $q->where('is_active', true)])
                ->orderByDesc('books_count')
                ->take(16)
                ->get(['id', 'name', 'slug']);

            // Resolve human-readable active filter title
            if ($request->filled('category')) {
                $catVal = $request->string('category')->trim()->value();
                $matchedCat = Category::where('slug', $catVal)
                    ->orWhere('id', is_numeric($catVal) ? (int)$catVal : 0)
                    ->orWhere('name', $catVal)
                    ->first();
                $activeFilterTitle = $matchedCat ? $matchedCat->name : $catVal;
            } elseif ($request->filled('author')) {
                $authVal = $request->string('author')->trim()->value();
                $matchedAuth = $sidebarAuthors->first(fn($a) => $a->slug === $authVal || (string)$a->id === $authVal || $a->name === $authVal);
                $activeFilterTitle = $matchedAuth ? $matchedAuth->name : $authVal;
            } elseif ($request->filled('publisher')) {
                $pubVal = $request->string('publisher')->trim()->value();
                $matchedPub = $sidebarPublishers->first(fn($p) => $p->slug === $pubVal || (string)$p->id === $pubVal || $p->name === $pubVal);
                $activeFilterTitle = $matchedPub ? $matchedPub->name : $pubVal;
            } elseif (!empty($rawSearch)) {
                $activeFilterTitle = 'অনুসন্ধান: "' . $rawSearch . '"';
            } elseif ($request->has('page') && (int)$request->get('page') > 1) {
                $activeFilterTitle = 'সকল বই (পৃষ্ঠা ' . $request->get('page') . ')';
            }

            // Base books query with robust filtering
            $booksQuery = Book::query()
                ->with(['category', 'authors', 'publisher'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->when($request->filled('category'), function ($q) use ($request) {
                    $catVal = $request->string('category')->trim()->value();
                    $matchedCat = \Modules\Book\Models\Category::where('slug', $catVal)
                        ->orWhere('id', is_numeric($catVal) ? (int)$catVal : 0)
                        ->orWhere('name', $catVal)
                        ->first();
                    $catIds = [];
                    if ($matchedCat) {
                        $catIds = array_merge([$matchedCat->id], $matchedCat->children()->pluck('id')->all());
                    }

                    $q->where(function ($sub) use ($catVal, $catIds) {
                        if (!empty($catIds)) {
                            $sub->whereIn('category_id', $catIds);
                        } else {
                            $sub->where('category_id', $catVal);
                        }
                        $sub->orWhere('sub_category_name', 'LIKE', "%{$catVal}%")
                            ->orWhere('genre_category', 'LIKE', "%{$catVal}%")
                            ->orWhere('ekushey_category', 'LIKE', "%{$catVal}%")
                            ->orWhere('audience_category', 'LIKE', "%{$catVal}%")
                            ->orWhereHas('category', function ($cat) use ($catVal) {
                                $cat->where('slug', $catVal)
                                    ->orWhere('name', 'LIKE', "%{$catVal}%");
                            });
                    });
                })
                ->when($request->filled('author'), function ($q) use ($request) {
                    $authorVal = $request->string('author')->trim()->value();
                    $q->where(function ($sub) use ($authorVal) {
                        $sub->where('author_link_id', $authorVal)
                            ->orWhere('author_name', 'LIKE', "%{$authorVal}%")
                            ->orWhereHas('authors', function ($auth) use ($authorVal) {
                                $auth->where('slug', $authorVal)
                                    ->orWhere('name', $authorVal)
                                    ->orWhere('id', $authorVal);
                            });
                    });
                })
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
                ->when($request->filled('publisher'), function ($q) use ($request) {
                    $pubVal = $request->string('publisher')->trim()->value();
                    $q->where(function ($sub) use ($pubVal) {
                        $sub->where('publisher_id', $pubVal)
                            ->orWhereHas('publisher', function ($pub) use ($pubVal) {
                                $pub->where('slug', $pubVal)
                                    ->orWhere('name', $pubVal)
                                    ->orWhere('id', $pubVal);
                            });
                    });
                })
                ->when(!empty($rawSearch), function ($q) use ($rawSearch) {
                    $tokens = array_filter(preg_split('/\s+/', $rawSearch));
                    $q->where(function ($master) use ($rawSearch, $tokens) {
                        $master->where('title', 'LIKE', "%{$rawSearch}%")
                            ->orWhere('title_en', 'LIKE', "%{$rawSearch}%")
                            ->orWhere('sku', 'LIKE', "%{$rawSearch}%")
                            ->orWhere('idea_serial_no', 'LIKE', "%{$rawSearch}%")
                            ->orWhere('isbn', 'LIKE', "%{$rawSearch}%")
                            ->orWhere('author_name', 'LIKE', "%{$rawSearch}%")
                            ->orWhereHas('authors', fn ($a) => $a->where('name', 'LIKE', "%{$rawSearch}%"))
                            ->orWhereHas('category', fn ($c) => $c->where('name', 'LIKE', "%{$rawSearch}%"))
                            ->orWhereHas('publisher', fn ($p) => $p->where('name', 'LIKE', "%{$rawSearch}%"));

                        foreach ($tokens as $token) {
                            $like = "%{$token}%";
                            $master->orWhere(function ($sub) use ($like) {
                                $sub->where('title', 'LIKE', $like)
                                    ->orWhere('title_en', 'LIKE', $like)
                                    ->orWhere('author_name', 'LIKE', $like)
                                    ->orWhereHas('authors', fn($a) => $a->where('name', 'LIKE', $like))
                                    ->orWhereHas('category', fn($c) => $c->where('name', 'LIKE', $like));
                            });
                        }
                    });
                });

            // Sorting logic
            match ($request->string('sort')->value()) {
                'price_low'     => $booksQuery->orderBy('price', 'asc'),
                'price_high'    => $booksQuery->orderBy('price', 'desc'),
                'discount_low'  => $booksQuery->orderByRaw('(price - COALESCE(discount_price, price)) asc'),
                'discount_high' => $booksQuery->orderByRaw('(price - COALESCE(discount_price, price)) desc'),
                'avg_rating'    => $booksQuery->orderByDesc('reviews_avg_rating'),
                'bestselling'   => $booksQuery->orderByDesc('sales_count'),
                'oldest'        => $booksQuery->oldest('id'),
                default         => $booksQuery->latest('id'),
            };

            $books = $booksQuery->paginate(20)->withQueryString();

            if (!$isSearchMode) {
                // Curated Highlights for Catalog Mode
                $recentlySold = Book::query()
                    ->with(['authors', 'category'])
                    ->where('is_active', true)
                    ->orderByDesc('sales_count')
                    ->latest('id')
                    ->take(15)
                    ->get();

                $bestSellerEbooks = Book::query()
                    ->with(['authors', 'category'])
                    ->where('is_active', true)
                    ->where('format', 'ebook')
                    ->orderByDesc('sales_count')
                    ->latest('id')
                    ->take(15)
                    ->get();

                $flashSales = Book::query()
                    ->with(['authors', 'category'])
                    ->where('is_active', true)
                    ->whereNotNull('discount_price')
                    ->where('discount_price', '>', 0)
                    ->whereColumn('discount_price', '<', 'price')
                    ->latest('id')
                    ->take(15)
                    ->get();

                $newArrivals = Book::query()
                    ->with(['authors', 'category'])
                    ->where('is_active', true)
                    ->latest('id')
                    ->take(15)
                    ->get();

                $recentlyViewedIds = session()->get('recently_viewed_books', []);
                if (!empty($recentlyViewedIds)) {
                    $recentlyViewedBooks = Book::query()
                        ->with(['authors', 'category'])
                        ->whereIn('id', $recentlyViewedIds)
                        ->where('is_active', true)
                        ->get()
                        ->sortBy(function($b) use ($recentlyViewedIds) {
                            return array_search($b->id, $recentlyViewedIds);
                        });
                }
            } else {
                // When in Search Mode, also fetch cross-entity matches (Writings/Blog Posts/Ideapatra, Webzine Articles, Research Papers, Authors, Categories, Site Pages)
                $matchedBlogPosts = collect();
                $matchedWebzineArticles = collect();
                $matchedResearchPapers = collect();
                $matchedAuthors = collect();
                $matchedCategories = collect();
                $matchedPages = [];

                if (!empty($rawSearch)) {
                    $matchedPages = $this->matchSitePages($rawSearch);
                    $tokens = array_filter(preg_split('/\s+/', $rawSearch));

                    try {
                        if (class_exists(\Modules\Blog\Models\BlogPost::class)) {
                            $matchedBlogPosts = \Modules\Blog\Models\BlogPost::query()
                                ->where(function ($sub) {
                                    $sub->where('status', 'published')
                                        ->orWhere('status', 'approved')
                                        ->orWhere('mod_status', 'approved')
                                        ->orWhereNull('status');
                                })
                                ->where(function ($b) use ($rawSearch, $tokens) {
                                    $b->where('title', 'LIKE', "%{$rawSearch}%")
                                      ->orWhere('subtitle', 'LIKE', "%{$rawSearch}%")
                                      ->orWhere('excerpt', 'LIKE', "%{$rawSearch}%")
                                      ->orWhere('slug', 'LIKE', "%{$rawSearch}%");
                                    foreach ($tokens as $tok) {
                                        $b->orWhere('title', 'LIKE', "%{$tok}%");
                                    }
                                })
                                ->with(['category', 'author'])
                                ->latest('id')
                                ->take(6)
                                ->get();
                        }
                    } catch (\Throwable) {}

                    try {
                        if (class_exists(\Modules\Webzine\Models\WebzineArticle::class)) {
                            $matchedWebzineArticles = \Modules\Webzine\Models\WebzineArticle::query()
                                ->where(function ($w) use ($rawSearch, $tokens) {
                                    $w->where('title', 'LIKE', "%{$rawSearch}%");
                                    foreach ($tokens as $tok) {
                                        $w->orWhere('title', 'LIKE', "%{$tok}%");
                                    }
                                })
                                ->with(['webzine', 'author'])
                                ->take(4)
                                ->get();
                        }
                    } catch (\Throwable) {}

                    try {
                        if (class_exists(\Modules\Research\Models\ResearchPaper::class)) {
                            $matchedResearchPapers = \Modules\Research\Models\ResearchPaper::query()
                                ->where(function ($r) use ($rawSearch, $tokens) {
                                    $r->where('title', 'LIKE', "%{$rawSearch}%")
                                      ->orWhere('abstract', 'LIKE', "%{$rawSearch}%");
                                    foreach ($tokens as $tok) {
                                        $r->orWhere('title', 'LIKE', "%{$tok}%");
                                    }
                                })
                                ->with('author')
                                ->take(4)
                                ->get();
                        }
                    } catch (\Throwable) {}

                    try {
                        if (class_exists(\Modules\Author\Models\Author::class)) {
                            $matchedAuthors = \Modules\Author\Models\Author::query()
                                ->where('is_active', true)
                                ->where(function ($q) use ($rawSearch) {
                                    $q->where('name', 'LIKE', "%{$rawSearch}%")
                                      ->orWhere('name_bn', 'LIKE', "%{$rawSearch}%")
                                      ->orWhere('name_en', 'LIKE', "%{$rawSearch}%")
                                      ->orWhere('slug', 'LIKE', "%{$rawSearch}%");
                                })
                                ->withCount(['books' => fn($q) => $q->where('is_active', true)])
                                ->take(6)
                                ->get();
                        }
                    } catch (\Throwable) {}

                    try {
                        if (class_exists(\Modules\Book\Models\Category::class)) {
                            $matchedCategories = \Modules\Book\Models\Category::query()
                                ->where('is_active', true)
                                ->where(function ($q) use ($rawSearch) {
                                    $q->where('name', 'LIKE', "%{$rawSearch}%")
                                      ->orWhere('slug', 'LIKE', "%{$rawSearch}%");
                                })
                                ->withCount(['books' => fn($q) => $q->where('is_active', true)])
                                ->take(6)
                                ->get();
                        }
                    } catch (\Throwable) {}
                }
            }
        }

        return view('book::frontend.index', compact(
            'books', 'categories', 'isSearchMode', 'recentlySold', 'newArrivals', 'bestSellerEbooks', 'categoryBooks', 'sidebarAuthors', 'sidebarPublishers', 'flashSales', 'recentlyViewedBooks', 'topSeller', 'dynamicCategories', 'activeFilterTitle', 'matchedBlogPosts', 'matchedWebzineArticles', 'matchedResearchPapers', 'matchedAuthors', 'matchedCategories', 'matchedPages'
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

        // If not found active, check if user is admin (to preview drafts/pending books)
        if (!$book && auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSubAdmin())) {
            $book = Book::query()
                ->with(['category', 'authors', 'publisher', 'reviews.user'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where(function ($q) use ($slug, $decoded) {
                    $q->where('slug', $slug)
                      ->orWhere('slug', $decoded)
                      ->orWhere('title', $decoded);
                    if (is_numeric($slug)) {
                        $q->orWhere('id', (int) $slug);
                    }
                })
                ->first();
        }

        // Fuzzy fallback for translated/hyphenated slug
        if (!$book) {
            $cleanSlug = str_replace('-', ' ', $decoded);
            $book = Book::query()
                ->with(['category', 'authors', 'publisher', 'reviews.user'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->where(function ($q) use ($cleanSlug, $decoded) {
                    $q->where('title', 'LIKE', "%{$cleanSlug}%")
                      ->orWhere('title', 'LIKE', "%{$decoded}%")
                      ->orWhere('slug', 'LIKE', "%{$decoded}%");
                })
                ->first();
        }

        if (!$book) {
            abort(404, 'অনুরোধকৃত বইটি পাওয়া যায়নি।');
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

    /**
     * World-Class Real-Time Smart Live Search & Spotlight Suggestions
     */
    /**
     * World-Class Real-Time Smart Live Search & Spotlight Suggestions (1-Letter Universal Search)
     */
    public function suggest(Request $request): JsonResponse
    {
        $query = trim((string)($request->input('q') ?: $request->input('search') ?: ''));
        $type = (string)$request->input('type', 'all');

        if (mb_strlen($query) < 1) {
            return response()->json([
                'success'             => true,
                'query'               => $query,
                'keyword_suggestions' => [],
                'scoped_suggestions'  => [],
                'quick_links'         => [],
                'books'               => [],
                'authors'             => [],
                'categories'          => [],
                'posts'               => [],
                'publishers'          => [],
                'webzines'            => [],
                'total'               => 0,
            ]);
        }

        // Tokenize query words
        $tokens = array_filter(preg_split('/\s+/', $query));

        // 1. Books Query (1-letter prefix & contains match)
        $booksQuery = Book::query()
            ->with(['authors:id,name,slug', 'category:id,name,slug', 'publisher:id,name,slug'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true);

        if ($type === 'ebooks') {
            $booksQuery->where('format', 'ebook');
        }

        if ($type === 'books' || $type === 'all' || $type === 'ebooks') {
            $booksQuery->where(function ($master) use ($tokens, $query) {
                $master->where('title', 'LIKE', "%{$query}%")
                       ->orWhere('title_en', 'LIKE', "%{$query}%")
                       ->orWhere('sku', 'LIKE', "%{$query}%")
                       ->orWhere('idea_serial_no', 'LIKE', "%{$query}%")
                       ->orWhere('isbn', 'LIKE', "%{$query}%")
                       ->orWhere('author_name', 'LIKE', "%{$query}%");

                foreach ($tokens as $token) {
                    $like = "%{$token}%";
                    $master->orWhere(function ($sub) use ($like) {
                        $sub->where('title', 'LIKE', $like)
                            ->orWhere('title_en', 'LIKE', $like)
                            ->orWhere('author_name', 'LIKE', $like)
                            ->orWhereHas('authors', fn($a) => $a->where('name', 'LIKE', $like))
                            ->orWhereHas('category', fn($c) => $c->where('name', 'LIKE', $like))
                            ->orWhereHas('publisher', fn($p) => $p->where('name', 'LIKE', $like));
                    });
                }
            });
        } else {
            $booksQuery->whereRaw('1 = 0');
        }

        $totalBooksCount = $booksQuery->count();
        $books = $booksQuery->orderByDesc('sales_count')->latest('id')->take(6)->get()->map(function ($book) {
            $authorName = $book->author_name ?: ($book->authors->first()?->name ?? 'আইডিয়া লেখক');
            $categoryName = $book->category?->name ?? 'সাধারণ';
            $cover = $book->cover_image ? (str_starts_with($book->cover_image, 'http') ? $book->cover_image : asset('storage/' . ltrim($book->cover_image, '/'))) : asset('assets/images/book-placeholder.png');
            $price = (float)$book->price;
            $discountPrice = (float)$book->discount_price;
            $hasDiscount = $discountPrice > 0 && $discountPrice < $price;
            $discountPercent = $hasDiscount && $price > 0 ? (int)round((($price - $discountPrice) / $price) * 100) : 0;

            return [
                'id'               => $book->id,
                'title'            => $book->title,
                'slug'             => $book->slug ?: (string)$book->id,
                'url'              => route('book.show', $book->slug ?: $book->id),
                'author'           => $authorName,
                'category'         => $categoryName,
                'format'           => $book->format ?? 'paperback',
                'format_label'     => $book->format === 'hardcover' ? 'হার্ডকভার' : ($book->format === 'ebook' ? 'ই-বুক' : 'কাগজের বই'),
                'cover'            => $cover,
                'price'            => $price,
                'discount_price'   => $discountPrice,
                'has_discount'     => $hasDiscount,
                'discount_percent' => $discountPercent,
                'price_formatted'  => '৳' . number_format($hasDiscount ? $discountPrice : $price, 0),
                'mrp_formatted'    => $hasDiscount ? '৳' . number_format($price, 0) : null,
                'in_stock'         => (int)$book->stock_quantity > 0,
                'rating_avg'       => $book->reviews_avg_rating ? round((float)$book->reviews_avg_rating, 1) : 0,
                'reviews_count'    => (int)$book->reviews_count,
                'idea_serial_no'   => $book->idea_serial_no,
            ];
        });

        // 2. Authors Query
        $authors = collect();
        if ($type === 'authors' || $type === 'all') {
            $authors = Author::query()
                ->where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('name_bn', 'LIKE', "%{$query}%")
                      ->orWhere('name_en', 'LIKE', "%{$query}%")
                      ->orWhere('slug', 'LIKE', "%{$query}%");
                })
                ->withCount(['books' => fn($bq) => $bq->where('is_active', true)])
                ->orderByDesc('books_count')
                ->take(4)
                ->get()
                ->map(function ($author) {
                    $avatar = $author->avatar ? (str_starts_with($author->avatar, 'http') ? $author->avatar : asset('storage/' . ltrim($author->avatar, '/'))) : null;
                    return [
                        'id'          => $author->id,
                        'name'        => $author->name,
                        'slug'        => $author->slug ?: (string)$author->id,
                        'url'         => route('authors.show', $author->slug ?: $author->id),
                        'avatar'      => $avatar,
                        'books_count' => $author->books_count,
                        'is_verified' => (bool)$author->is_verified,
                    ];
                });
        }

        // 3. Categories Query
        $categories = collect();
        if ($type === 'categories' || $type === 'all') {
            $categories = Category::query()
                ->where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('slug', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->withCount(['books' => fn($bq) => $bq->where('is_active', true)])
                ->orderByDesc('books_count')
                ->take(4)
                ->get()
                ->map(function ($cat) {
                    return [
                        'id'          => $cat->id,
                        'name'        => $cat->name,
                        'slug'        => $cat->slug ?: (string)$cat->id,
                        'url'         => route('book.index', ['category' => $cat->slug ?: $cat->id]),
                        'books_count' => $cat->books_count,
                    ];
                });
        }

        // 4. Ideapatra / Blog Articles Query
        $posts = collect();
        if ($type === 'blog' || $type === 'all') {
            try {
                if (class_exists(\Modules\Blog\Models\BlogPost::class)) {
                    $posts = \Modules\Blog\Models\BlogPost::query()
                        ->with(['category:id,name,slug', 'author:id,name'])
                        ->where(function ($sub) {
                            $sub->where('status', 'published')
                                ->orWhere('status', 'approved')
                                ->orWhere('mod_status', 'approved')
                                ->orWhereNull('status');
                        })
                        ->where(function ($q) use ($query, $tokens) {
                            $q->where('title', 'LIKE', "%{$query}%")
                              ->orWhere('subtitle', 'LIKE', "%{$query}%")
                              ->orWhere('excerpt', 'LIKE', "%{$query}%")
                              ->orWhere('slug', 'LIKE', "%{$query}%")
                              ->orWhere('owner_name', 'LIKE', "%{$query}%");
                            foreach ($tokens as $tok) {
                                $q->orWhere('title', 'LIKE', "%{$tok}%");
                            }
                        })
                        ->latest('published_at')
                        ->latest('id')
                        ->take(5)
                        ->get()
                        ->map(function ($post) {
                            $img = $post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . ltrim($post->featured_image, '/'))) : null;
                            return [
                                'id'           => $post->id,
                                'title'        => $post->title,
                                'slug'         => $post->slug,
                                'url'          => route('blog.show', $post->slug ?: $post->id),
                                'author'       => $post->author?->name ?: ($post->owner_name ?: 'আইডিয়াপত্র লেখক'),
                                'category'     => $post->category?->name ?: 'আইডিয়াপত্র',
                                'source'       => 'আইডিয়াপত্র',
                                'image'        => $img,
                                'published_at' => $post->published_at ? $post->published_at->format('d M Y') : null,
                            ];
                        });
                }
            } catch (\Throwable) {
                $posts = collect();
            }
        }

        // 5. Webzine Articles Query
        $webzineArticles = collect();
        if ($type === 'all' || $type === 'webzines') {
            try {
                if (class_exists(\Modules\Webzine\Models\WebzineArticle::class)) {
                    $webzineArticles = \Modules\Webzine\Models\WebzineArticle::query()
                        ->with(['webzine', 'author'])
                        ->where(function ($q) use ($query, $tokens) {
                            $q->where('title', 'LIKE', "%{$query}%");
                            foreach ($tokens as $tok) {
                                $q->orWhere('title', 'LIKE', "%{$tok}%");
                            }
                        })
                        ->take(4)
                        ->get()
                        ->map(function ($art) {
                            $wSlug = $art->webzine?->slug ?: $art->webzine_id;
                            return [
                                'id'           => $art->id,
                                'title'        => $art->title,
                                'author'       => $art->author_name ?: 'ওয়েবজিন লেখক',
                                'category'     => 'ওয়েবজিন সাহিত্য',
                                'source'       => 'ওয়েবজিন',
                                'url'          => Route::has('webzine.read') ? route('webzine.read', $wSlug) . '#page-' . ($art->page_number ?: 1) : url('/webzines/' . $wSlug),
                                'image'        => $art->featured_image ? (str_starts_with($art->featured_image, 'http') ? $art->featured_image : asset('storage/' . ltrim($art->featured_image, '/'))) : null,
                                'published_at' => null,
                            ];
                        });
                }
            } catch (\Throwable) {
                $webzineArticles = collect();
            }
        }

        // 6. Research Papers Query
        $researchPapers = collect();
        if ($type === 'all' || $type === 'research') {
            try {
                if (class_exists(\Modules\Research\Models\ResearchPaper::class)) {
                    $researchPapers = \Modules\Research\Models\ResearchPaper::query()
                        ->with('author')
                        ->where(function ($q) use ($query, $tokens) {
                            $q->where('title', 'LIKE', "%{$query}%")
                              ->orWhere('abstract', 'LIKE', "%{$query}%");
                            foreach ($tokens as $tok) {
                                $q->orWhere('title', 'LIKE', "%{$tok}%");
                            }
                        })
                        ->take(4)
                        ->get()
                        ->map(function ($paper) {
                            return [
                                'id'           => $paper->id,
                                'title'        => $paper->title,
                                'author'       => $paper->author?->name ?: 'গবেষক',
                                'category'     => $paper->category ?: 'গবেষণাপত্র',
                                'source'       => 'গবেষণা',
                                'url'          => Route::has('research.show') ? route('research.show', $paper->slug ?: $paper->id) : url('/research/' . ($paper->slug ?: $paper->id)),
                                'image'        => null,
                                'published_at' => $paper->published_at ? $paper->published_at->format('d M Y') : null,
                            ];
                        });
                }
            } catch (\Throwable) {
                $researchPapers = collect();
            }
        }

        // Unified Writings collection
        $writings = $posts->concat($webzineArticles)->concat($researchPapers)->values();

        // 7. Publishers Query
        $publishers = collect();
        if ($type === 'publishers' || $type === 'all') {
            $publishers = Publisher::query()
                ->where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('slug', 'LIKE', "%{$query}%");
                })
                ->withCount(['books' => fn($bq) => $bq->where('is_active', true)])
                ->orderByDesc('books_count')
                ->take(3)
                ->get()
                ->map(function ($pub) {
                    return [
                        'id'          => $pub->id,
                        'name'        => $pub->name,
                        'slug'        => $pub->slug ?: (string)$pub->id,
                        'url'         => route('publishers.show', $pub->slug ?: $pub->id),
                        'books_count' => $pub->books_count,
                    ];
                });
        }

        // 8. Webzines Query
        $webzines = collect();
        if ($type === 'all' || $type === 'webzines') {
            try {
                if (class_exists(\Modules\Webzine\Models\Webzine::class)) {
                    $webzines = \Modules\Webzine\Models\Webzine::query()
                        ->where('is_active', true)
                        ->where(function ($q) use ($query) {
                            $q->where('title', 'LIKE', "%{$query}%")
                              ->orWhere('theme', 'LIKE', "%{$query}%")
                              ->orWhere('slug', 'LIKE', "%{$query}%");
                        })
                        ->take(2)
                        ->get()
                        ->map(fn($w) => [
                            'id'    => $w->id,
                            'title' => $w->title,
                            'url'   => Route::has('webzine.show') ? route('webzine.show', $w->slug ?: $w->id) : url('/webzines/' . ($w->slug ?: $w->id)),
                        ]);
                }
            } catch (\Throwable) {
                $webzines = collect();
            }
        }

        // 9. Bilingual Dynamic Site Pages & Quick Navigation Match
        $matchedPages = $this->matchSitePages($query);
        $matchedQuickLinks = array_slice($matchedPages, 0, 4);

        // 10. Intelligent Keyword Suggestions (Prioritizing Book & Writing Titles)
        $keywordSuggestions = collect();
        // Add book titles
        foreach ($books as $b) {
            $keywordSuggestions->push($b['title']);
            if (!empty($b['author'])) {
                $keywordSuggestions->push($b['author']);
            }
        }
        // Add writing titles
        foreach ($writings as $w) {
            $keywordSuggestions->push($w['title']);
            if (!empty($w['author'])) {
                $keywordSuggestions->push($w['author']);
            }
        }
        // Add author names
        foreach ($authors as $a) {
            $keywordSuggestions->push($a['name']);
        }
        // Add category names
        foreach ($categories as $c) {
            $keywordSuggestions->push($c['name']);
        }
        // Add publisher names
        foreach ($publishers as $pub) {
            $keywordSuggestions->push($pub['name']);
        }
        // Add site page titles
        foreach ($matchedPages as $pg) {
            $keywordSuggestions->push($pg['title']);
            if (!empty($pg['title_en'])) {
                $keywordSuggestions->push($pg['title_en']);
            }
        }

        $keywordSuggestions = $keywordSuggestions
            ->filter(fn($text) => !empty($text) && mb_stripos($text, $query) !== false)
            ->unique()
            ->take(6)
            ->values()
            ->all();

        // 11. Scoped Suggestions ("query in Department")
        $scopedSuggestions = [];
        if ($type === 'all') {
            if ($writings->isNotEmpty()) {
                $scopedSuggestions[] = [
                    'title'       => $query,
                    'scope_label' => 'আইডিয়াপত্র ও সাহিত্য লেখা',
                    'icon'        => 'fa-newspaper',
                    'type'        => 'blog',
                    'url'         => route('blog.index', ['search' => $query]),
                ];
            }
            if ($categories->isNotEmpty()) {
                $scopedSuggestions[] = [
                    'title'       => $query,
                    'scope_label' => 'বিষয় ও ক্যাটাগরি',
                    'icon'        => 'fa-shapes',
                    'type'        => 'categories',
                    'url'         => route('book.index', ['search' => $query, 'type' => 'categories']),
                ];
            }
            if ($authors->isNotEmpty()) {
                $scopedSuggestions[] = [
                    'title'       => $query,
                    'scope_label' => 'লেখক',
                    'icon'        => 'fa-feather-pointed',
                    'type'        => 'authors',
                    'url'         => route('book.index', ['search' => $query, 'type' => 'authors']),
                ];
            }
            if ($publishers->isNotEmpty()) {
                $scopedSuggestions[] = [
                    'title'       => $query,
                    'scope_label' => 'প্রকাশক',
                    'icon'        => 'fa-building',
                    'type'        => 'publishers',
                    'url'         => route('book.index', ['search' => $query, 'type' => 'publishers']),
                ];
            }
        }

        $totalEntities = $totalBooksCount + $authors->count() + $categories->count() + $writings->count() + $publishers->count() + count($matchedPages);

        return response()->json([
            'success'             => true,
            'query'               => $query,
            'type'                => $type,
            'total_all'           => $totalEntities,
            'total_books'         => $totalBooksCount,
            'keyword_suggestions' => $keywordSuggestions,
            'scoped_suggestions'  => $scopedSuggestions,
            'pages'               => $matchedPages,
            'quick_links'         => $matchedQuickLinks,
            'books'               => $books,
            'posts'               => $posts,
            'writings'            => $writings,
            'webzine_articles'    => $webzineArticles,
            'research_papers'     => $researchPapers,
            'authors'             => $authors,
            'categories'          => $categories,
            'publishers'          => $publishers,
            'webzines'            => $webzines,
            'full_search_url'     => route('book.index', ['search' => $query, 'type' => $type]),
        ]);
    }

    /**
     * স্মার্ট দ্বিভাষিক পেজ ম্যাচিং ইঞ্জিন (বাংলা ও ইংরেজি)
     */
    private function matchSitePages(string $query): array
    {
        $q = mb_strtolower(trim($query));
        if (empty($q)) {
            return [];
        }

        $pages = $this->getSitePagesList();
        $tokens = array_filter(preg_split('/\s+/', $q));
        $matched = [];

        foreach ($pages as $page) {
            $score = 0;
            $titleBn = mb_strtolower($page['title']);
            $titleEn = mb_strtolower($page['title_en'] ?? '');
            $desc = mb_strtolower($page['description'] ?? '');
            $category = mb_strtolower($page['category'] ?? '');

            // 1. Direct match on Bangla or English title
            if (mb_stripos($titleBn, $q) !== false) {
                $score += 150;
            }
            if (!empty($titleEn) && mb_stripos($titleEn, $q) !== false) {
                $score += 130;
            }

            // 2. Keyword exact / partial matches
            if (isset($page['keywords']) && is_array($page['keywords'])) {
                foreach ($page['keywords'] as $kw) {
                    $kwLower = mb_strtolower($kw);
                    if ($kwLower === $q) {
                        $score += 140;
                    } elseif (mb_stripos($kwLower, $q) !== false || mb_stripos($q, $kwLower) !== false) {
                        $score += 60;
                    }
                }
            }

            // 3. Multi-token partial analysis
            foreach ($tokens as $token) {
                if (mb_stripos($titleBn, $token) !== false) {
                    $score += 40;
                }
                if (!empty($titleEn) && mb_stripos($titleEn, $token) !== false) {
                    $score += 35;
                }
                if (mb_stripos($desc, $token) !== false || mb_stripos($category, $token) !== false) {
                    $score += 20;
                }
                if (isset($page['keywords']) && is_array($page['keywords'])) {
                    foreach ($page['keywords'] as $kw) {
                        if (mb_stripos(mb_strtolower($kw), $token) !== false) {
                            $score += 25;
                        }
                    }
                }
            }

            if ($score > 0) {
                $page['score'] = $score;
                $matched[] = $page;
            }
        }

        // Sort by relevance score descending
        usort($matched, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_map(function($p) {
            unset($p['score']);
            return $p;
        }, $matched);
    }

    /**
     * সাইটের সকল স্ট্যাটিক ও ডায়নামিক পেজের সমৃদ্ধ দ্বিভাষিক (বাংলা ও ইংরেজি) ডিরেক্টরি
     */
    private function getSitePagesList(): array
    {
        return [
            [
                'id'          => 'ideapatra',
                'title'       => 'আইডিয়াপত্র ও ব্লগ',
                'title_en'    => 'Ideapatra & Literary Blog',
                'description' => 'সাহিত্য, প্রবন্ধ, কবিতা ও চিন্তামূলক লেখার উন্মুক্ত প্ল্যাটফর্ম',
                'category'    => 'সাহিত্য ও কন্টেন্ট',
                'url'         => route('blog.index'),
                'icon'        => 'fa-newspaper',
                'keywords'    => ['আইডিয়াপত্র', 'আইডিয়া পত্র', 'আইডিয়া পত্র', 'ব্লগ', 'লেখালেখি', 'সাহিত্য', 'নিবন্ধ', 'কবিতা', 'প্রবন্ধ', 'ideapatra', 'idea patra', 'blog', 'articles', 'literature', 'essay', 'poetry', 'aydiapatro'],
            ],
            [
                'id'          => 'ideapatra_write',
                'title'       => 'নিজের লেখা প্রকাশ করুন',
                'title_en'    => 'Write for Ideapatra / Publish Article',
                'description' => 'আইডিয়াপত্রে আপনার স্বরচিত সাহিত্যকর্ম ও প্রবন্ধ জমা দিন',
                'category'    => 'লেখক ও সেবা',
                'url'         => route('blog.write'),
                'icon'        => 'fa-pen-nib',
                'keywords'    => ['লেখা পোস্ট', 'লেখা প্রকাশ', 'লেখা পাঠান', 'নিজের লেখা', 'ব্লগ লিখুন', 'কবিতা প্রকাশ', 'গল্প প্রকাশ', 'write', 'publish article', 'submit post', 'author write', 'lekha'],
            ],
            [
                'id'          => 'ebooks',
                'title'       => 'ডিজিটাল ই-বুক সম্ভার',
                'title_en'    => 'Digital E-Books Library',
                'description' => 'অনলাইনে যেকোনো ডিভাইস থেকে সরাসরি ই-বুক পড়ুন ও সংগ্রহ করুন',
                'category'    => 'বই ও প্রকাশনা',
                'url'         => route('ebook.index'),
                'icon'        => 'fa-tablet-screen-button',
                'keywords'    => ['ইবুক', 'ই-বুক', 'ডিজিটাল বই', 'পিডিএফ', 'অনলাইন বই', 'ebook', 'ebooks', 'pdf', 'digital books', 'online reading'],
            ],
            [
                'id'          => 'books_catalog',
                'title'       => 'সকল বিষয় ও অনলাইন বই সম্ভার',
                'title_en'    => 'All Books & Catalog',
                'description' => 'আইডিয়া প্রকাশনের সকল ক্যাটাগরির মুদ্রিত বইয়ের পূর্ণাঙ্গ ক্যাটালগ',
                'category'    => 'বই ও প্রকাশনা',
                'url'         => route('book.index'),
                'icon'        => 'fa-book-open',
                'keywords'    => ['বইসমূহ', 'সকল বই', 'বই সম্ভার', 'বইয়ের দোকান', 'শপ', 'books', 'all books', 'bookstore', 'shop', 'catalog', 'boi'],
            ],
            [
                'id'          => 'authors',
                'title'       => 'লেখকবৃন্দ ও সাহিত্যিক তালিকা',
                'title_en'    => 'Authors & Writers Directory',
                'description' => 'সকল সম্মানিত লেখক ও কবিদের প্রোফাইল এবং প্রকাশিত বইসমূহ',
                'category'    => 'লেখক ও কমিউনিটি',
                'url'         => route('authors.index'),
                'icon'        => 'fa-feather-pointed',
                'keywords'    => ['লেখক', 'লেখকবৃন্দ', 'লেখক তালিকা', 'কবি', 'সাহিত্যিক', 'authors', 'writers', 'author list', 'poets', 'lekhok'],
            ],
            [
                'id'          => 'publishers',
                'title'       => 'প্রকাশনা সংস্থা ও ডিস্ট্রিবিউটরস',
                'title_en'    => 'Publishers & Distributors',
                'description' => 'আইডিয়া প্রকাশনের অংশীদার প্রকাশনী ও সহযোগী প্রতিষ্ঠানসমূহ',
                'category'    => 'প্রকাশক ও ডিস্ট্রিবিউশন',
                'url'         => route('publishers.index'),
                'icon'        => 'fa-building',
                'keywords'    => ['প্রকাশক', 'প্রকাশনী', 'পাবলিশার্স', 'প্রেস', 'প্রকাশনা সংস্থা', 'publishers', 'publications', 'press', 'prokashoni'],
            ],
            [
                'id'          => 'webzines',
                'title'       => 'অনলাইন ওয়েবজিন ও সাহিত্য সাময়িকী',
                'title_en'    => 'Online Webzines & Periodicals',
                'description' => 'আইডিয়া প্রকাশন ডিজিটাল সাহিত্য সাময়িকী ও নিয়মিত সংখ্যাসমূহ',
                'category'    => 'সাহিত্য ও কন্টেন্ট',
                'url'         => Route::has('webzine.index') ? route('webzine.index') : url('/webzines'),
                'icon'        => 'fa-book-journal-whills',
                'keywords'    => ['ওয়েবজিন', 'ওয়েবজিন', 'ম্যাগাজিন', 'সাহিত্য সাময়িকী', 'পত্রিকা', 'webzine', 'webzines', 'magazine', 'periodical', 'samoyiki'],
            ],
            [
                'id'          => 'research',
                'title'       => 'গবেষণা ও উন্নয়ন প্রবন্ধ',
                'title_en'    => 'Research Papers & Journals',
                'description' => 'একাডেমিক গবেষণা, জার্নাল ও বিশ্লেষণধর্মী গবেষণাপত্র',
                'category'    => 'গবেষণা ও শিক্ষা',
                'url'         => Route::has('research.index') ? route('research.index') : url('/research'),
                'icon'        => 'fa-flask',
                'keywords'    => ['গবেষণা', 'গবেষণাপত্র', 'রিসার্চ', 'প্রবন্ধ', 'জার্নাল', 'থিসিস', 'research', 'papers', 'journal', 'thesis', 'gobeshona'],
            ],
            [
                'id'          => 'hub',
                'title'       => 'আইডিয়া হাব ও কমিউনিটি নেটওয়ার্ক',
                'title_en'    => 'Idea Hub & Community',
                'description' => 'লেখক, পাঠক ও গবেষকদের সমন্বিত আইডিয়া নেটওয়ার্ক হাব',
                'category'    => 'কমিউনিটি ও হাব',
                'url'         => Route::has('hub') ? route('hub') : url('/hub'),
                'icon'        => 'fa-compass',
                'keywords'    => ['আইডিয়া হাব', 'হাব', 'কমিউনিটি', 'সদস্য', 'নেটওয়ার্ক', 'hub', 'idea hub', 'community', 'network', 'hab'],
            ],
            [
                'id'          => 'about',
                'title'       => 'আমাদের সম্পর্কে (About Us)',
                'title_en'    => 'About Us — Idea Prokashon',
                'description' => 'আইডিয়া প্রকাশনের লক্ষ্য, উদ্দেশ্য ও প্রকাশনা যাত্রার বিস্তারিত তথ্য',
                'category'    => 'প্রাতিষ্ঠানিক তথ্য',
                'url'         => Route::has('about') ? route('about') : url('/about'),
                'icon'        => 'fa-circle-info',
                'keywords'    => ['আমাদের সম্পর্কে', 'পরিচিতি', 'আমাদের কথা', 'আইডিয়া প্রকাশন কে', 'কোম্পানি পরিচিতি', 'about', 'about us', 'company', 'who we are', 'idea prokashon', 'amader somporke'],
            ],
            [
                'id'          => 'contact',
                'title'       => 'যোগাযোগ ও সহায়তা কেন্দ্র',
                'title_en'    => 'Contact Us & Customer Support',
                'description' => 'আমাদের অফিস ঠিকানা, ফোন নাম্বার, হোয়াটসঅ্যাপ ও সহায়তা বার্তা পাঠান',
                'category'    => 'গ্রাহক সেবা ও যোগাযোগ',
                'url'         => Route::has('contact') ? route('contact') : url('/contact'),
                'icon'        => 'fa-headset',
                'keywords'    => ['যোগাযোগ', 'ঠিকানা', 'ফোন নম্বর', 'ইমেইল', 'হেল্পলাইন', 'সহায়তা', 'হোয়াটসঅ্যাপ', 'কাস্টমার কেয়ার', 'contact', 'contact us', 'support', 'helpline', 'whatsapp', 'phone', 'email', 'address', 'help', 'jogajog'],
            ],
            [
                'id'          => 'faq',
                'title'       => 'সাধারণ জিজ্ঞাসা ও প্রশ্নোত্তর (FAQ)',
                'title_en'    => 'Frequently Asked Questions (FAQ)',
                'description' => 'অর্ডার, পেমেন্ট, ডেলিভারি ও প্রকাশনা সংক্রান্ত সাধারণ প্রশ্নের উত্তর',
                'category'    => 'গ্রাহক সেবা ও সহায়তা',
                'url'         => Route::has('faq') ? route('faq') : url('/faq'),
                'icon'        => 'fa-circle-question',
                'keywords'    => ['সাধারণ জিজ্ঞাসা', 'প্রশ্নোত্তর', 'এফএকিউ', 'প্রশ্ন ও উত্তর', 'সহায়তা গাইড', 'faq', 'frequently asked questions', 'help center', 'questions', 'answers', 'proshnottor'],
            ],
            [
                'id'          => 'documents',
                'title'       => 'নথিপত্র ও প্রকাশনা গাইডলাইন',
                'title_en'    => 'Documents, Guidelines & Author Contracts',
                'description' => 'বই প্রকাশনা চুক্তিপত্র, পাণ্ডুলিপি জমা দেওয়ার নিয়ম ও রয়্যালটি গাইড',
                'category'    => 'লেখক ও প্রকাশনা সেবা',
                'url'         => Route::has('documents') ? route('documents') : url('/documents'),
                'icon'        => 'fa-file-lines',
                'keywords'    => ['নথিপত্র', 'ডকুমেন্টস', 'প্রকাশনা গাইড', 'চুক্তিপত্র', 'রয়্যালটি নিয়ম', 'বই প্রকাশের নিয়ম', 'documents', 'guidelines', 'contract', 'author contract', 'publication rules', 'chuktir niyom', 'dokuments'],
            ],
            [
                'id'          => 'terms',
                'title'       => 'শর্তাবলী ও ব্যবহারের নিয়মাবলী',
                'title_en'    => 'Terms & Conditions',
                'description' => 'আইডিয়া প্রকাশন ওয়েবসাইট ও সেবা ব্যবহারের আইনি শর্তাবলী',
                'category'    => 'আইনি ও নীতিমালা',
                'url'         => Route::has('terms') ? route('terms') : url('/terms'),
                'icon'        => 'fa-scale-balanced',
                'keywords'    => ['শর্তাবলী', 'ব্যবহারের শর্ত', 'টার্মস', 'নিয়মাবলী', 'শর্তসমূহ', 'terms', 'terms and conditions', 'terms of service', 'policy', 'rules', 'shortaboli'],
            ],
            [
                'id'          => 'privacy',
                'title'       => 'গোপনীয়তা নীতি (Privacy Policy)',
                'title_en'    => 'Privacy Policy & Data Protection',
                'description' => 'গ্রাহকের তথ্যের সুরক্ষা ও ব্যক্তিগত গোপনীয়তা রক্ষা সংক্রান্ত নীতিমালা',
                'category'    => 'আইনি ও নীতিমালা',
                'url'         => Route::has('privacy') ? route('privacy') : url('/privacy'),
                'icon'        => 'fa-shield-halved',
                'keywords'    => ['গোপনীয়তা', 'গোপনীয়তা নীতি', 'প্রাইভেসি পলিসি', 'তথ্য সুরক্ষা', 'নিরাপত্তা', 'privacy', 'privacy policy', 'security', 'data protection', 'goponiyota'],
            ],
            [
                'id'          => 'refund',
                'title'       => 'রিফান্ড ও রিটার্ন নীতি',
                'title_en'    => 'Refund, Return & Cancellation Policy',
                'description' => 'ত্রুটিপূর্ণ বই পরিবর্তন ও টাকা ফেরতের সুনির্দিষ্ট নীতিমালা',
                'category'    => 'আইনি ও নীতিমালা',
                'url'         => Route::has('refund.policy') ? route('refund.policy') : url('/refund-policy'),
                'icon'        => 'fa-rotate-left',
                'keywords'    => ['রিফান্ড', 'রিটার্ন', 'টাকা ফেরত', 'বই পরিবর্তন', 'রিটার্ন পলিসি', 'রিফান্ড পলিসি', 'refund', 'return', 'refund policy', 'return policy', 'money back', 'exchange', 'cancellation'],
            ],
            [
                'id'          => 'shipping',
                'title'       => 'শিপিং ও হোম ডেলিভারি তথ্য',
                'title_en'    => 'Shipping & Delivery Policy',
                'description' => 'সারা দেশে ক্যাশ অন ডেলিভারি, কুরিয়ার চার্জ ও সময়সীমার তথ্য',
                'category'    => 'ডেলিভারি ও অর্ডার',
                'url'         => Route::has('shipping.policy') ? route('shipping.policy') : url('/shipping-policy'),
                'icon'        => 'fa-truck-fast',
                'keywords'    => ['শিপিং', 'ডেলিভারি', 'হোম ডেলিভারি', 'কুরিয়ার', 'ডেলিভারি চার্জ', 'shipping', 'delivery', 'delivery charges', 'courier', 'home delivery', 'tracking'],
            ],
            [
                'id'          => 'author_register',
                'title'       => 'লেখক হিসেবে রেজিস্ট্রেশন / নিবন্ধন',
                'title_en'    => 'Author Registration / Join as Author',
                'description' => 'আইডিয়া প্রকাশনে লেখক হিসেবে যুক্ত হয়ে নিজের বই ও আইডিয়াপত্র প্রকাশ করুন',
                'category'    => 'রেজিস্ট্রেশন ও একাউন্ট',
                'url'         => route('register.form', 'author'),
                'icon'        => 'fa-user-pen',
                'keywords'    => ['লেখক নিবন্ধন', 'লেখক রেজিস্ট্রেশন', 'লেখক একাউন্ট', 'বই প্রকাশ করতে চাই', 'author registration', 'author register', 'join author', 'author signup', 'lekhok registration'],
            ],
            [
                'id'          => 'publisher_register',
                'title'       => 'প্রকাশক হিসেবে রেজিস্ট্রেশন',
                'title_en'    => 'Publisher Registration & Partnership',
                'description' => 'সহযোগী প্রকাশনী হিসেবে যুক্ত হয়ে আপনার বই আইডিয়া প্ল্যাটফর্মে যুক্ত করুন',
                'category'    => 'রেজিস্ট্রেশন ও একাউন্ট',
                'url'         => route('register.form', 'publisher'),
                'icon'        => 'fa-briefcase',
                'keywords'    => ['প্রকাশক নিবন্ধন', 'প্রকাশক রেজিস্ট্রেশন', 'publisher registration', 'publisher register', 'publisher signup'],
            ],
            [
                'id'          => 'cart',
                'title'       => 'আমার শপিং কার্ট ও চেকআউট',
                'title_en'    => 'Shopping Cart & Checkout',
                'description' => 'অর্ডারকৃত বইসমূহের তালিকা এবং সরাসরি চেকআউট সম্পন্ন করার পাতা',
                'category'    => 'অর্ডার ও চেকআউট',
                'url'         => route('cart'),
                'icon'        => 'fa-bag-shopping',
                'keywords'    => ['কার্ট', 'শপিং কার্ট', 'ঝুড়ি', 'চেকআউট', 'অর্ডার', 'cart', 'shopping cart', 'checkout', 'bag', 'order'],
            ],
            [
                'id'          => 'login',
                'title'       => 'গ্রাহক ও লেখক লগইন',
                'title_en'    => 'User & Author Login',
                'description' => 'আপনার আইডিয়া প্রোফাইল, অর্ডার হিস্ট্রি ও ড্যাশবোর্ডে প্রবেশ করুন',
                'category'    => 'লগইন ও পোর্টাল',
                'url'         => route('login'),
                'icon'        => 'fa-arrow-right-to-bracket',
                'keywords'    => ['লগইন', 'সাইন ইন', 'একাউন্টে প্রবেশ', 'login', 'sign in', 'log in', 'portal', 'account'],
            ],
            [
                'id'          => 'my_account',
                'title'       => 'আমার অ্যাকাউন্ট ও অর্ডার হিস্ট্রি',
                'title_en'    => 'My Account & Order Tracking',
                'description' => 'আপনার প্রোফাইল তথ্য, সংরক্ষিত ঠিকানা ও সকল অর্ডারের বর্তমান অবস্থা',
                'category'    => 'গ্রাহক সেবা ও একাউন্ট',
                'url'         => route('my-account'),
                'icon'        => 'fa-user-gear',
                'keywords'    => ['আমার একাউন্ট', 'প্রোফাইল', 'অর্ডার হিস্ট্রি', 'অর্ডার ট্র্যাকিং', 'my account', 'profile', 'order history', 'track order', 'account dashboard'],
            ],
            [
                'id'          => 'boimela',
                'title'       => 'বইমেলা ২০২৬ বিশেষ কালেকশন',
                'title_en'    => 'Ekushey Boi Mela 2026 Collection',
                'description' => 'একুশে বইমেলা উপলক্ষে প্রকাশিত সকল নতুন ও সমাদৃত বইয়ের বিশেষ সম্ভার',
                'category'    => 'বিশেষ আয়োজন',
                'url'         => route('book.index', ['filter' => 'boimela-2026']),
                'icon'        => 'fa-fire',
                'keywords'    => ['বইমেলা', 'বইমেলা ২০২৬', 'একুশে বইমেলা', 'মেলা', 'boimela', 'boi mela', 'mela', 'ekushey boimela'],
            ],
        ];
    }
}