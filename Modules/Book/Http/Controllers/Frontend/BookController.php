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
            }
        }

        return view('book::frontend.index', compact(
            'books', 'categories', 'isSearchMode', 'recentlySold', 'newArrivals', 'bestSellerEbooks', 'categoryBooks', 'sidebarAuthors', 'sidebarPublishers', 'flashSales', 'recentlyViewedBooks', 'topSeller', 'dynamicCategories', 'activeFilterTitle'
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
    public function suggest(Request $request): JsonResponse
    {
        $query = trim((string)($request->input('q') ?: $request->input('search') ?: ''));
        $type = (string)$request->input('type', 'all');

        if (mb_strlen($query) < 1) {
            return response()->json([
                'success'    => true,
                'query'      => $query,
                'books'      => [],
                'authors'    => [],
                'categories' => [],
                'publishers' => [],
                'total'      => 0,
            ]);
        }

        // Tokenize query words
        $tokens = array_filter(preg_split('/\s+/', $query));

        // 1. Books Query
        $booksQuery = Book::query()
            ->with(['authors:id,name,slug', 'category:id,name,slug', 'publisher:id,name,slug'])
            ->where('is_active', true);

        if ($type === 'books' || $type === 'all') {
            $booksQuery->where(function ($master) use ($tokens, $query) {
                // Exact or full match priority
                $master->where('title', 'LIKE', "%{$query}%")
                       ->orWhere('title_en', 'LIKE', "%{$query}%")
                       ->orWhere('sku', 'LIKE', "%{$query}%")
                       ->orWhere('idea_serial_no', 'LIKE', "%{$query}%")
                       ->orWhere('isbn', 'LIKE', "%{$query}%")
                       ->orWhere('author_name', 'LIKE', "%{$query}%");

                // Multi-token match
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

            return [
                'id'             => $book->id,
                'title'          => $book->title,
                'slug'           => $book->slug ?: (string)$book->id,
                'url'            => route('book.show', $book->slug ?: $book->id),
                'author'         => $authorName,
                'category'       => $categoryName,
                'format'         => $book->format ?? 'paperback',
                'format_label'   => $book->format === 'hardcover' ? 'হার্ডকভার' : ($book->format === 'ebook' ? 'ই-বুক' : 'কাগজের বই'),
                'cover'          => $cover,
                'price'          => $price,
                'discount_price' => $discountPrice,
                'has_discount'   => $hasDiscount,
                'price_formatted'=> '৳' . number_format($hasDiscount ? $discountPrice : $price, 0),
                'mrp_formatted'  => $hasDiscount ? '৳' . number_format($price, 0) : null,
                'in_stock'       => (int)$book->stock_quantity > 0,
                'idea_serial_no' => $book->idea_serial_no,
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
        if ($type === 'all') {
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

        // 4. Publishers Query
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

        return response()->json([
            'success'          => true,
            'query'            => $query,
            'type'             => $type,
            'total_books'      => $totalBooksCount,
            'books'            => $books,
            'authors'          => $authors,
            'categories'       => $categories,
            'publishers'       => $publishers,
            'full_search_url'  => route('book.index', ['search' => $query, 'type' => $type]),
        ]);
    }
}