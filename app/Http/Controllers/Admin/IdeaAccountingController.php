<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CustomerInvoiceMail;
use App\Models\IdeaAccountingEntry;
use App\Models\IdeaInvoice;
use App\Models\IdeaInvoicePayment;
use App\Models\IdeaEmployee;
use App\Models\IdeaSalaryPayment;
use App\Models\IdeaEmployeeWorkLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Book\Models\Book;

class IdeaAccountingController extends Controller
{
    /**
     * Display Income/Expense Accounting Dashboard & Ledger.
     */
    public function index(Request $request): View
    {
        $type = $request->input('type');
        $category = $request->input('category');
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = IdeaAccountingEntry::query()
            ->with('creator', 'invoice')
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($dateFrom, fn($q) => $q->whereDate('entry_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('entry_date', '<=', $dateTo))
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('title', 'like', $like)
                      ->orWhere('entry_no', 'like', $like)
                      ->orWhere('party_name', 'like', $like)
                      ->orWhere('voucher_no', 'like', $like);
                });
            })
            ->latest('entry_date')
            ->latest('id');

        $entries = $query->paginate(20)->withQueryString();

        $totalIncome = (float) IdeaAccountingEntry::where('type', 'income')->sum('amount');
        $totalExpense = (float) IdeaAccountingEntry::where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        $categories = IdeaAccountingEntry::categories();

        // Sector wise expense breakdown
        $expenseBreakdown = IdeaAccountingEntry::where('type', 'expense')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return view('admin.accounting.index', compact(
            'entries', 'totalIncome', 'totalExpense', 'netBalance',
            'categories', 'expenseBreakdown', 'type', 'category', 'search', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Store new income or expense transaction entry.
     */
    public function storeEntry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'           => 'required|in:income,expense',
            'category'       => 'nullable|string|max:100',
            'custom_category'=> 'nullable|string|max:100',
            'title'          => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'entry_date'     => 'required|date',
            'voucher_no'     => 'nullable|string|max:100',
            'payment_method' => 'required|string|max:50',
            'party_name'     => 'nullable|string|max:255',
            'notes'          => 'nullable|string|max:3000',
            'item_name'      => 'nullable|array',
            'item_name.*'    => 'nullable|string|max:255',
            'item_qty'       => 'nullable|array',
            'item_price'     => 'nullable|array',
            'item_total'     => 'nullable|array',
        ], [
            'type.required'     => 'লেনদেনের ধরন (আয় বা ব্যয়) নির্বাচন করুন।',
            'title.required'    => 'বিবরণ লিখুন।',
            'amount.required'   => 'টাকার পরিমাণ দিন।',
        ]);

        $category = !empty($validated['custom_category']) 
            ? trim($validated['custom_category']) 
            : ($validated['category'] ?? 'বিবিধ খরচ (Miscellaneous Expense)');

        // Format dynamic line items if provided
        $notesText = $validated['notes'] ?? '';
        if (!empty($validated['item_name']) && is_array($validated['item_name'])) {
            $itemLines = [];
            foreach ($validated['item_name'] as $idx => $name) {
                $name = trim((string)$name);
                if (empty($name)) continue;
                $qty = $validated['item_qty'][$idx] ?? 1;
                $price = $validated['item_price'][$idx] ?? 0;
                $total = $validated['item_total'][$idx] ?? ($qty * $price);
                $itemLines[] = "• {$name} — {$qty} টি/একক @ ৳{$price} = ৳{$total}";
            }
            if (!empty($itemLines)) {
                $breakdownString = "\n[মালামাল ও আইটেমের বিবরণ / Itemized List]:\n" . implode("\n", $itemLines);
                $notesText = trim($notesText . $breakdownString);
            }
        }

        $prefix = $validated['type'] === 'income' ? 'INC-' : 'EXP-';
        $dateStr = date('Ymd', strtotime($validated['entry_date']));
        $entryNo = $prefix . $dateStr . '-' . rand(1000, 9999);

        IdeaAccountingEntry::create([
            'entry_no'       => $entryNo,
            'type'           => $validated['type'],
            'category'       => $category,
            'title'          => $validated['title'],
            'amount'         => (float) $validated['amount'],
            'entry_date'     => $validated['entry_date'],
            'voucher_no'     => $validated['voucher_no'] ?? null,
            'payment_method' => $validated['payment_method'],
            'party_name'     => $validated['party_name'] ?? null,
            'notes'          => $notesText ?: null,
            'created_by'     => auth()->id(),
        ]);

        $msg = $validated['type'] === 'income' ? 'নতুন আয় এন্ট্রি সংরক্ষিত হয়েছে।' : 'নতুন ব্যয় / ক্রয়ের হিসাব সফলভাবে সংরক্ষিত হয়েছে।';

        return back()->with('success', $msg);
    }

    /**
     * Delete accounting entry.
     */
    public function destroyEntry(IdeaAccountingEntry $entry): RedirectResponse
    {
        $entry->delete();
        return back()->with('success', 'লেনদেন রেকর্ডটি মুছে ফেলা হয়েছে।');
    }

    /**
     * Invoices & Challans List (বিল ও চালান তালিকা).
     */
    public function invoices(Request $request): View
    {
        $type = $request->input('type');
        $salesCategory = $request->input('sales_category');
        $status = $request->input('payment_status');
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = IdeaInvoice::query()
            ->with('creator')
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($salesCategory && in_array($salesCategory, ['books', 'stationery', 'printing_goods', 'other']), function($q) use ($salesCategory) {
                if ($salesCategory === 'books') {
                    $q->where(fn($sub) => $sub->where('sales_category', 'books')->orWhereNull('sales_category'));
                } else {
                    $q->where('sales_category', $salesCategory);
                }
            })
            ->when($status, fn($q) => $q->where('payment_status', $status))
            ->when($dateFrom, fn($q) => $q->whereDate('invoice_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('invoice_date', '<=', $dateTo))
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('invoice_no', 'like', $like)
                      ->orWhere('customer_name', 'like', $like)
                      ->orWhere('customer_org', 'like', $like)
                      ->orWhere('customer_phone', 'like', $like)
                      ->orWhere('customer_email', 'like', $like)
                      ->orWhere('reference_no', 'like', $like)
                      ->orWhere('subject', 'like', $like)
                      ->orWhere('notes', 'like', $like)
                      ->orWhere('items', 'like', $like);
                });
            })
            ->latest('invoice_date')
            ->latest('id');

        $invoices = $query->paginate(20)->withQueryString();

        $stats = [
            'total_invoices'   => IdeaInvoice::count(),
            'total_bills'      => IdeaInvoice::where('type', 'invoice')->count(),
            'total_challans'   => IdeaInvoice::where('type', 'challan')->count(),
            'total_quotations' => IdeaInvoice::where('type', 'quotation')->count(),
            'total_tenders'    => IdeaInvoice::where('type', 'tender')->count(),
            'books_count'      => IdeaInvoice::where(fn($q) => $q->where('sales_category', 'books')->orWhereNull('sales_category'))->count(),
            'stationery_count' => IdeaInvoice::where('sales_category', 'stationery')->count(),
            'printing_count'   => IdeaInvoice::where('sales_category', 'printing_goods')->count(),
            'other_count'      => IdeaInvoice::where('sales_category', 'other')->count(),
            'total_amount'     => (float) IdeaInvoice::whereIn('type', ['invoice', 'challan'])->sum('grand_total'),
            'total_paid'       => (float) IdeaInvoice::whereIn('type', ['invoice', 'challan'])->sum('paid_amount'),
            'total_due'        => (float) IdeaInvoice::whereIn('type', ['invoice', 'challan'])->sum('due_amount'),
        ];

        $invoiceSettings = self::getInvoiceSettings();

        return view('admin.accounting.invoices.index', compact(
            'invoices', 'stats', 'type', 'salesCategory', 'status', 'search', 'dateFrom', 'dateTo', 'invoiceSettings'
        ));
    }

    /**
     * Live search books for invoice creation.
     */
    public function searchBooks(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim()->value();
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $enDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $normalized = str_replace($bnDigits, $enDigits, $q);
        $words = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
        $tokens = array_values(array_unique(array_filter(array_merge([$q, $normalized], $words))));

        $books = Book::query()
            ->with(['publisher', 'authorLink', 'authors'])
            ->where(function ($masterQuery) use ($tokens) {
                foreach ($tokens as $token) {
                    $like = '%' . $token . '%';
                    $masterQuery->where(function ($query) use ($like, $token) {
                        $query->where('title', 'like', $like)
                              ->orWhere('subtitle', 'like', $like)
                              ->orWhere('isbn', 'like', $like)
                              ->orWhere('sku', 'like', $like)
                              ->orWhere('author_name', 'like', $like)
                              ->orWhere('translator_name', 'like', $like)
                              ->orWhere('editor_name', 'like', $like)
                              ->orWhereHas('authorLink', fn($a) => $a->where('name', 'like', $like))
                              ->orWhereHas('authors', fn($a) => $a->where('name', 'like', $like));
                    });
                }
            })
            ->limit(25)
            ->get()
            ->map(function ($book) {
                $pbReg = (float)($book->price ?: ($book->hardcover_price ?: 0));
                $pbDisc = (float)($book->discount_price ?: 0);
                $pbSell = ($pbDisc > 0 && $pbDisc < $pbReg) ? $pbDisc : $pbReg;
                $pbDiscPct = ($pbReg > 0 && $pbSell < $pbReg) ? round((($pbReg - $pbSell) / $pbReg) * 100, 1) : 0;

                $hcReg = (float)($book->hardcover_price ?: ($book->price ?: 0));
                $hcDisc = (float)($book->hardcover_discount_price ?: 0);
                $hcSell = ($hcDisc > 0 && $hcDisc < $hcReg) ? $hcDisc : ($pbSell ?: $hcReg);
                $hcDiscPct = ($hcReg > 0 && $hcSell < $hcReg) ? round((($hcReg - $hcSell) / $hcReg) * 100, 1) : 0;

                $hasHardcover = ($book->hardcover_price > 0 || in_array($book->cover_type, ['hardcover', 'both']));
                $hasPaperback = ($book->price > 0 || in_array($book->cover_type, ['paperback', 'both']) || !$hasHardcover);

                return [
                    'id'                       => $book->id,
                    'title'                    => $book->title,
                    'subtitle'                 => $book->subtitle,
                    'author_name'              => $book->author_name ?? ($book->authorLink->name ?? ''),
                    'cover_type'               => $book->cover_type ?? 'paperback',
                    'has_paperback'            => $hasPaperback,
                    'has_hardcover'            => $hasHardcover,
                    'paperback_price'          => $pbReg,
                    'paperback_discount_price' => $pbDisc,
                    'paperback_selling_price'  => $pbSell,
                    'paperback_discount_pct'   => $pbDiscPct,
                    'hardcover_price'          => $hcReg,
                    'hardcover_discount_price' => $hcDisc,
                    'hardcover_selling_price'  => $hcSell,
                    'hardcover_discount_pct'   => $hcDiscPct,
                    'regular_price'            => $pbReg ?: $hcReg,
                    'selling_price'            => $pbSell ?: $hcSell,
                    'discount_pct'             => $pbDiscPct ?: $hcDiscPct,
                    'stock_quantity'           => (int) ($book->stock_quantity ?? 0),
                    'isbn'                     => $book->isbn,
                    'cover_image'              => $book->cover_url ?: ($book->cover_image ? asset('storage/' . ltrim($book->cover_image, '/')) : null),
                ];
            });

        return response()->json($books);
    }

    /**
     * Quick publish / store new book into Bookshop catalog.
     */
    public function quickStoreBook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'author_name'    => 'nullable|string|max:255',
            'price'          => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'hardcover_price'=> 'nullable|numeric|min:0',
            'cover_type'     => 'nullable|in:paperback,hardcover,both',
            'stock_quantity' => 'nullable|integer|min:0',
            'isbn'           => 'nullable|string|max:50',
        ]);

        $title = trim($validated['title']);
        $slugBase = Str::slug($title);
        $slug = $slugBase ?: ('book-' . time() . '-' . rand(100, 999));
        $counter = 1;
        while (Book::where('slug', $slug)->exists()) {
            $slug = ($slugBase ?: 'book') . '-' . time() . '-' . $counter++;
        }

        $regPrice = (float) $validated['price'];
        $discPrice = isset($validated['discount_price']) && is_numeric($validated['discount_price']) && (float)$validated['discount_price'] > 0
            ? (float) $validated['discount_price']
            : null;
        $hcPrice = isset($validated['hardcover_price']) && is_numeric($validated['hardcover_price']) && (float)$validated['hardcover_price'] > 0
            ? (float) $validated['hardcover_price']
            : null;

        $coverType = $validated['cover_type'] ?? 'paperback';
        if ($hcPrice > 0 && $regPrice <= 0) {
            $coverType = 'hardcover';
        }

        $stock = isset($validated['stock_quantity']) ? (int)$validated['stock_quantity'] : 50;

        $book = Book::create([
            'title'           => $title,
            'slug'            => $slug,
            'author_name'     => !empty($validated['author_name']) ? trim($validated['author_name']) : null,
            'price'           => $regPrice,
            'discount_price'  => $discPrice,
            'hardcover_price' => $hcPrice,
            'cover_type'      => $coverType,
            'stock_quantity'  => $stock,
            'isbn'            => $validated['isbn'] ?? null,
            'publisher_id'    => $validated['publisher_id'] ?? (\Modules\Publisher\Models\Publisher::where('name', 'LIKE', '%আইডিয়া%')->orWhere('slug', 'ideaprokashon')->value('id') ?: 2),
            'is_active'       => true,
            'format'          => 'printed',
        ]);

        $sellPrice = ($discPrice && $discPrice < $regPrice) ? $discPrice : $regPrice;
        $discPct = ($regPrice > 0 && $sellPrice < $regPrice) ? round((($regPrice - $sellPrice) / $regPrice) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'message' => "বইটি সফলভাবে বুকশপে যুক্ত করা হয়েছে।",
            'book'    => [
                'id'                       => $book->id,
                'title'                    => $book->title,
                'author_name'              => $book->author_name,
                'cover_type'               => $book->cover_type,
                'regular_price'            => $regPrice,
                'discount_price'           => $discPrice,
                'selling_price'            => $sellPrice,
                'discount_pct'             => $discPct,
                'hardcover_price'          => $hcPrice,
                'stock_quantity'           => $stock,
                'isbn'                     => $book->isbn,
                'cover_image'              => null,
                'has_paperback'            => in_array($coverType, ['paperback', 'both']),
                'has_hardcover'            => in_array($coverType, ['hardcover', 'both']),
            ],
        ]);
    }

    /**
     * Create Bill, Delivery Challan, Quotation or Tender.
     */
    public function createInvoice(Request $request): View
    {
        $books = Book::where('is_active', true)
            ->with(['publisher:id,name', 'category:id,name'])
            ->select('id', 'title', 'subtitle', 'author_name', 'cover_type', 'format', 'price', 'discount_price', 'hardcover_price', 'hardcover_discount_price', 'stock_quantity', 'isbn', 'publisher_id', 'category_id')
            ->orderBy('title')
            ->limit(1000)
            ->get();
        
        $selectedType = $request->query('type', 'invoice');
        if (!in_array($selectedType, ['invoice', 'challan', 'quotation', 'tender'])) {
            $selectedType = 'invoice';
        }

        $salesCategory = $request->query('sales_category', 'books');
        if (!in_array($salesCategory, ['books', 'stationery', 'printing_goods', 'other'])) {
            $salesCategory = 'books';
        }

        $prefix = match($salesCategory) {
            'stationery'     => 'IDEA-STN-',
            'printing_goods' => 'IDEA-PRT-',
            'other'          => 'IDEA-OTH-',
            default          => match($selectedType) {
                'challan'   => 'IDEA-CHL-',
                'quotation' => 'IDEA-QUO-',
                'tender'    => 'IDEA-TND-',
                default     => 'IDEA-INV-',
            },
        };

        $dateStr = date('Ymd');
        $countToday = IdeaInvoice::whereDate('created_at', today())->count() + 1;
        $suggestedNo = $prefix . $dateStr . '-' . str_pad((string)$countToday, 3, '0', STR_PAD_LEFT);
        $invoiceSettings = self::getInvoiceSettings();

        return view('admin.accounting.invoices.create', compact('books', 'suggestedNo', 'selectedType', 'salesCategory', 'invoiceSettings'));
    }

    /**
     * Store Bill / Challan / Quotation / Tender.
     */
    public function storeInvoice(Request $request): RedirectResponse
    {
        if ($request->has('items') && is_array($request->items)) {
            $filteredItems = array_values(array_filter($request->items, function ($item) {
                return is_array($item) && !empty(trim((string)($item['title'] ?? '')));
            }));
            $request->merge(['items' => $filteredItems]);
        }

        if (!$request->filled('payment_method')) {
            $request->merge(['payment_method' => 'Cash']);
        }

        $validated = $request->validate([
            'type'             => 'required|in:invoice,challan,quotation,tender',
            'sales_category'   => 'nullable|in:books,stationery,printing_goods,other',
            'invoice_no'       => 'required|string|max:50|unique:idea_invoices,invoice_no',
            'subject'          => 'nullable|string|max:255',
            'reference_no'     => 'nullable|string|max:100',
            'customer_name'        => 'required|string|max:255',
            'customer_designation' => 'nullable|string|max:150',
            'customer_org'         => 'nullable|string|max:255',
            'customer_email'       => 'nullable|email|max:255',
            'customer_phone'       => 'nullable|string|max:50',
            'customer_address'     => 'nullable|string|max:255',
            'invoice_date'         => 'required|date',
            'due_date'             => 'nullable|date',
            'valid_until'          => 'nullable|date',
            'discount'             => 'nullable|numeric|min:0',
            'tax'                  => 'nullable|numeric|min:0',
            'paid_amount'          => 'nullable|numeric|min:0',
            'payment_method'       => 'required|string|max:50',
            'notes'                => 'nullable|string|max:1000',
            'terms_conditions'     => 'nullable|string|max:2000',
            'items'                => 'required|array|min:1',
            'items.*.title'            => 'required|string|max:2000',
            'items.*.author_name'      => 'nullable|string|max:1000',
            'items.*.item_type'        => 'nullable|string|max:50',
            'items.*.unit'             => 'nullable|string|max:50',
            'items.*.book_id'          => 'nullable|integer',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.regular_price'    => 'nullable|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.price'            => 'required|numeric|min:0',
        ], [
            'customer_name.required' => 'গ্রাহক বা প্রতিনিধির নাম লিখুন।',
            'items.required'         => 'কমপক্ষে একটি আইটেম বা বিবরণ যোগ করুন।',
            'items.min'              => 'কমপক্ষে একটি আইটেম বা বিবরণ যোগ করুন।',
        ]);

        $salesCategory = $validated['sales_category'] ?? $request->input('sales_category', 'books');
        $autoCreateBooks = $request->boolean('auto_create_books', true);

        try {
            return DB::transaction(function () use ($validated, $salesCategory, $autoCreateBooks, $request) {
                $subtotal = 0.0;
                $itemsProcessed = [];

                foreach ($validated['items'] as $item) {
                    $qty = (float) $item['quantity'];
                    $price = (float) $item['price'];
                    $regularPrice = isset($item['regular_price']) && is_numeric($item['regular_price']) && (float)$item['regular_price'] > 0 
                        ? (float)$item['regular_price'] 
                        : $price;
                    $discPct = isset($item['discount_percent']) && is_numeric($item['discount_percent']) 
                        ? (float)$item['discount_percent'] 
                        : 0.0;

                    if ($discPct == 0 && $regularPrice > $price && $regularPrice > 0) {
                        $discPct = round((($regularPrice - $price) / $regularPrice) * 100, 2);
                    }

                    $lineTotal = $qty * $price;
                    $subtotal += $lineTotal;

                    $bookId = !empty($item['book_id']) ? (int)$item['book_id'] : null;
                    $itemTitle = trim((string)$item['title']);

                    // Auto create book in Bookshop if sales category is books, book_id is missing, and title is provided
                    if (!$bookId && $salesCategory === 'books' && !empty($itemTitle) && $autoCreateBooks) {
                        $existingBook = Book::where('title', $itemTitle)->first();
                        if ($existingBook) {
                            $bookId = $existingBook->id;
                        } else {
                            $slugBase = Str::slug($itemTitle);
                            $slug = $slugBase ?: ('book-' . time() . '-' . rand(100, 999));
                            $counter = 1;
                            while (Book::where('slug', $slug)->exists()) {
                                $slug = ($slugBase ?: 'book') . '-' . time() . '-' . $counter++;
                            }
                            $createdBook = Book::create([
                                'title'          => $itemTitle,
                                'slug'           => $slug,
                                'author_name'    => !empty($item['author_name']) ? trim((string)$item['author_name']) : null,
                                'cover_type'     => ($item['item_type'] ?? '') === 'Book (Hardcover)' ? 'hardcover' : 'paperback',
                                'price'          => $regularPrice ?: $price,
                                'discount_price' => ($discPct > 0 && $price < $regularPrice) ? $price : null,
                                'stock_quantity' => 50,
                                'is_active'      => true,
                                'format'         => 'printed',
                            ]);
                            $bookId = $createdBook->id;
                        }
                    }

                    $itemsProcessed[] = [
                        'title'            => $item['title'],
                        'author_name'      => !empty($item['author_name']) ? trim((string)$item['author_name']) : null,
                        'item_type'        => $item['item_type'] ?? 'বই (Book)',
                        'unit'             => !empty($item['unit']) ? trim((string)$item['unit']) : 'কপি',
                        'book_id'          => $bookId,
                        'quantity'         => $qty,
                        'regular_price'    => $regularPrice,
                        'discount_percent' => $discPct,
                        'unit_price'       => $price,
                        'subtotal'         => $lineTotal,
                    ];
                }

                $discount = (float) ($validated['discount'] ?? 0);
                $tax = (float) ($validated['tax'] ?? 0);
                $grandTotal = max(0, $subtotal - $discount + $tax);
                $paid = (float) ($validated['paid_amount'] ?? 0);
                $due = max(0, $grandTotal - $paid);

                $paymentStatus = 'unpaid';
                if ($paid >= $grandTotal && $grandTotal > 0) {
                    $paymentStatus = 'paid';
                } elseif ($paid > 0 && $due > 0) {
                    $paymentStatus = 'partial';
                }

                $userId = auth()->id() ?: null;
                if ($userId && !\Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->exists()) {
                    $userId = null;
                }

                $salesCategory = $validated['sales_category'] ?? $request->input('sales_category', 'books');

                $invoice = IdeaInvoice::create([
                    'invoice_no'           => $validated['invoice_no'],
                    'type'                 => $validated['type'],
                    'sales_category'       => $salesCategory,
                    'subject'              => $validated['subject'] ?? null,
                    'reference_no'         => $validated['reference_no'] ?? null,
                    'customer_name'        => $validated['customer_name'],
                    'customer_designation' => $validated['customer_designation'] ?? null,
                    'customer_org'         => $validated['customer_org'] ?? null,
                    'customer_email'       => $validated['customer_email'] ?? null,
                    'customer_phone'       => $validated['customer_phone'] ?? null,
                    'customer_address'     => $validated['customer_address'] ?? null,
                    'invoice_date'         => $validated['invoice_date'],
                    'due_date'             => $validated['due_date'] ?? null,
                    'valid_until'          => $validated['valid_until'] ?? null,
                    'items'                => $itemsProcessed,
                    'subtotal'             => $subtotal,
                    'discount'             => $discount,
                    'tax'                  => $tax,
                    'grand_total'          => $grandTotal,
                    'paid_amount'          => $paid,
                    'due_amount'       => $due,
                    'payment_method'   => $validated['payment_method'],
                    'payment_status'   => $paymentStatus,
                    'notes'            => $validated['notes'] ?? null,
                    'terms_conditions' => $validated['terms_conditions'] ?? null,
                    'created_by'       => $userId,
                ]);

                // Record initial advance payment if paid amount > 0 and type is invoice/challan
                if ($paid > 0 && in_array($validated['type'], ['invoice', 'challan'])) {
                    $payNo = IdeaInvoicePayment::generatePaymentNo();

                    IdeaInvoicePayment::create([
                        'invoice_id'      => $invoice->id,
                        'customer_name'   => $invoice->customer_name,
                        'customer_phone'  => $invoice->customer_phone,
                        'payment_no'      => $payNo,
                        'payment_date'    => $invoice->invoice_date,
                        'amount'          => $paid,
                        'payment_method'  => $validated['payment_method'],
                        'transaction_ref' => $request->input('transaction_ref') ?: null,
                        'note'            => $request->input('payment_note') ?: 'অগ্রিম জমা (Advance Payment)',
                        'recorded_by'     => $userId,
                    ]);

                    $incomeCategory = match($salesCategory) {
                        'stationery'     => 'স্টেশনারী বিক্রয় (Stationery Sales)',
                        'printing_goods' => 'মুদ্রণ ও প্রকাশনা সেবা (Printing & Publication)',
                        'other'          => 'অন্যান্য আয় (Other Income)',
                        default          => ($validated['type'] === 'challan' ? 'পাইকারি বিক্রয় ও চালান (Wholesale Sales)' : 'বই বিক্রয় (Book Sales)')
                    };

                    IdeaAccountingEntry::create([
                        'entry_no'       => 'INC-' . date('Ymd') . '-' . rand(1000, 9999),
                        'type'           => 'income',
                        'category'       => $incomeCategory,
                        'title'          => "বিল #{$invoice->invoice_no} হতে অগ্রিম জমা — {$invoice->customer_name}",
                        'amount'         => $paid,
                        'entry_date'     => $invoice->invoice_date,
                        'payment_method' => $validated['payment_method'],
                        'party_name'     => $invoice->customer_name,
                        'voucher_no'     => $payNo,
                        'invoice_id'     => $invoice->id,
                        'notes'          => "চালান/বিল নম্বর #{$invoice->invoice_no} হতে প্রাপ্ত অগ্রিম অর্থ [রসিদ #{$payNo}]।",
                        'created_by'     => $userId,
                    ]);
                }

                $typeLabel = $invoice->type_label;

                return redirect()->route('admin.accounting.invoices.show', $invoice->id)
                    ->with('success', "{$typeLabel} #{$invoice->invoice_no} সফলভাবে তৈরি হয়েছে।");
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'ডকুমেন্ট তৈরিতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Show & Print Bill / Challan / Quotation / Tender.
     */
    public function showInvoice(IdeaInvoice $invoice): View
    {
        $invoice->load(['payments.recorder', 'creator']);
        $invoiceSettings = self::getInvoiceSettings();
        return view('admin.accounting.invoices.show', compact('invoice', 'invoiceSettings'));
    }

    /**
     * Edit Bill / Challan / Quotation / Tender.
     */
    public function editInvoice(IdeaInvoice $invoice): View
    {
        $books = Book::where('is_active', true)
            ->with(['publisher:id,name', 'category:id,name'])
            ->select('id', 'title', 'subtitle', 'author_name', 'cover_type', 'format', 'price', 'discount_price', 'hardcover_price', 'hardcover_discount_price', 'stock_quantity', 'isbn', 'publisher_id', 'category_id')
            ->orderBy('title')
            ->limit(1000)
            ->get();
        return view('admin.accounting.invoices.edit', compact('invoice', 'books'));
    }

    /**
     * Update Bill / Challan / Quotation / Tender.
     */
    public function updateInvoice(Request $request, IdeaInvoice $invoice): RedirectResponse
    {
        if ($request->has('items') && is_array($request->items)) {
            $filteredItems = array_values(array_filter($request->items, function ($item) {
                return is_array($item) && !empty(trim((string)($item['title'] ?? '')));
            }));
            $request->merge(['items' => $filteredItems]);
        }

        if (!$request->filled('payment_method')) {
            $request->merge(['payment_method' => $invoice->payment_method ?: 'Cash']);
        }

        $validated = $request->validate([
            'type'             => 'required|in:invoice,challan,quotation,tender',
            'sales_category'   => 'nullable|in:books,stationery,printing_goods,other',
            'invoice_no'       => 'required|string|max:50|unique:idea_invoices,invoice_no,' . $invoice->id,
            'subject'          => 'nullable|string|max:255',
            'reference_no'     => 'nullable|string|max:100',
            'customer_name'        => 'required|string|max:255',
            'customer_designation' => 'nullable|string|max:150',
            'customer_org'         => 'nullable|string|max:255',
            'customer_email'       => 'nullable|email|max:255',
            'customer_phone'       => 'nullable|string|max:50',
            'customer_address'     => 'nullable|string|max:255',
            'invoice_date'         => 'required|date',
            'due_date'             => 'nullable|date',
            'valid_until'          => 'nullable|date',
            'discount'             => 'nullable|numeric|min:0',
            'tax'                  => 'nullable|numeric|min:0',
            'paid_amount'          => 'nullable|numeric|min:0',
            'payment_method'       => 'required|string|max:50',
            'notes'                => 'nullable|string|max:1000',
            'terms_conditions'     => 'nullable|string|max:2000',
            'items'                => 'required|array|min:1',
            'items.*.title'            => 'required|string|max:2000',
            'items.*.author_name'      => 'nullable|string|max:1000',
            'items.*.item_type'        => 'nullable|string|max:50',
            'items.*.unit'             => 'nullable|string|max:50',
            'items.*.book_id'          => 'nullable|integer',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.regular_price'    => 'nullable|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.price'            => 'required|numeric|min:0',
        ], [
            'customer_name.required' => 'গ্রাহক বা প্রতিনিধির নাম লিখুন।',
            'items.required'         => 'কমপক্ষে একটি আইটেম বা বিবরণ যোগ করুন।',
        ]);

        try {
            return DB::transaction(function () use ($validated, $invoice, $request) {
                $subtotal = 0.0;
                $itemsProcessed = [];

                $salesCategory = $request->input('sales_category', $invoice->sales_category ?? 'books');
                $autoCreateBooks = $request->boolean('auto_create_books', true);

                foreach ($validated['items'] as $item) {
                    $qty = (float) $item['quantity'];
                    $price = (float) $item['price'];
                    $regularPrice = isset($item['regular_price']) && is_numeric($item['regular_price']) && (float)$item['regular_price'] > 0 
                        ? (float)$item['regular_price'] 
                        : $price;
                    $discPct = isset($item['discount_percent']) && is_numeric($item['discount_percent']) 
                        ? (float)$item['discount_percent'] 
                        : 0.0;

                    if ($discPct == 0 && $regularPrice > $price && $regularPrice > 0) {
                        $discPct = round((($regularPrice - $price) / $regularPrice) * 100, 2);
                    }

                    $lineTotal = $qty * $price;
                    $subtotal += $lineTotal;

                    $bookId = !empty($item['book_id']) ? (int)$item['book_id'] : null;
                    $itemTitle = trim((string)$item['title']);

                    // Auto create book in Bookshop if sales category is books, book_id is missing, and title is provided
                    if (!$bookId && $salesCategory === 'books' && !empty($itemTitle) && $autoCreateBooks) {
                        $existingBook = Book::where('title', $itemTitle)->first();
                        if ($existingBook) {
                            $bookId = $existingBook->id;
                        } else {
                            $slugBase = Str::slug($itemTitle);
                            $slug = $slugBase ?: ('book-' . time() . '-' . rand(100, 999));
                            $counter = 1;
                            while (Book::where('slug', $slug)->exists()) {
                                $slug = ($slugBase ?: 'book') . '-' . time() . '-' . $counter++;
                            }
                            $createdBook = Book::create([
                                'title'          => $itemTitle,
                                'slug'           => $slug,
                                'author_name'    => !empty($item['author_name']) ? trim((string)$item['author_name']) : null,
                                'cover_type'     => ($item['item_type'] ?? '') === 'Book (Hardcover)' ? 'hardcover' : 'paperback',
                                'price'          => $regularPrice ?: $price,
                                'discount_price' => ($discPct > 0 && $price < $regularPrice) ? $price : null,
                                'stock_quantity' => 50,
                                'is_active'      => true,
                                'format'         => 'printed',
                            ]);
                            $bookId = $createdBook->id;
                        }
                    }

                    $itemsProcessed[] = [
                        'title'            => $item['title'],
                        'author_name'      => !empty($item['author_name']) ? trim((string)$item['author_name']) : null,
                        'item_type'        => $item['item_type'] ?? 'বই (Book)',
                        'unit'             => !empty($item['unit']) ? trim((string)$item['unit']) : 'কপি',
                        'book_id'          => $bookId,
                        'quantity'         => $qty,
                        'regular_price'    => $regularPrice,
                        'discount_percent' => $discPct,
                        'unit_price'       => $price,
                        'subtotal'         => $lineTotal,
                    ];
                }

                $discount = (float) ($validated['discount'] ?? 0);
                $tax = (float) ($validated['tax'] ?? 0);
                $grandTotal = max(0, $subtotal - $discount + $tax);
                
                // Check if payments exist
                $hasPayments = $invoice->payments()->exists();
                $paid = $hasPayments ? (float)$invoice->payments()->sum('amount') : (float)($validated['paid_amount'] ?? 0);
                $due = max(0, $grandTotal - $paid);

                $paymentStatus = 'unpaid';
                if ($paid >= $grandTotal && $grandTotal > 0) {
                    $paymentStatus = 'paid';
                } elseif ($paid > 0 && $due > 0) {
                    $paymentStatus = 'partial';
                }

                $salesCategory = $request->input('sales_category', $invoice->sales_category ?? 'books');

                $invoice->update([
                    'invoice_no'           => $validated['invoice_no'],
                    'type'                 => $validated['type'],
                    'sales_category'       => $salesCategory,
                    'subject'              => $validated['subject'] ?? null,
                    'reference_no'         => $validated['reference_no'] ?? null,
                    'customer_name'        => $validated['customer_name'],
                    'customer_designation' => $validated['customer_designation'] ?? null,
                    'customer_org'         => $validated['customer_org'] ?? null,
                    'customer_email'       => $validated['customer_email'] ?? null,
                    'customer_phone'       => $validated['customer_phone'] ?? null,
                    'customer_address'     => $validated['customer_address'] ?? null,
                    'invoice_date'         => $validated['invoice_date'],
                    'due_date'             => $validated['due_date'] ?? null,
                    'valid_until'          => $validated['valid_until'] ?? null,
                    'items'                => $itemsProcessed,
                    'subtotal'             => $subtotal,
                    'discount'             => $discount,
                    'tax'                  => $tax,
                    'grand_total'          => $grandTotal,
                    'paid_amount'          => $paid,
                    'due_amount'           => $due,
                    'payment_method'       => $validated['payment_method'],
                    'payment_status'       => $paymentStatus,
                    'notes'                => $validated['notes'] ?? null,
                    'terms_conditions'     => $validated['terms_conditions'] ?? null,
                ]);

                // If no payments existed yet and a paid amount was provided on update, log the payment record
                if (!$hasPayments && $paid > 0 && in_array($validated['type'], ['invoice', 'challan'])) {
                    $payNo = IdeaInvoicePayment::generatePaymentNo();
                    IdeaInvoicePayment::create([
                        'invoice_id'      => $invoice->id,
                        'customer_name'   => $invoice->customer_name,
                        'customer_phone'  => $invoice->customer_phone,
                        'payment_no'      => $payNo,
                        'payment_date'    => $invoice->invoice_date,
                        'amount'          => $paid,
                        'payment_method'  => $validated['payment_method'],
                        'note'            => 'অগ্রিম জমা (Advance Payment)',
                        'recorded_by'     => auth()->id(),
                    ]);
                }

                // Sync accounting entry if exists
                $userId = auth()->id() ?: null;
                if ($userId && !\Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->exists()) {
                    $userId = null;
                }

                $entry = IdeaAccountingEntry::where('invoice_id', $invoice->id)->first();
                if ($paid > 0 && in_array($validated['type'], ['invoice', 'challan'])) {
                    if ($entry) {
                        $entry->update([
                            'amount'         => $paid,
                            'entry_date'     => $invoice->invoice_date,
                            'payment_method' => $validated['payment_method'],
                            'party_name'     => $invoice->customer_name,
                        ]);
                    } else {
                        IdeaAccountingEntry::create([
                            'entry_no'       => 'INC-' . date('Ymd') . '-' . rand(1000, 9999),
                            'type'           => 'income',
                            'category'       => $validated['type'] === 'challan' ? 'পাইকারি বিক্রয় ও চালান (Wholesale Sales)' : 'বই বিক্রয় (Book Sales)',
                            'title'          => "বিল #{$invoice->invoice_no} হতে পেমেন্ট প্রাপ্তি — {$invoice->customer_name}",
                            'amount'         => $paid,
                            'entry_date'     => $invoice->invoice_date,
                            'payment_method' => $validated['payment_method'],
                            'party_name'     => $invoice->customer_name,
                            'invoice_id'     => $invoice->id,
                            'notes'          => "চালান/বিল নম্বর #{$invoice->invoice_no} থেকে প্রাপ্ত অর্থ।",
                            'created_by'     => $userId,
                        ]);
                    }
                } elseif ($entry && ($paid == 0 || in_array($validated['type'], ['quotation', 'tender']))) {
                    $entry->delete();
                }

                $typeLabel = $invoice->type_label;

                return redirect()->route('admin.accounting.invoices.show', $invoice->id)
                    ->with('success', "{$typeLabel} #{$invoice->invoice_no} সফলভাবে আপডেট হয়েছে।");
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'ডকুমেন্ট আপডেটে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Convert Quotation / Tender into finalized Invoice or Delivery Challan.
     */
    public function convertInvoiceType(IdeaInvoice $invoice, Request $request): RedirectResponse
    {
        $targetType = $request->input('target_type', 'invoice');
        if (!in_array($targetType, ['invoice', 'challan'])) {
            $targetType = 'invoice';
        }

        $oldTypeLabel = $invoice->type_label;
        $invoice->update([
            'type' => $targetType,
        ]);

        $newTypeLabel = $invoice->type_label;

        return redirect()->route('admin.accounting.invoices.show', $invoice->id)
            ->with('success', "{$oldTypeLabel} সফলভাবে {$newTypeLabel}-এ রূপান্তর করা হয়েছে।");
    }

    /**
     * Record a new installment/step payment against an invoice.
     */
    public function storeInvoicePayment(Request $request, IdeaInvoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date'    => 'required|date',
            'amount'          => 'required|numeric|min:0.01',
            'payment_method'  => 'required|string|max:50',
            'transaction_ref' => 'nullable|string|max:100',
            'note'            => 'nullable|string|max:500',
            'due_date'        => 'nullable|date', // Optional next payment deadline
        ], [
            'amount.required' => 'জমার পরিমাণ (টাকা) প্রদান করুন।',
            'amount.min'      => 'জমার পরিমাণ কমপক্ষে ০.০১ টাকা হতে হবে।',
            'payment_date.required' => 'জমার তারিখ প্রদান করুন।',
        ]);

        $amount = (float) $validated['amount'];
        $paymentDate = $validated['payment_date'];
        $paymentMethod = $validated['payment_method'];
        $trxRef = $validated['transaction_ref'] ?? null;
        $note = $validated['note'] ?? null;

        try {
            return DB::transaction(function () use ($invoice, $amount, $paymentDate, $paymentMethod, $trxRef, $note, $request) {
                $payNo = IdeaInvoicePayment::generatePaymentNo();

                // If previous payments do not exist but invoice had existing paid_amount > 0, backfill the advance record first
                if ($invoice->payments()->count() === 0 && (float)$invoice->paid_amount > 0) {
                    IdeaInvoicePayment::create([
                        'invoice_id'      => $invoice->id,
                        'customer_name'   => $invoice->customer_name,
                        'customer_phone'  => $invoice->customer_phone,
                        'payment_no'      => IdeaInvoicePayment::generatePaymentNo(),
                        'payment_date'    => $invoice->invoice_date,
                        'amount'          => (float)$invoice->paid_amount,
                        'payment_method'  => $invoice->payment_method ?: 'cash',
                        'transaction_ref' => null,
                        'note'            => 'অগ্রিম জমা (Advance Payment)',
                        'recorded_by'     => $invoice->created_by ?: auth()->id(),
                    ]);
                }

                $payment = IdeaInvoicePayment::create([
                    'invoice_id'      => $invoice->id,
                    'customer_name'   => $invoice->customer_name,
                    'customer_phone'  => $invoice->customer_phone,
                    'payment_no'      => $payNo,
                    'payment_date'    => $paymentDate,
                    'amount'          => $amount,
                    'payment_method'  => $paymentMethod,
                    'transaction_ref' => $trxRef,
                    'note'            => $note,
                    'recorded_by'     => auth()->id(),
                ]);

                // Update due date if requested (optional next installment date)
                if ($request->has('due_date')) {
                    $invoice->due_date = $request->filled('due_date') ? $request->input('due_date') : null;
                }

                // Recalculate invoice totals and status
                $invoice->recalculatePayments();

                // Record non-duplicating accounting income entry
                $incomeCategory = match($invoice->sales_category) {
                    'stationery'     => 'স্টেশনারী বিক্রয় (Stationery Sales)',
                    'printing_goods' => 'মুদ্রণ ও প্রকাশনা সেবা (Printing & Publication)',
                    'other'          => 'অন্যান্য আয় (Other Income)',
                    default          => ($invoice->type === 'challan' ? 'পাইকারি বিক্রয় ও চালান (Wholesale Sales)' : 'বই বিক্রয় (Book Sales)')
                };

                $userId = auth()->id() ?: null;
                if ($userId && !\Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->exists()) {
                    $userId = null;
                }

                IdeaAccountingEntry::create([
                    'entry_no'       => 'INC-' . date('Ymd') . '-' . rand(1000, 9999),
                    'type'           => 'income',
                    'category'       => $incomeCategory,
                    'title'          => "বিল #{$invoice->invoice_no} হতে কিস্তি জমা প্রাপ্তি — {$invoice->customer_name}",
                    'amount'         => $amount,
                    'entry_date'     => $paymentDate,
                    'payment_method' => $paymentMethod,
                    'party_name'     => $invoice->customer_name,
                    'voucher_no'     => $payNo,
                    'invoice_id'     => $invoice->id,
                    'notes'          => ($note ? "{$note} — " : "") . "বিল #{$invoice->invoice_no} এর কিস্তি/জমা রসিদ #{$payNo}",
                    'created_by'     => $userId,
                ]);

                return back()->with('success', "বিল #{$invoice->invoice_no}-এর বিপরীতে ৳" . number_format($amount, 2) . " টাকা জমা রেকর্ড করা হয়েছে (রসিদ #{$payNo})।");
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'কিস্তি জমা রেকর্ডে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Delete an installment payment record and adjust invoice + accounting.
     */
    public function destroyInvoicePayment(IdeaInvoicePayment $payment): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($payment) {
                $invoice = $payment->invoice;
                $payNo = $payment->payment_no;
                $amount = (float) $payment->amount;

                // Delete linked accounting entry
                if (!empty($payNo)) {
                    IdeaAccountingEntry::where('voucher_no', $payNo)->delete();
                }

                $payment->delete();

                if ($invoice) {
                    $invoice->recalculatePayments();
                }

                return back()->with('success', "জমা রসিদ #{$payNo} (৳" . number_format($amount, 2) . ") সফলভাবে মুছে ফেলা হয়েছে।");
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'পেমেন্ট রেকর্ড মুছতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Printable Money Receipt (টাকা প্রাপ্তি রসিদ) for installment payment.
     */
    public function invoicePaymentReceipt(IdeaInvoicePayment $payment): View
    {
        $invoice = $payment->invoice;
        $invoiceSettings = self::getInvoiceSettings();
        return view('admin.accounting.invoices.receipt', compact('payment', 'invoice', 'invoiceSettings'));
    }

    /**
     * Customer & Party Running Ledger & Statement (গ্রাহক খতিয়ান ও রানিং স্টেটমেন্ট).
     */
    public function customerLedger(Request $request): View
    {
        $customerName = $request->string('customer_name')->trim()->value();
        $customerPhone = $request->string('customer_phone')->trim()->value();
        $customerKey = $request->input('customer'); // e.g. "Name___Phone"
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $hasDue = $request->boolean('has_due');
        $search = $request->string('search')->trim()->value();

        if (!empty($customerKey)) {
            $parts = explode('___', $customerKey);
            $customerName = $parts[0] ?? $customerName;
            $customerPhone = $parts[1] ?? $customerPhone;
        }

        $allSummaries = $this->buildCustomerLedgersSummary($search, $hasDue, $dateFrom, $dateTo);

        $statement = null;
        $activeCustomer = null;

        if (!empty($customerName) || !empty($customerPhone)) {
            $statement = $this->generateCustomerStatement($customerName, $customerPhone, $dateFrom, $dateTo);
            $activeCustomer = $statement['customer'];
        }

        $paymentMethods = IdeaInvoicePayment::paymentMethods();
        $invoiceSettings = self::getInvoiceSettings();

        return view('admin.accounting.customer_ledger.index', compact(
            'allSummaries', 'statement', 'activeCustomer', 'customerName', 'customerPhone',
            'dateFrom', 'dateTo', 'hasDue', 'search', 'paymentMethods', 'invoiceSettings'
        ));
    }

    /**
     * Direct Customer Payment Collection with FIFO settlement across pending bills or on-account deposit.
     */
    public function storeCustomerLedgerPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name'   => 'required|string|max:255',
            'customer_phone'  => 'nullable|string|max:50',
            'invoice_id'      => 'nullable|integer|exists:idea_invoices,id',
            'payment_date'    => 'required|date',
            'amount'          => 'required|numeric|min:0.01',
            'payment_method'  => 'required|string|max:50',
            'transaction_ref' => 'nullable|string|max:100',
            'note'            => 'nullable|string|max:500',
            'due_date'        => 'nullable|date', // Optional next payment deadline
        ], [
            'customer_name.required' => 'গ্রাহকের নাম দিন।',
            'amount.required'        => 'জমার পরিমাণ টাকা দিন।',
            'amount.min'             => 'জমার পরিমাণ কমপক্ষে ০.০১ টাকা হতে হবে।',
        ]);

        $amount = (float) $validated['amount'];
        $paymentDate = $validated['payment_date'];
        $paymentMethod = $validated['payment_method'];
        $trxRef = $validated['transaction_ref'] ?? null;
        $note = $validated['note'] ?? null;
        $customerName = trim($validated['customer_name']);
        $customerPhone = trim((string)($validated['customer_phone'] ?? ''));
        $specificInvoiceId = !empty($validated['invoice_id']) ? (int)$validated['invoice_id'] : null;

        try {
            return DB::transaction(function () use ($specificInvoiceId, $customerName, $customerPhone, $amount, $paymentDate, $paymentMethod, $trxRef, $note, $request) {
                $userId = auth()->id() ?: null;
                if ($userId && !\Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->exists()) {
                    $userId = null;
                }

                // 1. SPECIFIC INVOICE PAYMENT
                if ($specificInvoiceId) {
                    $invoice = IdeaInvoice::findOrFail($specificInvoiceId);
                    $payNo = IdeaInvoicePayment::generatePaymentNo();

                    // If previous payments do not exist but invoice had existing paid_amount > 0, backfill the advance record first
                    if ($invoice->payments()->count() === 0 && (float)$invoice->paid_amount > 0) {
                        IdeaInvoicePayment::create([
                            'invoice_id'      => $invoice->id,
                            'customer_name'   => $invoice->customer_name,
                            'customer_phone'  => $invoice->customer_phone,
                            'payment_no'      => IdeaInvoicePayment::generatePaymentNo(),
                            'payment_date'    => $invoice->invoice_date,
                            'amount'          => (float)$invoice->paid_amount,
                            'payment_method'  => $invoice->payment_method ?: 'cash',
                            'note'            => 'অগ্রিম জমা (Advance Payment)',
                            'recorded_by'     => $invoice->created_by ?: $userId,
                        ]);
                    }

                    IdeaInvoicePayment::create([
                        'invoice_id'      => $invoice->id,
                        'customer_name'   => $invoice->customer_name,
                        'customer_phone'  => $invoice->customer_phone,
                        'payment_no'      => $payNo,
                        'payment_date'    => $paymentDate,
                        'amount'          => $amount,
                        'payment_method'  => $paymentMethod,
                        'transaction_ref' => $trxRef,
                        'note'            => $note,
                        'recorded_by'     => $userId,
                    ]);

                    if ($request->has('due_date')) {
                        $invoice->due_date = $request->filled('due_date') ? $request->input('due_date') : null;
                    }

                    $invoice->recalculatePayments();

                    // Income entry
                    $incomeCat = $invoice->type === 'challan' ? 'পাইকারি বিক্রয় ও চালান (Wholesale Sales)' : 'বই বিক্রয় (Book Sales)';
                    IdeaAccountingEntry::create([
                        'entry_no'       => 'INC-' . date('Ymd') . '-' . rand(1000, 9999),
                        'type'           => 'income',
                        'category'       => $incomeCat,
                        'title'          => "বিল #{$invoice->invoice_no} হতে কিস্তি জমা প্রাপ্তি — {$invoice->customer_name}",
                        'amount'         => $amount,
                        'entry_date'     => $paymentDate,
                        'payment_method' => $paymentMethod,
                        'party_name'     => $invoice->customer_name,
                        'voucher_no'     => $payNo,
                        'invoice_id'     => $invoice->id,
                        'notes'          => ($note ? "{$note} — " : "") . "বিল #{$invoice->invoice_no} এর কিস্তি/জমা রসিদ #{$payNo}",
                        'created_by'     => $userId,
                    ]);

                    return back()->with('success', "বিল #{$invoice->invoice_no} ({$customerName})-এর বিপরীতে ৳" . number_format($amount, 2) . " টাকা সফলভাবে জমা রেকর্ড করা হয়েছে (রসিদ #{$payNo})।");
                }

                // 2. FIFO AUTO-SETTLEMENT ACROSS DUE INVOICES OF THIS CUSTOMER
                $dueInvoicesQuery = IdeaInvoice::whereIn('type', ['invoice', 'challan'])
                    ->whereIn('payment_status', ['unpaid', 'partial'])
                    ->where(function ($q) use ($customerName, $customerPhone) {
                        $q->where('customer_name', $customerName);
                        if (!empty($customerPhone)) {
                            $q->orWhere('customer_phone', $customerPhone);
                        }
                    })
                    ->orderBy('invoice_date', 'asc')
                    ->orderBy('id', 'asc');

                $dueInvoices = $dueInvoicesQuery->get();
                $remainingPayment = $amount;
                $settledSummary = [];

                foreach ($dueInvoices as $inv) {
                    if ($remainingPayment <= 0.001) {
                        break;
                    }

                    $invDue = (float) $inv->due_amount;
                    if ($invDue <= 0) continue;

                    $allocatedAmount = min($remainingPayment, $invDue);
                    $payNo = IdeaInvoicePayment::generatePaymentNo();

                    // If previous payments do not exist but invoice had existing paid_amount > 0, backfill
                    if ($inv->payments()->count() === 0 && (float)$inv->paid_amount > 0) {
                        IdeaInvoicePayment::create([
                            'invoice_id'      => $inv->id,
                            'customer_name'   => $inv->customer_name,
                            'customer_phone'  => $inv->customer_phone,
                            'payment_no'      => IdeaInvoicePayment::generatePaymentNo(),
                            'payment_date'    => $inv->invoice_date,
                            'amount'          => (float)$inv->paid_amount,
                            'payment_method'  => $inv->payment_method ?: 'cash',
                            'note'            => 'অগ্রিম জমা (Advance Payment)',
                            'recorded_by'     => $inv->created_by ?: $userId,
                        ]);
                    }

                    IdeaInvoicePayment::create([
                        'invoice_id'      => $inv->id,
                        'customer_name'   => $inv->customer_name,
                        'customer_phone'  => $inv->customer_phone,
                        'payment_no'      => $payNo,
                        'payment_date'    => $paymentDate,
                        'amount'          => $allocatedAmount,
                        'payment_method'  => $paymentMethod,
                        'transaction_ref' => $trxRef,
                        'note'            => $note ? "{$note} [খতিয়ান হতে সমন্বয়]" : "গ্রাহক চলতি খাতা থেকে বিল #{$inv->invoice_no} সমন্বয়",
                        'recorded_by'     => $userId,
                    ]);

                    if ($request->has('due_date')) {
                        $inv->due_date = $request->filled('due_date') ? $request->input('due_date') : null;
                    }

                    $inv->recalculatePayments();

                    // Accounting Income Entry
                    $incomeCat = $inv->type === 'challan' ? 'পাইকারি বিক্রয় ও চালান (Wholesale Sales)' : 'বই বিক্রয় (Book Sales)';
                    IdeaAccountingEntry::create([
                        'entry_no'       => 'INC-' . date('Ymd') . '-' . rand(1000, 9999),
                        'type'           => 'income',
                        'category'       => $incomeCat,
                        'title'          => "বিল #{$inv->invoice_no} হতে কিস্তি জমা — {$customerName}",
                        'amount'         => $allocatedAmount,
                        'entry_date'     => $paymentDate,
                        'payment_method' => $paymentMethod,
                        'party_name'     => $customerName,
                        'voucher_no'     => $payNo,
                        'invoice_id'     => $inv->id,
                        'notes'          => "গ্রাহক চলতি খাতা থেকে বিল #{$inv->invoice_no} এর কিস্তি/জমা রসিদ #{$payNo}",
                        'created_by'     => $userId,
                    ]);

                    $settledSummary[] = "#{$inv->invoice_no} (৳" . number_format($allocatedAmount, 2) . ")";
                    $remainingPayment -= $allocatedAmount;
                }

                // If extra money remains or customer had no due invoices, record unallocated on-account payment
                if ($remainingPayment > 0.001 || empty($settledSummary)) {
                    $payNo = IdeaInvoicePayment::generatePaymentNo();
                    $unallocatedAmount = $remainingPayment > 0.001 ? $remainingPayment : $amount;

                    IdeaInvoicePayment::create([
                        'invoice_id'      => null,
                        'customer_name'   => $customerName,
                        'customer_phone'  => $customerPhone,
                        'payment_no'      => $payNo,
                        'payment_date'    => $paymentDate,
                        'amount'          => $unallocatedAmount,
                        'payment_method'  => $paymentMethod,
                        'transaction_ref' => $trxRef,
                        'note'            => $note ? "{$note} [চলতি খাতা অগ্রিম/অতিরিক্ত জমা]" : "চলতি খাতা অগ্রিম জমা / কিস্তি",
                        'recorded_by'     => $userId,
                    ]);

                    IdeaAccountingEntry::create([
                        'entry_no'       => 'INC-' . date('Ymd') . '-' . rand(1000, 9999),
                        'type'           => 'income',
                        'category'       => 'বই বিক্রয় (Book Sales)',
                        'title'          => "গ্রাহক অগ্রিম/কিস্তি জমা প্রাপ্তি — {$customerName}",
                        'amount'         => $unallocatedAmount,
                        'entry_date'     => $paymentDate,
                        'payment_method' => $paymentMethod,
                        'party_name'     => $customerName,
                        'voucher_no'     => $payNo,
                        'invoice_id'     => null,
                        'notes'          => "গ্রাহক চলতি খাতায় অগ্রিম জমা রসিদ #{$payNo}",
                        'created_by'     => $userId,
                    ]);
                }

                $settledText = !empty($settledSummary) ? ' (' . implode(', ', $settledSummary) . ')' : '';
                return back()->with('success', "গ্রাহক {$customerName}-এর চলতি খাতায় ৳" . number_format($amount, 2) . " টাকা জমা ও সফলভাবে সমন্বয় করা হয়েছে{$settledText}!");
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'টাকা জমা প্রক্রিয়ায় সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Helper to build master customer summaries with total billed, paid, balance, overdue count and aging.
     */
    public function buildCustomerLedgersSummary(?string $search = null, bool $hasDueOnly = false, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $invoices = IdeaInvoice::whereIn('type', ['invoice', 'challan'])
            ->when($dateFrom, fn($q) => $q->whereDate('invoice_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('invoice_date', '<=', $dateTo))
            ->get();

        $standalonePayments = IdeaInvoicePayment::whereNull('invoice_id')->get();

        $customers = [];
        $today = \Carbon\Carbon::today();

        foreach ($invoices as $inv) {
            $name = trim((string)$inv->customer_name);
            $phone = trim((string)$inv->customer_phone);
            if (empty($name) && empty($phone)) continue;

            $key = $name . '___' . ($phone ?: 'no_phone');

            if (!isset($customers[$key])) {
                $customers[$key] = [
                    'key'              => $key,
                    'name'             => $name ?: 'গ্রাহক',
                    'phone'            => $phone ?: '—',
                    'org'              => $inv->customer_org ?: '—',
                    'address'          => $inv->customer_address ?: '—',
                    'designation'      => $inv->customer_designation ?: '—',
                    'email'            => $inv->customer_email ?: '—',
                    'total_billed'     => 0.0,
                    'total_paid'       => 0.0,
                    'current_due'      => 0.0,
                    'invoice_count'    => 0,
                    'overdue_count'    => 0,
                    'last_transaction' => null,
                    'next_due_date'    => null,
                    'aging'            => [
                        'current'  => 0.0, // 0-30 days
                        'days_30'  => 0.0, // 31-60 days
                        'days_60'  => 0.0, // 61-90 days
                        'days_90p' => 0.0, // 90+ days
                    ],
                ];
            }

            if ($phone && $customers[$key]['phone'] === '—') $customers[$key]['phone'] = $phone;
            if ($inv->customer_org && $customers[$key]['org'] === '—') $customers[$key]['org'] = $inv->customer_org;
            if ($inv->customer_address && $customers[$key]['address'] === '—') $customers[$key]['address'] = $inv->customer_address;

            $customers[$key]['total_billed'] += (float)$inv->grand_total;
            $customers[$key]['total_paid'] += (float)$inv->paid_amount;
            $invDue = (float)$inv->due_amount;
            $customers[$key]['current_due'] += $invDue;
            $customers[$key]['invoice_count'] += 1;

            if ($inv->is_overdue) {
                $customers[$key]['overdue_count'] += 1;
            }

            if (!$customers[$key]['last_transaction'] || $inv->invoice_date > $customers[$key]['last_transaction']) {
                $customers[$key]['last_transaction'] = $inv->invoice_date;
            }

            if ($inv->due_date && $invDue > 0) {
                if (!$customers[$key]['next_due_date'] || $inv->due_date < $customers[$key]['next_due_date']) {
                    $customers[$key]['next_due_date'] = $inv->due_date;
                }
            }

            // Calculate aging bucket for pending balance
            if ($invDue > 0) {
                $invDate = $inv->invoice_date ?: $inv->created_at;
                $days = $invDate ? $today->diffInDays($invDate) : 0;

                if ($days <= 30) {
                    $customers[$key]['aging']['current'] += $invDue;
                } elseif ($days <= 60) {
                    $customers[$key]['aging']['days_30'] += $invDue;
                } elseif ($days <= 90) {
                    $customers[$key]['aging']['days_60'] += $invDue;
                } else {
                    $customers[$key]['aging']['days_90p'] += $invDue;
                }
            }
        }

        // Incorporate standalone payments
        foreach ($standalonePayments as $pay) {
            $name = trim((string)$pay->customer_name);
            $phone = trim((string)$pay->customer_phone);
            if (empty($name) && empty($phone)) continue;

            $key = $name . '___' . ($phone ?: 'no_phone');
            if (isset($customers[$key])) {
                $customers[$key]['total_paid'] += (float)$pay->amount;
                $customers[$key]['current_due'] = max(0, $customers[$key]['total_billed'] - $customers[$key]['total_paid']);
            }
        }

        // Filter search term
        if (!empty($search)) {
            $term = mb_strtolower($search);
            $customers = array_filter($customers, function ($c) use ($term) {
                return str_contains(mb_strtolower($c['name']), $term) ||
                       str_contains(mb_strtolower($c['phone']), $term) ||
                       str_contains(mb_strtolower($c['org']), $term);
            });
        }

        // Filter has_due only
        if ($hasDueOnly) {
            $customers = array_filter($customers, fn($c) => $c['current_due'] > 0);
        }

        // Sort by highest current due balance
        uasort($customers, fn($a, $b) => $b['current_due'] <=> $a['current_due']);

        return array_values($customers);
    }

    /**
     * Helper to generate detailed chronological running ledger statement for a customer.
     */
    public function generateCustomerStatement(?string $customerName, ?string $customerPhone, ?string $dateFrom, ?string $dateTo): array
    {
        $invoicesQuery = IdeaInvoice::whereIn('type', ['invoice', 'challan'])
            ->with(['payments.recorder', 'creator'])
            ->where(function ($q) use ($customerName, $customerPhone) {
                if (!empty($customerName)) {
                    $q->where('customer_name', $customerName);
                }
                if (!empty($customerPhone)) {
                    $q->orWhere('customer_phone', $customerPhone);
                }
            });

        $allInvoices = $invoicesQuery->get();
        $invoiceIds = $allInvoices->pluck('id')->all();

        $paymentsQuery = IdeaInvoicePayment::with(['invoice', 'recorder'])
            ->where(function ($q) use ($invoiceIds, $customerName, $customerPhone) {
                $q->whereIn('invoice_id', $invoiceIds);
                if (!empty($customerName)) {
                    $q->orWhere('customer_name', $customerName);
                }
                if (!empty($customerPhone)) {
                    $q->orWhere('customer_phone', $customerPhone);
                }
            });

        $allPayments = $paymentsQuery->get();
        $latestInvoice = $allInvoices->sortByDesc('id')->first();

        $customerInfo = [
            'name'        => $customerName ?: ($latestInvoice?->customer_name ?? 'গ্রাহক'),
            'phone'       => $customerPhone ?: ($latestInvoice?->customer_phone ?? '—'),
            'org'         => $latestInvoice?->customer_org ?? '—',
            'designation' => $latestInvoice?->customer_designation ?? '—',
            'address'     => $latestInvoice?->customer_address ?? '—',
            'email'       => $latestInvoice?->customer_email ?? '—',
        ];

        // Combine into unified chronological ledger entries
        $entries = [];

        foreach ($allInvoices as $inv) {
            $itemsSummary = collect($inv->items ?? [])->pluck('title')->filter()->take(3)->implode(', ');
            $desc = "বিল/চালান #{$inv->invoice_no}";
            if ($itemsSummary) {
                $desc .= " ({$itemsSummary})";
            }

            $entries[] = [
                'date'        => $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : $inv->created_at->format('Y-m-d'),
                'sort_time'   => $inv->invoice_date ? $inv->invoice_date->timestamp : $inv->created_at->timestamp,
                'type'        => 'invoice',
                'type_label'  => $inv->type_label,
                'ref_no'      => $inv->invoice_no,
                'invoice_id'  => $inv->id,
                'due_date'    => $inv->due_date ? $inv->due_date->format('d M, Y') : null,
                'is_overdue'  => $inv->is_overdue,
                'description' => $desc,
                'debit'       => (float) $inv->grand_total, // Billed Amount
                'credit'      => 0.0,
                'notes'       => $inv->notes,
                'creator'     => $inv->creator?->name ?? 'Admin',
            ];

            // If this invoice has legacy paid_amount > 0 and no IdeaInvoicePayment rows linked to it yet:
            $hasLinkedPayments = $allPayments->where('invoice_id', $inv->id)->count() > 0;
            if (!$hasLinkedPayments && (float)$inv->paid_amount > 0) {
                $entries[] = [
                    'date'        => $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : $inv->created_at->format('Y-m-d'),
                    'sort_time'   => ($inv->invoice_date ? $inv->invoice_date->timestamp : $inv->created_at->timestamp) + 1,
                    'type'        => 'payment',
                    'type_label'  => 'অগ্রিম জমা (Advance)',
                    'ref_no'      => $inv->invoice_no . '-ADV',
                    'payment_id'  => null,
                    'invoice_id'  => $inv->id,
                    'due_date'    => null,
                    'is_overdue'  => false,
                    'description' => "বিল #{$inv->invoice_no} এর প্রারম্ভিক/অগ্রিম জমা",
                    'debit'       => 0.0,
                    'credit'      => (float) $inv->paid_amount,
                    'notes'       => $inv->payment_method ? "পদ্ধতি: {$inv->payment_method}" : 'অগ্রিম জমা',
                    'creator'     => $inv->creator?->name ?? 'Admin',
                ];
            }
        }

        foreach ($allPayments as $pay) {
            $desc = "জমা / কিস্তি পরিশোধ (রসিদ #{$pay->payment_no})";
            if ($pay->invoice) {
                $desc .= " — বিল #{$pay->invoice->invoice_no}";
            }
            if ($pay->transaction_ref) {
                $desc .= " (Trx: {$pay->transaction_ref})";
            }

            $entries[] = [
                'date'        => $pay->payment_date ? $pay->payment_date->format('Y-m-d') : $pay->created_at->format('Y-m-d'),
                'sort_time'   => ($pay->payment_date ? $pay->payment_date->timestamp : $pay->created_at->timestamp) + 1, // payments sort right after invoice on same day
                'type'        => 'payment',
                'type_label'  => 'কিস্তি / জমা (Payment)',
                'ref_no'      => $pay->payment_no,
                'payment_id'  => $pay->id,
                'invoice_id'  => $pay->invoice_id,
                'due_date'    => null,
                'is_overdue'  => false,
                'description' => $desc,
                'debit'       => 0.0,
                'credit'      => (float) $pay->amount, // Paid Amount
                'notes'       => $pay->note,
                'creator'     => $pay->recorder?->name ?? 'Admin',
            ];
        }

        // Sort chronologically
        usort($entries, function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return $a['sort_time'] <=> $b['sort_time'];
            }
            return strcmp($a['date'], $b['date']);
        });

        // Compute running balance
        $runningBalance = 0.0;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($entries as &$item) {
            $runningBalance += ($item['debit'] - $item['credit']);
            $item['balance'] = $runningBalance;
            $totalDebit += $item['debit'];
            $totalCredit += $item['credit'];
        }
        unset($item);

        // Date range filtering for view if requested
        $filteredEntries = $entries;
        $openingBalance = 0.0;

        if ($dateFrom || $dateTo) {
            $openingBalance = 0.0;
            $filtered = [];

            foreach ($entries as $item) {
                if ($dateFrom && $item['date'] < $dateFrom) {
                    $openingBalance += ($item['debit'] - $item['credit']);
                    continue;
                }
                if ($dateTo && $item['date'] > $dateTo) {
                    continue;
                }
                $filtered[] = $item;
            }
            $filteredEntries = $filtered;
        }

        return [
            'customer'        => $customerInfo,
            'entries'         => $filteredEntries,
            'total_debit'     => $totalDebit,
            'total_credit'    => $totalCredit,
            'net_due'         => max(0, $totalDebit - $totalCredit),
            'opening_balance' => $openingBalance,
            'invoice_count'   => $allInvoices->count(),
            'payment_count'   => $allPayments->count(),
            'due_invoices'    => $allInvoices->where('due_amount', '>', 0)->values(),
        ];
    }

    /**
     * Update Memo / Invoice Header Business Settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name'                  => 'required|string|max:255',
            'tagline'                        => 'nullable|string|max:255',
            'address'                        => 'nullable|string|max:255',
            'phone'                          => 'nullable|string|max:100',
            'email'                          => 'nullable|string|max:100',
            'terms_and_conditions'           => 'nullable|string',
            'challan_recipient_name_size'    => 'nullable|string|max:10',
            'challan_recipient_phone_size'   => 'nullable|string|max:10',
            'challan_recipient_address_size' => 'nullable|string|max:10',
            'challan_recipient_desig_size'   => 'nullable|string|max:10',
            'challan_recipient_org_size'     => 'nullable|string|max:10',
            'default_creator_designation'    => 'nullable|string|max:150',
            'default_creator_name'           => 'nullable|string|max:150',
            'logo_base64'                    => 'nullable|string',
            'logo_file'                      => 'nullable|image|max:5120',
            'logo_url'                       => 'nullable|string|max:255',
        ]);

        try {
            $settings = self::getInvoiceSettings();
            $settings['business_name'] = $validated['business_name'];
            $settings['tagline'] = $validated['tagline'] ?? '';
            $settings['address'] = $validated['address'] ?? '';
            $settings['phone'] = $validated['phone'] ?? '';
            $settings['email'] = $validated['email'] ?? '';
            $settings['terms_and_conditions'] = $validated['terms_and_conditions'] ?? '';
            $settings['challan_recipient_name_size'] = $validated['challan_recipient_name_size'] ?? '13px';
            $settings['challan_recipient_phone_size'] = $validated['challan_recipient_phone_size'] ?? '12px';
            $settings['challan_recipient_address_size'] = $validated['challan_recipient_address_size'] ?? '11.5px';
            $settings['challan_recipient_desig_size'] = $validated['challan_recipient_desig_size'] ?? '11.5px';
            $settings['challan_recipient_org_size'] = $validated['challan_recipient_org_size'] ?? '12px';
            $settings['default_creator_designation'] = $validated['default_creator_designation'] ?? '';
            $settings['default_creator_name'] = $validated['default_creator_name'] ?? '';

            // Handle 2:1 cropped base64 image
            if (!empty($validated['logo_base64']) && str_starts_with($validated['logo_base64'], 'data:image/')) {
                $base64 = $validated['logo_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $base64Data = substr($base64, strpos($base64, ',') + 1);
                    $decoded = base64_decode($base64Data);
                    if ($decoded !== false) {
                        $ext = strtolower($type[1] ?? 'png');
                        if ($ext === 'jpeg') $ext = 'jpg';
                        $filename = 'invoice_logo_' . time() . '.' . $ext;

                        // Primary target: public/images/settings
                        $targetDir = public_path('images/settings');
                        if (!is_dir($targetDir)) {
                            @mkdir($targetDir, 0777, true);
                        }
                        @file_put_contents($targetDir . '/' . $filename, $decoded);

                        // Backup target: storage/app/public/settings
                        $storageDir = storage_path('app/public/settings');
                        if (!is_dir($storageDir)) {
                            @mkdir($storageDir, 0777, true);
                        }
                        @file_put_contents($storageDir . '/' . $filename, $decoded);

                        $settings['logo'] = 'images/settings/' . $filename;
                    }
                }
            } elseif ($request->hasFile('logo_file')) {
                $file = $request->file('logo_file');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = 'invoice_logo_' . time() . '.' . $ext;
                
                $targetDir = public_path('images/settings');
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                $file->move($targetDir, $filename);
                $settings['logo'] = 'images/settings/' . $filename;
            } elseif (!empty($validated['logo_url'])) {
                $settings['logo'] = $validated['logo_url'];
            }

            $userId = auth()->id() ?: null;
            if ($userId && !\Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->exists()) {
                $userId = null;
            }

            \Illuminate\Support\Facades\DB::table('admin_dashboard_settings')->updateOrInsert(
                ['key' => 'invoice_settings'],
                [
                    'value'      => json_encode($settings, JSON_UNESCAPED_UNICODE),
                    'updated_by' => $userId,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            \App\Support\SiteSetting::clearCache();

            return back()->with('success', 'বিল ও চালানের ডিজাইন, ফন্ট সাইজ এবং অফিশিয়াল তথ্য সফলভাবে আপডেট করা হয়েছে।');
        } catch (\Throwable $e) {
            return back()->with('error', 'তথ্য সংরক্ষণে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Get Invoice / Memo Header Business Settings.
     */
    public static function getInvoiceSettings(): array
    {
        $default = [
            'business_name'                  => \App\Support\SiteSetting::name(),
            'tagline'                        => \App\Support\SiteSetting::tagline(),
            'address'                        => 'ঢাকা, বাংলাদেশ',
            'phone'                          => '018XXXXXXXX',
            'email'                          => 'info@ideaabd.com',
            'logo'                           => '/images/logo.png',
            'challan_recipient_name_size'    => '13px',
            'challan_recipient_phone_size'   => '12px',
            'challan_recipient_address_size' => '11.5px',
            'challan_recipient_desig_size'   => '11.5px',
            'challan_recipient_org_size'     => '12px',
            'default_creator_designation'    => '',
            'default_creator_name'           => '',
        ];

        try {
            $stored = \App\Support\SiteSetting::get('invoice_settings', []);
            if (is_string($stored)) {
                $decoded = json_decode($stored, true);
                if (is_array($decoded)) {
                    $stored = $decoded;
                } elseif (is_string($decoded)) {
                    $second = json_decode($decoded, true);
                    if (is_array($second)) {
                        $stored = $second;
                    }
                }
            }

            if (!is_array($stored)) {
                $stored = [];
            }

            return array_merge($default, $stored);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Public / Client View for Invoice & Challan via shared link / QR.
     */
    public function publicShow(string $token): View
    {
        $invoice = IdeaInvoice::where('access_token', $token)
            ->orWhere('invoice_no', $token)
            ->orWhere('id', is_numeric($token) ? $token : 0)
            ->firstOrFail();

        $invoiceSettings = self::getInvoiceSettings();

        return view('invoices.public-show', compact('invoice', 'invoiceSettings'));
    }

    /**
     * Send Invoice & Delivery Challan to Customer Email (Single or Multiple Recipients).
     */
    public function sendInvoiceEmail(Request $request, IdeaInvoice $invoice): RedirectResponse
    {
        $rawEmails = $request->input('email') ?? $request->input('emails');
        
        $emailList = [];
        if (is_array($rawEmails)) {
            $emailList = $rawEmails;
        } elseif (is_string($rawEmails)) {
            $emailList = preg_split('/[\s,;]+/', trim($rawEmails), -1, PREG_SPLIT_NO_EMPTY);
        }

        $validEmails = [];
        $invalidEmails = [];
        foreach ($emailList as $em) {
            $emClean = trim($em);
            if (filter_var($emClean, FILTER_VALIDATE_EMAIL)) {
                $validEmails[] = strtolower($emClean);
            } elseif (!empty($emClean)) {
                $invalidEmails[] = $emClean;
            }
        }
        $validEmails = array_values(array_unique($validEmails));

        if (empty($validEmails)) {
            return back()->with('error', 'অনুগ্রহ করে অন্তত একটি সঠিক ইমেইল ঠিকানা লিখুন।')->withInput();
        }

        if (!empty($invalidEmails)) {
            return back()->with('error', 'নিচের ইমেইল ঠিকানাগুলো সঠিক ফরম্যাটে নেই: ' . implode(', ', $invalidEmails))->withInput();
        }

        try {
            IdeaInvoice::ensureColumnsExist();

            if (empty($invoice->customer_email) || $invoice->customer_email === 'customer@example.com') {
                $invoice->customer_email = $validEmails[0];
            }
            if (empty($invoice->access_token)) {
                $invoice->access_token = Str::random(32);
            }
            $invoice->emailed_at = now();

            $customMsg = $request->input('custom_message') ? trim($request->input('custom_message')) : null;
            $senderEmail = config('mail.from.address', 'ideapbd@gmail.com');
            $sentLogs = $invoice->email_logs ?? [];
            if (!is_array($sentLogs)) {
                $sentLogs = [];
            }

            $successRecipients = [];
            $failedRecipients = [];

            // Send individual direct emails to all valid recipients
            foreach ($validEmails as $singleRecipient) {
                try {
                    Mail::to($singleRecipient)->send(new CustomerInvoiceMail(
                        $invoice,
                        $customMsg,
                        self::getInvoiceSettings()
                    ));
                    $successRecipients[] = $singleRecipient;
                } catch (\Throwable $sendEx) {
                    Log::error("Failed sending invoice email to {$singleRecipient}: " . $sendEx->getMessage());
                    $failedRecipients[] = $singleRecipient . " (" . $sendEx->getMessage() . ")";
                }
            }

            // Append to Email Logs
            $newLogEntry = [
                'id'             => Str::uuid()->toString(),
                'sent_at'        => now()->toDateTimeString(),
                'sender'         => $senderEmail,
                'recipients'     => $successRecipients,
                'failed'         => $failedRecipients,
                'total_sent'     => count($successRecipients),
                'custom_message' => $customMsg,
                'sent_by'        => auth()->user()?->name ?? 'Admin',
                'status'         => empty($failedRecipients) ? 'success' : (empty($successRecipients) ? 'failed' : 'partial'),
            ];

            array_unshift($sentLogs, $newLogEntry);
            $invoice->email_logs = array_slice($sentLogs, 0, 50); // Keep latest 50 logs
            $invoice->save();

            if (empty($successRecipients)) {
                $errDetail = implode(', ', $failedRecipients);
                return back()->with('error', "ইমেইল পাঠাতে ব্যর্থ হয়েছে: {$errDetail}");
            }

            $count = count($successRecipients);
            $recipientsList = implode(', ', $successRecipients);
            $successMsg = ($count > 1)
                ? "📧 মেইল সেন্ড রিপোর্ট: মোট {$count}টি ঠিকানায় ({$recipientsList}) সফলভাবে বিল ও চালানের ডিজিটাল লিংক পাঠানো হয়েছে।"
                : "📧 মেইল সেন্ড রিপোর্ট: গ্রাহকের ইমেইল ({$successRecipients[0]}) এ সফলভাবে বিল ও চালানের ডিজিটাল লিংক পাঠানো হয়েছে।";

            if (!empty($failedRecipients)) {
                $successMsg .= " [ব্যর্থ: " . implode(', ', $failedRecipients) . "]";
            }

            return back()->with('success', $successMsg);
        } catch (\Throwable $e) {
            Log::error("Invoice email failed for #{$invoice->invoice_no}: " . $e->getMessage());
            return back()->with('error', 'ইমেইল পাঠাতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Delete Bill / Challan / Quotation / Tender.
     */
    public function destroyInvoice(IdeaInvoice $invoice): RedirectResponse
    {
        $invoice->delete();
        return redirect()->route('admin.accounting.invoices.index')
            ->with('success', 'ডকুমেন্টটি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * Comprehensive Financial & Profit/Loss Reports (Daily, Weekly, Monthly, Yearly).
     */
    public function reports(Request $request): View
    {
        $period = $request->input('period', 'monthly'); // daily, weekly, monthly, yearly, custom
        $year = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('n'));
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Resolve Date Range based on period
        $now = Carbon::now();
        switch ($period) {
            case 'daily':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $periodLabel = 'আজকের হিসাব (' . $now->format('d M, Y') . ')';
                break;
            case 'weekly':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $periodLabel = 'চলতি সপ্তাহের হিসাব (' . $startDate->format('d M') . ' - ' . $endDate->format('d M, Y') . ')';
                break;
            case 'yearly':
                $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
                $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();
                $periodLabel = $year . ' সালের বাৎসরিক হিসাব';
                break;
            case 'custom':
                $startDate = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : $now->copy()->startOfMonth();
                $endDate = $dateTo ? Carbon::parse($dateTo)->endOfDay() : $now->copy()->endOfDay();
                $periodLabel = 'কাস্টম সময়কাল (' . $startDate->format('d M, Y') . ' - ' . $endDate->format('d M, Y') . ')';
                break;
            case 'monthly':
            default:
                $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
                $endDate = $startDate->copy()->endOfMonth()->endOfDay();
                $periodLabel = $startDate->format('F Y') . ' এর মাসিক হিসাব';
                break;
        }

        // Base Queries within range
        $entriesQuery = IdeaAccountingEntry::whereBetween('entry_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $totalIncome = (float) (clone $entriesQuery)->where('type', 'income')->sum('amount');
        $totalExpense = (float) (clone $entriesQuery)->where('type', 'expense')->sum('amount');

        // Raw Materials & Production Costs
        $prodCategories = IdeaAccountingEntry::productionCategories();
        $productionCost = (float) (clone $entriesQuery)
            ->where('type', 'expense')
            ->whereIn('category', $prodCategories)
            ->sum('amount');

        // Production Cost Item Breakdown (কাগজ, বোর্ড, কালি, প্রেস, লেমিনেশন, ইত্যাদি)
        $productionBreakdown = (clone $entriesQuery)
            ->where('type', 'expense')
            ->whereIn('category', $prodCategories)
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Staff & Payroll Costs
        $payrollCategories = IdeaAccountingEntry::payrollCategories();
        $payrollCost = (float) (clone $entriesQuery)
            ->where('type', 'expense')
            ->whereIn('category', $payrollCategories)
            ->sum('amount');

        // Other Operating & Administrative Expenses
        $otherExpense = (float) (clone $entriesQuery)
            ->where('type', 'expense')
            ->whereNotIn('category', array_merge($prodCategories, $payrollCategories))
            ->sum('amount');

        // Operating Expenses Breakdown
        $operatingBreakdown = (clone $entriesQuery)
            ->where('type', 'expense')
            ->whereNotIn('category', $prodCategories)
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Book Sales Specific Revenue
        $bookSalesIncome = (float) (clone $entriesQuery)
            ->where('type', 'income')
            ->where(function($q) {
                $q->where('category', 'LIKE', '%বই বিক্রয়%')
                  ->orWhere('category', 'LIKE', '%পাইকারি%')
                  ->orWhere('category', 'LIKE', '%Book Sales%');
            })
            ->sum('amount');
        if ($bookSalesIncome <= 0 && $totalIncome > 0) {
            $bookSalesIncome = $totalIncome;
        }

        // Gross Profit = Total Book Sales - Raw Materials & Production Cost
        $grossProfit = $totalIncome - $productionCost;

        // Net Profit = Total Income - Total Expense
        $netProfit = $totalIncome - $totalExpense;
        $netProfitMargin = $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 2) : 0;

        // Monthly breakdown for yearly view or daily breakdown for monthly view
        $trendDataset = [];
        if ($period === 'yearly') {
            for ($m = 1; $m <= 12; $m++) {
                $mStart = Carbon::createFromDate($year, $m, 1)->startOfDay();
                $mEnd = $mStart->copy()->endOfMonth()->endOfDay();
                $mInc = (float) IdeaAccountingEntry::whereBetween('entry_date', [$mStart->toDateString(), $mEnd->toDateString()])->where('type', 'income')->sum('amount');
                $mExp = (float) IdeaAccountingEntry::whereBetween('entry_date', [$mStart->toDateString(), $mEnd->toDateString()])->where('type', 'expense')->sum('amount');
                $mProd = (float) IdeaAccountingEntry::whereBetween('entry_date', [$mStart->toDateString(), $mEnd->toDateString()])->where('type', 'expense')->whereIn('category', $prodCategories)->sum('amount');
                $trendDataset[] = [
                    'label' => $mStart->format('M'),
                    'income' => $mInc,
                    'expense' => $mExp,
                    'production' => $mProd,
                    'profit' => $mInc - $mExp,
                ];
            }
        } elseif ($period === 'monthly') {
            $daysInMonth = $startDate->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dDate = Carbon::createFromDate($year, $month, $d)->toDateString();
                $dInc = (float) IdeaAccountingEntry::whereDate('entry_date', $dDate)->where('type', 'income')->sum('amount');
                $dExp = (float) IdeaAccountingEntry::whereDate('entry_date', $dDate)->where('type', 'expense')->sum('amount');
                $trendDataset[] = [
                    'label' => $d,
                    'income' => $dInc,
                    'expense' => $dExp,
                    'profit' => $dInc - $dExp,
                ];
            }
        }

        // Detailed Transactions within the period
        $transactions = (clone $entriesQuery)
            ->with('creator')
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $invoiceSettings = self::getInvoiceSettings();

        return view('admin.accounting.reports.index', compact(
            'period', 'year', 'month', 'dateFrom', 'dateTo', 'periodLabel',
            'startDate', 'endDate', 'totalIncome', 'totalExpense', 'productionCost',
            'productionBreakdown', 'payrollCost', 'otherExpense', 'operatingBreakdown',
            'bookSalesIncome', 'grossProfit', 'netProfit', 'netProfitMargin',
            'trendDataset', 'transactions', 'invoiceSettings'
        ));
    }

    /**
     * Employee Directory & Staff Management.
     */
    public function employees(Request $request): View
    {
        $search = $request->input('search');
        $department = $request->input('department');
        $employmentType = $request->input('employment_type');
        $status = $request->input('status');

        $query = IdeaEmployee::query()
            ->withCount('salaryPayments')
            ->withSum('salaryPayments', 'net_paid')
            ->when($department, fn($q) => $q->where('department', $department))
            ->when($employmentType, fn($q) => $q->where('employment_type', $employmentType))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                      ->orWhere('phone', 'like', $like)
                      ->orWhere('designation', 'like', $like)
                      ->orWhere('skill_category', 'like', $like);
                });
            })
            ->latest('id');

        $employees = $query->paginate(20)->withQueryString();
        $departments = IdeaEmployee::departments();
        $employmentTypes = IdeaEmployee::employmentTypes();
        $rateTypes = IdeaEmployee::rateTypes();
        $skillCategories = IdeaEmployee::skillCategories();

        $totalEmployees = IdeaEmployee::count();
        $activeEmployees = IdeaEmployee::where('status', 'active')->count();
        $monthlyPayroll = (float) IdeaEmployee::where('status', 'active')->where(function($q) {
            $q->where('employment_type', 'monthly')->orWhereNull('employment_type');
        })->sum('basic_salary');
        $pieceRateCount = IdeaEmployee::where('status', 'active')->where('employment_type', 'contract_piece')->count();
        $dailyWageCount = IdeaEmployee::where('status', 'active')->where('employment_type', 'daily')->count();

        return view('admin.accounting.employees.index', compact(
            'employees', 'departments', 'employmentTypes', 'rateTypes', 'skillCategories',
            'search', 'department', 'employmentType', 'status',
            'totalEmployees', 'activeEmployees', 'monthlyPayroll', 'pieceRateCount', 'dailyWageCount'
        ));
    }

    /**
     * Store a new employee.
     */
    public function storeEmployee(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'designation'       => 'required|string|max:255',
            'department'        => 'required|string|max:255',
            'employment_type'   => 'required|string|max:40',
            'skill_category'    => 'nullable|string|max:100',
            'phone'             => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:255',
            'basic_salary'      => 'required|numeric|min:0',
            'salary_rate_type'  => 'required|string|max:40',
            'rate_unit_name'    => 'nullable|string|max:60',
            'payment_schedule'  => 'nullable|string|max:40',
            'joining_date'      => 'nullable|date',
            'status'            => 'required|in:active,inactive,on_leave',
            'address'           => 'nullable|string|max:500',
            'nid_passport'      => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:1000',
        ]);

        IdeaEmployee::create($validated);

        return back()->with('success', 'নতুন কর্মচারী / কারিগরের তথ্য সফলভাবে যুক্ত করা হয়েছে।');
    }

    /**
     * Update employee details.
     */
    public function updateEmployee(Request $request, $id): RedirectResponse
    {
        $employee = IdeaEmployee::findOrFail($id);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'designation'       => 'required|string|max:255',
            'department'        => 'required|string|max:255',
            'employment_type'   => 'required|string|max:40',
            'skill_category'    => 'nullable|string|max:100',
            'phone'             => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:255',
            'basic_salary'      => 'required|numeric|min:0',
            'salary_rate_type'  => 'required|string|max:40',
            'rate_unit_name'    => 'nullable|string|max:60',
            'payment_schedule'  => 'nullable|string|max:40',
            'joining_date'      => 'nullable|date',
            'status'            => 'required|in:active,inactive,on_leave',
            'address'           => 'nullable|string|max:500',
            'nid_passport'      => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:1000',
        ]);

        $employee->update($validated);

        return back()->with('success', 'কর্মচারী / কারিগরের তথ্য সফলভাবে আপডেট করা হয়েছে।');
    }

    /**
     * Delete employee record.
     */
    public function destroyEmployee($id): RedirectResponse
    {
        $employee = IdeaEmployee::findOrFail($id);
        $employee->delete();

        return back()->with('success', 'কর্মচারী সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * Book Binder & Worker Work Log & Cash Ledger (কাজের খতিয়ান ও টাকা তোলার হিসাব).
     */
    public function employeeLedger($id, Request $request): View
    {
        $employee = IdeaEmployee::with(['workLogs' => fn($q) => $q->latest('log_date')->latest('id')])->findOrFail($id);
        
        $perPage = $request->input('per_page', 25);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $bookFilter = $request->input('book_title');

        $query = $employee->workLogs()->latest('log_date')->latest('id');
        if ($dateFrom) {
            $query->whereDate('log_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('log_date', '<=', $dateTo);
        }
        if ($bookFilter) {
            $query->where('book_title', 'like', '%' . $bookFilter . '%');
        }

        $workLogs = ($perPage === 'all' || $request->has('print'))
            ? $query->paginate(500)
            : $query->paginate((int) $perPage);

        $totalEarned = (float) $employee->totalWorkEarned();
        $totalPaid = (float) $employee->totalWorkPaid();
        $balanceDue = $totalEarned - $totalPaid;
        $totalWorkQuantity = (float) $employee->workLogs()->where('entry_type', 'work')->sum('quantity');

        // Multi-day Book Production Aggregation
        $allWorkLogs = $employee->workLogs()->where('entry_type', 'work')->get();
        $bookSummaries = $allWorkLogs
            ->filter(fn($l) => !empty($l->book_title))
            ->groupBy('book_title')
            ->map(function ($logs, $bookTitle) {
                $maxPrinted = (float) $logs->max('printed_quantity');
                $maxReceived = (float) ($logs->max('received_quantity') ?: $maxPrinted);
                $totalDelivered = (float) $logs->sum('delivered_quantity');
                if ($totalDelivered == 0) {
                    $totalDelivered = (float) $logs->sum('quantity');
                }
                $totalWastage = (float) $logs->sum('wastage_quantity');
                $totalEarned = (float) $logs->sum('earned_amount');
                $firstPrintDate = $logs->whereNotNull('print_date')->sortBy('print_date')->first()?->print_date;
                $lastLogDate = $logs->sortByDesc('log_date')->first()?->log_date;
                $daysCount = $logs->pluck('log_date')->map(fn($d) => $d ? $d->format('Y-m-d') : '')->unique()->filter()->count();
                $incomplete = max(0, $maxReceived - ($totalDelivered + $totalWastage));
                $progress = $maxReceived > 0 ? min(100, round(($totalDelivered / $maxReceived) * 100, 1)) : 100;
                $lastUnitRate = (float) ($logs->sortByDesc('id')->first()?->unit_rate ?: 0);

                return [
                    'book_title'      => $bookTitle,
                    'print_date'      => $firstPrintDate ? $firstPrintDate->format('Y-m-d') : null,
                    'last_log_date'   => $lastLogDate ? $lastLogDate->format('d M, Y') : null,
                    'days_count'      => $daysCount,
                    'entries_count'   => $logs->count(),
                    'printed_qty'     => $maxPrinted,
                    'received_qty'    => $maxReceived,
                    'total_delivered' => $totalDelivered,
                    'total_wastage'   => $totalWastage,
                    'incomplete_qty'  => $incomplete,
                    'total_earned'    => $totalEarned,
                    'progress'        => $progress,
                    'unit_rate'       => $lastUnitRate,
                    'status'          => $incomplete <= 0 ? 'completed' : 'in_progress',
                ];
            })->values();

        $invoiceSettings = self::getInvoiceSettings();

        return view('admin.accounting.employees.ledger', compact(
            'employee', 'workLogs', 'totalEarned', 'totalPaid', 'balanceDue', 'totalWorkQuantity', 'bookSummaries', 'invoiceSettings'
        ));
    }

    /**
     * Store Work Entry or Cash Withdrawal for Book Binder / Worker.
     */
    public function storeWorkLog($id, Request $request): RedirectResponse
    {
        $employee = IdeaEmployee::findOrFail($id);

        $validated = $request->validate([
            'entry_type'          => 'required|in:work,payment',
            'log_date'            => 'required|date',
            'print_date'          => 'nullable|date',
            'book_title'          => 'nullable|string|max:255',
            'printed_quantity'    => 'nullable|numeric|min:0',
            'received_quantity'   => 'nullable|numeric|min:0',
            'delivered_quantity'  => 'nullable|numeric|min:0',
            'incomplete_quantity' => 'nullable|numeric|min:0',
            'wastage_quantity'    => 'nullable|numeric|min:0',
            'quantity'            => 'nullable|numeric|min:0',
            'unit_rate'           => 'nullable|numeric|min:0',
            'unit_name'           => 'nullable|string|max:50',
            'earned_amount'       => 'nullable|numeric|min:0',
            'paid_amount'         => 'nullable|numeric|min:0',
            'payment_method'      => 'nullable|string|max:50',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $entryType = $validated['entry_type'];
        $bookTitle = trim($validated['book_title'] ?? '');
        $printedQty = (float) ($validated['printed_quantity'] ?? 0);
        $receivedQty = (float) ($validated['received_quantity'] ?? 0);
        $deliveredQty = (float) ($validated['delivered_quantity'] ?? 0);
        $wastageQty = (float) ($validated['wastage_quantity'] ?? 0);

        // 5. Total Binding (Auto) = 2. Received for Binding + 3. Delivered to Godown
        $totalBinding = (float) ($validated['quantity'] ?? ($receivedQty + $deliveredQty));
        if ($totalBinding <= 0 && ($receivedQty > 0 || $deliveredQty > 0)) {
            $totalBinding = $receivedQty + $deliveredQty;
        }

        // 4. Incomplete (Auto) = 1. Total Printed - (2. Received for Binding + 3. Delivered to Godown + Wastage)
        $incompleteQty = isset($validated['incomplete_quantity']) && $validated['incomplete_quantity'] !== null && $validated['incomplete_quantity'] !== ''
            ? (float) $validated['incomplete_quantity']
            : ($printedQty > 0 ? max(0, $printedQty - ($totalBinding + $wastageQty)) : 0);

        // Billable quantity is 5. Total Binding
        $qty = $totalBinding > 0 ? $totalBinding : ($deliveredQty > 0 ? $deliveredQty : 0);
        $rate = (float) ($validated['unit_rate'] ?? 0);
        $earned = (float) ($validated['earned_amount'] ?? 0);
        $paid = (float) ($validated['paid_amount'] ?? 0);

        if ($entryType === 'work') {
            if ($earned <= 0 && $qty > 0 && $rate > 0) {
                $earned = $qty * $rate;
            }
            $paid = 0;
        } else {
            $earned = 0;
            if ($paid <= 0) {
                return back()->with('error', 'Please enter withdrawal amount.');
            }
        }

        $voucherNo = 'LOG-' . date('Ymd', strtotime($validated['log_date'])) . '-' . rand(1000, 9999);
        $accountingEntryId = null;

        // If it's a cash withdrawal or payment, automatically record in IdeaAccountingEntry
        if ($entryType === 'payment' && $paid > 0) {
            $paymentMethod = $validated['payment_method'] ?? 'cash';
            $accEntry = IdeaAccountingEntry::create([
                'entry_no'       => 'EXP-' . date('Ymd', strtotime($validated['log_date'])) . '-' . rand(1000, 9999),
                'type'           => 'expense',
                'category'       => 'চুক্তিভিত্তিক ও বাইন্ডিং মজুরি (Piece-rate Wages)',
                'title'          => "Artisan Payout / Wage Draw: {$employee->name} ({$employee->designation})",
                'amount'         => $paid,
                'entry_date'     => $validated['log_date'],
                'voucher_no'     => $voucherNo,
                'payment_method' => $paymentMethod,
                'party_name'     => $employee->name,
                'notes'          => "Artisan Cash Withdrawal: ৳{$paid}. " . ($validated['notes'] ?? ''),
                'created_by'     => auth()->id(),
            ]);
            $accountingEntryId = $accEntry->id;
        }

        IdeaEmployeeWorkLog::create([
            'employee_id'         => $employee->id,
            'entry_type'          => $entryType,
            'log_date'            => $validated['log_date'],
            'print_date'          => $validated['print_date'] ?? null,
            'book_title'          => $validated['book_title'] ?? null,
            'printed_quantity'    => $printedQty,
            'received_quantity'   => $receivedQty,
            'delivered_quantity'  => $deliveredQty,
            'incomplete_quantity' => $incompleteQty,
            'wastage_quantity'    => $wastageQty,
            'quantity'            => $qty,
            'unit_rate'           => $rate,
            'unit_name'           => $validated['unit_name'] ?? ($employee->rate_unit_name ?: 'Book'),
            'earned_amount'       => $earned,
            'paid_amount'         => $paid,
            'payment_method'      => $validated['payment_method'] ?? 'cash',
            'voucher_no'          => $voucherNo,
            'accounting_entry_id' => $accountingEntryId,
            'notes'               => $validated['notes'] ?? null,
            'created_by'          => auth()->id(),
        ]);

        $msg = $entryType === 'work' 
            ? "Work entry successfully saved. Delivered: {$deliveredQty}, Incomplete: {$incompleteQty} (Total Earned: ৳" . number_format($earned, 2) . ")."
            : "Cash payout of ৳" . number_format($paid, 2) . " successfully recorded and posted to accounts ledger.";

        return back()->with('success', $msg);
    }

    /**
     * Delete a work log entry.
     */
    public function destroyWorkLog($workLogId): RedirectResponse
    {
        $log = IdeaEmployeeWorkLog::findOrFail($workLogId);
        if ($log->accounting_entry_id) {
            IdeaAccountingEntry::where('id', $log->accounting_entry_id)->delete();
        }
        $log->delete();

        return back()->with('success', 'এন্ট্রি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * Salary Payments & Disbursement Ledger.
     */
    public function salaryDisbursements(Request $request): View
    {
        $search = $request->input('search');
        $month = $request->input('month', date('Y-m'));
        $employeeId = $request->input('employee_id');

        $query = IdeaSalaryPayment::with('employee', 'accountingEntry', 'creator')
            ->when($month, fn($q) => $q->where('salary_month', $month))
            ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId))
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->whereHas('employee', function ($eq) use ($like) {
                    $eq->where('name', 'like', $like)
                       ->orWhere('designation', 'like', $like)
                       ->orWhere('skill_category', 'like', $like);
                })->orWhere('slip_no', 'like', $like)
                  ->orWhere('work_details', 'like', $like);
            })
            ->latest('payment_date')
            ->latest('id');

        $payments = $query->paginate(20)->withQueryString();
        $employees = IdeaEmployee::where('status', 'active')->orderBy('name')->get();

        $totalPaidInMonth = (float) IdeaSalaryPayment::where('salary_month', $month)->sum('net_paid');
        $totalPaidAllTime = (float) IdeaSalaryPayment::sum('net_paid');

        return view('admin.accounting.salary.index', compact(
            'payments', 'employees', 'search', 'month', 'employeeId',
            'totalPaidInMonth', 'totalPaidAllTime'
        ));
    }

    /**
     * Store Salary Payment & Automatically Create Accounting Expense Entry.
     */
    public function storeSalaryPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id'      => 'required|exists:idea_employees,id',
            'salary_month'     => 'required|string|max:7',
            'payment_date'     => 'required|date',
            'work_details'     => 'nullable|string|max:1000',
            'job_quantity'     => 'nullable|numeric|min:0',
            'rate_per_unit'    => 'nullable|numeric|min:0',
            'rate_unit_name'   => 'nullable|string|max:60',
            'basic_amount'     => 'required|numeric|min:0',
            'bonus_amount'     => 'nullable|numeric|min:0',
            'overtime_amount'  => 'nullable|numeric|min:0',
            'deduction_amount' => 'nullable|numeric|min:0',
            'payment_method'   => 'required|string|max:50',
            'trx_reference'    => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $employee = IdeaEmployee::findOrFail($validated['employee_id']);

        $basic = (float) $validated['basic_amount'];
        $bonus = (float) ($validated['bonus_amount'] ?? 0);
        $overtime = (float) ($validated['overtime_amount'] ?? 0);
        $deduction = (float) ($validated['deduction_amount'] ?? 0);
        $netPaid = max(0, $basic + $bonus + $overtime - $deduction);

        $slipNo = 'PAY-' . str_replace('-', '', $validated['salary_month']) . '-' . rand(1000, 9999);

        // 1. Create corresponding expense entry in IdeaAccountingEntry
        $entryDateStr = date('Ymd', strtotime($validated['payment_date']));
        $entryNo = 'EXP-' . $entryDateStr . '-' . rand(1000, 9999);

        $monthFormatted = Carbon::createFromFormat('Y-m', $validated['salary_month'])->format('F Y');
        
        $expenseCategory = 'কর্মচারী মূল বেতন (Staff Basic Salary)';
        if ($employee->employment_type === 'contract_piece') {
            $expenseCategory = 'চুক্তিভিত্তিক ও বাইন্ডিং মজুরি (Piece-rate Wages)';
        } elseif ($employee->employment_type === 'daily' || $employee->employment_type === 'weekly') {
            $expenseCategory = 'দৈনিক/সাপ্তাহিক মজুরি (Wages & Labour)';
        } elseif ($employee->employment_type === 'contract_project') {
            $expenseCategory = 'চুক্তিভিত্তিক প্রজেক্ট ফি (Contract Project Fee)';
        }

        $entryTitle = "বেতন/মজুরি প্রদান: {$employee->name} ({$employee->designation}) — {$monthFormatted}";
        if (!empty($validated['work_details'])) {
            $entryTitle .= " [{$validated['work_details']}]";
        }

        $calcBreakdown = "মূল মজুরি/বেতন: ৳{$basic}";
        if (!empty($validated['job_quantity']) && !empty($validated['rate_per_unit'])) {
            $calcBreakdown .= " ({$validated['job_quantity']} {$validated['rate_unit_name']} @ ৳{$validated['rate_per_unit']})";
        }
        $calcBreakdown .= ", বোনাস: ৳{$bonus}, ওভারটাইম: ৳{$overtime}, কর্তন: ৳{$deduction}. " . ($validated['notes'] ?? '');

        $entry = IdeaAccountingEntry::create([
            'entry_no'       => $entryNo,
            'type'           => 'expense',
            'category'       => $expenseCategory,
            'title'          => $entryTitle,
            'amount'         => $netPaid,
            'entry_date'     => $validated['payment_date'],
            'voucher_no'     => $slipNo,
            'payment_method' => $validated['payment_method'],
            'party_name'     => $employee->name,
            'notes'          => $calcBreakdown,
            'created_by'     => auth()->id(),
        ]);

        // 2. Create Salary Payment Record
        IdeaSalaryPayment::create([
            'employee_id'         => $employee->id,
            'salary_month'        => $validated['salary_month'],
            'employment_type'     => $employee->employment_type,
            'payment_date'        => $validated['payment_date'],
            'work_details'        => $validated['work_details'] ?? null,
            'job_quantity'        => $validated['job_quantity'] ?? null,
            'rate_per_unit'       => $validated['rate_per_unit'] ?? null,
            'rate_unit_name'      => $validated['rate_unit_name'] ?? null,
            'basic_amount'        => $basic,
            'bonus_amount'        => $bonus,
            'overtime_amount'     => $overtime,
            'deduction_amount'    => $deduction,
            'net_paid'            => $netPaid,
            'payment_method'      => $validated['payment_method'],
            'trx_reference'       => $validated['trx_reference'],
            'slip_no'             => $slipNo,
            'accounting_entry_id' => $entry->id,
            'notes'               => $validated['notes'],
            'created_by'          => auth()->id(),
        ]);

        return back()->with('success', "{$employee->name} এর {$monthFormatted} এর বেতন/মজুরি (৳" . number_format($netPaid, 2) . ") সফলভাবে প্রদান ও হিসাব খতিয়ানে যুক্ত হয়েছে।");
    }

    /**
     * Printable Salary Slip / Pay Receipt.
     */
    public function salarySlip($id): View
    {
        $salary = IdeaSalaryPayment::with('employee', 'creator')->findOrFail($id);
        $invoiceSettings = self::getInvoiceSettings();

        return view('admin.accounting.salary.slip', compact('salary', 'invoiceSettings'));
    }
}
