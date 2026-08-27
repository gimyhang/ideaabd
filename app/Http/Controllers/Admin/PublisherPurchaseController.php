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
        $books = Book::select('id', 'title', 'subtitle', 'slug', 'price', 'discount_price', 'cost_price', 'stock_quantity', 'publisher_id', 'category_id', 'author_name', 'isbn', 'edition', 'paper_type', 'book_size', 'page_count', 'cover_type', 'cover_image')
            ->orderBy('title')
            ->get();

        // Auto generate next purchase invoice number based on class
        $dateStr = date('Ymd');
        $prefix = $currentType === 'raw_materials' ? 'RM-' : ($currentType === 'other' ? 'OTH-' : 'PUR-');
        $countToday = PublisherPurchase::whereDate('created_at', today())->count() + 1;
        $suggestedInvoiceNo = $prefix . $dateStr . '-' . str_pad((string)$countToday, 3, '0', STR_PAD_LEFT);

        return view('admin.purchases.create', compact('publishers', 'authors', 'categories', 'books', 'suggestedInvoiceNo', 'currentType'));
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

            return redirect()->route('admin.purchases.show', $purchase->id)
                ->with('success', "ক্রয় ইনভয়েস #{$purchase->purchase_no} সফলভাবে সংরক্ষিত হয়েছে এবং বইসমূহ স্বয়ংক্রিয়ভাবে বুকশপ ইনভেনটরিতে যুক্ত হয়েছে।");
        });
    }

    /**
     * Show form to edit an existing publisher purchase.
     */
    public function edit(PublisherPurchase $purchase): View
    {
        $purchase->load(['publisher', 'items.book', 'payments']);
        $publishers = Publisher::where('is_active', true)->orderBy('name')->get();
        $authors = \Modules\Author\Models\Author::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $books = Book::select('id', 'title', 'price', 'stock_quantity', 'publisher_id', 'category_id', 'author_name')->orderBy('title')->get();

        return view('admin.purchases.edit', compact('purchase', 'publishers', 'authors', 'categories', 'books'));
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
                    } else {
                        $pub = Publisher::first();
                        $publisherId = $pub ? $pub->id : null;
                    }
                }
            }

            $totalAmount = 0.0;
            $discount = (float) ($validated['discount_amount'] ?? 0);

            // Update Purchase header record
            $purchase->purchase_no = $validated['purchase_no'];
            $purchase->purchase_category = $purchaseCategory;
            $purchase->publisher_memo_no = $validated['publisher_memo_no'] ?? null;
            $purchase->publisher_id = $publisherId;
            $purchase->supplier_name = $vendorName ?: ($purchaseCategory === 'books' ? 'বই প্রকাশনী' : 'ভেন্ডর');
            $purchase->vendor_name = $vendorName ?: ($purchaseCategory === 'books' ? 'বই প্রকাশনী' : 'ভেন্ডর');
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
     * Repayment history and installment payments list.
     */
    public function payments(Request $request): View
    {
        $publisherId = $request->input('publisher_id');
        $method = $request->input('payment_method');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = PublisherPayment::query()
            ->with(['publisher', 'purchase', 'recorder'])
            ->when($publisherId, fn($q) => $q->where('publisher_id', $publisherId))
            ->when($method, fn($q) => $q->where('payment_method', $method))
            ->when($dateFrom, fn($q) => $q->whereDate('payment_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('payment_date', '<=', $dateTo))
            ->latest('payment_date')
            ->latest('id');

        $payments = $query->paginate(20)->withQueryString();

        $publishers = Publisher::orderBy('name')->pluck('name', 'id')->all();
        $paymentMethods = PublisherPayment::paymentMethods();

        $pendingPurchases = PublisherPurchase::whereIn('payment_status', ['due', 'partial'])
            ->with('publisher')
            ->orderBy('purchase_date')
            ->get();

        $totalPaidSum = (float) PublisherPayment::sum('amount');

        return view('admin.purchases.payments', compact('payments', 'publishers', 'paymentMethods', 'pendingPurchases', 'totalPaidSum', 'publisherId', 'method', 'dateFrom', 'dateTo'));
    }

    /**
     * Record a new installment payment against a purchase.
     */
    public function storePayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_id'     => 'required|integer|exists:publisher_purchases,id',
            'payment_date'    => 'required|date',
            'amount'          => 'required|numeric|min:1',
            'payment_method'  => 'required|string|max:50',
            'transaction_ref' => 'nullable|string|max:100',
            'note'            => 'nullable|string|max:500',
        ], [
            'purchase_id.required' => 'ক্রয় ইনভয়েস নির্বাচন করুন।',
            'amount.required'      => 'পরিশোধের পরিমাণ টাকা দিন।',
            'amount.min'           => 'পরিশোধের পরিমাণ কমপক্ষে ১ টাকা হতে হবে।',
        ]);

        $purchase = PublisherPurchase::findOrFail($validated['purchase_id']);

        if ((float)$validated['amount'] > (float)$purchase->due_amount) {
            return back()->with('error', "পরিশোধের পরিমাণ বকেয়া টাকার চেয়ে বেশি হতে পারে না। বর্তমান বকেয়া: ৳" . number_format($purchase->due_amount, 2));
        }

        $payNo = 'PAY-' . date('Ymd') . '-' . rand(1000, 9999);

        PublisherPayment::create([
            'purchase_id'     => $purchase->id,
            'publisher_id'    => $purchase->publisher_id,
            'payment_no'      => $payNo,
            'payment_date'    => $validated['payment_date'],
            'amount'          => $validated['amount'],
            'payment_method'  => $validated['payment_method'],
            'transaction_ref' => $validated['transaction_ref'] ?? null,
            'note'            => $validated['note'] ?? null,
            'recorded_by'     => auth()->id(),
        ]);

        $purchase->recalculate();

        return back()->with('success', "৳" . number_format($validated['amount'], 2) . " টাকা সফলভাবে পরিশোধ রেকর্ড করা হয়েছে।");
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
