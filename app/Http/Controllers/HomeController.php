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
                ->with(['category', 'authors', 'publisher'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->orderByDesc('sales_count')
                ->latest('id')
                ->take(12)
                ->get();
            if ($recentlySold->isEmpty()) {
                $recentlySold = $books;
            }

            $bestSellerEbooks = \Modules\Book\Models\Book::query()
                ->with(['category', 'authors', 'publisher'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->where('format', 'ebook')
                ->orderByDesc('sales_count')
                ->latest('id')
                ->take(12)
                ->get();

            $flashSales = \Modules\Book\Models\Book::query()
                ->with(['category', 'authors', 'publisher'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->whereNotNull('discount_price')
                ->where('discount_price', '>', 0)
                ->whereColumn('discount_price', '<', 'price')
                ->latest('id')
                ->take(12)
                ->get();
            if ($flashSales->isEmpty()) {
                $flashSales = $books->slice(0, 8);
            }

            $ideaSpecialBooks = \Modules\Book\Models\Book::query()
                ->with(['category', 'authors', 'publisher'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereHas('publisher', fn($pq) => $pq->where('name', 'LIKE', '%আইডিয়া%')->orWhere('name', 'LIKE', '%Idea%'))
                      ->orWhere('author_name', 'LIKE', '%আইডিয়া%');
                })
                ->latest('id')
                ->take(12)
                ->get();
            if ($ideaSpecialBooks->isEmpty()) {
                $ideaSpecialBooks = $books->slice(2, 8);
            }

            $recentlyViewedIds = session()->get('recently_viewed_books', []);
            if (!empty($recentlyViewedIds)) {
                $recentlyViewedBooks = \Modules\Book\Models\Book::query()
                    ->with(['category', 'authors', 'publisher'])
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

            // বিষয়ভিত্তিক ডায়নামিক ক্যাটাগরিগুলো (উপন্যাস, ইসলামি বই, শিশু-কিশোর ইত্যাদি)
            $dynamicCategories = \Modules\Book\Models\Category::query()
                ->where('is_active', true)
                ->whereHas('books', fn($q) => $q->where('is_active', true))
                ->withCount(['books' => fn($q) => $q->where('is_active', true)])
                ->orderByDesc('books_count')
                ->take(16)
                ->get();

            $sidebarAuthors = \Modules\Author\Models\Author::query()
                ->withCount('books')
                ->orderByDesc('books_count')
                ->take(16)
                ->get();

            $sidebarPublishers = \Modules\Publisher\Models\Publisher::query()
                ->withCount('books')
                ->orderByDesc('books_count')
                ->take(12)
                ->get();

            $preOrderBooks = \Modules\Book\Models\Book::query()
                ->with(['category', 'authors', 'publisher'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', true)
                ->where('stock_status', 'pre_order')
                ->latest('id')
                ->take(12)
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
            'books', 'recentlySold', 'bestSellerEbooks', 'flashSales', 'ideaSpecialBooks', 'preOrderBooks',
            'recentlyViewedBooks', 'dynamicCategories', 'categoryBooks', 'categoryGridCards',
            'blogPosts', 'latestBlogPosts', 'mostReadBlogPosts', 'topHonorariumBlogPosts', 'blogCategories',
            'sidebarAuthors', 'sidebarPublishers', 'topSeller'
        ));
    }
}
