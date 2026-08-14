<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $canUseBooks = false;

        try {
            $canUseBooks = DB::getSchemaBuilder()->hasTable('books') && DB::getSchemaBuilder()->hasTable('categories');
        } catch (\Throwable) {
            $canUseBooks = false;
        }

        $books = collect();
        $recentlySold = collect();
        $bestSellerEbooks = collect();
        $flashSales = collect();
        $recentlyViewedBooks = collect();
        $dynamicCategories = collect();
        $sidebarAuthors = collect();
        $sidebarPublishers = collect();

        if ($canUseBooks) {
            $books = \Modules\Book\Models\Book::query()
                ->with(['category', 'authors', 'publisher'])
                ->where('is_active', true)
                ->latest()
                ->take(12)
                ->get();

            $recentlySold = \Modules\Book\Models\Book::query()
                ->with(['authors'])
                ->where('is_active', true)
                ->orderByDesc('sales_count')
                ->take(10)
                ->get();

            $bestSellerEbooks = \Modules\Book\Models\Book::query()
                ->with(['authors'])
                ->where('is_active', true)
                ->where('format', 'ebook')
                ->orderByDesc('sales_count')
                ->take(10)
                ->get();

            $flashSales = \Modules\Book\Models\Book::query()
                ->with(['authors'])
                ->where('is_active', true)
                ->whereNotNull('discount_price')
                ->whereColumn('discount_price', '<', 'price')
                ->inRandomOrder()
                ->take(10)
                ->get();

            $recentlyViewedIds = session()->get('recently_viewed_books', []);
            if (!empty($recentlyViewedIds)) {
                $recentlyViewedBooks = \Modules\Book\Models\Book::query()
                    ->with(['authors'])
                    ->whereIn('id', $recentlyViewedIds)
                    ->where('is_active', true)
                    ->get()
                    ->sortBy(function($b) use ($recentlyViewedIds) {
                        return array_search($b->id, $recentlyViewedIds);
                    });
            }

            $dynamicCategories = \Modules\Book\Models\Category::query()
                ->where('is_active', true)
                ->withCount('books')
                ->orderByDesc('books_count')
                ->take(10)
                ->get();

            $sidebarAuthors = \Modules\Author\Models\Author::query()
                ->withCount('books')
                ->orderByDesc('books_count')
                ->take(10)
                ->get();

            $sidebarPublishers = \Modules\Publisher\Models\Publisher::query()
                ->withCount('books')
                ->orderByDesc('books_count')
                ->take(10)
                ->get();
        }

        return view('frontend.home', compact(
            'books', 'recentlySold', 'bestSellerEbooks', 'flashSales',
            'recentlyViewedBooks', 'dynamicCategories', 'sidebarAuthors', 'sidebarPublishers'
        ));
    }
}
