<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $canUseBooks = \Illuminate\Support\Facades\Cache::remember('db_has_books_tables', 86400, function () {
            try {
                return DB::getSchemaBuilder()->hasTable('books') && DB::getSchemaBuilder()->hasTable('categories');
            } catch (\Throwable) {
                return false;
            }
        });

        $books = collect();
        $recentlySold = collect();
        $bestSellerEbooks = collect();
        $flashSales = collect();
        $recentlyViewedBooks = collect();
        $dynamicCategories = collect();
        $sidebarAuthors = collect();
        $sidebarPublishers = collect();
        $topSeller = null;

        $blogPosts = collect();
        $latestBlogPosts = collect();
        $mostReadBlogPosts = collect();
        $topHonorariumBlogPosts = collect();
        $blogCategories = collect();
        $categoryBooks = collect();
        $categoryGridCards = collect();

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('blog_posts')) {
                $blogBaseQuery = fn() => \Modules\Blog\Models\BlogPost::query()
                    ->with(['category', 'author'])
                    ->where(function($q) {
                        $q->where('status', 'published')
                          ->orWhere('mod_status', 'approved');
                    });

                // Column 1: Latest Published Posts
                $latestBlogPosts = $blogBaseQuery()
                    ->latest('published_at')
                    ->latest('id')
                    ->take(4)
                    ->get();
                $blogPosts = $latestBlogPosts;

                // Column 2.1: Most Read Posts
                $mostReadBlogPosts = $blogBaseQuery()
                    ->orderByDesc('view_count')
                    ->latest('id')
                    ->take(3)
                    ->get();

                // Column 2.2: Top Honorarium Posts
                try {
                    $topHonorariumBlogPosts = $blogBaseQuery()
                        ->withSum(['honorariums' => fn($q) => $q->where('payment_status', 'completed')], 'amount')
                        ->orderByDesc('honorariums_sum_amount')
                        ->latest('id')
                        ->take(3)
                        ->get();
                } catch (\Throwable) {
                    $topHonorariumBlogPosts = collect();
                }

                if ($topHonorariumBlogPosts->isEmpty() || ($topHonorariumBlogPosts->first()->honorariums_sum_amount ?? 0) <= 0) {
                    $topHonorariumBlogPosts = $blogBaseQuery()
                        ->where('is_featured', true)
                        ->latest('id')
                        ->take(3)
                        ->get();
                    if ($topHonorariumBlogPosts->isEmpty()) {
                        $topHonorariumBlogPosts = $latestBlogPosts->slice(1, 3);
                    }
                }

                // Column 3: Category with articles
                if (\Illuminate\Support\Facades\Schema::hasTable('blog_categories')) {
                    $blogCategories = \Modules\Blog\Models\BlogCategory::query()
                        ->whereHas('posts', function($sq) {
                            $sq->where('status', 'published')
                               ->orWhere('mod_status', 'approved');
                        })
                        ->withCount(['posts' => function($q) {
                            $q->where(function($sq) {
                                $sq->where('status', 'published')
                                   ->orWhere('mod_status', 'approved');
                            });
                        }])
                        ->with(['posts' => function($q) {
                            $q->where(function($sq) {
                                $sq->where('status', 'published')
                                   ->orWhere('mod_status', 'approved');
                            })->latest('id')->take(2);
                        }])
                        ->orderByDesc('posts_count')
                        ->take(6)
                        ->get();
                }
            }
        } catch (\Throwable $e) {}

        if ($canUseBooks) {
            $books = \Modules\Book\Models\Book::query()
                ->with(['category', 'authors', 'publisher'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->latest('id')
                ->take(12)
                ->get();

            $recentlySold = \Modules\Book\Models\Book::query()
                ->with(['authors'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->orderByDesc('sales_count')
                ->latest('id')
                ->take(10)
                ->get();

            $bestSellerEbooks = \Modules\Book\Models\Book::query()
                ->with(['authors'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->where('format', 'ebook')
                ->orderByDesc('sales_count')
                ->latest('id')
                ->take(10)
                ->get();

            $flashSales = \Modules\Book\Models\Book::query()
                ->with(['authors'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->whereNotNull('discount_price')
                ->where('discount_price', '>', 0)
                ->whereColumn('discount_price', '<', 'price')
                ->latest('id')
                ->take(10)
                ->get();

            $recentlyViewedIds = session()->get('recently_viewed_books', []);
            if (!empty($recentlyViewedIds)) {
                $recentlyViewedBooks = \Modules\Book\Models\Book::query()
                    ->with(['authors'])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->whereIn('id', $recentlyViewedIds)
                    ->where('is_active', true)
                    ->get()
                    ->sortBy(function($b) use ($recentlyViewedIds) {
                        return array_search($b->id, $recentlyViewedIds);
                    });
            }

            $categoryGridCards = collect();

            // 1. ফ্ল্যাশ সেলস (Flash Sales - 2 Books)
            $flashSaleBooks = \Modules\Book\Models\Book::query()
                ->with(['authors', 'category'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->whereNotNull('discount_price')
                ->where('discount_price', '>', 0)
                ->whereColumn('discount_price', '<', 'price')
                ->latest('id')
                ->take(2)
                ->get();
            if ($flashSaleBooks->isEmpty()) {
                $flashSaleBooks = $books->slice(0, 2);
            }
            if ($flashSaleBooks->isNotEmpty()) {
                $categoryGridCards->push((object)[
                    'title' => 'ফ্ল্যাশ সেলস',
                    'icon' => 'fa-solid fa-bolt text-warning',
                    'badge' => 'সীমিত অফার',
                    'badge_class' => 'bg-warning text-dark',
                    'url' => route('book.index', ['filter' => 'flash_sale']),
                    'books' => $flashSaleBooks,
                ]);
            }

            // 2. নতুন কালেকশন (New Arrivals - 2 Books)
            $newBooks = \Modules\Book\Models\Book::query()
                ->with(['authors', 'category'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->latest('id')
                ->take(2)
                ->get();
            if ($newBooks->isNotEmpty()) {
                $categoryGridCards->push((object)[
                    'title' => 'নতুন কালেকশন',
                    'icon' => 'fa-solid fa-sparkles text-success',
                    'badge' => 'নতুন বই',
                    'badge_class' => 'bg-success text-white',
                    'url' => route('book.index', ['sort' => 'latest']),
                    'books' => $newBooks,
                ]);
            }

            // 3. সর্বাধিক বিক্রিত বই (Bestsellers - 2 Books)
            $bestsellers = \Modules\Book\Models\Book::query()
                ->with(['authors', 'category'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->orderByDesc('sales_count')
                ->take(2)
                ->get();
            if ($bestsellers->isNotEmpty()) {
                $categoryGridCards->push((object)[
                    'title' => 'সর্বাধিক বিক্রিত বই',
                    'icon' => 'fa-solid fa-fire text-danger',
                    'badge' => 'টপ চার্ট',
                    'badge_class' => 'bg-danger text-white',
                    'url' => route('book.index', ['sort' => 'bestselling']),
                    'books' => $bestsellers,
                ]);
            }

            // 4. আইডিয়া প্রকাশনের বই (Idea Publications Books - 2 Books)
            $ideaBooks = \Modules\Book\Models\Book::query()
                ->with(['authors', 'category'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereHas('publisher', fn($pq) => $pq->where('name', 'LIKE', '%আইডিয়া%')->orWhere('name', 'LIKE', '%Idea%'))
                      ->orWhere('author_name', 'LIKE', '%আইডিয়া%');
                })
                ->latest('id')
                ->take(2)
                ->get();
            if ($ideaBooks->isEmpty()) {
                $ideaBooks = $books->slice(2, 2);
            }
            if ($ideaBooks->isNotEmpty()) {
                $categoryGridCards->push((object)[
                    'title' => 'আইডিয়া প্রকাশনের বই',
                    'icon' => 'fa-solid fa-feather-pointed text-primary',
                    'badge' => 'অরিজিনাল',
                    'badge_class' => 'bg-primary text-white',
                    'url' => route('book.index'),
                    'books' => $ideaBooks,
                ]);
            }

            // 5. প্রি-অর্ডার বই (Pre-order Books - 2 Books)
            $preOrderBooks = \Modules\Book\Models\Book::query()
                ->with(['authors', 'category'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->where('stock_status', 'pre_order')
                ->latest('id')
                ->take(2)
                ->get();
            if ($preOrderBooks->isEmpty()) {
                $preOrderBooks = $books->slice(4, 2);
            }
            if ($preOrderBooks->isNotEmpty()) {
                $categoryGridCards->push((object)[
                    'title' => 'প্রি-অর্ডার বই',
                    'icon' => 'fa-solid fa-clock-rotate-left text-warning',
                    'badge' => 'প্রি-অর্ডার',
                    'badge_class' => 'bg-warning text-dark',
                    'url' => route('book.index', ['stock_status' => 'pre_order']),
                    'books' => $preOrderBooks,
                ]);
            }

            // 6. স্পেশাল অফার বই (Special Offer Books - 2 Books)
            $specialOffers = \Modules\Book\Models\Book::query()
                ->with(['authors', 'category'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->whereNotNull('discount_price')
                ->where('discount_price', '>', 0)
                ->whereColumn('discount_price', '<', 'price')
                ->skip(2)
                ->take(2)
                ->get();
            if ($specialOffers->isEmpty()) {
                $specialOffers = $books->slice(6, 2);
            }
            if ($specialOffers->isNotEmpty()) {
                $categoryGridCards->push((object)[
                    'title' => 'স্পেশাল অফার বই',
                    'icon' => 'fa-solid fa-percent text-danger',
                    'badge' => 'বিশেষ ছাড়',
                    'badge_class' => 'bg-danger text-white',
                    'url' => route('book.index', ['filter' => 'discounted']),
                    'books' => $specialOffers,
                ]);
            }

            // 7. আইডিয়া প্রকাশনের বেস্টসেলার (Idea Bestsellers - 2 Books)
            $ideaBestsellers = \Modules\Book\Models\Book::query()
                ->with(['authors', 'category'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->orderByDesc('sales_count')
                ->skip(2)
                ->take(2)
                ->get();
            if ($ideaBestsellers->isEmpty()) {
                $ideaBestsellers = $books->slice(8, 2);
            }
            if ($ideaBestsellers->isNotEmpty()) {
                $categoryGridCards->push((object)[
                    'title' => 'আইডিয়া বেস্টসেলার',
                    'icon' => 'fa-solid fa-crown text-warning',
                    'badge' => 'আইডিয়া সেরা',
                    'badge_class' => 'bg-warning text-dark',
                    'url' => route('book.index', ['sort' => 'bestselling']),
                    'books' => $ideaBestsellers,
                ]);
            }

            // 8. স্টক ক্লিয়ারেন্স অফার (Stock Clearance Books - 2 Books)
            $clearanceBooks = \Modules\Book\Models\Book::query()
                ->with(['authors', 'category'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->whereNotNull('discount_price')
                ->where('discount_price', '>', 0)
                ->orderByDesc('discount_price')
                ->skip(4)
                ->take(2)
                ->get();
            if ($clearanceBooks->isEmpty()) {
                $clearanceBooks = $books->slice(10, 2);
            }
            if ($clearanceBooks->isNotEmpty()) {
                $categoryGridCards->push((object)[
                    'title' => 'স্টক ক্লিয়ারেন্স অফার',
                    'icon' => 'fa-solid fa-tags text-info',
                    'badge' => 'ক্লিয়ারেন্স',
                    'badge_class' => 'bg-info text-dark',
                    'url' => route('book.index', ['filter' => 'clearance']),
                    'books' => $clearanceBooks,
                ]);
            }

            // 8. বিষয়ভিত্তিক ডায়নামিক ক্যাটাগরিগুলো (উপন্যাস, ইসলামি বই, শিশু-কিশোর ইত্যাদি)
            $dynamicCategories = \Modules\Book\Models\Category::query()
                ->where('is_active', true)
                ->whereHas('books', fn($q) => $q->where('is_active', true))
                ->withCount(['books' => fn($q) => $q->where('is_active', true)])
                ->orderByDesc('books_count')
                ->take(16)
                ->get();

            foreach ($dynamicCategories as $dCat) {
                $cBooks = \Modules\Book\Models\Book::query()
                    ->where('is_active', true)
                    ->where('category_id', $dCat->id)
                    ->with(['authors', 'category'])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->latest('id')
                    ->take(2)
                    ->get();
                if ($cBooks->isNotEmpty()) {
                    $categoryGridCards->push((object)[
                        'title' => $dCat->name,
                        'icon' => 'fa-solid fa-bookmark text-primary',
                        'badge' => ($dCat->books_count ?? $cBooks->count()) . 'টি বই',
                        'badge_class' => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
                        'url' => route('book.index', ['category' => $dCat->slug]),
                        'books' => $cBooks,
                    ]);
                }
            }

            $sidebarAuthors = \Modules\Author\Models\Author::query()
                ->withCount('books')
                ->orderByDesc('books_count')
                ->take(16)
                ->get();

            $sidebarPublishers = \Modules\Publisher\Models\Publisher::query()
                ->withCount('books')
                ->orderByDesc('books_count')
                ->take(10)
                ->get();

            $topSeller = \Modules\Book\Models\Book::query()
                ->with(['authors', 'category'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->orderByDesc('sales_count')
                ->first() ?? $books->first();
        }

        return view('frontend.home', compact(
            'books', 'recentlySold', 'bestSellerEbooks', 'flashSales',
            'recentlyViewedBooks', 'dynamicCategories', 'categoryBooks', 'categoryGridCards',
            'blogPosts', 'latestBlogPosts', 'mostReadBlogPosts', 'topHonorariumBlogPosts', 'blogCategories',
            'sidebarAuthors', 'sidebarPublishers', 'topSeller'
        ));
    }
}
