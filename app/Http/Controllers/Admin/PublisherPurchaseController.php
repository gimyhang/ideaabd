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
     * List all publisher purchases.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $publisherId = $request->input('publisher_id');
        $paymentStatus = $request->string('payment_status')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = PublisherPurchase::query()
            ->with(['publisher', 'items.book', 'payments'])
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where('purchase_no', 'like', $like)
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
        ];

        return view('admin.purchases.index', compact('purchases', 'publishers', 'stats', 'search', 'publisherId', 'paymentStatus', 'dateFrom', 'dateTo'));
    }

    /**
     * Show form to create a new publisher purchase.
     */
    public function create(): View
    {
        $publishers = Publisher::where('is_active', true)->orderBy('name')->get();
        $authors = \Modules\Author\Models\Author::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $books = Book::select('id', 'title', 'price', 'stock_quantity', 'publisher_id', 'category_id', 'author_name')->orderBy('title')->get();

        // Auto generate next purchase invoice number
        $dateStr = date('Ymd');
        $countToday = PublisherPurchase::whereDate('created_at', today())->count() + 1;
        $suggestedInvoiceNo = 'PUR-' . $dateStr . '-' . str_pad((string)$countToday, 3, '0', STR_PAD_LEFT);

        return view('admin.purchases.create', compact('publishers', 'authors', 'categories', 'books', 'suggestedInvoiceNo'));
    }

    /**
     * Store new purchase, items, auto-sync books into bookshop, and record initial payment if paid.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'publisher_id'        => 'nullable|integer',
            'publisher_name'      => 'nullable|string|max:255',
            'publisher_phone'     => 'nullable|string|max:50',
            'publisher_address'   => 'nullable|string|max:255',
            'purchase_no'         => 'required|string|max:50|unique:publisher_purchases,purchase_no',
            'purchase_date'       => 'required|date',
            'payment_type'        => 'required|in:cash,credit,partial',
            'discount_amount'     => 'nullable|numeric|min:0',
            'paid_amount'         => 'nullable|numeric|min:0',
            'payment_method'      => 'nullable|string|max:50',
            'transaction_ref'     => 'nullable|string|max:100',
            'notes'               => 'nullable|string|max:1000',
            'items'               => 'required|array|min:1',
            'items.*.title'       => 'required|string|max:255',
            'items.*.book_id'     => 'nullable|integer',
            'items.*.author'      => 'nullable|string|max:255',
            'items.*.category_id' => 'nullable|integer',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.cost_price'  => 'required|numeric|min:0',
            'items.*.sale_price'  => 'required|numeric|min:0',
        ], [
            'purchase_no.required'  => 'ক্রয় ইনভয়েস নম্বর দিন।',
            'purchase_no.unique'    => 'এই ইনভয়েস নম্বরটি পূর্বে ব্যবহার করা হয়েছে।',
            'items.required'        => 'কমপক্ষে একটি বই যোগ করুন।',
            'items.min'             => 'কমপক্ষে একটি বই যোগ করুন।',
        ]);

        if (empty($validated['publisher_id']) && empty($validated['publisher_name'])) {
            return back()->withInput()->withErrors(['publisher_id' => 'অনুগ্রহ করে বিদ্যমান প্রকাশনী বেছে নিন অথবা নতুন প্রকাশনীর নাম লিখুন।']);
        }

        return DB::transaction(function () use ($request, $validated) {
            // Auto resolve or create Publisher
            $publisherId = !empty($validated['publisher_id']) ? (int)$validated['publisher_id'] : null;
            $publisherName = trim((string)($validated['publisher_name'] ?? ''));

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

            $totalAmount = 0.0;
            $discount = (float) ($validated['discount_amount'] ?? 0);
            $initialPaid = (float) ($validated['paid_amount'] ?? 0);

            // Create Purchase record
            $purchase = new PublisherPurchase();
            $purchase->purchase_no = $validated['purchase_no'];
            $purchase->publisher_id = $publisherId;
            $purchase->purchase_date = $validated['purchase_date'];
            $purchase->payment_type = $validated['payment_type'];
            $purchase->discount_amount = $discount;
            $purchase->notes = $validated['notes'] ?? null;
            $purchase->created_by = auth()->id();
            $purchase->save();

            // Process purchase items & sync with Bookshop
            foreach ($validated['items'] as $itemData) {
                $qty = (int) $itemData['quantity'];
                $cost = (float) $itemData['cost_price'];
                $sale = (float) $itemData['sale_price'];
                $itemSubtotal = $qty * $cost;
                $totalAmount += $itemSubtotal;

                $bookId = !empty($itemData['book_id']) ? (int)$itemData['book_id'] : null;
                $authorName = trim((string)($itemData['author'] ?? ''));

                // Auto resolve or create Author in Author directory
                $authorId = null;
                if (!empty($authorName)) {
                    $author = \Modules\Author\Models\Author::where('name', $authorName)->first();
                    if (!$author) {
                        $authSlugBase = $this->bengaliToEnglish($authorName) ?: 'author-' . uniqid();
                        $authSlug = $authSlugBase;
                        $c = 1;
                        while (\Modules\Author\Models\Author::where('slug', $authSlug)->exists()) {
                            $authSlug = $authSlugBase . '-' . (++$c);
                        }
                        $author = \Modules\Author\Models\Author::create([
                            'name' => $authorName,
                            'slug' => $authSlug,
                            'is_active' => true,
                        ]);
                    }
                    $authorId = $author->id;
                }

                // If existing book is selected, update bookshop stock and price
                if ($bookId && Book::where('id', $bookId)->exists()) {
                    $book = Book::find($bookId);
                    $book->increment('stock_quantity', $qty);
                    $book->stock_status = 'in_stock';
                    if ($sale > 0) {
                        $book->price = $sale;
                    }
                    if (!$book->publisher_id) {
                        $book->publisher_id = $publisherId;
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
                    $newBook->category_id = !empty($itemData['category_id']) ? (int)$itemData['category_id'] : null;
                    $newBook->author_name = !empty($authorName) ? $authorName : null;
                    $newBook->stock_quantity = $qty;
                    $newBook->stock_status = 'in_stock';
                    $newBook->price = $sale > 0 ? $sale : $cost;
                    $newBook->is_active = true;
                    $newBook->published_at = now();
                    $newBook->save();

                    if ($authorId) {
                        $newBook->authors()->syncWithoutDetaching([$authorId]);
                    }

                    $bookId = $newBook->id;
                }

                // Save Purchase Item
                $item = new PublisherPurchaseItem();
                $item->purchase_id = $purchase->id;
                $item->book_id = $bookId;
                $item->book_title = $itemData['title'];
                $item->author_name = $authorName ?: null;
                $item->category_id = !empty($itemData['category_id']) ? (int)$itemData['category_id'] : null;
                $item->quantity = $qty;
                $item->unit_cost_price = $cost;
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
     * Delete a purchase.
     */
    public function destroy(PublisherPurchase $purchase): RedirectResponse
    {
        $no = $purchase->purchase_no;
        $purchase->payments()->delete();
        $purchase->items()->delete();
        $purchase->delete();

        return redirect()->route('admin.purchases.index')
            ->with('success', "ক্রয় ইনভয়েস #{$no} সফলভাবে মুছে ফেলা হয়েছে।");
    }

    private function bengaliToEnglish(string $text): string
    {
        $bengali = ['অ','আ','ই','ঈ','উ','ঊ','ঋ','এ','ঐ','ও','ঔ','ক','খ','গ','ঘ','ঙ','চ','ছ','জ','ঝ','ঞ','ট','ঠ','ড','ঢ','ণ','ত','থ','দ','ধ','ন','প','ফ','ব','ভ','ম','য','র','ল','শ','ষ','স','হ','ড়','ঢ়','য়','ৎ','ং','ঃ','ঁ','া','ি','ী','ু','ূ','ৃ','ে','ৈ','ো','ৌ','্'];
        $english = ['a','a','i','i','u','u','ri','e','oi','o','ou','k','kh','g','gh','ng','ch','ch','j','jh','n','t','th','d','dh','n','t','th','d','dh','n','p','f','b','bh','m','z','r','l','sh','sh','s','h','r','rh','y','t','ng','h','n','a','i','i','u','u','ri','e','oi','o','ou',''];
        $text = str_replace($bengali, $english, $text);
        return Str::slug($text, '-', null);
    }
}
