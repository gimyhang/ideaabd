<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Book\Models\Book;
use Modules\Book\Models\Category;
use Modules\Author\Models\Author;
use Modules\Publisher\Models\Publisher;
use App\Models\PublisherPurchase;
use App\Models\PublisherPayment;

class PublisherPortalController extends Controller
{
    /**
     * Get or create publisher record for current authenticated user.
     */
    private function getPublisher()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        // If admin is viewing, allow selecting or use first publisher
        if ($user->isAdmin()) {
            return Publisher::first() ?: Publisher::create([
                'name' => 'আইডিয়া প্রকাশন',
                'slug' => 'idea-prokashon',
                'email' => 'ideapbd@gmail.com',
                'is_active' => true,
                'is_verified' => true,
            ]);
        }

        $publisher = $user->getPublisherRecord();
        if (!$publisher) {
            $pName = !empty($user->reg_data['publisher_name']) ? $user->reg_data['publisher_name'] : $user->name;
            $slug = Str::slug($pName) ?: 'publisher-' . $user->id;
            if (Publisher::where('slug', $slug)->exists()) {
                $slug .= '-' . $user->id;
            }
            $publisher = Publisher::create([
                'name' => $pName,
                'slug' => $slug,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->reg_data['address'] ?? null,
                'is_active' => true,
                'is_verified' => true,
            ]);
        }

        return $publisher;
    }

    /**
     * Publisher main dashboard & catalog view.
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $publisher = $this->getPublisher();

        $search = $request->string('search')->trim()->value();
        $categoryId = $request->input('category_id');
        $stockFilter = $request->string('stock')->trim()->value();
        $sort = $request->string('sort')->trim()->value() ?: 'latest';
        $activeTab = $request->string('tab')->trim()->value() ?: 'overview';
        $purchaseDateFilter = $request->string('date_filter')->trim()->value() ?: 'all';
        $selectedDate = $request->string('date')->trim()->value() ?: now()->format('Y-m-d');

        // Publisher's Books Query
        $booksQuery = Book::where('publisher_id', $publisher->id)
            ->with(['category', 'authorLink', 'authors'])
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('title', 'like', $like)
                      ->orWhere('subtitle', 'like', $like)
                      ->orWhere('isbn', 'like', $like)
                      ->orWhere('sku', 'like', $like)
                      ->orWhere('author_name', 'like', $like);
                });
            })
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($stockFilter === 'pre_order', fn ($q) => $q->where('stock_status', 'pre_order'))
            ->when($stockFilter === 'low', fn ($q) => $q->where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0))
            ->when($stockFilter === 'out', fn ($q) => $q->where('stock_quantity', '<=', 0))
            ->when($stockFilter === 'in_stock', fn ($q) => $q->where('stock_quantity', '>', 5));

        match ($sort) {
            'oldest'     => $booksQuery->oldest('id'),
            'title_asc'  => $booksQuery->orderBy('title', 'asc'),
            'title_desc' => $booksQuery->orderBy('title', 'desc'),
            'price_low'  => $booksQuery->orderBy('price', 'asc'),
            'price_high' => $booksQuery->orderBy('price', 'desc'),
            'stock_low'  => $booksQuery->orderBy('stock_quantity', 'asc'),
            'stock_high' => $booksQuery->orderByDesc('stock_quantity'),
            default      => $booksQuery->latest('id'),
        };

        $books = $booksQuery->paginate(15)->withQueryString();

        // Summary Statistics
        $totalBooks = Book::where('publisher_id', $publisher->id)->count();
        $activeBooks = Book::where('publisher_id', $publisher->id)->where('is_active', true)->count();
        $preOrderBooks = Book::where('publisher_id', $publisher->id)->where('stock_status', 'pre_order')->count();
        $lowStockBooks = Book::where('publisher_id', $publisher->id)->where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0)->count();
        $outStockBooks = Book::where('publisher_id', $publisher->id)->where('stock_quantity', '<=', 0)->count();
        $totalStockUnits = Book::where('publisher_id', $publisher->id)->sum('stock_quantity');

        // All Purchases Query
        $purchasesQuery = PublisherPurchase::where('publisher_id', $publisher->id)
            ->with(['items.book', 'payments']);

        $allPurchases = (clone $purchasesQuery)->latest('purchase_date')->paginate(15, ['*'], 'purchase_page');

        // Today's Purchase Orders (Rokomari Company Panel Style)
        $todayDate = now()->toDateString();
        $todayPurchasesQuery = PublisherPurchase::where('publisher_id', $publisher->id)
            ->with(['items.book', 'payments'])
            ->when($purchaseDateFilter === 'today', fn($q) => $q->whereDate('purchase_date', $todayDate))
            ->when($purchaseDateFilter === 'yesterday', fn($q) => $q->whereDate('purchase_date', now()->subDay()->toDateString()))
            ->when($purchaseDateFilter === 'custom' && $selectedDate, fn($q) => $q->whereDate('purchase_date', $selectedDate));

        $todayPurchases = $todayPurchasesQuery->latest('purchase_date')->get();

        // If no purchase recorded for today specifically in filter, get recent purchase items for display
        $recentPurchases = (clone $purchasesQuery)->latest('purchase_date')->take(10)->get();

        $totalPurchasesAmount = PublisherPurchase::where('publisher_id', $publisher->id)->sum('total_amount');
        $totalPaidAmount = PublisherPurchase::where('publisher_id', $publisher->id)->sum('paid_amount');
        $totalDueAmount = max(0, $totalPurchasesAmount - $totalPaidAmount);

        $todayPurchasesAmount = PublisherPurchase::where('publisher_id', $publisher->id)->whereDate('purchase_date', $todayDate)->sum('total_amount');
        $todayItemsCount = \App\Models\PublisherPurchaseItem::whereHas('purchase', function($q) use ($publisher, $todayDate) {
            $q->where('publisher_id', $publisher->id)->whereDate('purchase_date', $todayDate);
        })->sum('quantity');

        $payments = PublisherPayment::where('publisher_id', $publisher->id)->latest('payment_date')->take(15)->get();

        // Form Select Data
        $categories = Category::where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();
        $authors = Author::where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();

        // Edit Book if edit_id is present
        $editBook = null;
        if ($request->filled('edit_id')) {
            $editBook = Book::where('publisher_id', $publisher->id)->where('id', $request->edit_id)->first();
        }

        return view('publisher.dashboard', compact(
            'user',
            'publisher',
            'books',
            'totalBooks',
            'activeBooks',
            'preOrderBooks',
            'lowStockBooks',
            'outStockBooks',
            'totalStockUnits',
            'allPurchases',
            'todayPurchases',
            'recentPurchases',
            'totalPurchasesAmount',
            'totalPaidAmount',
            'totalDueAmount',
            'todayPurchasesAmount',
            'todayItemsCount',
            'payments',
            'categories',
            'authors',
            'editBook',
            'activeTab',
            'purchaseDateFilter',
            'selectedDate'
        ));
    }

    /**
     * View / Print Challan for a specific purchase order.
     */
    public function printChallan($id)
    {
        $publisher = $this->getPublisher();
        $purchase = PublisherPurchase::where('publisher_id', $publisher->id)
            ->with(['items.book', 'publisher', 'creator'])
            ->findOrFail($id);

        return view('publisher.challan', compact('publisher', 'purchase'));
    }

    /**
     * Store a newly created book by the publisher.
     */
    public function storeBook(Request $request)
    {
        $publisher = $this->getPublisher();

        $validated = $request->validate([
            'title'                    => 'required|string|max:255',
            'subtitle'                 => 'nullable|string|max:255',
            'product_type'             => 'nullable|string|max:50',
            'category_id'              => 'required|exists:categories,id',
            'author_id'                => 'nullable|exists:authors,id',
            'author_ids'               => 'nullable|array',
            'author_ids.*'             => 'nullable|exists:authors,id',
            'author_name'              => 'nullable|string|max:255',
            'translator_name'          => 'nullable|string|max:255',
            'editor_name'              => 'nullable|string|max:255',
            'language'                 => 'nullable|string|max:50',
            'country'                  => 'nullable|string|max:100',
            'cover_type'               => 'required|in:paperback,hardcover,board_book,spiral,both',
            'price'                    => 'nullable|numeric|min:0',
            'discount_price'           => 'nullable|numeric|min:0',
            'hardcover_price'          => 'nullable|numeric|min:0',
            'hardcover_discount_price' => 'nullable|numeric|min:0',
            'cost_price'               => 'nullable|numeric|min:0',
            'stock_quantity'           => 'required|integer|min:0',
            'stock_status'             => 'required|in:in_stock,low,out,pre_order,upcoming',
            'edition'                  => 'nullable|string|max:100',
            'isbn'                     => 'nullable|string|max:50',
            'number_of_pages'          => 'nullable|integer|min:1',
            'summary'                  => 'nullable|string',
            'description'              => 'nullable|string',
            'cover_image'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'pdf_sample'               => 'nullable|mimes:pdf|max:10240',
        ]);

        // Handle Cover Image Upload
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('books/covers', 'public');
        }

        // Handle Sample PDF Upload
        $pdfPath = null;
        if ($request->hasFile('pdf_sample')) {
            $pdfPath = $request->file('pdf_sample')->store('books/samples', 'public');
        }

        // Generate Slug
        $slug = Str::slug($validated['title']) ?: 'book-' . time();
        if (Book::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(100, 999);
        }

        // Generate SKU if not provided
        $sku = !empty($validated['isbn']) ? $validated['isbn'] : 'IDP-PUB-' . $publisher->id . '-' . rand(1000, 9999);

        // Resolve Author Name
        $authorName = $validated['author_name'] ?? null;
        if (!empty($validated['author_id'])) {
            $authorObj = Author::find($validated['author_id']);
            if ($authorObj) {
                $authorName = $authorObj->name;
            }
        }

        $book = Book::create([
            'title'                    => $validated['title'],
            'subtitle'                 => $validated['subtitle'] ?? null,
            'slug'                     => $slug,
            'sku'                      => $sku,
            'isbn'                     => $validated['isbn'] ?? null,
            'product_type'             => $validated['product_type'] ?? 'book',
            'category_id'              => $validated['category_id'],
            'publisher_id'             => $publisher->id,
            'author_link_id'           => $validated['author_id'] ?? null,
            'author_name'              => $authorName,
            'translator_name'          => $validated['translator_name'] ?? null,
            'editor_name'              => $validated['editor_name'] ?? null,
            'language'                 => $validated['language'] ?? 'Bengali',
            'country'                  => $validated['country'] ?? 'Bangladesh',
            'cover_type'               => $validated['cover_type'],
            'price'                    => $validated['price'] ?? 0,
            'discount_price'           => $validated['discount_price'] ?? null,
            'hardcover_price'          => $validated['hardcover_price'] ?? null,
            'hardcover_discount_price' => $validated['hardcover_discount_price'] ?? null,
            'cost_price'               => $validated['cost_price'] ?? null,
            'stock_quantity'           => (int) $validated['stock_quantity'],
            'stock_status'             => $validated['stock_status'],
            'edition'                  => $validated['edition'] ?? null,
            'number_of_pages'          => $validated['number_of_pages'] ?? null,
            'summary'                  => $validated['summary'] ?? null,
            'description'              => $validated['description'] ?? null,
            'cover_image'              => $coverPath,
            'sample_pdf_path'          => $pdfPath,
            'is_active'                => true,
            'is_featured'              => false,
            'created_by'               => auth()->id(),
        ]);

        // Attach Multiple Authors if provided
        $allAuthorIds = array_filter((array) ($validated['author_ids'] ?? []));
        if (!empty($validated['author_id']) && !in_array($validated['author_id'], $allAuthorIds)) {
            $allAuthorIds[] = $validated['author_id'];
        }
        if (!empty($allAuthorIds)) {
            $book->authors()->sync($allAuthorIds);
        }

        return redirect()->route('publisher.dashboard', ['tab' => 'books'])
            ->with('success', "Book '{$book->title}' has been successfully added to your catalog!");
    }

    /**
     * Update an existing book of the publisher.
     */
    public function updateBook(Request $request, $id)
    {
        $publisher = $this->getPublisher();
        $book = Book::where('publisher_id', $publisher->id)->findOrFail($id);

        $validated = $request->validate([
            'title'                    => 'required|string|max:255',
            'subtitle'                 => 'nullable|string|max:255',
            'product_type'             => 'nullable|string|max:50',
            'category_id'              => 'required|exists:categories,id',
            'author_id'                => 'nullable|exists:authors,id',
            'author_ids'               => 'nullable|array',
            'author_ids.*'             => 'nullable|exists:authors,id',
            'author_name'              => 'nullable|string|max:255',
            'translator_name'          => 'nullable|string|max:255',
            'editor_name'              => 'nullable|string|max:255',
            'language'                 => 'nullable|string|max:50',
            'country'                  => 'nullable|string|max:100',
            'cover_type'               => 'required|in:paperback,hardcover,board_book,spiral,both',
            'price'                    => 'nullable|numeric|min:0',
            'discount_price'           => 'nullable|numeric|min:0',
            'hardcover_price'          => 'nullable|numeric|min:0',
            'hardcover_discount_price' => 'nullable|numeric|min:0',
            'cost_price'               => 'nullable|numeric|min:0',
            'stock_quantity'           => 'required|integer|min:0',
            'stock_status'             => 'required|in:in_stock,low,out,pre_order,upcoming',
            'edition'                  => 'nullable|string|max:100',
            'isbn'                     => 'nullable|string|max:50',
            'number_of_pages'          => 'nullable|integer|min:1',
            'summary'                  => 'nullable|string',
            'description'              => 'nullable|string',
            'cover_image'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'pdf_sample'               => 'nullable|mimes:pdf|max:10240',
        ]);

        $updates = [
            'title'                    => $validated['title'],
            'subtitle'                 => $validated['subtitle'] ?? null,
            'isbn'                     => $validated['isbn'] ?? null,
            'product_type'             => $validated['product_type'] ?? 'book',
            'category_id'              => $validated['category_id'],
            'author_link_id'           => $validated['author_id'] ?? null,
            'author_name'              => $validated['author_name'] ?? null,
            'translator_name'          => $validated['translator_name'] ?? null,
            'editor_name'              => $validated['editor_name'] ?? null,
            'language'                 => $validated['language'] ?? 'Bengali',
            'country'                  => $validated['country'] ?? 'Bangladesh',
            'cover_type'               => $validated['cover_type'],
            'price'                    => $validated['price'] ?? 0,
            'discount_price'           => $validated['discount_price'] ?? null,
            'hardcover_price'          => $validated['hardcover_price'] ?? null,
            'hardcover_discount_price' => $validated['hardcover_discount_price'] ?? null,
            'cost_price'               => $validated['cost_price'] ?? null,
            'stock_quantity'           => (int) $validated['stock_quantity'],
            'stock_status'             => $validated['stock_status'],
            'edition'                  => $validated['edition'] ?? null,
            'number_of_pages'          => $validated['number_of_pages'] ?? null,
            'summary'                  => $validated['summary'] ?? null,
            'description'              => $validated['description'] ?? null,
        ];

        if ($request->hasFile('cover_image')) {
            $updates['cover_image'] = $request->file('cover_image')->store('books/covers', 'public');
        }

        if ($request->hasFile('pdf_sample')) {
            $updates['sample_pdf_path'] = $request->file('pdf_sample')->store('books/samples', 'public');
        }

        if (!empty($validated['author_id'])) {
            $authorObj = Author::find($validated['author_id']);
            if ($authorObj) {
                $updates['author_name'] = $authorObj->name;
            }
        }

        $book->update($updates);

        $allAuthorIds = array_filter((array) ($validated['author_ids'] ?? []));
        if (!empty($validated['author_id']) && !in_array($validated['author_id'], $allAuthorIds)) {
            $allAuthorIds[] = $validated['author_id'];
        }
        if (!empty($allAuthorIds)) {
            $book->authors()->sync($allAuthorIds);
        }

        return redirect()->route('publisher.dashboard', ['tab' => 'books'])
            ->with('success', "Book '{$book->title}' has been successfully updated!");
    }

    /**
     * Quick AJAX in-table update for pricing & stock.
     */
    public function quickUpdateBook(Request $request, $id)
    {
        $publisher = $this->getPublisher();
        $book = Book::where('publisher_id', $publisher->id)->findOrFail($id);

        $validated = $request->validate([
            'price'          => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'stock_status'   => 'nullable|in:in_stock,low,out,pre_order',
            'is_active'      => 'nullable|boolean',
        ]);

        $updates = [];
        if ($request->has('price')) $updates['price'] = $validated['price'];
        if ($request->has('discount_price')) $updates['discount_price'] = $validated['discount_price'];
        if ($request->has('stock_quantity')) {
            $qty = (int) $validated['stock_quantity'];
            $updates['stock_quantity'] = $qty;
            if (!$request->filled('stock_status')) {
                $updates['stock_status'] = $qty <= 0 ? 'out' : ($qty <= 5 ? 'low' : 'in_stock');
            }
        }
        if ($request->filled('stock_status')) $updates['stock_status'] = $validated['stock_status'];
        if ($request->has('is_active')) $updates['is_active'] = $request->boolean('is_active');

        $book->update($updates);

        return response()->json([
            'success' => true,
            'message' => 'Book information updated successfully!',
            'book'    => $book
        ]);
    }

    /**
     * Delete a book belonging to the publisher.
     */
    public function destroyBook($id)
    {
        $publisher = $this->getPublisher();
        $book = Book::where('publisher_id', $publisher->id)->findOrFail($id);
        $title = $book->title;
        $book->delete();

        return redirect()->route('publisher.dashboard', ['tab' => 'books'])
            ->with('success', "Book '{$title}' has been removed from your catalog.");
    }

    /**
     * Update publisher company / profile details.
     */
    public function updateProfile(Request $request)
    {
        $publisher = $this->getPublisher();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string|max:30',
            'email'       => 'nullable|email|max:255',
            'website'     => 'nullable|url|max:255',
            'address'     => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
        ]);

        $updates = [
            'name'        => $validated['name'],
            'phone'       => $validated['phone'],
            'email'       => $validated['email'] ?? $publisher->email,
            'website'     => $validated['website'] ?? null,
            'address'     => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('logo')) {
            $updates['logo'] = $request->file('logo')->store('publishers/logos', 'public');
        }

        $publisher->update($updates);

        return redirect()->route('publisher.dashboard', ['tab' => 'settings'])
            ->with('success', 'Publisher company details and profile saved successfully!');
    }
}
