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
    private function getPublisher(?Request $request = null)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        // An admin works as the publisher picked with ?publisher_id= (remembered in the session, so saving a book
        // or the profile lands on the same publisher), else the first active one
        if ($user->isAdmin()) {
            $request ??= request();
            if ($request->filled('publisher_id')) {
                session(['admin_publisher_id' => (int) $request->input('publisher_id')]);
            }
            if (($id = session('admin_publisher_id')) && ($picked = Publisher::find($id))) {
                return $picked;
            }

            return Publisher::where('is_active', true)->first() ?: (Publisher::first() ?: Publisher::create([
                'name' => 'আইডিয়া প্রকাশন',
                'slug' => 'idea-prokashon',
                'email' => 'ideapbd@gmail.com',
                'is_active' => true,
                'is_verified' => true,
            ]));
        }

        if (!$user->isApproved() || (!$user->isPublisher() && $user->role !== 'publisher' && $user->reg_type !== 'publisher')) {
            abort(redirect()->route('pending.approval')->with('warning', 'আপনার প্রকাশনী অ্যাকাউন্টটি বর্তমানে অ্যাডমিন অনুমোদনের অপেক্ষায় রয়েছে। অ্যাডমিন কর্তৃক অনুমোদনের পর আপনি পোর্টাল ব্যবহার করতে পারবেন।'));
        }

        $publisher = $user->getPublisherRecord();
        if (!$publisher) {
            $pName = !empty($user->reg_data['publisher_name']) ? $user->reg_data['publisher_name'] : $user->name;
            $slug = Str::slug($pName) ?: 'publisher-' . $user->id;
            if (Publisher::where('slug', $slug)->exists()) {
                $slug .= '-' . $user->id;
            }
            $publisher = Publisher::create([
                'name'        => $pName,
                'slug'        => $slug,
                'email'       => $user->email,
                'phone'       => $user->phone,
                'address'     => $user->reg_data['address'] ?? null,
                'is_active'   => true,
                'is_verified' => true,
            ]);
        }

        return $publisher;
    }

    /** The tabs of the dashboard. Anything else in ?tab= falls back to the overview. */
    private const TABS = ['overview', 'today-purchases', 'books', 'add-book', 'orders', 'settings'];

    /** The Idea Prokashon account keeps its own serial numbers and sorts by them. */
    private function isIdeaPublisher(Publisher $publisher): bool
    {
        return (int) $publisher->id === (int) config('idea.publisher_id', 2) || $publisher->slug === 'ideaprokashon';
    }

    /**
     * Publisher main dashboard & catalog view.
     */
    public function dashboard(Request $request)
    {
        $user      = auth()->user();
        $publisher = $this->getPublisher($request);
        $isAdmin   = $user->isAdmin();
        $isIdeaPub = $this->isIdeaPublisher($publisher);

        $search      = $request->string('search')->trim()->value();
        $categoryId  = $request->integer('category_id') ?: null;
        $stockFilter = $request->string('stock')->trim()->value();
        $sort        = $request->string('sort')->trim()->value() ?: ($isIdeaPub ? 'idea_serial_asc' : 'latest');
        $dateFilter  = in_array($request->input('date_filter'), ['all', 'today', 'yesterday', 'custom'], true) ? $request->input('date_filter') : 'all';
        $selectedDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->input('date')) ? $request->input('date') : now()->format('Y-m-d');

        // The book being edited (only one of this publisher's own books)
        $editBook = $request->filled('edit_id')
            ? Book::where('publisher_id', $publisher->id)->where('id', $request->integer('edit_id'))->first()
            : null;

        $activeTab = $editBook ? 'add-book' : (in_array($request->input('tab'), self::TABS, true) ? $request->input('tab') : 'overview');

        // ── Books ────────────────────────────────────────────────────────────
        $booksQuery = Book::where('publisher_id', $publisher->id)
            ->with(['category', 'authorLink'])
            ->when($search !== '', function ($q) use ($search) {
                // % and _ typed by the user are plain text (ESCAPE '!' behaves the same on MySQL and SQLite)
                $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
                $q->where(function ($w) use ($like) {
                    foreach (['title', 'subtitle', 'isbn', 'sku', 'idea_serial_no', 'author_name'] as $col) {
                        $w->orWhereRaw("{$col} LIKE ? ESCAPE '!'", [$like]);
                    }
                });
            })
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($stockFilter === 'pre_order', fn ($q) => $q->where('stock_status', 'pre_order'))
            ->when($stockFilter === 'low', fn ($q) => $q->where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0))
            ->when($stockFilter === 'out', fn ($q) => $q->where('stock_quantity', '<=', 0))
            ->when($stockFilter === 'in_stock', fn ($q) => $q->where('stock_quantity', '>', 5));

        // Idea Prokashon serials look like "IDP-0042": sort by the number inside (needs REGEXP_REPLACE: MySQL 8 / MariaDB 10).
        $serialNumber = in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)
            ? "CASE WHEN idea_serial_no IS NOT NULL AND idea_serial_no != '' THEN CAST(REGEXP_REPLACE(idea_serial_no, '[^0-9]', '') AS UNSIGNED) ELSE id END"
            : 'id';

        match (true) {
            $sort === 'oldest'                          => $booksQuery->oldest('id'),
            $sort === 'title_asc'                       => $booksQuery->orderBy('title'),
            $sort === 'title_desc'                      => $booksQuery->orderByDesc('title'),
            $sort === 'price_low'                       => $booksQuery->orderBy('price'),
            $sort === 'price_high'                      => $booksQuery->orderByDesc('price'),
            $sort === 'stock_low'                       => $booksQuery->orderBy('stock_quantity'),
            $sort === 'stock_high'                      => $booksQuery->orderByDesc('stock_quantity'),
            $isIdeaPub && $sort === 'idea_serial_desc'  => $booksQuery->orderByRaw("{$serialNumber} DESC"),
            $isIdeaPub && $sort === 'idea_serial_asc'   => $booksQuery->orderByRaw("{$serialNumber} ASC"),
            default                                     => $booksQuery->latest('id'),
        };

        $books = $booksQuery->paginate(15, ['*'], 'page')->withQueryString()->appends(['tab' => 'books']);

        // One query for every number about the catalogue.
        $bookStats = Book::where('publisher_id', $publisher->id)->toBase()->selectRaw(
            "COUNT(*) AS total,
             COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) AS active,
             COALESCE(SUM(CASE WHEN stock_status = 'pre_order' THEN 1 ELSE 0 END), 0) AS pre_order,
             COALESCE(SUM(CASE WHEN stock_quantity <= 5 AND stock_quantity > 0 THEN 1 ELSE 0 END), 0) AS low,
             COALESCE(SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END), 0) AS out_of_stock,
             COALESCE(SUM(stock_quantity), 0) AS units"
        )->first();

        $lowStockItems = Book::where('publisher_id', $publisher->id)
            ->where('stock_quantity', '<=', 5)
            ->where(fn ($q) => $q->whereNull('stock_status')->orWhere('stock_status', '!=', 'pre_order'))
            ->orderBy('stock_quantity')
            ->take(6)
            ->get(['id', 'title', 'price', 'hardcover_price', 'stock_quantity']);

        // ── Purchases (supply orders from the shop) ─────────────────────────
        // A purchase's amount is its grand total (after discount); old rows may only have total_amount.
        $amount = 'CASE WHEN grand_total > 0 THEN grand_total ELSE total_amount END';
        $purchaseStats = PublisherPurchase::where('publisher_id', $publisher->id)->toBase()->selectRaw(
            "COUNT(*) AS orders,
             COALESCE(SUM({$amount}), 0) AS total,
             COALESCE(SUM(paid_amount), 0) AS paid,
             COALESCE(SUM(CASE WHEN ({$amount}) > paid_amount THEN ({$amount}) - paid_amount ELSE 0 END), 0) AS due,
             COALESCE(SUM(CASE WHEN purchase_date = ? THEN ({$amount}) ELSE 0 END), 0) AS today_amount,
             COALESCE(SUM(CASE WHEN purchase_date = ? THEN 1 ELSE 0 END), 0) AS today_orders",
            [now()->toDateString(), now()->toDateString()]
        )->first();

        $purchaseScope = fn () => PublisherPurchase::where('publisher_id', $publisher->id)->with('items');

        // "Orders" tab: filterable by day, paginated (it used to load every purchase at once)
        $dayList = $purchaseScope()
            ->when($dateFilter === 'today', fn ($q) => $q->whereDate('purchase_date', now()->toDateString()))
            ->when($dateFilter === 'yesterday', fn ($q) => $q->whereDate('purchase_date', now()->subDay()->toDateString()))
            ->when($dateFilter === 'custom', fn ($q) => $q->whereDate('purchase_date', $selectedDate))
            ->latest('purchase_date')->latest('id')
            ->paginate(20, ['*'], 'order_page')->withQueryString()->appends(['tab' => 'today-purchases']);

        // "Bills" tab and the overview
        $allPurchases    = $purchaseScope()->latest('purchase_date')->latest('id')->paginate(15, ['*'], 'purchase_page')->withQueryString()->appends(['tab' => 'orders']);
        $recentPurchases = $purchaseScope()->latest('purchase_date')->latest('id')->take(5)->get();
        $payments        = PublisherPayment::where('publisher_id', $publisher->id)->latest('payment_date')->latest('id')->take(10)->get();

        // ── Form data ────────────────────────────────────────────────────────
        $categories = Category::where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();
        $authors    = Author::where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();

        $publishers = $isAdmin ? Publisher::orderBy('name')->pluck('name', 'id')->all() : [];

        return view('publisher.dashboard', [
            'user'           => $user,
            'isAdmin'        => $isAdmin,
            'isIdeaPub'      => $isIdeaPub,
            'publisher'      => $publisher,
            'publishers'     => $publishers,
            'books'          => $books,
            'bookStats'      => $bookStats,
            'lowStockItems'  => $lowStockItems,
            'purchaseStats'  => $purchaseStats,
            'dayList'        => $dayList,
            'allPurchases'   => $allPurchases,
            'recentPurchases' => $recentPurchases,
            'payments'       => $payments,
            'categories'     => $categories,
            'authors'        => $authors,
            'editBook'       => $editBook,
            'activeTab'      => $activeTab,
            'sort'           => $sort,
            'dateFilter'     => $dateFilter,
            'selectedDate'   => $selectedDate,
        ]);
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
     * Edit book action - redirects to dashboard edit form.
     */
    public function editBook($id)
    {
        return redirect()->route('publisher.dashboard', ['tab' => 'books', 'edit_id' => $id]);
    }

    /**
     * Store a newly created book by the publisher.
     */
    public function storeBook(Request $request)
    {
        $publisher = $this->getPublisher();

        // Clean author_ids array before validation
        if ($request->has('author_ids')) {
            $cleanAuthorIds = array_filter(array_map('trim', (array) $request->input('author_ids')), fn($v) => !empty($v) && is_numeric($v));
            $request->merge(['author_ids' => !empty($cleanAuthorIds) ? array_values($cleanAuthorIds) : null]);
        }

        $validated = $request->validate([
            'title'                    => 'required|string|max:255',
            'title_en'                 => 'nullable|string|max:255',
            'subtitle'                 => 'nullable|string|max:255',
            'product_type'             => 'nullable|string|max:50',
            'category_id'              => 'required|exists:categories,id',
            'sub_category_name'        => 'nullable|string|max:255',
            'ekushey_category'         => 'nullable|string|max:100',
            'genre_category'           => \Modules\Book\Models\Genre::rules(),
            'audience_category'        => 'nullable|string|max:100',
            'author_id'                => 'nullable|exists:authors,id',
            'author_ids'               => 'nullable|array',
            'author_ids.*'             => 'nullable|exists:authors,id',
            'author_name'              => 'nullable|string|max:255',
            'translator_name'          => 'nullable|string|max:255',
            'editor_name'              => 'nullable|string|max:255',
            'rewriter_name'            => 'nullable|string|max:255',
            'language'                 => 'nullable|string|max:50',
            'country'                  => 'nullable|string|max:100',
            'cover_type'               => 'required|in:paperback,hardcover,board_book,spiral,both',
            'paper_type'               => 'nullable|string|max:100',
            'book_size'                => 'nullable|string|max:100',
            'price'                    => 'nullable|numeric|min:0',
            'discount_price'           => 'nullable|numeric|min:0',
            'hardcover_price'          => 'nullable|numeric|min:0',
            'hardcover_discount_price' => 'nullable|numeric|min:0',
            'cost_price'               => 'nullable|numeric|min:0',
            'stock_quantity'           => 'nullable|integer|min:0',
            'stock_status'             => 'nullable|in:in_stock,low,out,pre_order,upcoming',
            'pre_order_release_date'   => 'nullable|date',
            'pre_order_note'           => 'nullable|string|max:1000',
            'published_at'             => 'nullable|date',
            'edition'                  => 'nullable|string|max:100',
            'isbn'                     => 'nullable|string|max:50',
            'number_of_pages'          => 'nullable|integer|min:1',
            'page_count'               => 'nullable|integer|min:1',
            'summary'                  => 'nullable|string',
            'description'              => 'nullable|string',
            'look_inside_type'         => 'nullable|string|in:pdf,images',
            'cover_image'              => 'nullable|file|mimes:jpg,jpeg,png,webp,avif,gif|max:25600',
            'pdf_sample'               => 'nullable|mimes:pdf|max:25600',
        ]);

        if ($request->filled('summary')) {
            $summaryClean = trim(strip_tags((string) $request->input('summary')));
            $words = preg_split('/\s+/u', $summaryClean, -1, PREG_SPLIT_NO_EMPTY);
            if (count($words) > 1000) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'summary' => 'বইয়ের সারসংক্ষেপ সর্বোচ্চ ১০০০ শব্দের মধ্যে হতে হবে। বর্তমানে ' . count($words) . ' টি শব্দ রয়েছে।',
                ]);
            }
        }

        $authorNames = array_values(array_filter(array_map('trim', (array) $request->input('author_names', []))));
        $duplicateCandidate = new Book();
        $duplicateCandidate->forceFill([
            'title' => $validated['title'],
            'isbn' => $validated['isbn'] ?? null,
            'author_name' => implode(', ', $authorNames) ?: ($validated['author_name'] ?? null),
            'author_link_id' => $validated['author_ids'][0] ?? ($validated['author_id'] ?? null),
            'edition' => $validated['edition'] ?? null,
        ]);
        $duplicateCandidate->assertCatalogEntryIsUnique();

        try {
            // Handle Cover Image Upload (Convert to AVIF with fallback)
            $coverPath = null;
            if ($request->hasFile('cover_image')) {
                try {
                    $coverPath = \App\Services\ImageOptimizerService::convertAndStore($request->file('cover_image'), 'books/covers', 'public');
                } catch (\Throwable $imgEx) {
                    $coverPath = $request->file('cover_image')->store('books/covers', 'public');
                }
            }

            // Handle Sample PDF Upload
            $pdfPath = null;
            if ($request->hasFile('pdf_sample')) {
                $pdfPath = $request->file('pdf_sample')->store('books/samples', 'public');
            }

            // Handle Multiple Look Inside Images
            $lookInsideImagesJson = null;
            if ($request->hasFile('look_inside_images')) {
                $imagesList = [];
                foreach ((array)$request->file('look_inside_images') as $img) {
                    if ($img instanceof \Illuminate\Http\UploadedFile) {
                        try {
                            $imagesList[] = \App\Services\ImageOptimizerService::convertAndStore($img, 'books/look_inside', 'public');
                        } catch (\Throwable $imgEx) {
                            $imagesList[] = $img->store('books/look_inside', 'public');
                        }
                    }
                }
                if (!empty($imagesList)) {
                    $lookInsideImagesJson = json_encode($imagesList);
                }
            }

            // Generate Slug
            $slug = Str::slug($validated['title']) ?: 'book-' . time();
            if (Book::where('slug', $slug)->exists()) {
                $slug .= '-' . rand(100, 999);
            }

            // Generate SKU if not provided
            $sku = !empty($validated['isbn']) ? $validated['isbn'] : 'IDP-PUB-' . $publisher->id . '-' . rand(1000, 9999);

            // Resolve Author Name(s)
            $authorName = $validated['author_name'] ?? null;
            if ($request->has('author_names')) {
                $authorNames = array_filter(array_map('trim', (array) $request->input('author_names')));
                if (!empty($authorNames)) {
                    $authorName = implode(', ', $authorNames);
                }
            } elseif (!empty($validated['author_id'])) {
                $authorObj = Author::find($validated['author_id']);
                if ($authorObj) {
                    $authorName = $authorObj->name;
                }
            }

            // Multiple Translators
            $translatorName = $validated['translator_name'] ?? null;
            if ($request->has('translator_names')) {
                $translators = array_filter(array_map('trim', (array) $request->input('translator_names')));
                if (!empty($translators)) {
                    $translatorName = implode(', ', $translators);
                }
            }

            // Multiple Editors
            $editorName = $validated['editor_name'] ?? null;
            if ($request->has('editor_names')) {
                $editors = array_filter(array_map('trim', (array) $request->input('editor_names')));
                if (!empty($editors)) {
                    $editorName = implode(', ', $editors);
                }
            }

            // Multiple Rewriters
            $rewriterName = $validated['rewriter_name'] ?? null;
            if ($request->has('rewriter_names')) {
                $rewriters = array_filter(array_map('trim', (array) $request->input('rewriter_names')));
                if (!empty($rewriters)) {
                    $rewriterName = implode(', ', $rewriters);
                }
            }

            // Book Size & Dimensions
            $heightCm = $request->filled('book_height_cm') ? (float)$request->input('book_height_cm') : null;
            $widthCm = $request->filled('book_width_cm') ? (float)$request->input('book_width_cm') : null;
            $bookSize = $validated['book_size'] ?? null;
            if ($heightCm && $widthCm) {
                $bookSize = "{$heightCm} cm × {$widthCm} cm";
            } elseif ($heightCm) {
                $bookSize = "{$heightCm} cm (Height)";
            } elseif ($widthCm) {
                $bookSize = "{$widthCm} cm (Width)";
            }

            $book = Book::create([
                'title'                    => $validated['title'],
                'title_en'                 => $validated['title_en'] ?? ($validated['subtitle'] ?? null),
                'subtitle'                 => $validated['subtitle'] ?? null,
                'slug'                     => $slug,
                'sku'                      => $sku,
                'isbn'                     => $validated['isbn'] ?? null,
                'product_type'             => $validated['product_type'] ?? 'book',
                'category_id'              => $validated['category_id'],
                'sub_category_name'        => $validated['sub_category_name'] ?? null,
                'ekushey_category'         => $validated['ekushey_category'] ?? null,
                'genre_category'           => $validated['genre_category'] ?? null,
                'audience_category'        => $validated['audience_category'] ?? null,
                'publisher_id'             => $publisher->id,
                'author_link_id'           => $validated['author_id'] ?? null,
                'author_name'              => $authorName,
                'translator_name'          => $translatorName,
                'editor_name'              => $editorName,
                'rewriter_name'            => $rewriterName,
                'language'                 => $validated['language'] ?? 'Bengali',
                'country'                  => $validated['country'] ?? 'Bangladesh',
                'cover_type'               => $validated['cover_type'] ?? 'paperback',
                'paper_type'               => $validated['paper_type'] ?? null,
                'book_size'                => $bookSize,
                'book_height_cm'           => $heightCm,
                'book_width_cm'            => $widthCm,
                'price'                    => $validated['price'] ?? 0,
                'discount_price'           => $validated['discount_price'] ?? null,
                'hardcover_price'          => $validated['hardcover_price'] ?? null,
                'hardcover_discount_price' => $validated['hardcover_discount_price'] ?? null,
                'cost_price'               => $validated['cost_price'] ?? null,
                'stock_quantity'           => (int) ($validated['stock_quantity'] ?? 10),
                'stock_status'             => $validated['stock_status'] ?? 'in_stock',
                'pre_order_release_date'   => $validated['pre_order_release_date'] ?? null,
                'pre_order_note'           => $validated['pre_order_note'] ?? null,
                'published_at'             => $validated['published_at'] ?? null,
                'edition'                  => $validated['edition'] ?? null,
                'page_count'               => $validated['page_count'] ?? ($validated['number_of_pages'] ?? null),
                'summary'                  => $validated['summary'] ?? null,
                'description'              => $validated['description'] ?? null,
                'cover_image'              => $coverPath,
                'sample_pdf_path'          => $pdfPath,
                'look_inside_type'         => $validated['look_inside_type'] ?? 'pdf',
                'look_inside_images'       => $lookInsideImagesJson,
                'is_active'                => false, // Inactive until Admin Approval
                'mod_status'               => 'pending', // Pending Admin Moderation Queue
                'is_featured'              => false,
                'created_by'               => auth()->id(),
                'submitted_by'             => auth()->id(),
                'owner_name'               => $publisher->name,
                'owner_phone'              => $publisher->phone,
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
                ->with('success', "‘{$book->title}’ বইটি সফলভাবে যুক্ত হয়েছে! অ্যাডমিনের পর্যালোচনার পর এটি বুক শপে প্রকাশিত হবে।");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Publisher storeBook error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            return back()->withInput()->with('error', 'বই/পণ্য সংরক্ষণের সময় সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing book of the publisher.
     */
    public function updateBook(Request $request, $id)
    {
        $publisher = $this->getPublisher();
        $book = Book::where('publisher_id', $publisher->id)->findOrFail($id);

        // Clean author_ids array before validation
        if ($request->has('author_ids')) {
            $cleanAuthorIds = array_filter(array_map('trim', (array) $request->input('author_ids')), fn($v) => !empty($v) && is_numeric($v));
            $request->merge(['author_ids' => !empty($cleanAuthorIds) ? array_values($cleanAuthorIds) : null]);
        }

        $validated = $request->validate([
            'title'                    => 'required|string|max:255',
            'title_en'                 => 'nullable|string|max:255',
            'subtitle'                 => 'nullable|string|max:255',
            'product_type'             => 'nullable|string|max:50',
            'category_id'              => 'required|exists:categories,id',
            'sub_category_name'        => 'nullable|string|max:255',
            'ekushey_category'         => 'nullable|string|max:100',
            'genre_category'           => \Modules\Book\Models\Genre::rules($book->genre_category ?? null),
            'audience_category'        => 'nullable|string|max:100',
            'author_id'                => 'nullable|exists:authors,id',
            'author_ids'               => 'nullable|array',
            'author_ids.*'             => 'nullable|exists:authors,id',
            'author_name'              => 'nullable|string|max:255',
            'translator_name'          => 'nullable|string|max:255',
            'editor_name'              => 'nullable|string|max:255',
            'rewriter_name'            => 'nullable|string|max:255',
            'language'                 => 'nullable|string|max:50',
            'country'                  => 'nullable|string|max:100',
            'cover_type'               => 'required|in:paperback,hardcover,board_book,spiral,both',
            'paper_type'               => 'nullable|string|max:100',
            'book_size'                => 'nullable|string|max:100',
            'price'                    => 'nullable|numeric|min:0',
            'discount_price'           => 'nullable|numeric|min:0',
            'hardcover_price'          => 'nullable|numeric|min:0',
            'hardcover_discount_price' => 'nullable|numeric|min:0',
            'cost_price'               => 'nullable|numeric|min:0',
            'stock_quantity'           => 'nullable|integer|min:0',
            'stock_status'             => 'nullable|in:in_stock,low,out,pre_order,upcoming',
            'pre_order_release_date'   => 'nullable|date',
            'pre_order_note'           => 'nullable|string|max:1000',
            'published_at'             => 'nullable|date',
            'edition'                  => 'nullable|string|max:100',
            'isbn'                     => 'nullable|string|max:50',
            'number_of_pages'          => 'nullable|integer|min:1',
            'page_count'               => 'nullable|integer|min:1',
            'summary'                  => 'nullable|string',
            'description'              => 'nullable|string',
            'look_inside_type'         => 'nullable|string|in:pdf,images',
            'cover_image'              => 'nullable|file|mimes:jpg,jpeg,png,webp,avif,gif|max:25600',
            'pdf_sample'               => 'nullable|mimes:pdf|max:25600',
        ]);

        if ($request->filled('summary')) {
            $summaryClean = trim(strip_tags((string) $request->input('summary')));
            $words = preg_split('/\s+/u', $summaryClean, -1, PREG_SPLIT_NO_EMPTY);
            if (count($words) > 1000) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'summary' => 'বইয়ের সারসংক্ষেপ সর্বোচ্চ ১০০০ শব্দের মধ্যে হতে হবে। বর্তমানে ' . count($words) . ' টি শব্দ রয়েছে।',
                ]);
            }
        }

        try {
            // Multiple Translators
            $translatorName = $validated['translator_name'] ?? $book->translator_name;
            if ($request->has('translator_names')) {
                $translators = array_filter(array_map('trim', (array) $request->input('translator_names')));
                $translatorName = !empty($translators) ? implode(', ', $translators) : null;
            }

            // Multiple Editors
            $editorName = $validated['editor_name'] ?? $book->editor_name;
            if ($request->has('editor_names')) {
                $editors = array_filter(array_map('trim', (array) $request->input('editor_names')));
                $editorName = !empty($editors) ? implode(', ', $editors) : null;
            }

            // Multiple Rewriters
            $rewriterName = $validated['rewriter_name'] ?? $book->rewriter_name;
            if ($request->has('rewriter_names')) {
                $rewriters = array_filter(array_map('trim', (array) $request->input('rewriter_names')));
                $rewriterName = !empty($rewriters) ? implode(', ', $rewriters) : null;
            }

            // Dimensions
            $heightCm = $request->filled('book_height_cm') ? (float)$request->input('book_height_cm') : $book->book_height_cm;
            $widthCm = $request->filled('book_width_cm') ? (float)$request->input('book_width_cm') : $book->book_width_cm;
            $bookSize = $validated['book_size'] ?? $book->book_size;
            if ($request->filled('book_height_cm') || $request->filled('book_width_cm')) {
                if ($heightCm && $widthCm) {
                    $bookSize = "{$heightCm} cm × {$widthCm} cm";
                } elseif ($heightCm) {
                    $bookSize = "{$heightCm} cm (Height)";
                } elseif ($widthCm) {
                    $bookSize = "{$widthCm} cm (Width)";
                }
            }

            $authorName = $validated['author_name'] ?? $book->author_name;
            if ($request->has('author_names')) {
                $authorNames = array_filter(array_map('trim', (array) $request->input('author_names')));
                if (!empty($authorNames)) {
                    $authorName = implode(', ', $authorNames);
                }
            }

            $updates = [
                'title'                    => $validated['title'],
                'title_en'                 => $validated['title_en'] ?? ($validated['subtitle'] ?? null),
                'subtitle'                 => $validated['subtitle'] ?? null,
                'isbn'                     => $validated['isbn'] ?? null,
                'product_type'             => $validated['product_type'] ?? 'book',
                'category_id'              => $validated['category_id'],
                'sub_category_name'        => $validated['sub_category_name'] ?? null,
                'ekushey_category'         => $validated['ekushey_category'] ?? null,
                'genre_category'           => $validated['genre_category'] ?? null,
                'audience_category'        => $validated['audience_category'] ?? null,
                'author_link_id'           => $validated['author_id'] ?? null,
                'author_name'              => $authorName,
                'translator_name'          => $translatorName,
                'editor_name'              => $editorName,
                'rewriter_name'            => $rewriterName,
                'language'                 => $validated['language'] ?? 'Bengali',
                'country'                  => $validated['country'] ?? 'Bangladesh',
                'cover_type'               => $validated['cover_type'] ?? 'paperback',
                'paper_type'               => $validated['paper_type'] ?? null,
                'book_size'                => $bookSize,
                'book_height_cm'           => $heightCm,
                'book_width_cm'            => $widthCm,
                'price'                    => $validated['price'] ?? 0,
                'discount_price'           => $validated['discount_price'] ?? null,
                'hardcover_price'          => $validated['hardcover_price'] ?? null,
                'hardcover_discount_price' => $validated['hardcover_discount_price'] ?? null,
                'cost_price'               => $validated['cost_price'] ?? null,
                'stock_quantity'           => (int) ($validated['stock_quantity'] ?? $book->stock_quantity),
                'stock_status'             => $validated['stock_status'] ?? $book->stock_status,
                'pre_order_release_date'   => $validated['pre_order_release_date'] ?? null,
                'pre_order_note'           => $validated['pre_order_note'] ?? null,
                'published_at'             => $validated['published_at'] ?? null,
                'edition'                  => $validated['edition'] ?? null,
                'page_count'               => $validated['page_count'] ?? ($validated['number_of_pages'] ?? null),
                'summary'                  => $validated['summary'] ?? null,
                'description'              => $validated['description'] ?? null,
                'look_inside_type'         => $validated['look_inside_type'] ?? ($book->look_inside_type ?? 'pdf'),
            ];

            if ($request->hasFile('cover_image')) {
                try {
                    $updates['cover_image'] = \App\Services\ImageOptimizerService::convertAndStore($request->file('cover_image'), 'books/covers', 'public');
                } catch (\Throwable $imgEx) {
                    $updates['cover_image'] = $request->file('cover_image')->store('books/covers', 'public');
                }
            }

            if ($request->hasFile('pdf_sample')) {
                $updates['sample_pdf_path'] = $request->file('pdf_sample')->store('books/samples', 'public');
            }

            if ($request->hasFile('look_inside_images')) {
                $imagesList = [];
                foreach ((array)$request->file('look_inside_images') as $img) {
                    if ($img instanceof \Illuminate\Http\UploadedFile) {
                        try {
                            $imagesList[] = \App\Services\ImageOptimizerService::convertAndStore($img, 'books/look_inside', 'public');
                        } catch (\Throwable $imgEx) {
                            $imagesList[] = $img->store('books/look_inside', 'public');
                        }
                    }
                }
                if (!empty($imagesList)) {
                    $updates['look_inside_images'] = json_encode($imagesList);
                }
            }

            if (!empty($validated['author_id'])) {
                $authorObj = Author::find($validated['author_id']);
                if ($authorObj) {
                    $updates['author_name'] = $authorObj->name;
                }
            }

            if (!auth()->user()->isAdmin()) {
                $updates['mod_status'] = 'pending';
                $updates['is_active'] = false;
            }

            $book->update($updates);

            $allAuthorIds = array_filter((array) ($validated['author_ids'] ?? []));
            if (!empty($validated['author_id']) && !in_array($validated['author_id'], $allAuthorIds)) {
                $allAuthorIds[] = $validated['author_id'];
            }
            if (!empty($allAuthorIds)) {
                $book->authors()->sync($allAuthorIds);
            }

            $msg = auth()->user()->isAdmin() 
                ? "‘{$book->title}’ বইটি সফলভাবে আপডেট করা হয়েছে।"
                : "‘{$book->title}’ বইটি সফলভাবে আপডেট হয়েছে! অ্যাডমিনের পর্যালোচনার পর এটি পুনরায় লাইভ শপে প্রকাশিত হবে।";

            return redirect()->route('publisher.dashboard', ['tab' => 'books'])
                ->with('success', $msg);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Publisher updateBook error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            return back()->withInput()->with('error', 'বই/পণ্য আপডেটের সময় সমস্যা হয়েছে: ' . $e->getMessage());
        }
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
            'logo'        => 'nullable|file|mimes:jpg,jpeg,png,webp,avif,gif|max:25600',
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
            $updates['logo'] = \App\Services\ImageOptimizerService::convertAndStore($request->file('logo'), 'publishers/logos', 'public');
        }

        $publisher->update($updates);

        return redirect()->route('publisher.dashboard', ['tab' => 'settings'])
            ->with('success', 'Publisher company details and profile saved successfully!');
    }

    /**
     * Quickly create a new category via AJAX.
     */
    public function quickStoreCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name) ?: 'cat-' . time();
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(100, 999);
        }

        $category = Category::create([
            'name'      => $name,
            'slug'      => $slug,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "ক্যাটাগরি '{$category->name}' সফলভাবে তৈরি হয়েছে।",
            'item'    => [
                'id'   => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
        ]);
    }

    /**
     * Quickly create a new author via AJAX.
     */
    public function quickStoreAuthor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name) ?: 'author-' . time();
        if (Author::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(100, 999);
        }

        $author = Author::create([
            'name'        => $name,
            'slug'        => $slug,
            'is_active'   => true,
            'is_verified' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => "লেখক '{$author->name}' সফলভাবে যুক্ত হয়েছে।",
            'item'    => [
                'id'   => $author->id,
                'name' => $author->name,
                'slug' => $author->slug,
            ],
        ]);
    }
}
