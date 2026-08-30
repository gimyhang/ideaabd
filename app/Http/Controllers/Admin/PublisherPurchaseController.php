<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublisherPurchase;
use App\Models\PublisherPurchaseItem;
use App\Models\PublisherPayment;
use Modules\Publisher\Models\Publisher;
use Modules\Book\Models\Book;
use Modules\Book\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class PublisherPurchaseController extends Controller
{
    /**
     * List all purchases (Separated into 3 classes: Books, Raw Materials, and Other Purchases).
     */
    public function index(Request $request): View
    {
        $category = $request->input('category'); // 'books', 'raw_materials', 'other', or null (all)
        $search = $request->string('search')->trim()->value();
        $publisherId = $request->input('publisher_id');
        $paymentStatus = $request->string('payment_status')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = PublisherPurchase::query()
            ->with(['publisher', 'items.book', 'payments'])
            ->when($category && in_array($category, ['books', 'raw_materials', 'other']), function($q) use ($category) {
                if ($category === 'books') {
                    $q->where(fn($sub) => $sub->where('purchase_category', 'books')->orWhereNull('purchase_category'));
                } elseif ($category === 'raw_materials') {
                    $q->where('purchase_category', 'raw_materials');
                } else {
                    $q->where('purchase_category', 'other');
                }
            })
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where('purchase_no', 'like', $like)
                  ->orWhere('supplier_name', 'like', $like)
                  ->orWhere('vendor_name', 'like', $like)
                  ->orWhereHas('publisher', fn($p) => $p->where('name', 'like', $like))
                  ->orWhereHas('items', fn($i) => $i->where('book_title', 'like', $like));
            })
            ->when($publisherId, fn($q) => $q->where('publisher_id', $publisherId))
            ->when($paymentStatus && $paymentStatus !== 'all', fn($q) => $q->where('payment_status', $paymentStatus))
            ->when($dateFrom, fn($q) => $q->whereDate('purchase_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('purchase_date', '<=', $dateTo))
            ->latest('purchase_date')
            ->latest('id');

        $purchases = $query->paginate(20)->withQueryString();

        $publishers = Publisher::orderBy('name')->pluck('name', 'id')->all();

        $stats = [
            'total_invoices' => PublisherPurchase::count(),
            'total_purchase' => (float) PublisherPurchase::sum('grand_total'),
            'total_paid'     => (float) PublisherPurchase::sum('paid_amount'),
            'total_due'      => (float) PublisherPurchase::sum('due_amount'),
            'due_count'      => PublisherPurchase::whereIn('payment_status', ['due', 'partial'])->count(),
            'books_count'    => PublisherPurchase::where(fn($q) => $q->where('purchase_category', 'books')->orWhereNull('purchase_category'))->count(),
            'books_total'    => (float) PublisherPurchase::where(fn($q) => $q->where('purchase_category', 'books')->orWhereNull('purchase_category'))->sum('grand_total'),
            'raw_count'      => PublisherPurchase::where('purchase_category', 'raw_materials')->count(),
            'raw_total'      => (float) PublisherPurchase::where('purchase_category', 'raw_materials')->sum('grand_total'),
            'other_count'    => PublisherPurchase::where('purchase_category', 'other')->count(),
            'other_total'    => (float) PublisherPurchase::where('purchase_category', 'other')->sum('grand_total'),
        ];

        return view('admin.purchases.index', compact('purchases', 'publishers', 'stats', 'search', 'publisherId', 'paymentStatus', 'dateFrom', 'dateTo', 'category'));
    }

    /**
     * Show form to create a new purchase.
     */
    public function create(Request $request): View
    {
        $currentType = $request->input('type', 'books');
        if (!in_array($currentType, ['books', 'raw_materials', 'other'])) {
            $currentType = 'books';
        }

        $publishers = Publisher::where('is_active', true)
            ->withCount('books')
            ->withSum('purchases as total_due', 'due_amount')
            ->orderBy('name')
            ->get();
        $authors = \Modules\Author\Models\Author::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $books = Book::with(['publisher:id,name', 'category:id,name'])
            ->select('id', 'title', 'subtitle', 'slug', 'price', 'discount_price', 'cost_price', 'stock_quantity', 'publisher_id', 'category_id', 'author_name', 'isbn', 'edition', 'paper_type', 'book_size', 'page_count', 'cover_type', 'cover_image')
            ->orderBy('title')
            ->limit(1000)
            ->get()
            ->map(function ($b) {
                return [
                    'id'            => $b->id,
                    'title'         => $b->title,
                    'subtitle'      => $b->subtitle ?? '',
                    'author'        => $b->author_name ?? '',
                    'category_id'   => $b->category_id,
                    'category_name' => $b->category?->name ?? '',
                    'publisher_id'  => $b->publisher_id,
                    'publisher_name'=> $b->publisher?->name ?? '',
                    'price'         => (float)($b->price ?: 0),
                    'mrp_price'     => (float)($b->price ?: 0),
                    'discount_price'=> (float)($b->discount_price ?: 0),
                    'sale_price'    => (float)($b->discount_price > 0 ? $b->discount_price : $b->price),
                    'cost_price'    => (float)($b->cost_price ?: 0),
                    'stock'         => (int)$b->stock_quantity,
                    'isbn'          => $b->isbn ?? '',
                    'edition'       => $b->edition ?? '',
                    'cover_type'    => $b->cover_type ?? 'paperback',
                    'page_count'    => $b->page_count ?? '',
                    'book_size'     => $b->book_size ?? '',
                    'paper_type'    => $b->paper_type ?? '',
                ];
            });

        // Auto generate next purchase invoice number based on class
        $dateStr = date('Ymd');
        $prefix = $currentType === 'raw_materials' ? 'RM-' : ($currentType === 'other' ? 'OTH-' : 'PUR-');
        $countToday = PublisherPurchase::whereDate('created_at', today())->count() + 1;
        $suggestedInvoiceNo = $prefix . $dateStr . '-' . str_pad((string)$countToday, 3, '0', STR_PAD_LEFT);

        // Fetch distinct registered vendors for raw materials / production
        $existingVendors = PublisherPurchase::whereNotNull('vendor_name')
            ->where('vendor_name', '!=', '')
            ->select('vendor_name', 'vendor_phone', 'vendor_address')
            ->distinct()
            ->orderBy('vendor_name')
            ->get();

        return view('admin.purchases.create', compact('publishers', 'authors', 'categories', 'books', 'suggestedInvoiceNo', 'currentType', 'existingVendors'));
    }

    /**
     * Search books dynamically for autocomplete with single character, tokenized search, publisher filter, and full metadata.
     */
    public function searchBooks(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim()->value();
        $publisherId = $request->input('publisher_id');

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $enDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $normalized = str_replace($bnDigits, $enDigits, $q);
        $words = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
        $tokens = array_values(array_unique(array_filter(array_merge([$q, $normalized], $words))));

        $books = Book::query()
            ->with(['publisher', 'authorLink', 'authors', 'category'])
            ->when($publisherId, fn($query) => $query->where('publisher_id', $publisherId))
            ->where(function ($masterQuery) use ($tokens) {
                foreach ($tokens as $token) {
                    $like = '%' . $token . '%';
                    $masterQuery->where(function ($query) use ($like, $token) {
                        $query->where('title', 'like', $like)
                              ->orWhere('subtitle', 'like', $like)
                              ->orWhere('isbn', 'like', $like)
                              ->orWhere('sku', 'like', $like)
                              ->orWhere('author_name', 'like', $like)
                              ->orWhereHas('authorLink', fn($a) => $a->where('name', 'like', $like))
                              ->orWhereHas('authors', fn($a) => $a->where('name', 'like', $like))
                              ->orWhereHas('publisher', fn($p) => $p->where('name', 'like', $like));
                    });
                }
            })
            ->limit(30)
            ->get()
            ->map(function ($book) {
                $regular = (float)($book->price ?: ($book->hardcover_price ?: 0));
                $discount = (float)($book->discount_price ?: 0);
                $cost = (float)($book->cost_price ?: ($regular > 0 ? $regular * 0.6 : 0));
                $authorName = $book->author_name ?: ($book->authors->pluck('name')->implode(', ') ?: ($book->authorLink->name ?? ''));

                return [
                    'id'            => $book->id,
                    'title'         => $book->title,
                    'subtitle'      => $book->subtitle,
                    'author'        => $authorName,
                    'category_id'   => $book->category_id,
                    'category_name' => $book->category?->name ?? '',
                    'publisher_id'  => $book->publisher_id,
                    'publisher_name'=> $book->publisher?->name ?? '',
                    'mrp_price'     => $regular,
                    'sale_price'    => $discount > 0 ? $discount : $regular,
                    'cost_price'    => $cost,
                    'stock'         => (int) $book->stock_quantity,
                    'isbn'          => $book->isbn ?? '',
                    'edition'       => $book->edition ?? '',
                    'cover_type'    => $book->cover_type ?? 'paperback',
                    'page_count'    => $book->page_count ?? '',
                    'book_size'     => $book->book_size ?? '',
                    'paper_type'    => $book->paper_type ?? '',
                ];
            });

        return response()->json($books);
    }

    /**
     * Store new purchase (Books, Raw Materials, or Other).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_category'           => 'nullable|in:books,raw_materials,other',
            'supplier_name'               => 'nullable|string|max:255',
            'vendor_name'                 => 'nullable|string|max:255',
            'publisher_id'                => 'nullable|integer',
            'publisher_name'              => 'nullable|string|max:255',
            'publisher_phone'             => 'nullable|string|max:50',
            'publisher_email'             => 'nullable|email|max:255',
            'publisher_address'           => 'nullable|string|max:500',
            'publisher_website'           => 'nullable|string|max:255',
            'publisher_memo_no'           => 'nullable|string|max:100',
            'purchase_no'                 => 'required|string|max:50|unique:publisher_purchases,purchase_no',
            'purchase_date'               => 'required|date',
            'due_date'                    => 'nullable|date',
            'payment_type'                => 'required|in:cash,credit,partial,installment',
            'installment_count'           => 'nullable|integer|min:1',
            'installment_notes'           => 'nullable|string|max:1000',
            'discount_amount'             => 'nullable|numeric|min:0',
            'paid_amount'                 => 'nullable|numeric|min:0',
            'payment_method'              => 'nullable|string|max:50',
            'transaction_ref'             => 'nullable|string|max:100',
            'notes'                       => 'nullable|string|max:1000',
            'items'                       => 'required|array|min:1',
            'items.*.title'               => 'required|string|max:255',
            'items.*.book_id'             => 'nullable|integer',
            'items.*.author'              => 'nullable|string|max:255',
            'items.*.category_id'         => 'nullable|integer',
            'items.*.category_name'       => 'nullable|string|max:100',
            'items.*.quantity'            => 'required|integer|min:1',
            'items.*.mrp_price'           => 'nullable|numeric|min:0',
            'items.*.purchase_commission_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.cost_price'          => 'required|numeric|min:0',
            'items.*.shop_discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.sale_price'          => 'nullable|numeric|min:0',
            'items.*.isbn'                => 'nullable|string|max:50',
            'items.*.edition'             => 'nullable|string|max:100',
            'items.*.page_count'          => 'nullable|integer|min:0',
            'items.*.cover_type'          => 'nullable|string|max:50',
            'items.*.book_size'           => 'nullable|string|max:50',
            'items.*.paper_type'          => 'nullable|string|max:50',
        ], [
            'purchase_no.required'  => 'ক্রয় ইনভয়েস নম্বর দিন।',
            'purchase_no.unique'    => 'এই ইনভয়েস নম্বরটি পূর্বে ব্যবহার করা হয়েছে।',
            'items.required'        => 'কমপক্ষে একটি আইটেম/বই যোগ করুন।',
            'items.min'             => 'কমপক্ষে একটি আইটেম/বই যোগ করুন।',
        ]);

        $purchaseCategory = $validated['purchase_category'] ?? 'books';
        $vendorName = trim((string)($validated['vendor_name'] ?? $validated['supplier_name'] ?? $validated['publisher_name'] ?? ''));

        if ($purchaseCategory === 'books' && empty($validated['publisher_id']) && empty($vendorName)) {
            return back()->withInput()->withErrors(['publisher_id' => 'বই ক্রয়ের ক্ষেত্রে অনুগ্রহ করে প্রকাশক নির্বাচন করুন বা নতুন প্রকাশনীর নাম লিখুন।']);
        }

        if ($purchaseCategory !== 'books' && empty($vendorName)) {
            $vendorName = $purchaseCategory === 'raw_materials' ? 'কাঁচামাল ও প্রেস সরবরাহকারী' : 'বিবিধ সরবরাহকারী / বিক্রেতা';
        }

        return DB::transaction(function () use ($request, $validated, $purchaseCategory, $vendorName) {
            // If books purchase, resolve Publisher; for raw_materials & other, publisher_id is null and uses vendor_name
            $publisherId = null;
            if ($purchaseCategory === 'books') {
                $publisherId = !empty($validated['publisher_id']) ? (int)$validated['publisher_id'] : null;
                $publisherName = $vendorName;

                if (!$publisherId || !Publisher::where('id', $publisherId)->exists()) {
                    if (!empty($publisherName)) {
                        $pub = Publisher::where('name', $publisherName)->first();
                        if (!$pub) {
                            $slugBase = $this->bengaliToEnglish($publisherName) ?: 'pub-' . uniqid();
                            $slug = $slugBase;
                            $c = 1;
                            while (Publisher::where('slug', $slug)->exists()) {
                                $slug = $slugBase . '-' . (++$c);
                            }
                            $pub = Publisher::create([
                                'name'      => $publisherName,
                                'slug'      => $slug,
                                'phone'     => $validated['publisher_phone'] ?? null,
                                'email'     => $validated['publisher_email'] ?? null,
                                'address'   => $validated['publisher_address'] ?? null,
                                'website'   => $validated['publisher_website'] ?? null,
                                'is_active' => true,
                            ]);
                        }
                        $publisherId = $pub->id;
                    }
                }
            }

            $totalAmount = 0.0;
            $discount = (float) ($validated['discount_amount'] ?? 0);
            $initialPaid = (float) ($validated['paid_amount'] ?? 0);

            // Create Purchase record
            $purchase = new PublisherPurchase();
            $purchase->purchase_no = $validated['purchase_no'];
            $purchase->purchase_category = $purchaseCategory;
            $purchase->publisher_memo_no = $validated['publisher_memo_no'] ?? null;
            $purchase->publisher_id = $publisherId;
            $purchase->supplier_name = $vendorName;
            $purchase->vendor_name = $vendorName;
            $publisher = $publisherId ? Publisher::find($publisherId) : null;
            $purchase->vendor_phone = $request->input('vendor_phone') ?: ($publisher ? $publisher->phone : null);
            $purchase->vendor_address = $request->input('vendor_address') ?: ($publisher ? $publisher->address : null);
            $purchase->purchase_date = $validated['purchase_date'];
            $purchase->due_date = $validated['due_date'] ?? null;
            $purchase->payment_type = $validated['payment_type'];
            $purchase->installment_count = !empty($validated['installment_count']) ? (int)$validated['installment_count'] : 1;
            $purchase->installment_notes = $validated['installment_notes'] ?? null;
            $purchase->discount_amount = $discount;
            $purchase->notes = $validated['notes'] ?? null;
            $purchase->created_by = auth()->id();
            $purchase->save();

            // Process purchase items & sync with Bookshop (only for books class)
            foreach ($validated['items'] as $itemData) {
                $qty = (int) $itemData['quantity'];
                $mrp = (float) ($itemData['mrp_price'] ?? 0);
                $commPercent = (float) ($itemData['purchase_commission_percent'] ?? 0);
                $cost = (float) $itemData['cost_price'];
                $shopDiscPercent = (float) ($itemData['shop_discount_percent'] ?? 0);
                $sale = (float) ($itemData['sale_price'] ?? $cost);

                $itemSubtotal = $qty * $cost;
                $totalAmount += $itemSubtotal;

                $bookId = !empty($itemData['book_id']) ? (int)$itemData['book_id'] : null;
                $authorName = trim((string)($itemData['author'] ?? ''));
                $categoryName = trim((string)($itemData['category_name'] ?? ''));
                $categoryId = !empty($itemData['category_id']) ? (int)$itemData['category_id'] : null;

                if ($purchaseCategory === 'books') {
                    // Auto resolve or create Category if new name is entered
                    if (!$categoryId && !empty($categoryName)) {
                        $cat = Category::where('name', $categoryName)->first();
                        if (!$cat) {
                            $catSlugBase = $this->bengaliToEnglish($categoryName) ?: 'cat-' . uniqid();
                            $catSlug = $catSlugBase;
                            $c = 1;
                            while (Category::where('slug', $catSlug)->exists()) {
                                $catSlug = $catSlugBase . '-' . (++$c);
                            }
                            $cat = Category::create([
                                'name'      => $categoryName,
                                'slug'      => $catSlug,
                                'is_active' => true,
                            ]);
                        }
                        $categoryId = $cat->id;
                    }

                    // Auto resolve or create Author in Author directory using Unified registration
                    $authorId = null;
                    if (!empty($authorName)) {
                        $author = \Modules\Author\Models\Author::findOrCreateUnified([
                            'name'      => $authorName,
                            'is_active' => true,
                        ]);
                        $authorId = $author->id;
                    }

                    $bookRegularPrice = $mrp > 0 ? $mrp : ($sale > 0 ? $sale : $cost);
                    $bookDiscountPrice = ($sale > 0 && $sale < $bookRegularPrice) ? $sale : null;

                    // Deduplication: If bookId was not explicitly selected from dropdown, check if matching title/ISBN exists
                    if (!$bookId && !empty($itemData['title'])) {
                        $existingBook = Book::where('title', trim($itemData['title']))
                            ->when($publisherId, fn($q) => $q->where('publisher_id', $publisherId))
                            ->first();
                        if (!$existingBook && !empty($itemData['isbn'])) {
                            $existingBook = Book::where('isbn', trim($itemData['isbn']))->first();
                        }
                        if (!$existingBook) {
                            $existingBook = Book::where('title', trim($itemData['title']))->first();
                        }
                        if ($existingBook) {
                            $bookId = $existingBook->id;
                        }
                    }

                    // If existing book is selected or matched, update bookshop stock and price
                    if ($bookId && Book::where('id', $bookId)->exists()) {
                        $book = Book::find($bookId);
                        $book->increment('stock_quantity', $qty);
                        $book->stock_status = 'in_stock';
                        $book->price = $bookRegularPrice;
                        $book->discount_price = $bookDiscountPrice;
                        $book->cost_price = $cost;
                        if (!$book->publisher_id) {
                            $book->publisher_id = $publisherId;
                        }
                        if ($categoryId && !$book->category_id) {
                            $book->category_id = $categoryId;
                        }
                        if (!empty($itemData['isbn'])) {
                            $book->isbn = $itemData['isbn'];
                        }
                        if (!empty($itemData['edition'])) {
                            $book->edition = $itemData['edition'];
                        }
                        if (!empty($itemData['cover_type'])) {
                            $book->cover_type = $itemData['cover_type'];
                        }
                        if (!empty($itemData['page_count'])) {
                            $book->page_count = (int)$itemData['page_count'];
                        }
                        if (!empty($itemData['book_size'])) {
                            $book->book_size = $itemData['book_size'];
                        }
                        if (!empty($itemData['paper_type'])) {
                            $book->paper_type = $itemData['paper_type'];
                        }
                        if ($authorId) {
                            $book->authors()->syncWithoutDetaching([$authorId]);
                        }
                        $book->is_active = true;
                        $book->save();
                    } else {
                        // AUTO CREATE NEW BOOK IN BOOKSHOP INVENTORY!
                        $bookTitle = trim($itemData['title']);
                        $slugBase = $this->bengaliToEnglish($bookTitle) ?: Str::slug(Str::random(8));
                        $slug = $slugBase;
                        $c = 1;
                        while (Book::withTrashed()->where('slug', $slug)->exists()) {
                            $slug = $slugBase . '-' . (++$c);
                        }

                        $newBook = new Book();
                        $newBook->title = $bookTitle;
                        $newBook->slug = $slug;
                        $newBook->publisher_id = $publisherId;
                        $newBook->category_id = $categoryId;
                        $newBook->author_name = !empty($authorName) ? $authorName : null;
                        $newBook->stock_quantity = $qty;
                        $newBook->stock_status = 'in_stock';
                        $newBook->price = $bookRegularPrice;
                        $newBook->discount_price = $bookDiscountPrice;
                        $newBook->cost_price = $cost;
                        $newBook->isbn = $itemData['isbn'] ?? null;
                        $newBook->edition = $itemData['edition'] ?? null;
                        $newBook->cover_type = $itemData['cover_type'] ?? 'paperback';
                        $newBook->page_count = !empty($itemData['page_count']) ? (int)$itemData['page_count'] : null;
                        $newBook->book_size = $itemData['book_size'] ?? null;
                        $newBook->paper_type = $itemData['paper_type'] ?? null;
                        $newBook->is_active = true;
                        $newBook->save();

                        if ($authorId) {
                            $newBook->authors()->syncWithoutDetaching([$authorId]);
                        }
                        $bookId = $newBook->id;
                    }
                } else {
                    $bookId = null;
                    $categoryId = null;
                }

                // Save Purchase Item
                $item = new PublisherPurchaseItem();
                $item->purchase_id = $purchase->id;
                $item->item_type = $itemData['item_type'] ?? ($purchaseCategory === 'books' ? 'book' : ($purchaseCategory === 'raw_materials' ? 'raw_material' : 'other'));
                $item->item_name = $itemData['item_name'] ?? $itemData['title'];
                $item->size_spec = $itemData['size_spec'] ?? ($itemData['book_size'] ?? null);
                $item->unit = $itemData['unit'] ?? ($purchaseCategory === 'books' ? 'কপি' : 'পিস');
                $item->quality_spec = $itemData['quality_spec'] ?? ($itemData['cover_type'] ?? null);
                $item->item_notes = $itemData['item_notes'] ?? null;
                $item->book_id = $bookId;
                $item->book_title = $itemData['title'];
                $item->author_name = $authorName ?: null;
                $item->category_id = $categoryId;
                $item->quantity = $qty;
                $item->reams_quantity = isset($itemData['reams_quantity']) && $itemData['reams_quantity'] !== '' ? (float)$itemData['reams_quantity'] : null;
                $item->mrp_price = $mrp;
                $item->purchase_commission_percent = $commPercent;
                $item->unit_cost_price = $cost;
                $item->shop_discount_percent = $shopDiscPercent;
                $item->unit_sale_price = $sale;
                $item->subtotal = $itemSubtotal;
                $item->save();
            }

            // Calculate Grand Total and Dues
            $grandTotal = max(0, $totalAmount - $discount);
            $purchase->total_amount = $totalAmount;
            $purchase->grand_total = $grandTotal;

            // If initial payment was made
            if ($initialPaid > 0) {
                $payNo = 'PAY-' . date('Ymd') . '-' . rand(1000, 9999);
                PublisherPayment::create([
                    'purchase_id'     => $purchase->id,
                    'publisher_id'    => $purchase->publisher_id,
                    'payment_no'      => $payNo,
                    'payment_date'    => $purchase->purchase_date,
                    'amount'          => $initialPaid,
                    'payment_method'  => $request->input('payment_method', 'cash'),
                    'transaction_ref' => $request->input('transaction_ref'),
                    'note'            => 'ক্রয়ের প্রাথমিক পরিশোধ',
                    'recorded_by'     => auth()->id(),
                ]);
            }

            $purchase->recalculate();

            $successMsg = $purchaseCategory === 'books'
                ? "বই ক্রয় ইনভয়েস #{$purchase->purchase_no} সফলভাবে সংরক্ষিত হয়েছে এবং বইসমূহ স্বয়ংক্রিয়ভাবে বুকশপ ইনভেনটরিতে যুক্ত হয়েছে।"
                : "কাঁচামাল ও প্রেস বিল ইনভয়েস #{$purchase->purchase_no} সফলভাবে সংরক্ষিত হয়েছে।";

            return redirect()->route('admin.purchases.show', $purchase->id)
                ->with('success', $successMsg);
        });
    }

    /**
     * Show form to edit an existing publisher purchase.
     */
    public function edit(PublisherPurchase $purchase): View
    {
        $purchase->load(['publisher', 'items.book.category', 'payments.recorder']);
        $publishers = Publisher::where('is_active', true)->orderBy('name')->get();
        $authors = \Modules\Author\Models\Author::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $books = Book::select('id', 'title', 'price', 'stock_quantity', 'publisher_id', 'category_id', 'author_name')->orderBy('title')->get();

        // Distinct existing vendors for raw materials & other purchases
        $existingVendors = PublisherPurchase::whereNotNull('vendor_name')
            ->where('vendor_name', '!=', '')
            ->select('vendor_name', 'vendor_phone', 'vendor_address')
            ->distinct()
            ->orderBy('vendor_name')
            ->get();

        // Calculate previous dues for this same publisher / vendor from other invoices
        $vendorName = $purchase->vendor_name ?: $purchase->supplier_name;
        $publisherId = $purchase->publisher_id;

        $otherPurchasesQuery = PublisherPurchase::where('id', '!=', $purchase->id)
            ->whereIn('payment_status', ['due', 'partial']);

        if ($publisherId) {
            $otherPurchasesQuery->where('publisher_id', $publisherId);
        } elseif (!empty($vendorName)) {
            $otherPurchasesQuery->where(function($q) use ($vendorName) {
                $q->where('vendor_name', $vendorName)->orWhere('supplier_name', $vendorName);
            });
        } else {
            $otherPurchasesQuery->whereRaw('1 = 0');
        }

        $previousDue = (float) $otherPurchasesQuery->sum('due_amount');
        $otherPendingInvoices = $otherPurchasesQuery->orderBy('purchase_date')->get();

        // Preload publisher and vendor due maps for instant dynamic recalculation on party change
        $publisherDueMap = PublisherPurchase::where('id', '!=', $purchase->id)
            ->whereNotNull('publisher_id')
            ->whereIn('payment_status', ['due', 'partial'])
            ->groupBy('publisher_id')
            ->selectRaw('publisher_id, sum(due_amount) as total_due')
            ->pluck('total_due', 'publisher_id')
            ->all();

        $vendorDueMap = PublisherPurchase::where('id', '!=', $purchase->id)
            ->whereNotNull('vendor_name')
            ->where('vendor_name', '!=', '')
            ->whereIn('payment_status', ['due', 'partial'])
            ->groupBy('vendor_name')
            ->selectRaw('vendor_name, sum(due_amount) as total_due')
            ->pluck('total_due', 'vendor_name')
            ->all();

        $paymentMethods = PublisherPayment::paymentMethods();

        return view('admin.purchases.edit', compact(
            'purchase', 'publishers', 'authors', 'categories', 'books', 'existingVendors',
            'previousDue', 'otherPendingInvoices', 'publisherDueMap', 'vendorDueMap', 'paymentMethods'
        ));
    }

    /**
     * Update purchase, re-adjust inventory stocks, items, and recalculate financials.
     */
    public function update(Request $request, PublisherPurchase $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_category'           => 'nullable|in:books,raw_materials,other',
            'supplier_name'               => 'nullable|string|max:255',
            'vendor_name'                 => 'nullable|string|max:255',
            'publisher_id'                => 'nullable|integer',
            'publisher_name'              => 'nullable|string|max:255',
            'publisher_phone'             => 'nullable|string|max:50',
            'publisher_address'           => 'nullable|string|max:255',
            'publisher_memo_no'           => 'nullable|string|max:100',
            'purchase_no'                 => 'required|string|max:50|unique:publisher_purchases,purchase_no,' . $purchase->id,
            'purchase_date'               => 'required|date',
            'due_date'                    => 'nullable|date',
            'payment_type'                => 'required|in:cash,credit,partial,installment',
            'installment_count'           => 'nullable|integer|min:1',
            'installment_notes'           => 'nullable|string|max:1000',
            'discount_amount'             => 'nullable|numeric|min:0',
            'notes'                       => 'nullable|string|max:1000',
            'items'                       => 'required|array|min:1',
            'items.*.title'               => 'required|string|max:255',
            'items.*.book_id'             => 'nullable|integer',
            'items.*.author'              => 'nullable|string|max:255',
            'items.*.category_id'         => 'nullable|integer',
            'items.*.category_name'       => 'nullable|string|max:100',
            'items.*.quantity'            => 'required|integer|min:1',
            'items.*.mrp_price'           => 'nullable|numeric|min:0',
            'items.*.purchase_commission_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.cost_price'          => 'required|numeric|min:0',
            'items.*.shop_discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.sale_price'          => 'nullable|numeric|min:0',
        ], [
            'purchase_no.required' => 'ক্রয় ইনভয়েস নম্বর দিন।',
            'purchase_no.unique'   => 'এই ইনভয়েস নম্বরটি অন্য ক্রয়ে ব্যবহার করা হয়েছে।',
            'items.required'       => 'কমপক্ষে একটি বই তালিকায় থাকতে হবে।',
            'items.min'            => 'কমপক্ষে একটি বই তালিকায় থাকতে হবে।',
        ]);

        $purchaseCategory = $validated['purchase_category'] ?? ($purchase->purchase_category ?: 'books');
        $vendorName = trim((string)($validated['vendor_name'] ?? $validated['supplier_name'] ?? $validated['publisher_name'] ?? ''));

        if ($purchaseCategory === 'books' && empty($validated['publisher_id']) && empty($vendorName)) {
            return back()->withInput()->withErrors(['publisher_id' => 'অনুগ্রহ করে বিদ্যমান প্রকাশনী বেছে নিন অথবা নতুন প্রকাশনীর নাম লিখুন।']);
        }

        return DB::transaction(function () use ($request, $validated, $purchase, $purchaseCategory, $vendorName) {
            // Roll back previous inventory quantities for old items
            $oldItems = $purchase->items()->get();
            foreach ($oldItems as $oldItem) {
                if ($oldItem->book_id && Book::where('id', $oldItem->book_id)->exists()) {
                    $book = Book::find($oldItem->book_id);
                    $book->stock_quantity = max(0, $book->stock_quantity - $oldItem->quantity);
                    $book->save();
                }
            }
            $purchase->items()->delete();

            // Auto resolve or create Publisher
            $publisherId = null;
            $publisher = null;
            if ($purchaseCategory === 'books') {
                $publisherId = !empty($validated['publisher_id']) ? (int)$validated['publisher_id'] : null;
                $publisherName = $vendorName;

                if (!$publisherId || !Publisher::where('id', $publisherId)->exists()) {
                    if (!empty($publisherName)) {
                        $pub = Publisher::where('name', $publisherName)->first();
                        if (!$pub) {
                            $slugBase = $this->bengaliToEnglish($publisherName) ?: 'pub-' . uniqid();
                            $slug = $slugBase;
                            $c = 1;
                            while (Publisher::where('slug', $slug)->exists()) {
                                $slug = $slugBase . '-' . (++$c);
                            }
                            $pub = Publisher::create([
                                'name' => $publisherName,
                                'slug' => $slug,
                                'phone' => $validated['publisher_phone'] ?? null,
                                'address' => $validated['publisher_address'] ?? null,
                                'is_active' => true,
                            ]);
                        }
                        $publisherId = $pub->id;
                        $publisher = $pub;
                    } else {
                        $pub = Publisher::first();
                        $publisherId = $pub ? $pub->id : null;
                        $publisher = $pub;
                    }
                } else {
                    $publisher = Publisher::find($publisherId);
                }
            }

            $totalAmount = 0.0;
            $discount = (float) ($validated['discount_amount'] ?? 0);

            // Update Purchase header record
            $purchase->purchase_no = $validated['purchase_no'];
            $purchase->purchase_category = $purchaseCategory;
            $purchase->publisher_memo_no = $validated['publisher_memo_no'] ?? null;
            $purchase->publisher_id = $publisherId;
            $purchase->supplier_name = $vendorName ?: ($purchaseCategory === 'books' ? ($publisher?->name ?? 'বই প্রকাশনী') : 'ভেন্ডর');
            $purchase->vendor_name = $vendorName ?: ($purchaseCategory === 'books' ? ($publisher?->name ?? 'বই প্রকাশনী') : 'ভেন্ডর');
            $purchase->vendor_phone = $request->input('vendor_phone') ?: ($publisher ? $publisher->phone : null);
            $purchase->vendor_address = $request->input('vendor_address') ?: ($publisher ? $publisher->address : null);
            $purchase->purchase_date = $validated['purchase_date'];
            $purchase->due_date = $validated['due_date'] ?? null;
            $purchase->payment_type = $validated['payment_type'];
            $purchase->installment_count = !empty($validated['installment_count']) ? (int)$validated['installment_count'] : 1;
            $purchase->installment_notes = $validated['installment_notes'] ?? null;
            $purchase->discount_amount = $discount;
            $purchase->notes = $validated['notes'] ?? null;

            // Process purchase items & sync with Bookshop
            foreach ($validated['items'] as $itemData) {
                $qty = (int) $itemData['quantity'];
                $mrp = (float) ($itemData['mrp_price'] ?? 0);
                $commPercent = (float) ($itemData['purchase_commission_percent'] ?? 0);
                $cost = (float) $itemData['cost_price'];
                $shopDiscPercent = (float) ($itemData['shop_discount_percent'] ?? 0);
                $sale = (float) ($itemData['sale_price'] ?? $cost);

                $itemSubtotal = $qty * $cost;
                $totalAmount += $itemSubtotal;

                $bookId = !empty($itemData['book_id']) ? (int)$itemData['book_id'] : null;
                $authorName = trim((string)($itemData['author'] ?? ''));
                $categoryName = trim((string)($itemData['category_name'] ?? ''));

                if ($purchaseCategory === 'books') {
                    // Auto resolve or create Category
                    $categoryId = !empty($itemData['category_id']) ? (int)$itemData['category_id'] : null;
                    if (!$categoryId && !empty($categoryName)) {
                        $cat = Category::where('name', $categoryName)->first();
                        if (!$cat) {
                            $catSlugBase = $this->bengaliToEnglish($categoryName) ?: 'cat-' . uniqid();
                            $catSlug = $catSlugBase;
                            $c = 1;
                            while (Category::where('slug', $catSlug)->exists()) {
                                $catSlug = $catSlugBase . '-' . (++$c);
                            }
                            $cat = Category::create([
                                'name' => $categoryName,
                                'slug' => $catSlug,
                                'is_active' => true,
                            ]);
                        }
                        $categoryId = $cat->id;
                    }

                    // Auto resolve or create Author using Unified registration
                    $authorId = null;
                    if (!empty($authorName)) {
                        $author = \Modules\Author\Models\Author::findOrCreateUnified([
                            'name'      => $authorName,
                            'is_active' => true,
                        ]);
                        $authorId = $author->id;
                    }

                    // Deduplication: Match existing book by title/publisher/ISBN if bookId wasn't passed
                    if (!$bookId && !empty($itemData['title'])) {
                        $existingBook = Book::where('title', trim($itemData['title']))
                            ->when($publisherId, fn($q) => $q->where('publisher_id', $publisherId))
                            ->first();
                        if (!$existingBook && !empty($itemData['isbn'])) {
                            $existingBook = Book::where('isbn', trim($itemData['isbn']))->first();
                        }
                        if (!$existingBook) {
                            $existingBook = Book::where('title', trim($itemData['title']))->first();
                        }
                        if ($existingBook) {
                            $bookId = $existingBook->id;
                        }
                    }

                    $bookRegularPrice = $mrp > 0 ? $mrp : ($sale > 0 ? $sale : $cost);
                    $bookDiscountPrice = ($sale > 0 && $sale < $bookRegularPrice) ? $sale : null;

                    if ($bookId && Book::where('id', $bookId)->exists()) {
                        $book = Book::find($bookId);
                        $book->increment('stock_quantity', $qty);
                        $book->stock_status = 'in_stock';
                        $book->price = $bookRegularPrice;
                        $book->discount_price = $bookDiscountPrice;
                        if (!$book->publisher_id) {
                            $book->publisher_id = $publisherId;
                        }
                        if ($categoryId && !$book->category_id) {
                            $book->category_id = $categoryId;
                        }
                        if ($authorId) {
                            $book->authors()->syncWithoutDetaching([$authorId]);
                        }
                        $book->is_active = true;
                        $book->save();
                    } else {
                        $bookTitle = trim($itemData['title']);
                        $slugBase = $this->bengaliToEnglish($bookTitle) ?: Str::slug(Str::random(8));
                        $slug = $slugBase;
                        $c = 1;
                        while (Book::withTrashed()->where('slug', $slug)->exists()) {
                            $slug = $slugBase . '-' . (++$c);
                        }

                        $newBook = new Book();
                        $newBook->title = $bookTitle;
                        $newBook->slug = $slug;
                        $newBook->publisher_id = $publisherId;
                        $newBook->category_id = $categoryId;
                        $newBook->author_name = !empty($authorName) ? $authorName : null;
                        $newBook->stock_quantity = $qty;
                        $newBook->stock_status = 'in_stock';
                        $newBook->price = $bookRegularPrice;
                        $newBook->discount_price = $bookDiscountPrice;
                        $newBook->is_active = true;
                        $newBook->published_at = now();
                        $newBook->save();

                        if ($authorId) {
                            $newBook->authors()->syncWithoutDetaching([$authorId]);
                        }

                        $bookId = $newBook->id;
                    }
                } else {
                    $bookId = null;
                    $categoryId = null;
                }

                // Save Purchase Item
                $item = new PublisherPurchaseItem();
                $item->purchase_id = $purchase->id;
                $item->item_type = $itemData['item_type'] ?? ($purchaseCategory === 'books' ? 'book' : ($purchaseCategory === 'raw_materials' ? 'raw_material' : 'other'));
                $item->item_name = $itemData['item_name'] ?? $itemData['title'];
                $item->size_spec = $itemData['size_spec'] ?? ($itemData['book_size'] ?? null);
                $item->unit = $itemData['unit'] ?? ($purchaseCategory === 'books' ? 'কপি' : 'পিস');
                $item->quality_spec = $itemData['quality_spec'] ?? ($itemData['cover_type'] ?? null);
                $item->item_notes = $itemData['item_notes'] ?? null;
                $item->book_id = $bookId;
                $item->book_title = $itemData['title'];
                $item->author_name = $authorName ?: null;
                $item->category_id = $categoryId;
                $item->quantity = $qty;
                $item->reams_quantity = isset($itemData['reams_quantity']) && $itemData['reams_quantity'] !== '' ? (float)$itemData['reams_quantity'] : null;
                $item->mrp_price = $mrp;
                $item->purchase_commission_percent = $commPercent;
                $item->unit_cost_price = $cost;
                $item->shop_discount_percent = $shopDiscPercent;
                $item->unit_sale_price = $sale;
                $item->subtotal = $itemSubtotal;
                $item->save();
            }

            $grandTotal = max(0, $totalAmount - $discount);
            $purchase->total_amount = $totalAmount;
            $purchase->grand_total = $grandTotal;
            $purchase->save();

            // Recalculate paid and due amount
            $purchase->recalculate();

            return redirect()->route('admin.purchases.show', $purchase->id)
                ->with('success', "ক্রয় ইনভয়েস #{$purchase->purchase_no} সফলভাবে আপডেট ও সংশোধন করা হয়েছে।");
        });
    }

    /**
     * Show single purchase invoice with items and repayment history.
     */
    public function show(PublisherPurchase $purchase): View
    {
        $purchase->load(['publisher', 'items.book.category', 'payments.recorder', 'creator']);
        $paymentMethods = PublisherPayment::paymentMethods();

        return view('admin.purchases.show', compact('purchase', 'paymentMethods'));
    }

    /**
     * Repayment history, installment payments list, and vendor ledger overview.
     */
    public function payments(Request $request): View
    {
        $publisherId = $request->input('publisher_id');
        $vendorName = $request->string('vendor_name')->trim()->value();
        $method = $request->input('payment_method');
        $category = $request->input('category');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $search = $request->string('search')->trim()->value();

        $query = PublisherPayment::query()
            ->with(['publisher', 'purchase', 'recorder'])
            ->when($publisherId, fn($q) => $q->where('publisher_id', $publisherId))
            ->when($vendorName, function($q) use ($vendorName) {
                $q->where(function($sub) use ($vendorName) {
                    $sub->where('vendor_name', $vendorName)
                        ->orWhereHas('purchase', fn($p) => $p->where('vendor_name', $vendorName)->orWhere('supplier_name', $vendorName));
                });
            })
            ->when($method, fn($q) => $q->where('payment_method', $method))
            ->when($category, function($q) use ($category) {
                $q->whereHas('purchase', fn($p) => $p->where('purchase_category', $category));
            })
            ->when($dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('payment_date', '<=', $dateTo))
            ->when($search, function($q, $term) {
                $like = '%' . $term . '%';
                $q->where('payment_no', 'like', $like)
                  ->orWhere('transaction_ref', 'like', $like)
                  ->orWhere('note', 'like', $like)
                  ->orWhere('vendor_name', 'like', $like)
                  ->orWhereHas('publisher', fn($p) => $p->where('name', 'like', $like))
                  ->orWhereHas('purchase', fn($p) => $p->where('purchase_no', 'like', $like)->orWhere('vendor_name', 'like', $like)->orWhere('supplier_name', 'like', $like));
            })
            ->latest('payment_date')
            ->latest('id');

        $payments = $query->paginate(20)->withQueryString();

        $publishers = Publisher::orderBy('name')->pluck('name', 'id')->all();
        $paymentMethods = PublisherPayment::paymentMethods();

        // Get unique vendors (raw materials, press, paper shops, other)
        $rawVendors = PublisherPurchase::whereNotNull('vendor_name')
            ->where('vendor_name', '!=', '')
            ->distinct()
            ->pluck('vendor_name')
            ->merge(
                PublisherPurchase::whereNotNull('supplier_name')
                    ->where('supplier_name', '!=', '')
                    ->distinct()
                    ->pluck('supplier_name')
            )
            ->unique()
            ->sort()
            ->values()
            ->all();

        // Fetch all pending purchases with safe party name and due amounts
        $pendingPurchases = PublisherPurchase::whereIn('payment_status', ['due', 'partial'])
            ->with('publisher')
            ->orderBy('purchase_date')
            ->get();

        // Overall stats
        $totalPaidSum = (float) PublisherPayment::sum('amount');
        $totalDueSum = (float) PublisherPurchase::sum('due_amount');
        $totalPurchaseSum = (float) PublisherPurchase::sum('grand_total');
        $pendingCount = $pendingPurchases->count();

        // Build summary cards for vendor ledger
        $vendorLedgers = $this->buildVendorLedgersSummary();

        return view('admin.purchases.payments', compact(
            'payments', 'publishers', 'rawVendors', 'paymentMethods', 'pendingPurchases',
            'totalPaidSum', 'totalDueSum', 'totalPurchaseSum', 'pendingCount',
            'vendorLedgers', 'publisherId', 'vendorName', 'method', 'category', 'dateFrom', 'dateTo', 'search'
        ));
    }

    /**
     * Dedicated Vendor / Supplier / Press / Paper Shop Running Ledger & Statement.
     */
    public function ledger(Request $request): View
    {
        $publisherId = $request->input('publisher_id');
        $vendorName = $request->string('vendor_name')->trim()->value();
        $partyKey = $request->input('party'); // e.g. "pub_1" or "vendor_XYZ"
        $category = $request->input('category');
        $search = $request->string('search')->trim()->value();
        $hasDue = $request->boolean('has_due');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if (!empty($partyKey)) {
            if (str_starts_with($partyKey, 'pub_')) {
                $publisherId = (int) substr($partyKey, 4);
                $vendorName = null;
            } elseif (str_starts_with($partyKey, 'vendor_')) {
                $vendorName = substr($partyKey, 7);
                $publisherId = null;
            }
        }

        $publishers = Publisher::orderBy('name')->pluck('name', 'id')->all();
        $rawVendors = PublisherPurchase::whereNotNull('vendor_name')
            ->where('vendor_name', '!=', '')
            ->distinct()
            ->pluck('vendor_name')
            ->unique()
            ->sort()
            ->values()
            ->all();

        $paymentMethods = PublisherPayment::paymentMethods();
        $invoiceSettings = IdeaAccountingController::getInvoiceSettings();

        // If a specific party is selected, generate their detailed running statement
        $statement = null;
        $activeParty = null;

        if ($publisherId || !empty($vendorName)) {
            $statement = $this->generatePartyStatement($publisherId, $vendorName, $dateFrom, $dateTo);
            $activeParty = $statement['party'];
        }

        $allSummaries = $this->buildVendorLedgersSummary($search, $category, $hasDue, $dateFrom, $dateTo);

        return view('admin.purchases.ledger', compact(
            'publishers', 'rawVendors', 'paymentMethods', 'statement', 'activeParty',
            'allSummaries', 'publisherId', 'vendorName', 'category', 'search', 'hasDue',
            'dateFrom', 'dateTo', 'invoiceSettings'
        ));
    }

    /**
     * Printable Official Vendor Payment Voucher Slip.
     */
    public function paymentVoucher(PublisherPayment $payment): View
    {
        $payment->load(['purchase.publisher', 'recorder']);
        $settings = IdeaAccountingController::getInvoiceSettings();
        
        $partyName = $payment->party_name;
        $partyPhone = $payment->purchase ? ($payment->purchase->party_phone ?: ($payment->publisher?->phone ?? '—')) : '—';
        $partyAddress = $payment->purchase ? ($payment->purchase->party_address ?: ($payment->publisher?->address ?? '—')) : '—';
        
        return view('admin.purchases.voucher', compact('payment', 'settings', 'partyName', 'partyPhone', 'partyAddress'));
    }

    /**
     * Record a new payment (either against a specific purchase or direct to supplier ledger with FIFO auto-settlement).
    */
    public function storePayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_target'  => 'nullable|in:specific_invoice,supplier_account',
            'purchase_id'     => 'nullable|integer|exists:publisher_purchases,id',
            'publisher_id'    => 'nullable|integer|exists:publishers,id',
            'vendor_name'     => 'nullable|string|max:255',
            'payment_date'    => 'required|date',
            'amount'          => 'required|numeric|min:0.01',
            'payment_method'  => 'required|string|max:50',
            'transaction_ref' => 'nullable|string|max:100',
            'note'            => 'nullable|string|max:500',
            'due_date'        => 'nullable|date', // Optional next installment deadline
        ], [
            'amount.required' => 'পরিশোধের পরিমাণ টাকা দিন।',
            'amount.min'      => 'পরিশোধের পরিমাণ কমপক্ষে ০.০১ টাকা হতে হবে।',
        ]);

        $amount = (float) $validated['amount'];
        $paymentDate = $validated['payment_date'];
        $paymentMethod = $validated['payment_method'];
        $trxRef = $validated['transaction_ref'] ?? null;
        $note = $validated['note'] ?? null;
        $dueDate = $request->filled('due_date') ? $request->input('due_date') : null;

        return DB::transaction(function () use ($validated, $amount, $paymentDate, $paymentMethod, $trxRef, $note, $dueDate) {
            $purchaseId = !empty($validated['purchase_id']) ? (int)$validated['purchase_id'] : null;

            // 1. SPECIFIC INVOICE PAYMENT
            if ($purchaseId) {
                $purchase = PublisherPurchase::findOrFail($purchaseId);
                $payNo = 'PAY-' . date('Ymd') . '-' . rand(1000, 9999);

                PublisherPayment::create([
                    'purchase_id'     => $purchase->id,
                    'publisher_id'    => $purchase->publisher_id,
                    'vendor_name'     => $purchase->vendor_name ?: $purchase->supplier_name,
                    'payment_no'      => $payNo,
                    'payment_date'    => $paymentDate,
                    'amount'          => $amount,
                    'payment_method'  => $paymentMethod,
                    'transaction_ref' => $trxRef,
                    'note'            => $note,
                    'recorded_by'     => auth()->id(),
                ]);

                if ($dueDate !== null || request()->has('due_date')) {
                    $purchase->due_date = $dueDate;
                }

                $purchase->recalculate();

                $party = $purchase->party_name;
                return back()->with('success', "ইনভয়েস #{$purchase->purchase_no} ({$party})-এর বিপরীতে ৳" . number_format($amount, 2) . " টাকা সফলভাবে পরিশোধ রেকর্ড করা হয়েছে (ভাউচার #{$payNo})।");
            }

            // 2. SUPPLIER ACCOUNT / RUNNING LEDGER PAYMENT (FIFO Auto-Settlement across due invoices)
            $publisherId = !empty($validated['publisher_id']) ? (int)$validated['publisher_id'] : null;
            $vendorName = trim((string)($validated['vendor_name'] ?? ''));

            if (!$publisherId && empty($vendorName)) {
                return back()->withInput()->withErrors(['purchase_id' => 'অনুগ্রহ করে একটি ক্রয় ইনভয়েস নির্বাচন করুন অথবা সরবরাহকারী/ভেন্ডরের নাম দিন।']);
            }

            // Find all pending / partial due purchases for this party in chronological order
            $duePurchasesQuery = PublisherPurchase::whereIn('payment_status', ['due', 'partial'])
                ->orderBy('purchase_date', 'asc')
                ->orderBy('id', 'asc');

            if ($publisherId) {
                $duePurchasesQuery->where('publisher_id', $publisherId);
                $pubModel = Publisher::find($publisherId);
                $partyDisplayName = $pubModel ? $pubModel->name : 'প্রকাশনী #' . $publisherId;
            } else {
                $duePurchasesQuery->where(function($q) use ($vendorName) {
                    $q->where('vendor_name', $vendorName)->orWhere('supplier_name', $vendorName);
                });
                $partyDisplayName = $vendorName;
            }

            $duePurchases = $duePurchasesQuery->get();
            $remainingPayment = $amount;
            $settledInvoices = [];

            foreach ($duePurchases as $p) {
                if ($remainingPayment <= 0.001) {
                    break;
                }

                $pDue = (float) $p->due_amount;
                if ($pDue <= 0) continue;

                $allocatedAmount = min($remainingPayment, $pDue);
                $payNo = 'PAY-' . date('Ymd') . '-' . rand(1000, 9999);

                PublisherPayment::create([
                    'purchase_id'     => $p->id,
                    'publisher_id'    => $p->publisher_id,
                    'vendor_name'     => $p->vendor_name ?: ($p->supplier_name ?: $vendorName),
                    'payment_no'      => $payNo,
                    'payment_date'    => $paymentDate,
                    'amount'          => $allocatedAmount,
                    'payment_method'  => $paymentMethod,
                    'transaction_ref' => $trxRef,
                    'note'            => $note ? "{$note} [সাপ্লায়ার চলতি খাতা থেকে সমন্বয়]" : "সাপ্লায়ার চলতি খাতা থেকে ইনভয়েস #{$p->purchase_no} সমন্বয়",
                    'recorded_by'     => auth()->id(),
                ]);

                if ($dueDate !== null || request()->has('due_date')) {
                    $p->due_date = $dueDate;
                }

                $p->recalculate();
                $settledInvoices[] = "#{$p->purchase_no} (৳" . number_format($allocatedAmount, 2) . ")";
                $remainingPayment -= $allocatedAmount;
            }

            // If there was extra money or no due invoices at all, record a standalone unallocated payment on account
            if ($remainingPayment > 0.001 || empty($settledInvoices)) {
                $payNo = 'PAY-' . date('Ymd') . '-' . rand(1000, 9999);
                PublisherPayment::create([
                    'purchase_id'     => null,
                    'publisher_id'    => $publisherId,
                    'vendor_name'     => $vendorName ?: null,
                    'payment_no'      => $payNo,
                    'payment_date'    => $paymentDate,
                    'amount'          => $remainingPayment > 0.001 ? $remainingPayment : $amount,
                    'payment_method'  => $paymentMethod,
                    'transaction_ref' => $trxRef,
                    'note'            => $note ? "{$note} [চলতি খাতা অগ্রিম/অতিরিক্ত জমা]" : "চলতি খাতা জমা / কিস্তি",
                    'recorded_by'     => auth()->id(),
                ]);
            }

            $settledSummary = !empty($settledInvoices) ? ' (' . implode(', ', $settledInvoices) . ')' : '';
            return back()->with('success', "{$partyDisplayName}-এর চলতি খাতায় ৳" . number_format($amount, 2) . " টাকা সফলভাবে জমা ও সমন্বয় করা হয়েছে{$settledSummary}!");
        });
    }

    /**
     * Delete an existing repayment and re-sync invoice dues.
     */
    public function destroyPayment(PublisherPayment $payment): RedirectResponse
    {
        $purchase = $payment->purchase;
        $amount = (float) $payment->amount;
        $voucherNo = $payment->payment_no;

        $payment->delete();

        if ($purchase) {
            $purchase->recalculate();
        }

        return back()->with('success', "পরিশোধ ভাউচার #{$voucherNo} (৳" . number_format($amount, 2) . ") সফলভাবে মুছে ফেলা হয়েছে এবং বকেয়া পুনঃগণনা করা হয়েছে।");
    }

    /**
     * Helper to build master ledger summaries for all active suppliers / vendors / publishers with aging.
     */
    public function buildVendorLedgersSummary(?string $search = null, ?string $categoryFilter = null, bool $hasDueOnly = false, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $purchases = PublisherPurchase::with('publisher')
            ->when($dateFrom, fn($q) => $q->whereDate('purchase_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('purchase_date', '<=', $dateTo))
            ->when($categoryFilter, fn($q) => $q->where('purchase_category', $categoryFilter))
            ->get();

        $standalonePayments = PublisherPayment::whereNull('purchase_id')->get();

        $parties = [];
        $today = \Carbon\Carbon::today();

        // 1. Process from purchases
        foreach ($purchases as $p) {
            $isPub = !empty($p->publisher_id) && $p->publisher;
            $key = $isPub ? 'pub_' . $p->publisher_id : 'vendor_' . ($p->vendor_name ?: ($p->supplier_name ?: 'Unknown'));
            $name = $p->party_name;
            $category = $p->purchase_category ?: ($isPub ? 'books' : 'raw_materials');
            $phone = $p->party_phone ?: ($isPub ? ($p->publisher->phone ?? '—') : '—');
            $address = $p->party_address ?: ($isPub ? ($p->publisher->address ?? '—') : '—');

            if (!isset($parties[$key])) {
                $parties[$key] = [
                    'key'              => $key,
                    'party_type'       => $isPub ? 'publisher' : 'vendor',
                    'publisher_id'     => $p->publisher_id,
                    'vendor_name'      => $p->vendor_name ?: $p->supplier_name,
                    'name'             => $name,
                    'phone'            => $phone,
                    'address'          => $address,
                    'category'         => $category,
                    'total_billed'     => 0.0,
                    'total_paid'       => 0.0,
                    'current_due'      => 0.0,
                    'invoice_count'    => 0,
                    'overdue_count'    => 0,
                    'last_transaction' => null,
                    'aging'            => [
                        'current'  => 0.0, // 0-30 days
                        'days_30'  => 0.0, // 31-60 days
                        'days_60'  => 0.0, // 61-90 days
                        'days_90p' => 0.0, // 90+ days
                    ],
                ];
            }

            if ($phone !== '—' && $parties[$key]['phone'] === '—') {
                $parties[$key]['phone'] = $phone;
            }
            if ($address !== '—' && $parties[$key]['address'] === '—') {
                $parties[$key]['address'] = $address;
            }

            $parties[$key]['total_billed'] += (float)$p->grand_total;
            $parties[$key]['total_paid'] += (float)$p->paid_amount;
            $pDue = (float)$p->due_amount;
            $parties[$key]['current_due'] += $pDue;
            $parties[$key]['invoice_count'] += 1;

            if ($p->is_overdue) {
                $parties[$key]['overdue_count'] += 1;
            }

            if (!$parties[$key]['last_transaction'] || $p->purchase_date > $parties[$key]['last_transaction']) {
                $parties[$key]['last_transaction'] = $p->purchase_date;
            }

            // Calculate Accounts Payable Aging
            if ($pDue > 0) {
                $pDate = $p->purchase_date ?: $p->created_at;
                $days = $pDate ? $today->diffInDays($pDate) : 0;

                if ($days <= 30) {
                    $parties[$key]['aging']['current'] += $pDue;
                } elseif ($days <= 60) {
                    $parties[$key]['aging']['days_30'] += $pDue;
                } elseif ($days <= 90) {
                    $parties[$key]['aging']['days_60'] += $pDue;
                } else {
                    $parties[$key]['aging']['days_90p'] += $pDue;
                }
            }
        }

        // 2. Adjust standalone payments on account
        foreach ($standalonePayments as $pay) {
            $isPub = !empty($pay->publisher_id);
            $key = $isPub ? 'pub_' . $pay->publisher_id : 'vendor_' . ($pay->vendor_name ?: 'Unknown');

            if (isset($parties[$key])) {
                $parties[$key]['total_paid'] += (float)$pay->amount;
                $parties[$key]['current_due'] = max(0, $parties[$key]['total_billed'] - $parties[$key]['total_paid']);
            }
        }

        // Filter search term
        if (!empty($search)) {
            $term = mb_strtolower($search);
            $parties = array_filter($parties, function ($c) use ($term) {
                return str_contains(mb_strtolower($c['name']), $term) ||
                       str_contains(mb_strtolower($c['phone']), $term) ||
                       str_contains(mb_strtolower($c['address']), $term);
            });
        }

        // Filter has_due only
        if ($hasDueOnly) {
            $parties = array_filter($parties, fn($c) => $c['current_due'] > 0);
        }

        // Sort parties by highest due balance first
        uasort($parties, fn($a, $b) => $b['current_due'] <=> $a['current_due']);

        return array_values($parties);
    }

    /**
     * Helper to generate full chronological statement for a specific vendor or publisher.
     */
    public function generatePartyStatement(?int $publisherId, ?string $vendorName, ?string $dateFrom, ?string $dateTo): array
    {
        $purchasesQuery = PublisherPurchase::with(['items', 'payments.recorder'])
            ->when($publisherId, fn($q) => $q->where('publisher_id', $publisherId))
            ->when(!$publisherId && $vendorName, fn($q) => $q->where(fn($sub) => $sub->where('vendor_name', $vendorName)->orWhere('supplier_name', $vendorName)));

        $paymentsQuery = PublisherPayment::with(['purchase', 'recorder'])
            ->when($publisherId, fn($q) => $q->where('publisher_id', $publisherId))
            ->when(!$publisherId && $vendorName, fn($q) => $q->where(fn($sub) => $sub->where('vendor_name', $vendorName)->orWhereHas('purchase', fn($p) => $p->where('vendor_name', $vendorName)->orWhere('supplier_name', $vendorName))));

        $allPurchases = $purchasesQuery->get();
        $allPayments = $paymentsQuery->get();
        $latestPurchase = $allPurchases->last();

        $partyInfo = [
            'name'     => $publisherId ? (Publisher::find($publisherId)?->name ?? 'Publisher #' . $publisherId) : ($vendorName ?: 'Vendor'),
            'phone'    => $publisherId ? (Publisher::find($publisherId)?->phone ?? '—') : ($latestPurchase?->party_phone ?? '—'),
            'address'  => $publisherId ? (Publisher::find($publisherId)?->address ?? '—') : ($latestPurchase?->party_address ?? '—'),
            'category' => $publisherId ? 'books' : ($allPurchases->first()?->purchase_category ?? 'raw_materials'),
            'type'     => $publisherId ? 'publisher' : 'vendor',
            'pub_id'   => $publisherId,
            'vendor'   => $vendorName,
        ];

        // Due Purchases for Quick Settlement
        $duePurchases = $allPurchases->filter(fn($p) => (float)$p->due_amount > 0);

        // Combine into unified entries
        $entries = [];

        foreach ($allPurchases as $pur) {
            $itemTitles = $pur->items->pluck('item_name')->filter()->take(3)->implode(', ');
            $desc = "ক্রয় চালান/বিল #{$pur->purchase_no}";
            if ($itemTitles) $desc .= " ({$itemTitles})";

            $entries[] = [
                'date'        => $pur->purchase_date ? $pur->purchase_date->format('Y-m-d') : $pur->created_at->format('Y-m-d'),
                'sort_time'   => $pur->purchase_date ? $pur->purchase_date->timestamp : $pur->created_at->timestamp,
                'type'        => 'purchase',
                'type_label'  => 'বাকিতে ক্রয় দাবি (Purchase)',
                'ref_no'      => $pur->purchase_no,
                'purchase_id' => $pur->id,
                'due_date'    => $pur->due_date ? $pur->due_date->format('d M, Y') : null,
                'is_overdue'  => $pur->is_overdue,
                'description' => $desc,
                'category'    => $pur->purchase_category,
                'debit'       => (float) $pur->grand_total, // Payable added
                'credit'      => 0.0,
                'notes'       => $pur->notes,
            ];

            // If this purchase has legacy paid_amount > 0 and no explicit payments linked
            $hasLinkedPayments = $allPayments->where('purchase_id', $pur->id)->count() > 0;
            if (!$hasLinkedPayments && (float)$pur->paid_amount > 0) {
                $entries[] = [
                    'date'        => $pur->purchase_date ? $pur->purchase_date->format('Y-m-d') : $pur->created_at->format('Y-m-d'),
                    'sort_time'   => ($pur->purchase_date ? $pur->purchase_date->timestamp : $pur->created_at->timestamp) + 1,
                    'type'        => 'payment',
                    'type_label'  => 'অগ্রিম পরিশোধ (Advance Paid)',
                    'ref_no'      => $pur->purchase_no . '-ADV',
                    'purchase_id' => $pur->id,
                    'payment_id'  => null,
                    'description' => "ক্রয় চালান #{$pur->purchase_no} এর অগ্রিম পরিশোধ",
                    'category'    => $pur->purchase_category,
                    'debit'       => 0.0,
                    'credit'      => (float) $pur->paid_amount,
                    'notes'       => 'অগ্রিম পরিশোধ',
                    'method'      => $pur->payment_method ?: 'cash',
                ];
            }
        }

        foreach ($allPayments as $pay) {
            $desc = "পরিশোধ / কিস্তি প্রদান (ভাউচার #{$pay->payment_no})";
            if ($pay->purchase) {
                $desc .= " — বিল #{$pay->purchase->purchase_no}";
            }
            if ($pay->transaction_ref) {
                $desc .= " (Trx: {$pay->transaction_ref})";
            }

            $entries[] = [
                'date'        => $pay->payment_date ? $pay->payment_date->format('Y-m-d') : $pay->created_at->format('Y-m-d'),
                'sort_time'   => ($pay->payment_date ? $pay->payment_date->timestamp : $pay->created_at->timestamp) + 1, // payments after purchases on same day
                'type'        => 'payment',
                'type_label'  => 'কিস্তি পরিশোধ (Payment)',
                'ref_no'      => $pay->payment_no,
                'purchase_id' => $pay->purchase_id,
                'payment_id'  => $pay->id,
                'description' => $desc,
                'category'    => $pay->purchase?->purchase_category ?? 'payment',
                'debit'       => 0.0,
                'credit'      => (float) $pay->amount, // Paid
                'notes'       => $pay->note,
                'method'      => $pay->payment_method,
            ];
        }

        // Sort chronologically
        usort($entries, fn($a, $b) => $a['sort_time'] <=> $b['sort_time']);

        // Calculate running balance
        $runningBalance = 0.0;
        $totalBilled = 0.0;
        $totalPaid = 0.0;

        foreach ($entries as &$entry) {
            $runningBalance += ($entry['debit'] - $entry['credit']);
            $entry['balance'] = $runningBalance;
            $totalBilled += $entry['debit'];
            $totalPaid += $entry['credit'];
        }
        unset($entry);

        // Filter by date range if provided
        $filteredEntries = $entries;
        if ($dateFrom) {
            $filteredEntries = array_filter($filteredEntries, fn($e) => $e['date'] >= $dateFrom);
        }
        if ($dateTo) {
            $filteredEntries = array_filter($filteredEntries, fn($e) => $e['date'] <= $dateTo);
        }

        return [
            'party'           => $partyInfo,
            'entries'         => array_values($filteredEntries),
            'due_purchases'   => $duePurchases,
            'total_billed'    => $totalBilled,
            'total_paid'      => $totalPaid,
            'net_due_balance' => max(0, $totalBilled - $totalPaid),
            'total_entries'   => count($filteredEntries),
        ];
    }

    /**
     * Delete a purchase and safely roll back inventory stock quantities.
     */
    public function destroy(PublisherPurchase $purchase): RedirectResponse
    {
        $no = $purchase->purchase_no;

        // Roll back bookshop inventory stock quantities for all items in this purchase
        foreach ($purchase->items as $item) {
            if ($item->book_id && Book::where('id', $item->book_id)->exists()) {
                $book = Book::find($item->book_id);
                $book->stock_quantity = max(0, $book->stock_quantity - $item->quantity);
                $book->save();
            }
        }

        $purchase->payments()->delete();
        $purchase->items()->delete();
        $purchase->delete();

        return redirect()->route('admin.purchases.index')
            ->with('success', "ক্রয় ইনভয়েস #{$no} সফলভাবে মুছে ফেলা হয়েছে এবং ইনভেন্টরি স্টক সমন্বয় করা হয়েছে।");
    }

    /**
     * Monthly Purchase & Sales Comprehensive Report.
     */
    public function monthlyReport(Request $request): View
    {
        $month = (int) $request->input('month', date('n'));
        $year = (int) $request->input('year', date('Y'));

        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }
        if ($year < 2020 || $year > 2035) {
            $year = (int) date('Y');
        }

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        // 1. All Purchases in this month
        $purchases = PublisherPurchase::with(['publisher', 'items.book', 'payments'])
            ->whereDate('purchase_date', '>=', $startDate)
            ->whereDate('purchase_date', '<=', $endDate)
            ->latest('purchase_date')
            ->get();

        $booksPurchases = $purchases->filter(fn($p) => $p->purchase_category === 'books' || is_null($p->purchase_category));
        $rawPurchases = $purchases->filter(fn($p) => $p->purchase_category === 'raw_materials');
        $otherPurchases = $purchases->filter(fn($p) => $p->purchase_category === 'other');

        // Raw Materials Categorized Breakdown (কাগজ, ছাপা বিল, বাঁধাই বিল, প্লেট, কালি, অন্যান্য)
        $rawItems = PublisherPurchaseItem::whereHas('purchase', function($q) use ($startDate, $endDate) {
            $q->where('purchase_category', 'raw_materials')
              ->whereDate('purchase_date', '>=', $startDate)
              ->whereDate('purchase_date', '<=', $endDate);
        })->get();

        $paperTotal = 0; $printTotal = 0; $bindingTotal = 0; $plateTotal = 0; $otherRawTotal = 0;
        foreach ($rawItems as $rItem) {
            $name = mb_strtolower($rItem->item_name ?: $rItem->book_title);
            $sub = (float) $rItem->subtotal;
            if (str_contains($name, 'কাগজ') || str_contains($name, 'paper') || str_contains($name, 'রিম') || str_contains($name, 'আর্টকার্ড')) {
                $paperTotal += $sub;
            } elseif (str_contains($name, 'ছাপা') || str_contains($name, 'প্রিন্ট') || str_contains($name, 'print') || str_contains($name, 'কালার') || str_contains($name, 'ইম্প্রেশন')) {
                $printTotal += $sub;
            } elseif (str_contains($name, 'বাঁধাই') || str_contains($name, 'বাইন্ডিং') || str_contains($name, 'bind') || str_contains($name, 'ল্যামিনেশন')) {
                $bindingTotal += $sub;
            } elseif (str_contains($name, 'প্লেট') || str_contains($name, 'plate')) {
                $plateTotal += $sub;
            } else {
                $otherRawTotal += $sub;
            }
        }

        // 2. Sales in this month (from Orders)
        $orders = \App\Models\Order::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->latest()
            ->get();

        $totalSalesAmount = (float) $orders->sum('total_amount');
        $totalOrdersCount = $orders->count();
        $deliveredOrders = $orders->where('status', 'delivered');
        $deliveredSalesAmount = (float) $deliveredOrders->sum('total_amount');

        // Financial Totals
        $totalPurchaseAmount = (float) $purchases->sum('grand_total');
        $totalPurchasePaid = (float) $purchases->sum('paid_amount');
        $totalPurchaseDue = (float) $purchases->sum('due_amount');

        $booksPurchaseTotal = (float) $booksPurchases->sum('grand_total');
        $rawPurchaseTotal = (float) $rawPurchases->sum('grand_total');
        $otherPurchaseTotal = (float) $otherPurchases->sum('grand_total');

        $netBalance = $totalSalesAmount - $totalPurchaseAmount;

        return view('admin.purchases.monthly-report', compact(
            'month', 'year', 'startDate', 'endDate',
            'purchases', 'booksPurchases', 'rawPurchases', 'otherPurchases',
            'totalPurchaseAmount', 'totalPurchasePaid', 'totalPurchaseDue',
            'booksPurchaseTotal', 'rawPurchaseTotal', 'otherPurchaseTotal',
            'paperTotal', 'printTotal', 'bindingTotal', 'plateTotal', 'otherRawTotal',
            'orders', 'totalSalesAmount', 'totalOrdersCount', 'deliveredSalesAmount', 'netBalance'
        ));
    }

    private function bengaliToEnglish(string $text): string
    {
        $bengali = ['অ','আ','ই','ঈ','উ','ঊ','ঋ','এ','ঐ','ও','ঔ','ক','খ','গ','ঘ','ঙ','চ','ছ','জ','ঝ','ঞ','ট','ঠ','ড','ঢ','ণ','ত','থ','দ','ধ','ন','প','ফ','ব','ভ','ম','য','র','ল','শ','ষ','স','হ','ড়','ঢ়','য়','ৎ','ং','ঃ','ঁ','া','ি','ী','ু','ূ','ৃ','ে','ৈ','ো','ৌ','্'];
        $english = ['a','a','i','i','u','u','ri','e','oi','o','ou','k','kh','g','gh','ng','ch','ch','j','jh','n','t','th','d','dh','n','t','th','d','dh','n','p','f','b','bh','m','z','r','l','sh','sh','s','h','r','rh','y','t','ng','h','n','a','i','i','u','u','ri','e','oi','o','ou',''];
        $text = str_replace($bengali, $english, $text);
        return Str::slug($text, '-', null);
    }
}
