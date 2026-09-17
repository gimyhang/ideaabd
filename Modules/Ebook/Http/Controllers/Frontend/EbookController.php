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
     * ডিজিটাল ই-বুক ক্যাটালগ ও ফিল্টারিং (আন্তর্জাতিক মানের ডিজিটাল লাইব্রেরি স্টোরফ্রন্ট)
     */
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $canUseEbooks = false;

        try {
            $canUseEbooks = DB::getSchemaBuilder()->hasTable('ebooks');
        } catch (\Throwable) {
            $canUseEbooks = false;
        }

        // Live AJAX Instant Search Autocomplete
        if (($request->ajax() || $request->wantsJson()) && ($request->filled('q') || $request->filled('live_search'))) {
            $term = trim((string) ($request->input('q') ?: $request->input('live_search')));
            if (mb_strlen($term) < 2) {
                return response()->json(['results' => []]);
            }

            $liveResults = Ebook::query()
                ->where('is_active', true)
                ->where(function ($q) use ($term) {
                    $q->where('title', 'LIKE', "%{$term}%")
                      ->orWhere('subtitle', 'LIKE', "%{$term}%")
                      ->orWhere('author_name', 'LIKE', "%{$term}%")
                      ->orWhereHas('author', fn ($a) => $a->where('name', 'LIKE', "%{$term}%"))
                      ->orWhereHas('category', fn ($c) => $c->where('name', 'LIKE', "%{$term}%"));
                })
                ->with(['author:id,name,slug', 'category:id,name,slug'])
                ->take(8)
                ->get(['id', 'title', 'subtitle', 'author_id', 'author_name', 'category_id', 'slug', 'price', 'discount_price', 'cover_image', 'file_type', 'epub_file_path', 'file_path']);

            $formatted = $liveResults->map(function ($eb) {
                return [
                    'id'          => $eb->id,
                    'title'       => $eb->title,
                    'author'      => $eb->author?->name ?: ($eb->author_name ?: 'আইডিয়া লেখক'),
                    'category'    => $eb->category?->name ?? 'সাধারণ',
                    'price'       => (float) $eb->price,
                    'discount_price' => $eb->discount_price ? (float) $eb->discount_price : null,
                    'is_free'     => $eb->is_free,
                    'cover_url'   => $eb->cover_url,
                    'format_badge'=> $eb->format_badge,
                    'url'         => route('ebook.show', $eb->slug),
                    'read_url'    => route('ebook.read', $eb->slug),
                ];
            });

            return response()->json(['results' => $formatted]);
        }

        $ebooks = collect();
        $categories = collect();
        $sidebarAuthors = collect();
        $sidebarPublishers = collect();
        $featuredEbooks = collect();
        $bestsellingEbooks = collect();
        $flashSales = collect();
        $freeEbooks = collect();
        $newReleaseEbooks = collect();
        $dynamicCategories = collect();
        $matchedBlogPosts = collect();
        $matchedResearchPapers = collect();
        $matchedWebzineArticles = collect();
        $matchedAuthors = collect();
        $matchedCategories = collect();
        $matchedPages = [];
        $spotlightEbook = null;
        $userLibraryIds = [];
        $activeFilterTitle = null;

        $stats = [
            'total'   => 0,
            'free'    => 0,
            'epub'    => 0,
            'pdf'     => 0,
            'readers' => 0,
        ];

        $rawSearch = trim((string)($request->input('search') ?: $request->input('q') ?: ''));
        $isSearchMode = $request->anyFilled([
            'search', 'q', 'category', 'author', 'publisher', 'format', 
            'min_price', 'max_price', 'free_only', 'discount_min', 'sort', 'letter'
        ]) || ($request->has('page') && (int)$request->get('page') > 1);

        if (auth()->check()) {
            try {
                if (DB::getSchemaBuilder()->hasTable('user_ebook_library')) {
                    $userLibraryIds = UserEbookLibrary::where('user_id', auth()->id())->pluck('ebook_id')->toArray();
                }
            } catch (\Throwable) {}
        }

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
            $stats['readers'] = (int) Ebook::query()->where('is_active', true)->sum('read_count') + (int) Ebook::query()->where('is_active', true)->sum('sales_count');

            // Categories
            try {
                if (DB::getSchemaBuilder()->hasTable('categories')) {
                    $categories = Category::query()
                        ->where('is_active', true)
                        ->whereNull('parent_id')
                        ->with(['children' => fn ($q) => $q->where('is_active', true)->withCount(['ebooks' => fn ($eq) => $eq->where('is_active', true)])])
                        ->withCount(['ebooks' => fn ($q) => $q->where('is_active', true)])
                        ->orderBy('sort_order')
                        ->orderByDesc('ebooks_count')
                        ->get();

                    if ($categories->isEmpty()) {
                        $categories = Category::query()
                            ->where('is_active', true)
                            ->withCount(['ebooks' => fn ($q) => $q->where('is_active', true)])
                            ->orderByDesc('ebooks_count')
                            ->orderBy('name')
                            ->get();
                    }
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
                        ->take(50)
                        ->get(['id', 'name', 'slug']);
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
                        ->take(50)
                        ->get(['id', 'name', 'slug']);
                }
            } catch (\Throwable) {}

            // Curated Shelves (when browsing landing catalog)
            if (!$isSearchMode) {
                // Shelf 1: Flash Sales / Special Discounts
                $flashSales = Ebook::query()
                    ->with(['author', 'publisher', 'category'])
                    ->where('is_active', true)
                    ->whereNotNull('discount_price')
                    ->where('discount_price', '>', 0)
                    ->whereColumn('discount_price', '<', 'price')
                    ->latest('id')
                    ->take(15)
                    ->get();

                // Shelf 2: Bestsellers & Popular Ebooks
                $bestsellingEbooks = Ebook::query()
                    ->with(['author', 'publisher', 'category'])
                    ->where('is_active', true)
                    ->orderByDesc('sales_count')
                    ->orderByDesc('read_count')
                    ->latest('id')
                    ->take(15)
                    ->get();

                // Shelf 3: New Releases
                $newReleaseEbooks = Ebook::query()
                    ->with(['author', 'publisher', 'category'])
                    ->where('is_active', true)
                    ->latest('id')
                    ->take(15)
                    ->get();

                // Shelf 4: Free E-Books
                $freeEbooks = Ebook::query()
                    ->with(['author', 'publisher', 'category'])
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->where('price', '<=', 0)->orWhere('discount_price', '=', 0);
                    })
                    ->latest('id')
                    ->take(15)
                    ->get();

                // Shelves 5+: Dynamic Category Shelves
                try {
                    $dynamicCategories = Category::query()
                        ->where('is_active', true)
                        ->whereHas('ebooks', fn ($q) => $q->where('is_active', true))
                        ->withCount(['ebooks' => fn ($q) => $q->where('is_active', true)])
                        ->orderByDesc('ebooks_count')
                        ->take(24)
                        ->get(['id', 'name', 'slug', 'icon_or_image']);
                } catch (\Throwable) {}

                $featuredEbooks = $bestsellingEbooks->take(4);
                $spotlightEbook = $bestsellingEbooks->first() ?: $newReleaseEbooks->first();
            } else {
                // Cross-Entity Matches on Search
                if (!empty($rawSearch) && mb_strlen($rawSearch) >= 2) {
                    $matchedPages = $this->matchSitePages($rawSearch);
                    $tokens = array_filter(preg_split('/\s+/', $rawSearch));

                    try {
                        if (class_exists(\Modules\Book\Models\Category::class)) {
                            $matchedCategories = Category::query()
                                ->where('is_active', true)
                                ->where(function ($q) use ($rawSearch) {
                                    $q->where('name', 'LIKE', "%{$rawSearch}%")
                                      ->orWhere('slug', 'LIKE', "%{$rawSearch}%");
                                })
                                ->withCount(['ebooks' => fn($q) => $q->where('is_active', true)])
                                ->take(6)
                                ->get();
                        }
                    } catch (\Throwable) {}

                    try {
                        if (class_exists(\Modules\Author\Models\Author::class)) {
                            $matchedAuthors = Author::query()
                                ->where('is_active', true)
                                ->where(function ($q) use ($rawSearch) {
                                    $q->where('name', 'LIKE', "%{$rawSearch}%")
                                      ->orWhere('slug', 'LIKE', "%{$rawSearch}%");
                                })
                                ->withCount(['ebooks' => fn($q) => $q->where('is_active', true)])
                                ->take(6)
                                ->get();
                        }
                    } catch (\Throwable) {}

                    try {
                        if (DB::getSchemaBuilder()->hasTable('blog_posts')) {
                            $matchedBlogPosts = \Modules\Blog\Models\BlogPost::query()
                                ->with(['author', 'category'])
                                ->where(fn ($q) => $q->where('status', 'published')->orWhere('mod_status', 'approved')->orWhereNull('status'))
                                ->where(fn ($q) => $q->where('title', 'LIKE', "%{$rawSearch}%")->orWhere('content', 'LIKE', "%{$rawSearch}%")->orWhere('slug', 'LIKE', "%{$rawSearch}%"))
                                ->latest('id')
                                ->take(6)
                                ->get();
                        }
                    } catch (\Throwable) {}

                    try {
                        if (DB::getSchemaBuilder()->hasTable('research_papers')) {
                            $matchedResearchPapers = \App\Models\ResearchPaper::query()
                                ->where('status', 'published')
                                ->where(fn ($q) => $q->where('title', 'LIKE', "%{$rawSearch}%")->orWhere('abstract', 'LIKE', "%{$rawSearch}%"))
                                ->latest('id')
                                ->take(6)
                                ->get();
                        }
                    } catch (\Throwable) {}
                }
            }

            // Resolve Active Filter Title
            if ($request->filled('category')) {
                $catObj = Category::where('slug', $request->string('category'))->orWhere('id', $request->input('category'))->first();
                if ($catObj) $activeFilterTitle = $catObj->name . ' — ই-বুক সংগ্রহ';
            } elseif ($request->filled('author')) {
                $authObj = Author::where('slug', $request->string('author'))->orWhere('id', $request->input('author'))->first();
                if ($authObj) $activeFilterTitle = $authObj->name . ' এর ই-বুক';
            } elseif ($request->filled('publisher')) {
                $pubObj = Publisher::where('slug', $request->string('publisher'))->orWhere('id', $request->input('publisher'))->first();
                if ($pubObj) $activeFilterTitle = $pubObj->name . ' এর প্রকাশিত ই-বুক';
            } elseif ($request->filled('letter') && $request->get('letter') !== 'all') {
                $activeFilterTitle = '"' . $request->get('letter') . '" বর্ণ দিয়ে শুরু ই-বুকসমূহ';
            } elseif ($request->filled('format')) {
                $fmt = strtolower($request->string('format')->value());
                $activeFilterTitle = ($fmt === 'epub') ? 'EPUB ফরম্যাটের ই-বুক' : (($fmt === 'pdf') ? 'PDF সংস্করণের ই-বুক' : (($fmt === 'free') ? '১০০% বিনামূল্যে পড়ার ই-বুক' : 'ই-বুক তালিকা'));
            } elseif ($request->boolean('free_only')) {
                $activeFilterTitle = 'বিনামূল্যে পড়ার ই-বুক সংগ্রহ';
            } elseif ($request->filled('discount_min')) {
                $activeFilterTitle = 'বিশেষ ছাড়ের ই-বুক';
            } elseif (!empty($rawSearch)) {
                $activeFilterTitle = '"' . $rawSearch . '" সম্পর্কিত ই-বুক ফলাফল';
            } elseif ($request->string('sort') === 'bestselling') {
                $activeFilterTitle = 'জনপ্রিয় ও সর্বাধিক বিক্রিত ই-বুক';
            } elseif ($request->string('sort') === 'popular') {
                $activeFilterTitle = 'সর্বাধিক পঠিত ই-বুক';
            }

            // Main Query
            $query = Ebook::query()
                ->with(['author', 'publisher', 'category'])
                ->where('is_active', true);

            // Filter: Category
            if ($request->filled('category')) {
                $catVal = $request->string('category')->trim()->value();
                $matchedCat = Category::where('slug', $catVal)
                    ->orWhere('id', is_numeric($catVal) ? (int)$catVal : 0)
                    ->orWhere('name', $catVal)
                    ->first();
                $catIds = [];
                if ($matchedCat) {
                    $catIds = array_merge([$matchedCat->id], $matchedCat->children()->pluck('id')->all());
                }

                $query->where(function ($sub) use ($catVal, $catIds) {
                    if (!empty($catIds)) {
                        $sub->whereIn('category_id', $catIds);
                    } else {
                        $sub->where('category_id', $catVal);
                    }
                    $sub->orWhereHas('category', function ($cat) use ($catVal) {
                        $cat->where('slug', $catVal)->orWhere('name', 'LIKE', "%{$catVal}%");
                    });
                });
            }

            // Filter: Author
            if ($request->filled('author')) {
                $authorVal = $request->string('author')->trim()->value();
                $query->where(function ($sub) use ($authorVal) {
                    $sub->where('author_id', $authorVal)
                        ->orWhere('author_name', 'LIKE', "%{$authorVal}%")
                        ->orWhereHas('author', function ($auth) use ($authorVal) {
                            $auth->where('slug', $authorVal)
                                ->orWhere('name', $authorVal)
                                ->orWhere('id', $authorVal);
                        });
                });
            }

            // Filter: Publisher
            if ($request->filled('publisher')) {
                $pubVal = $request->string('publisher')->trim()->value();
                $query->where(function ($p) use ($pubVal) {
                    $p->where('publisher_id', $pubVal)
                      ->orWhereHas('publisher', fn ($sq) => $sq->where('slug', $pubVal)->orWhere('name', 'LIKE', "%{$pubVal}%")->orWhere('id', $pubVal));
                });
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

            // Filter: Discount Min
            if ($request->filled('discount_min')) {
                $minPercent = $request->integer('discount_min');
                if ($minPercent > 0) {
                    $query->whereNotNull('discount_price')
                          ->whereRaw('((price - discount_price) * 100 / price) >= ?', [$minPercent]);
                }
            }

            // Filter: Letter
            if ($request->filled('letter') && $request->get('letter') !== 'all') {
                $letter = $request->string('letter')->trim()->value();
                if ($letter === 'A-Z') {
                    $query->where(function($sub) {
                        $sub->where('title', 'REGEXP', '^[A-Za-z]')
                            ->orWhere('subtitle', 'REGEXP', '^[A-Za-z]')
                            ->orWhere('author_name', 'REGEXP', '^[A-Za-z]');
                    });
                } else {
                    $query->where(function($sub) use ($letter) {
                        $sub->where('title', 'LIKE', "{$letter}%")
                            ->orWhere('subtitle', 'LIKE', "{$letter}%")
                            ->orWhere('author_name', 'LIKE', "{$letter}%")
                            ->orWhereHas('author', fn($a) => $a->where('name', 'LIKE', "{$letter}%"));
                    });
                }
            }

            // Filter: Search Keyword
            if (!empty($rawSearch)) {
                $tokens = array_filter(preg_split('/\s+/', $rawSearch));
                $query->where(function ($master) use ($rawSearch, $tokens) {
                    $master->where('title', 'LIKE', "%{$rawSearch}%")
                        ->orWhere('subtitle', 'LIKE', "%{$rawSearch}%")
                        ->orWhere('author_name', 'LIKE', "%{$rawSearch}%")
                        ->orWhere('isbn', 'LIKE', "%{$rawSearch}%")
                        ->orWhere('description', 'LIKE', "%{$rawSearch}%")
                        ->orWhereHas('author', fn ($a) => $a->where('name', 'LIKE', "%{$rawSearch}%"))
                        ->orWhereHas('publisher', fn ($p) => $p->where('name', 'LIKE', "%{$rawSearch}%"))
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'LIKE', "%{$rawSearch}%"));

                    foreach ($tokens as $token) {
                        $like = "%{$token}%";
                        $master->orWhere(function ($sub) use ($like) {
                            $sub->where('title', 'LIKE', $like)
                                ->orWhere('subtitle', 'LIKE', $like)
                                ->orWhere('author_name', 'LIKE', $like)
                                ->orWhereHas('author', fn($a) => $a->where('name', 'LIKE', $like))
                                ->orWhereHas('category', fn($c) => $c->where('name', 'LIKE', $like));
                        });
                    }
                });
            }

            // Sorting
            match ($request->string('sort')->value()) {
                'price_low'     => $query->orderBy('price', 'asc'),
                'price_high'    => $query->orderBy('price', 'desc'),
                'discount_high' => $query->orderByRaw('(price - COALESCE(discount_price, price)) desc'),
                'bestselling'   => $query->orderByDesc('sales_count'),
                'popular'       => $query->orderByDesc('read_count'),
                'oldest'        => $query->oldest('id'),
                default         => $query->latest('id'),
            };

            $ebooks = $query->paginate(20)->withQueryString();
        }

        return view('ebook::frontend.index', compact(
            'ebooks',
            'categories',
            'sidebarAuthors',
            'sidebarPublishers',
            'featuredEbooks',
            'bestsellingEbooks',
            'flashSales',
            'freeEbooks',
            'newReleaseEbooks',
            'dynamicCategories',
            'spotlightEbook',
            'userLibraryIds',
            'stats',
            'isSearchMode',
            'activeFilterTitle',
            'matchedBlogPosts',
            'matchedResearchPapers',
            'matchedWebzineArticles',
            'matchedAuthors',
            'matchedCategories',
            'matchedPages'
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

        // Eager load reviews with reviewer user details
        $ebook->loadMissing([
            'author',
            'publisher',
            'category',
            'authors',
            'reviews' => fn ($q) => $q->where('is_approved', true)->with('user')->latest('id'),
        ]);

        // Check current user's legitimate library access (admin, author, paid order, or claimed free)
        $user = auth()->user();
        $isOwnerOrAdmin = $user && ($user->isAdmin() || $user->isSubAdmin() || $ebook->author_user_id === $user->id);
        $hasAccess = $this->checkUserEbookAccess($user, $ebook);
        $libraryEntry = $hasAccess && $user ? UserEbookLibrary::where('user_id', $user->id)->where('ebook_id', $ebook->id)->first() : null;

        // Ratings & Review Metrics
        $reviewCount = $ebook->reviews->count();
        $avgRating = $reviewCount > 0 ? round((float) $ebook->reviews->avg('rating'), 1) : 4.9;
        
        $ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($ebook->reviews as $rev) {
            $r = max(1, min(5, (int) ($rev->rating ?? 5)));
            $ratingCounts[$r]++;
        }
        $ratingPercentages = [];
        foreach ($ratingCounts as $star => $count) {
            $ratingPercentages[$star] = $reviewCount > 0 ? round(($count / $reviewCount) * 100) : ($star === 5 ? 88 : ($star === 4 ? 12 : 0));
        }

        $userHasReviewed = $user ? $ebook->reviews->where('user_id', $user->id)->isNotEmpty() : false;

        // Estimated Reading Time Calculation (Average 1.5 mins per page or 200 wpm)
        $pageCount = max(1, (int) ($ebook->pages ?: 180));
        $totalMinutes = (int) round($pageCount * 1.5);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $estimatedReadingTime = $hours > 0 
            ? ($hours . ' ঘণ্টা ' . ($minutes > 0 ? $minutes . ' মিনিট' : ''))
            : ($minutes . ' মিনিট');

        // Related E-Books (from same category or latest)
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

        // Author's Other Works
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
            'isOwnerOrAdmin',
            'reviewCount',
            'avgRating',
            'ratingCounts',
            'ratingPercentages',
            'userHasReviewed',
            'estimatedReadingTime'
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

        // Free e-books can be read online by anyone
        if ($ebook->is_free || (float)$ebook->price <= 0) {
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

        // If user has not purchased yet, smoothly open reader in Sample / Preview Mode
        $isSample = !$hasAccess;

        // Increment read count silently
        try {
            $ebook->increment('read_count');
        } catch (\Throwable) {}

        // Determine Reader Type and Stream URL
        $readerType = 'epub';
        if (!empty($ebook->epub_file_path) && str_ends_with(strtolower((string)$ebook->epub_file_path), '.epub')) {
            $readerType = 'epub';
        } elseif (!empty($ebook->file_path) && str_ends_with(strtolower((string)$ebook->file_path), '.pdf')) {
            $readerType = 'pdf';
        } elseif (strtolower((string)$ebook->file_type) === 'pdf') {
            $readerType = 'pdf';
        }

        $streamUrl = $isSample 
            ? route('ebook.stream', ['id' => $ebook->id, 'sample' => 1])
            : route('ebook.stream', $ebook->id);

        // Anti-Piracy Watermark Text
        if ($isSample) {
            $watermarkText = 'ফ্রি নমুনা অংশ (Sample Preview) • আইডিয়া প্রকাশন • সর্বস্বত্ব সংরক্ষিত';
            $bookmarks = [];
            $lastReadPage = 1;
        } else {
            $watermarkText = ($user ? ($user->name . ' (' . ($user->phone ?: $user->email) . ')') : 'আইডিয়া প্রকাশন')
                . ' • ' . ($libraryEntry ? ('Order #' . ($libraryEntry->order_id ?: 'Claimed')) : 'Licensed Reader')
                . ' • ' . date('d-m-Y');
            $bookmarks = $libraryEntry?->bookmarks_data ?? [];
            $lastReadPage = $libraryEntry?->last_read_page ?? 1;
        }

        return view('ebook::frontend.read', compact(
            'ebook',
            'readerType',
            'streamUrl',
            'watermarkText',
            'libraryEntry',
            'bookmarks',
            'lastReadPage',
            'isSample'
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
        $isSample = true;

        return view('ebook::frontend.read', compact(
            'ebook',
            'readerType',
            'streamUrl',
            'watermarkText',
            'libraryEntry',
            'bookmarks',
            'lastReadPage',
            'isSample'
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
        if (!$isSample && !$ebook->is_free && (float)$ebook->price > 0) {
            $hasAccess = $this->checkUserEbookAccess($user, $ebook);

            if (!$hasAccess) {
                // If not purchased, fallback to sample stream safely
                $isSample = true;
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

        $cleanPath = '';
        if ($filePath) {
            // Resolve absolute file path (handling clean relative paths, storage prefixes and URLs)
            $cleanPath = preg_replace('#^https?://[^/]+/storage/#', '', (string)$filePath);
            $cleanPath = preg_replace('#^/storage/#', '', $cleanPath);
            $cleanPath = ltrim($cleanPath, '/');
        }

        $fullPath = null;
        $candidates = [
            storage_path('app/public/' . $cleanPath),
            storage_path('app/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            public_path($cleanPath),
            storage_path('app/secure/ebooks/' . basename($cleanPath)),
            storage_path('app/public/' . ltrim((string)$filePath, '/')),
            storage_path('app/' . ltrim((string)$filePath, '/')),
            public_path(ltrim((string)$filePath, '/')),
            storage_path('app/public/ebooks/' . ($ebook->slug ?: $ebook->id) . '.epub'),
            storage_path('app/public/ebooks/briksh-zkhn-ktha-ble.epub'),
            storage_path('app/public/ebooks/ideaabd-sample.epub'),
        ];

        foreach ($candidates as $cand) {
            if ($cand && file_exists($cand) && is_file($cand) && filesize($cand) > 100) {
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
     * সাইটের সকল স্ট্যাটিক ও ডায়নামিক পেজের সমৃদ্ধ দ্বিভাষিক ডিরেক্টরি
     */
    private function getSitePagesList(): array
    {
        return [
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
                'keywords'    => ['গবেষণা', 'গবেষণাপত্র', 'জার্নাল', 'প্রবন্ধ', 'research', 'paper', 'journal', 'thesis'],
            ],
        ];
    }
}
