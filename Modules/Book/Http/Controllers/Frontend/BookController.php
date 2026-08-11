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

        if ($canUseBooks) {
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
                ->when($request->filled('rating'), fn ($q) =>
                    $q->having('reviews_avg_rating', '>=', $request->float('rating'))
                )
                ->when($request->filled('format'), fn ($q) =>
                    $q->where('format', $request->string('format'))
                )
                ->when($request->boolean('in_stock'), fn ($q) =>
                    $q->where('stock_quantity', '>', 0)
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
                        'avg_rating'  => $q->orderByDesc('reviews_avg_rating'),
                        'bestselling' => $q->orderByDesc('sales_count'),
                        'oldest'      => $q->oldest(),
                        default       => $q->latest(),
                    };
                }, fn ($q) => $q->latest())
                ->paginate(16)
                ->withQueryString();

            $categories = Category::query()
                ->where('is_active', true)
                ->withCount('books')
                ->get(['id', 'name', 'slug']);
        }

        return view('book::frontend.index', compact('books', 'categories'));
    }

    /**
     * একক বইয়ের ডিটেইলস ও ক্রস-সেলিং (Frequently Bought Together)
     */
    public function show(string $slug): View
    {
        $book = Book::query()
            ->with(['category', 'authors', 'publisher', 'reviews.user'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // ১. একসাথে কেনা উপযোগী বই (Frequently Bought Together)
        $frequentlyBoughtTogether = Book::query()
            ->where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->take(2)
            ->get();

        // ২. পাঠকদের পছন্দ অনুযায়ী সম্পর্কিত বই (Customers also viewed)
        $relatedBooks = Book::query()
            ->with(['authors'])
            ->where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->where('is_active', true)
            ->take(6)
            ->get();

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