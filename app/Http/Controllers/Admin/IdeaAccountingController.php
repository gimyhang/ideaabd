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
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Services\IdeaInvoiceItemService;
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
        $paymentMethod = $request->input('payment_method');

        $query = IdeaAccountingEntry::query()
            ->with('creator', 'invoice')
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($paymentMethod, fn($q) => $q->where('payment_method', $paymentMethod))
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

        $entries = $query->paginate(25)->withQueryString();

        $totalIncome = (float) IdeaAccountingEntry::where('type', 'income')->sum('amount');
        $totalExpense = (float) IdeaAccountingEntry::where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        // Today & Current Month Stats
        $today = Carbon::today();
        $thisMonth = Carbon::now();

        $todayIncome = (float) IdeaAccountingEntry::where('type', 'income')->whereDate('entry_date', $today)->sum('amount');
        $todayExpense = (float) IdeaAccountingEntry::where('type', 'expense')->whereDate('entry_date', $today)->sum('amount');

        $thisMonthIncome = (float) IdeaAccountingEntry::where('type', 'income')
            ->whereYear('entry_date', $thisMonth->year)
            ->whereMonth('entry_date', $thisMonth->month)
            ->sum('amount');
        $thisMonthExpense = (float) IdeaAccountingEntry::where('type', 'expense')
            ->whereYear('entry_date', $thisMonth->year)
            ->whereMonth('entry_date', $thisMonth->month)
            ->sum('amount');
        $thisMonthNet = $thisMonthIncome - $thisMonthExpense;

        // Invoice stats overview
        $totalInvoiced = (float) IdeaInvoice::where('status', '!=', 'cancelled')->sum('grand_total');
        $totalInvoicePaid = (float) IdeaInvoice::where('status', '!=', 'cancelled')->sum('paid_amount');
        $totalInvoiceDue = (float) IdeaInvoice::where('status', '!=', 'cancelled')->sum('due_amount');

        $categories = IdeaAccountingEntry::categories();

        // Sector wise expense breakdown
        $expenseBreakdown = IdeaAccountingEntry::where('type', 'expense')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        // Sector wise income breakdown
        $incomeBreakdown = IdeaAccountingEntry::where('type', 'income')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->take(6)
            ->get();

        // 6-month historical trend for visual chart
        $trendMonths = [];
        $trendIncome = [];
        $trendExpense = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $trendMonths[] = $m->format('M Y');

            $inc = (float) IdeaAccountingEntry::where('type', 'income')
                ->whereYear('entry_date', $m->year)
                ->whereMonth('entry_date', $m->month)
                ->sum('amount');
            $exp = (float) IdeaAccountingEntry::where('type', 'expense')
                ->whereYear('entry_date', $m->year)
                ->whereMonth('entry_date', $m->month)
                ->sum('amount');

            $trendIncome[] = $inc;
            $trendExpense[] = $exp;
        }

        return view('admin.accounting.index', compact(
            'entries', 'totalIncome', 'totalExpense', 'netBalance',
            'todayIncome', 'todayExpense', 'thisMonthIncome', 'thisMonthExpense', 'thisMonthNet',
            'totalInvoiced', 'totalInvoicePaid', 'totalInvoiceDue',
            'categories', 'expenseBreakdown', 'incomeBreakdown',
            'trendMonths', 'trendIncome', 'trendExpense',
            'type', 'category', 'search', 'dateFrom', 'dateTo', 'paymentMethod'
        ));
    }

    /**
     * Store new income or expense transaction entry.
     */
    public function storeEntry(Request $request): RedirectResponse|JsonResponse
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
            : ($validated['category'] ?? ($validated['type'] === 'income' ? 'সাধারণ বিক্রয় ও আয়' : 'বিবিধ খরচ (Miscellaneous Expense)'));

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

        $entry = IdeaAccountingEntry::create([
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'entry'   => $entry,
            ]);
        }

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

            // Folder-specific Metrics
            'bills_amount'     => (float) IdeaInvoice::where('type', 'invoice')->sum('grand_total'),
            'bills_paid'       => (float) IdeaInvoice::where('type', 'invoice')->sum('paid_amount'),
            'bills_due'        => (float) IdeaInvoice::where('type', 'invoice')->sum('due_amount'),

            'challans_amount'      => (float) IdeaInvoice::where('type', 'challan')->sum('grand_total'),
            'challans_paid'        => (float) IdeaInvoice::where('type', 'challan')->sum('paid_amount'),
            'challans_due'         => (float) IdeaInvoice::where('type', 'challan')->sum('due_amount'),
            'challans_books_count' => IdeaInvoice::where('type', 'challan')->where(fn($q) => $q->where('sales_category', 'books')->orWhereNull('sales_category'))->count(),
            'challans_other_count' => IdeaInvoice::where('type', 'challan')->where('sales_category', '!=', 'books')->count(),

            'quotations_amount'  => (float) IdeaInvoice::where('type', 'quotation')->sum('grand_total'),
            'quotations_active'  => IdeaInvoice::where('type', 'quotation')->where(fn($q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()))->count(),
            'quotations_expired' => IdeaInvoice::where('type', 'quotation')->whereNotNull('valid_until')->whereDate('valid_until', '<', now())->count(),

            'tenders_amount' => (float) IdeaInvoice::where('type', 'tender')->sum('grand_total'),
            'tenders_active' => IdeaInvoice::where('type', 'tender')->where(fn($q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()))->count(),
            'tenders_orgs'   => IdeaInvoice::where('type', 'tender')->whereNotNull('customer_org')->where('customer_org', '!=', '')->distinct('customer_org')->count('customer_org'),
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
     * Live Customer Search for Auto-filling Document & Client Information.
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        $q = trim((string)$request->input('q', ''));
        if (mb_strlen($q) < 1) {
            return response()->json([]);
        }

        $results = collect();
        $seenKeys = [];

        // 1. Search in IdeaInvoice history (contains full client details: org, designation, address)
        $invoices = IdeaInvoice::query()
            ->where(function ($query) use ($q) {
                $query->where('customer_name', 'like', "%{$q}%")
                    ->orWhere('customer_phone', 'like', "%{$q}%")
                    ->orWhere('customer_org', 'like', "%{$q}%")
                    ->orWhere('customer_email', 'like', "%{$q}%");
            })
            ->latest('id')
            ->limit(40)
            ->get();

        foreach ($invoices as $inv) {
            $name = trim((string)$inv->customer_name);
            $phone = trim((string)$inv->customer_phone);
            if (empty($name) && empty($phone)) continue;

            $key = mb_strtolower(($phone ?: '') . '_' . ($name ?: ''));
            if (isset($seenKeys[$key])) continue;
            $seenKeys[$key] = true;

            $clientDue = (float)IdeaInvoice::whereIn('type', ['invoice', 'challan'])
                ->where('due_amount', '>', 0)
                ->where(function ($sub) use ($name, $phone) {
                    if (!empty($name) && !empty($phone)) {
                        $sub->where('customer_name', $name)->orWhere('customer_phone', $phone);
                    } elseif (!empty($name)) {
                        $sub->where('customer_name', $name);
                    } else {
                        $sub->where('customer_phone', $phone);
                    }
                })
                ->sum('due_amount');

            $results->push([
                'id'            => 'inv_' . $inv->id,
                'name'          => $inv->customer_name ?? '',
                'designation'   => $inv->customer_designation ?? '',
                'org'           => $inv->customer_org ?? '',
                'phone'         => $inv->customer_phone ?? '',
                'email'         => $inv->customer_email ?? '',
                'address'       => $inv->customer_address ?? '',
                'source'        => 'পূর্বের ইনভয়েস / চালান (Invoice History)',
                'source_type'   => 'invoice',
                'due_amount'    => $clientDue,
                'due_formatted' => number_format($clientDue, 2),
            ]);
        }

        // 2. Search in Registered Users (users table)
        $users = User::query()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->limit(25)
            ->get();

        foreach ($users as $user) {
            $name = trim((string)$user->name);
            $phone = trim((string)$user->phone);
            if (empty($name) && empty($phone)) continue;

            $key = mb_strtolower(($phone ?: '') . '_' . ($name ?: ''));
            if (isset($seenKeys[$key])) continue;
            $seenKeys[$key] = true;

            $regData = is_array($user->reg_data) ? $user->reg_data : [];
            $org = $regData['organization'] ?? $regData['institution'] ?? $regData['org_name'] ?? $regData['shop_name'] ?? $regData['company_name'] ?? '';
            $designation = $regData['designation'] ?? '';
            $address = $regData['address'] ?? ($user->address ?? '');

            $clientDue = (float)IdeaInvoice::whereIn('type', ['invoice', 'challan'])
                ->where('due_amount', '>', 0)
                ->where(function ($sub) use ($name, $phone) {
                    if (!empty($name) && !empty($phone)) {
                        $sub->where('customer_name', $name)->orWhere('customer_phone', $phone);
                    } elseif (!empty($name)) {
                        $sub->where('customer_name', $name);
                    } else {
                        $sub->where('customer_phone', $phone);
                    }
                })
                ->sum('due_amount');

            $roleLabel = match($user->role) {
                'seller'    => 'রেজিস্টার্ড সেলার (Registered Seller)',
                'publisher' => 'রেজিস্টার্ড প্রকাশক (Publisher)',
                'author'    => 'রেজিস্টার্ড লেখক (Author)',
                'buyer'     => 'রেজিস্টার্ড ক্রেতা (Buyer)',
                default     => 'রেজিস্টার্ড কাস্টমার (Registered User)',
            };

            $results->push([
                'id'            => 'user_' . $user->id,
                'name'          => $user->name ?? '',
                'designation'   => $designation,
                'org'           => $org,
                'phone'         => $user->phone ?? '',
                'email'         => $user->email ?? '',
                'address'       => $address,
                'source'        => $roleLabel,
                'source_type'   => 'user',
                'due_amount'    => $clientDue,
                'due_formatted' => number_format($clientDue, 2),
            ]);
        }

        // 3. Search in Seller Bills (Bill) if model exists
        if (class_exists(\App\Models\Bill::class)) {
            $bills = \App\Models\Bill::query()
                ->whereNotNull('customer_name')
                ->where(function ($query) use ($q) {
                    $query->where('customer_name', 'like', "%{$q}%")
                        ->orWhere('customer_phone', 'like', "%{$q}%");
                })
                ->latest('id')
                ->limit(20)
                ->get();

            foreach ($bills as $bill) {
                $name = trim((string)$bill->customer_name);
                $phone = trim((string)$bill->customer_phone);
                if (empty($name) && empty($phone)) continue;

                $key = mb_strtolower(($phone ?: '') . '_' . ($name ?: ''));
                if (isset($seenKeys[$key])) continue;
                $seenKeys[$key] = true;

                $results->push([
                    'id'            => 'bill_' . $bill->id,
                    'name'          => $bill->customer_name ?? '',
                    'designation'   => '',
                    'org'           => '',
                    'phone'         => $bill->customer_phone ?? '',
                    'email'         => $bill->customer_email ?? '',
                    'address'       => $bill->customer_address ?? '',
                    'source'        => 'সেলার বিক্রয় বিল (POS/Bill)',
                    'source_type'   => 'bill',
                    'due_amount'    => 0,
                    'due_formatted' => '0.00',
                ]);
            }
        }

        return response()->json($results->take(20)->values());
    }

    /**
     * Get Customer Pending Dues & Invoices for Live Lookup in Invoice Creation.
     */
    public function getCustomerDueInfo(Request $request): JsonResponse
    {
        $name = trim((string)$request->input('name', ''));
        $phone = trim((string)$request->input('phone', ''));
        $excludeId = $request->input('exclude_id');

        if (empty($name) && empty($phone)) {
            return response()->json([
                'found' => false,
                'total_due' => 0,
                'total_due_formatted' => '0.00',
                'invoices_count' => 0,
                'invoices' => []
            ]);
        }

        $query = IdeaInvoice::whereIn('type', ['invoice', 'challan'])
            ->where('due_amount', '>', 0)
            ->where(function ($q) use ($name, $phone) {
                if (!empty($name) && !empty($phone)) {
                    $q->where('customer_name', $name)->orWhere('customer_phone', $phone);
                } elseif (!empty($name)) {
                    $q->where('customer_name', $name);
                } else {
                    $q->where('customer_phone', $phone);
                }
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $dueInvoices = $query->orderBy('invoice_date', 'asc')->get();
        $totalDue = (float)$dueInvoices->sum('due_amount');

        return response()->json([
            'found'               => $dueInvoices->isNotEmpty(),
            'total_due'           => $totalDue,
            'total_due_formatted' => number_format($totalDue, 2),
            'invoices_count'      => $dueInvoices->count(),
            'invoices'            => $dueInvoices->map(fn($inv) => [
                'id'                   => $inv->id,
                'invoice_no'           => $inv->invoice_no,
                'date'                 => $inv->invoice_date ? $inv->invoice_date->format('d M, Y') : '—',
                'month_year'           => $inv->invoice_date ? $inv->invoice_date->format('F Y') : '—',
                'type'                 => $inv->type_label,
                'grand_total'          => (float)$inv->grand_total,
                'paid_amount'          => (float)$inv->paid_amount,
                'due_amount'           => (float)$inv->due_amount,
                'due_amount_formatted' => number_format($inv->due_amount, 2),
            ]),
        ]);
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
            'previous_due'         => 'nullable|numeric|min:0',
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
                $itemResult = IdeaInvoiceItemService::processItems($validated['items'], $salesCategory, $autoCreateBooks);
                $itemsProcessed = $itemResult['items'];
                $subtotal = $itemResult['subtotal'];

                $discount = (float) ($validated['discount'] ?? 0);
                $tax = (float) ($validated['tax'] ?? 0);
                $previousDue = (float) ($validated['previous_due'] ?? 0);
                $grandTotal = max(0, $subtotal - $discount + $tax + $previousDue);
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
                    'previous_due'         => $previousDue,
                    'grand_total'          => $grandTotal,
                    'paid_amount'          => $paid,
                    'due_amount'           => $due,
                    'payment_method'       => $validated['payment_method'],
                    'payment_status'       => $paymentStatus,
                    'notes'                => $validated['notes'] ?? null,
                    'terms_conditions'     => $validated['terms_conditions'] ?? null,
                    'created_by'           => $userId,
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
        IdeaInvoice::ensureColumnsExist();
        $invoice->load(['payments.recorder', 'creator']);
        $invoiceSettings = self::getInvoiceSettings();

        // Customer Dues Summary (গ্রাহকের অন্যান্য ও সর্বমোট বকেয়া বিলের হিসাব)
        $custName = trim((string)$invoice->customer_name);
        $custPhone = trim((string)$invoice->customer_phone);

        $otherDueInvoices = collect();
        $allDueInvoices = collect();
        $customerTotalDue = (float)$invoice->due_amount;
        $customerTotalBilled = (float)$invoice->grand_total;
        $customerTotalPaid = (float)$invoice->paid_amount;
        $customerDueCount = ($invoice->due_amount > 0) ? 1 : 0;

        if (!empty($custName) || !empty($custPhone)) {
            $allCustomerInvoices = IdeaInvoice::whereIn('type', ['invoice', 'challan'])
                ->where(function ($q) use ($custName, $custPhone) {
                    if (!empty($custName) && !empty($custPhone)) {
                        $q->where('customer_name', $custName)->orWhere('customer_phone', $custPhone);
                    } elseif (!empty($custName)) {
                        $q->where('customer_name', $custName);
                    } else {
                        $q->where('customer_phone', $custPhone);
                    }
                })
                ->orderBy('invoice_date', 'asc')
                ->get();

            $allDueInvoices = $allCustomerInvoices->where('due_amount', '>', 0)->values();
            $otherDueInvoices = $allCustomerInvoices->where('id', '!=', $invoice->id)->where('due_amount', '>', 0)->values();
            $customerTotalDue = (float)$allCustomerInvoices->sum('due_amount');
            $customerTotalBilled = (float)$allCustomerInvoices->sum('grand_total');
            $customerTotalPaid = (float)$allCustomerInvoices->sum('paid_amount');
            $customerDueCount = $allDueInvoices->count();
        }

        return view('admin.accounting.invoices.show', compact(
            'invoice', 'invoiceSettings', 'otherDueInvoices', 'allDueInvoices',
            'customerTotalDue', 'customerTotalBilled', 'customerTotalPaid', 'customerDueCount'
        ));
    }

    /**
     * Edit Bill / Challan / Quotation / Tender.
     */
    public function editInvoice(IdeaInvoice $invoice): View
    {
        IdeaInvoice::ensureColumnsExist();
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
            'previous_due'         => 'nullable|numeric|min:0',
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
                $salesCategory = $request->input('sales_category', $invoice->sales_category ?? 'books');
                $autoCreateBooks = $request->boolean('auto_create_books', true);

                $itemResult = IdeaInvoiceItemService::processItems($validated['items'], $salesCategory, $autoCreateBooks);
                $itemsProcessed = $itemResult['items'];
                $subtotal = $itemResult['subtotal'];

                $discount = (float) ($validated['discount'] ?? 0);
                $tax = (float) ($validated['tax'] ?? 0);
                $previousDue = (float) ($validated['previous_due'] ?? ($invoice->previous_due ?? 0));
                $grandTotal = max(0, $subtotal - $discount + $tax + $previousDue);
                
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

                $salesCategory = $validated['sales_category'] ?? $request->input('sales_category', $invoice->sales_category ?? 'books');

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
                    'previous_due'         => $previousDue,
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
                                        // Handle Banking Details
            $settings['bank_name'] = $request->input('bank_name') ?? ($settings['bank_name'] ?? '');
            $settings['bank_account_name'] = $request->input('bank_account_name') ?? ($settings['bank_account_name'] ?? '');
            $settings['bank_account_no'] = $request->input('bank_account_no') ?? ($settings['bank_account_no'] ?? '');
            $settings['bank_branch'] = $request->input('bank_branch') ?? ($settings['bank_branch'] ?? '');
            $settings['bank_routing_no'] = $request->input('bank_routing_no') ?? ($settings['bank_routing_no'] ?? '');

                        // Handle MFS QR (bKash / Nagad / Rocket)
            if ($request->has('mfs_qr_note')) {
                $settings['mfs_qr_note'] = $request->input('mfs_qr_note') ?: 'bKash / Nagad / Rocket';
            }
            if ($request->filled('remove_mfs_qr') && $request->input('remove_mfs_qr') == '1') {
                unset($settings['mfs_qr_image']);
            } elseif ($request->hasFile('mfs_qr_file')) {
                $file = $request->file('mfs_qr_file');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = 'mfs_qr_' . time() . '.' . $ext;
                $targetDir = public_path('images/settings');
                if (!is_dir($targetDir)) { @mkdir($targetDir, 0777, true); }
                $file->move($targetDir, $filename);
                $settings['mfs_qr_image'] = 'images/settings/' . $filename;
            }

            // Handle Bank QR (Bank Payment)
            if ($request->has('bank_qr_note')) {
                $settings['bank_qr_note'] = $request->input('bank_qr_note') ?: 'Bank Payment';
            }
            if ($request->filled('remove_bank_qr') && $request->input('remove_bank_qr') == '1') {
                unset($settings['bank_qr_image']);
            } elseif ($request->hasFile('bank_qr_file')) {
                $file = $request->file('bank_qr_file');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = 'bank_qr_' . time() . '.' . $ext;
                $targetDir = public_path('images/settings');
                if (!is_dir($targetDir)) { @mkdir($targetDir, 0777, true); }
                $file->move($targetDir, $filename);
                $settings['bank_qr_image'] = 'images/settings/' . $filename;
            }

            // Handle Payment QR (Bangla QR / bKash / Nagad / Bank)
            if ($request->has('payment_qr_note')) {
                $settings['payment_qr_note'] = $request->input('payment_qr_note') ?: 'বিকাশ / নগদ / রকেট';
            }

            if ($request->filled('remove_payment_qr') && $request->input('remove_payment_qr') == '1') {
                unset($settings['payment_qr_image']);
            } elseif ($request->hasFile('payment_qr_file')) {
                $file = $request->file('payment_qr_file');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = 'payment_qr_' . time() . '.' . $ext;

                $targetDir = public_path('images/settings');
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                $file->move($targetDir, $filename);
                $settings['payment_qr_image'] = 'images/settings/' . $filename;
            } elseif (!empty($request->input('payment_qr_base64')) && str_starts_with($request->input('payment_qr_base64'), 'data:image/')) {
                $base64 = $request->input('payment_qr_base64');
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $base64Data = substr($base64, strpos($base64, ',') + 1);
                    $decoded = base64_decode($base64Data);
                    if ($decoded !== false) {
                        $ext = strtolower($type[1] ?? 'png');
                        if ($ext === 'jpeg') $ext = 'jpg';
                        $filename = 'payment_qr_' . time() . '.' . $ext;

                        $targetDir = public_path('images/settings');
                        if (!is_dir($targetDir)) {
                            @mkdir($targetDir, 0777, true);
                        }
                        @file_put_contents($targetDir . '/' . $filename, $decoded);
                        $settings['payment_qr_image'] = 'images/settings/' . $filename;
                    }
                }
            } elseif (!empty($request->input('payment_qr_url'))) {
                $settings['payment_qr_image'] = $request->input('payment_qr_url');
            }

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
    /**
     * Record a new installment/step payment against an invoice.
     */
    public function storeInvoicePayment(Request $request, IdeaInvoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date'           => 'required|date',
            'amount'                 => 'required|numeric|min:0.01',
            'net_amount'             => 'nullable|numeric|min:0',
            'vat_deduction_rate'     => 'nullable|numeric|min:0|max:100',
            'vat_deduction_amount'   => 'nullable|numeric|min:0',
            'tax_deduction_rate'     => 'nullable|numeric|min:0|max:100',
            'tax_deduction_amount'   => 'nullable|numeric|min:0',
            'other_deduction_amount' => 'nullable|numeric|min:0',
            'deduction_challan_no'   => 'nullable|string|max:100',
            'deduction_notes'        => 'nullable|string|max:1000',
            'payment_method'         => 'required|string|max:50',
            'transaction_ref'        => 'nullable|string|max:100',
            'note'                   => 'nullable|string|max:500',
            'due_date'               => 'nullable|date', // Optional next payment deadline
        ], [
            'amount.required'       => 'জমার পরিমাণ (টাকা) প্রদান করুন।',
            'amount.min'            => 'জমার পরিমাণ কমপক্ষে ০.০১ টাকা হতে হবে।',
            'payment_date.required' => 'জমার তারিখ প্রদান করুন।',
        ]);

        $amount = (float) $validated['amount'];
        $vatRate = (float) ($validated['vat_deduction_rate'] ?? 0);
        $vatAmount = (float) ($validated['vat_deduction_amount'] ?? 0);
        $taxRate = (float) ($validated['tax_deduction_rate'] ?? 0);
        $taxAmount = (float) ($validated['tax_deduction_amount'] ?? 0);
        $otherAmount = (float) ($validated['other_deduction_amount'] ?? 0);
        $totalDeductions = $vatAmount + $taxAmount + $otherAmount;
        
        $netAmount = isset($validated['net_amount']) && (float)$validated['net_amount'] > 0
            ? (float)$validated['net_amount']
            : max(0, $amount - $totalDeductions);

        // If net_amount + deductions is explicitly calculated, ensure total settlement credit is synchronized
        if ($netAmount > 0 && $totalDeductions > 0 && abs(($netAmount + $totalDeductions) - $amount) > 0.01) {
            $amount = $netAmount + $totalDeductions;
        }

        $challanNo = $validated['deduction_challan_no'] ?? null;
        $deductionNotes = $validated['deduction_notes'] ?? null;
        $paymentDate = $validated['payment_date'];
        $paymentMethod = $validated['payment_method'];
        $trxRef = $validated['transaction_ref'] ?? null;
        $note = $validated['note'] ?? null;

        try {
            return DB::transaction(function () use ($invoice, $amount, $netAmount, $vatRate, $vatAmount, $taxRate, $taxAmount, $otherAmount, $challanNo, $deductionNotes, $totalDeductions, $paymentDate, $paymentMethod, $trxRef, $note, $request) {
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
                        'net_amount'      => (float)$invoice->paid_amount,
                        'payment_method'  => $invoice->payment_method ?: 'cash',
                        'transaction_ref' => null,
                        'note'            => 'অগ্রিম জমা (Advance Payment)',
                        'recorded_by'     => $invoice->created_by ?: auth()->id(),
                    ]);
                }

                $payment = IdeaInvoicePayment::create([
                    'invoice_id'             => $invoice->id,
                    'customer_name'          => $invoice->customer_name,
                    'customer_phone'         => $invoice->customer_phone,
                    'payment_no'             => $payNo,
                    'payment_date'           => $paymentDate,
                    'amount'                 => $amount,
                    'net_amount'             => $netAmount,
                    'vat_deduction_rate'     => $vatRate,
                    'vat_deduction_amount'   => $vatAmount,
                    'tax_deduction_rate'     => $taxRate,
                    'tax_deduction_amount'   => $taxAmount,
                    'other_deduction_amount' => $otherAmount,
                    'deduction_challan_no'   => $challanNo,
                    'deduction_notes'        => $deductionNotes,
                    'payment_method'         => $paymentMethod,
                    'transaction_ref'        => $trxRef,
                    'note'                   => $note,
                    'recorded_by'            => auth()->id(),
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

                $deductionSummaryTxt = '';
                if ($totalDeductions > 0.001) {
                    $parts = [];
                    if ($netAmount > 0) $parts[] = "নিট প্রাপ্তি: ৳" . number_format($netAmount, 2);
                    if ($vatAmount > 0) $parts[] = "মূসক কর্তন" . ($vatRate > 0 ? " ({$vatRate}%)" : "") . ": ৳" . number_format($vatAmount, 2);
                    if ($taxAmount > 0) $parts[] = "উৎসে কর" . ($taxRate > 0 ? " ({$taxRate}%)" : "") . ": ৳" . number_format($taxAmount, 2);
                    if ($otherAmount > 0) $parts[] = "অন্যান্য কর্তন: ৳" . number_format($otherAmount, 2);
                    $deductionSummaryTxt = " [" . implode(', ', $parts) . ($challanNo ? " | চালান/সনদ: {$challanNo}" : "") . "]";
                }

                IdeaAccountingEntry::create([
                    'entry_no'       => 'INC-' . date('Ymd') . '-' . rand(1000, 9999),
                    'type'           => 'income',
                    'category'       => $incomeCategory,
                    'title'          => "বিল #{$invoice->invoice_no} হতে কিস্তি/বকেয়া সমন্বয় — {$invoice->customer_name}",
                    'amount'         => $amount,
                    'entry_date'     => $paymentDate,
                    'payment_method' => $paymentMethod,
                    'party_name'     => $invoice->customer_name,
                    'voucher_no'     => $payNo,
                    'invoice_id'     => $invoice->id,
                    'notes'          => ($note ? "{$note} — " : "") . "বিল #{$invoice->invoice_no} এর কিস্তি/জমা রসিদ #{$payNo}{$deductionSummaryTxt}",
                    'created_by'     => $userId,
                ]);

                $msg = "বিল #{$invoice->invoice_no}-এর বিপরীতে ৳" . number_format($amount, 2) . " টাকা জমা/সমন্বয় রেকর্ড করা হয়েছে (রসিদ #{$payNo})।";
                if ($totalDeductions > 0.001) {
                    $msg .= " (নিট প্রাপ্তি: ৳" . number_format($netAmount, 2) . ", ভ্যাট/ট্যাক্স কর্তন: ৳" . number_format($totalDeductions, 2) . ")";
                }

                return back()->with('success', $msg);
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'কিস্তি জমা রেকর্ডে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing installment payment record and adjust invoice + accounting.
     */
    public function updateInvoicePayment(Request $request, IdeaInvoicePayment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date'           => 'required|date',
            'amount'                 => 'required|numeric|min:0.01',
            'net_amount'             => 'nullable|numeric|min:0',
            'vat_deduction_rate'     => 'nullable|numeric|min:0|max:100',
            'vat_deduction_amount'   => 'nullable|numeric|min:0',
            'tax_deduction_rate'     => 'nullable|numeric|min:0|max:100',
            'tax_deduction_amount'   => 'nullable|numeric|min:0',
            'other_deduction_amount' => 'nullable|numeric|min:0',
            'deduction_challan_no'   => 'nullable|string|max:100',
            'deduction_notes'        => 'nullable|string|max:1000',
            'payment_method'         => 'required|string|max:50',
            'transaction_ref'        => 'nullable|string|max:100',
            'note'                   => 'nullable|string|max:500',
            'due_date'               => 'nullable|date',
        ], [
            'amount.required'       => 'মোট জমার পরিমাণ প্রদান করুন।',
            'amount.min'            => 'জমার পরিমাণ কমপক্ষে ০.০১ টাকা হতে হবে।',
            'payment_date.required' => 'জমার তারিখ প্রদান করুন।',
        ]);

        $amount = (float) $validated['amount'];
        $vatRate = isset($validated['vat_deduction_rate']) && $validated['vat_deduction_rate'] !== '' ? (float) $validated['vat_deduction_rate'] : null;
        $vatAmount = (float) ($validated['vat_deduction_amount'] ?? 0);
        $taxRate = isset($validated['tax_deduction_rate']) && $validated['tax_deduction_rate'] !== '' ? (float) $validated['tax_deduction_rate'] : null;
        $taxAmount = (float) ($validated['tax_deduction_amount'] ?? 0);
        $otherAmount = (float) ($validated['other_deduction_amount'] ?? 0);
        $totalDeductions = $vatAmount + $taxAmount + $otherAmount;

        $netAmount = isset($validated['net_amount']) && (float)$validated['net_amount'] > 0
            ? (float)$validated['net_amount']
            : max(0, $amount - $totalDeductions);

        // If net_amount + totalDeductions is calculated, ensure gross settlement amount is synchronized
        if ($netAmount > 0 && $totalDeductions > 0 && abs(($netAmount + $totalDeductions) - $amount) > 0.01) {
            $amount = $netAmount + $totalDeductions;
        }

        $challanNo = $validated['deduction_challan_no'] ?? null;
        $deductionNotes = $validated['deduction_notes'] ?? null;
        $paymentDate = $validated['payment_date'];
        $paymentMethod = $validated['payment_method'];
        $trxRef = $validated['transaction_ref'] ?? null;
        $note = $validated['note'] ?? null;

        try {
            return DB::transaction(function () use ($payment, $amount, $netAmount, $vatRate, $vatAmount, $taxRate, $taxAmount, $otherAmount, $challanNo, $deductionNotes, $totalDeductions, $paymentDate, $paymentMethod, $trxRef, $note, $request) {
                $invoice = $payment->invoice;
                $payNo = $payment->payment_no;

                $payment->update([
                    'payment_date'           => $paymentDate,
                    'amount'                 => $amount,
                    'net_amount'             => $netAmount,
                    'vat_deduction_rate'     => $vatRate,
                    'vat_deduction_amount'   => $vatAmount,
                    'tax_deduction_rate'     => $taxRate,
                    'tax_deduction_amount'   => $taxAmount,
                    'other_deduction_amount' => $otherAmount,
                    'deduction_challan_no'   => $challanNo,
                    'deduction_notes'        => $deductionNotes,
                    'payment_method'         => $paymentMethod,
                    'transaction_ref'        => $trxRef,
                    'note'                   => $note,
                ]);

                if ($invoice) {
                    if ($request->has('due_date')) {
                        $invoice->due_date = $request->filled('due_date') ? $request->input('due_date') : null;
                    }
                    $invoice->recalculatePayments();
                }

                // Update linked accounting entry
                if (!empty($payNo)) {
                    $deductionSummaryTxt = '';
                    if ($totalDeductions > 0.001) {
                        $parts = [];
                        if ($netAmount > 0) $parts[] = "নিট প্রাপ্তি: ৳" . number_format($netAmount, 2);
                        if ($vatAmount > 0) $parts[] = "মূসক কর্তন" . ($vatRate > 0 ? " ({$vatRate}%)" : "") . ": ৳" . number_format($vatAmount, 2);
                        if ($taxAmount > 0) $parts[] = "উৎসে কর" . ($taxRate > 0 ? " ({$taxRate}%)" : "") . ": ৳" . number_format($taxAmount, 2);
                        if ($otherAmount > 0) $parts[] = "অন্যান্য কর্তন: ৳" . number_format($otherAmount, 2);
                        $deductionSummaryTxt = " [" . implode(', ', $parts) . ($challanNo ? " | চালান/সনদ: {$challanNo}" : "") . "]";
                    }

                    $invoiceNo = $invoice?->invoice_no ?? '—';

                    IdeaAccountingEntry::where('voucher_no', $payNo)->update([
                        'amount'         => $amount,
                        'entry_date'     => $paymentDate,
                        'payment_method' => $paymentMethod,
                        'notes'          => ($note ? "{$note} — " : "") . "বিল #{$invoiceNo} এর কিস্তি/জমা রসিদ #{$payNo}{$deductionSummaryTxt}",
                    ]);
                }

                $msg = "জমা রসিদ #{$payNo} (৳" . number_format($amount, 2) . ") সফলভাবে আপডেট করা হয়েছে।";
                return back()->with('success', $msg);
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'পেমেন্ট আপডেট করতে সমস্যা হয়েছে: ' . $e->getMessage());
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
            'customer_name'          => 'required|string|max:255',
            'customer_phone'         => 'nullable|string|max:50',
            'invoice_id'             => 'nullable|integer|exists:idea_invoices,id',
            'payment_date'           => 'required|date',
            'amount'                 => 'required|numeric|min:0.01',
            'net_amount'             => 'nullable|numeric|min:0',
            'vat_deduction_rate'     => 'nullable|numeric|min:0|max:100',
            'vat_deduction_amount'   => 'nullable|numeric|min:0',
            'tax_deduction_rate'     => 'nullable|numeric|min:0|max:100',
            'tax_deduction_amount'   => 'nullable|numeric|min:0',
            'other_deduction_amount' => 'nullable|numeric|min:0',
            'deduction_challan_no'   => 'nullable|string|max:100',
            'deduction_notes'        => 'nullable|string|max:1000',
            'payment_method'         => 'required|string|max:50',
            'transaction_ref'        => 'nullable|string|max:100',
            'note'                   => 'nullable|string|max:500',
            'due_date'               => 'nullable|date', // Optional next payment deadline
        ], [
            'customer_name.required' => 'গ্রাহকের নাম দিন।',
            'amount.required'        => 'জমার পরিমাণ টাকা দিন।',
            'amount.min'             => 'জমার পরিমাণ কমপক্ষে ০.০১ টাকা হতে হবে।',
        ]);

        $amount = (float) $validated['amount'];
        $vatRate = (float) ($validated['vat_deduction_rate'] ?? 0);
        $vatAmount = (float) ($validated['vat_deduction_amount'] ?? 0);
        $taxRate = (float) ($validated['tax_deduction_rate'] ?? 0);
        $taxAmount = (float) ($validated['tax_deduction_amount'] ?? 0);
        $otherAmount = (float) ($validated['other_deduction_amount'] ?? 0);
        $totalDeductions = $vatAmount + $taxAmount + $otherAmount;
        
        $netAmount = isset($validated['net_amount']) && (float)$validated['net_amount'] > 0
            ? (float)$validated['net_amount']
            : max(0, $amount - $totalDeductions);

        if ($netAmount > 0 && $totalDeductions > 0 && abs(($netAmount + $totalDeductions) - $amount) > 0.01) {
            $amount = $netAmount + $totalDeductions;
        }

        $challanNo = $validated['deduction_challan_no'] ?? null;
        $deductionNotes = $validated['deduction_notes'] ?? null;
        $paymentDate = $validated['payment_date'];
        $paymentMethod = $validated['payment_method'];
        $trxRef = $validated['transaction_ref'] ?? null;
        $note = $validated['note'] ?? null;
        $customerName = trim($validated['customer_name']);
        $customerPhone = trim((string)($validated['customer_phone'] ?? ''));
        $specificInvoiceId = !empty($validated['invoice_id']) ? (int)$validated['invoice_id'] : null;

        try {
            return DB::transaction(function () use ($specificInvoiceId, $customerName, $customerPhone, $amount, $netAmount, $vatRate, $vatAmount, $taxRate, $taxAmount, $otherAmount, $challanNo, $deductionNotes, $totalDeductions, $paymentDate, $paymentMethod, $trxRef, $note, $request) {
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
                            'net_amount'      => (float)$invoice->paid_amount,
                            'payment_method'  => $invoice->payment_method ?: 'cash',
                            'note'            => 'অগ্রিম জমা (Advance Payment)',
                            'recorded_by'     => $invoice->created_by ?: $userId,
                        ]);
                    }

                    IdeaInvoicePayment::create([
                        'invoice_id'             => $invoice->id,
                        'customer_name'          => $invoice->customer_name,
                        'customer_phone'         => $invoice->customer_phone,
                        'payment_no'             => $payNo,
                        'payment_date'           => $paymentDate,
                        'amount'                 => $amount,
                        'net_amount'             => $netAmount,
                        'vat_deduction_rate'     => $vatRate,
                        'vat_deduction_amount'   => $vatAmount,
                        'tax_deduction_rate'     => $taxRate,
                        'tax_deduction_amount'   => $taxAmount,
                        'other_deduction_amount' => $otherAmount,
                        'deduction_challan_no'   => $challanNo,
                        'deduction_notes'        => $deductionNotes,
                        'payment_method'         => $paymentMethod,
                        'transaction_ref'        => $trxRef,
                        'note'                   => $note,
                        'recorded_by'            => $userId,
                    ]);

                    if ($request->has('due_date')) {
                        $invoice->due_date = $request->filled('due_date') ? $request->input('due_date') : null;
                    }

                    $invoice->recalculatePayments();

                    // Income entry
                    $incomeCat = $invoice->type === 'challan' ? 'পাইকারি বিক্রয় ও চালান (Wholesale Sales)' : 'বই বিক্রয় (Book Sales)';
                    
                    $deductionSummaryTxt = '';
                    if ($totalDeductions > 0.001) {
                        $parts = [];
                        if ($netAmount > 0) $parts[] = "নিট প্রাপ্তি: ৳" . number_format($netAmount, 2);
                        if ($vatAmount > 0) $parts[] = "মূসক কর্তন" . ($vatRate > 0 ? " ({$vatRate}%)" : "") . ": ৳" . number_format($vatAmount, 2);
                        if ($taxAmount > 0) $parts[] = "উৎসে কর" . ($taxRate > 0 ? " ({$taxRate}%)" : "") . ": ৳" . number_format($taxAmount, 2);
                        if ($otherAmount > 0) $parts[] = "অন্যান্য কর্তন: ৳" . number_format($otherAmount, 2);
                        $deductionSummaryTxt = " [" . implode(', ', $parts) . ($challanNo ? " | চালান/সনদ: {$challanNo}" : "") . "]";
                    }

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
                        'notes'          => ($note ? "{$note} — " : "") . "বিল #{$invoice->invoice_no} এর কিস্তি/জমা রসিদ #{$payNo}{$deductionSummaryTxt}",
                        'created_by'     => $userId,
                    ]);

                    $msg = "বিল #{$invoice->invoice_no} ({$customerName})-এর বিপরীতে ৳" . number_format($amount, 2) . " টাকা সফলভাবে জমা/সমন্বয় রেকর্ড করা হয়েছে (রসিদ #{$payNo})।";
                    if ($totalDeductions > 0.001) {
                        $msg .= " (নিট প্রাপ্তি: ৳" . number_format($netAmount, 2) . ", ভ্যাট/ট্যাক্স কর্তন: ৳" . number_format($totalDeductions, 2) . ")";
                    }

                    return back()->with('success', $msg);
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

                    // Calculate proportional deductions for this allocated slice if applicable
                    $ratio = $amount > 0 ? ($allocatedAmount / $amount) : 1;
                    $allocatedNet = $netAmount * $ratio;
                    $allocatedVat = $vatAmount * $ratio;
                    $allocatedTax = $taxAmount * $ratio;
                    $allocatedOther = $otherAmount * $ratio;

                    // If previous payments do not exist but invoice had existing paid_amount > 0, backfill
                    if ($inv->payments()->count() === 0 && (float)$inv->paid_amount > 0) {
                        IdeaInvoicePayment::create([
                            'invoice_id'      => $inv->id,
                            'customer_name'   => $inv->customer_name,
                            'customer_phone'  => $inv->customer_phone,
                            'payment_no'      => IdeaInvoicePayment::generatePaymentNo(),
                            'payment_date'    => $inv->invoice_date,
                            'amount'          => (float)$inv->paid_amount,
                            'net_amount'      => (float)$inv->paid_amount,
                            'payment_method'  => $inv->payment_method ?: 'cash',
                            'note'            => 'অগ্রিম জমা (Advance Payment)',
                            'recorded_by'     => $inv->created_by ?: $userId,
                        ]);
                    }

                    IdeaInvoicePayment::create([
                        'invoice_id'             => $inv->id,
                        'customer_name'          => $inv->customer_name,
                        'customer_phone'         => $inv->customer_phone,
                        'payment_no'             => $payNo,
                        'payment_date'           => $paymentDate,
                        'amount'                 => $allocatedAmount,
                        'net_amount'             => $allocatedNet,
                        'vat_deduction_rate'     => $vatRate,
                        'vat_deduction_amount'   => $allocatedVat,
                        'tax_deduction_rate'     => $taxRate,
                        'tax_deduction_amount'   => $allocatedTax,
                        'other_deduction_amount' => $allocatedOther,
                        'deduction_challan_no'   => $challanNo,
                        'deduction_notes'        => $deductionNotes,
                        'payment_method'         => $paymentMethod,
                        'transaction_ref'        => $trxRef,
                        'note'                   => $note ? "{$note} [খতিয়ান হতে সমন্বয়]" : "গ্রাহক চলতি খাতা থেকে বিল #{$inv->invoice_no} সমন্বয়",
                        'recorded_by'            => $userId,
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
                        'notes'          => "গ্রাহক চলতি খাতা থেকে বিল #{$inv->invoice_no} এর কিস্তি/জমা রসিদ #{$payNo}" . ($totalDeductions > 0.001 ? " [ভ্যাট-ট্যাক্স সমন্বিত]" : ""),
                        'created_by'     => $userId,
                    ]);

                    $settledSummary[] = "#{$inv->invoice_no} (৳" . number_format($allocatedAmount, 2) . ")";
                    $remainingPayment -= $allocatedAmount;
                }

                // If extra money remains or customer had no due invoices, record unallocated on-account payment
                if ($remainingPayment > 0.001 || empty($settledSummary)) {
                    $payNo = IdeaInvoicePayment::generatePaymentNo();
                    $unallocatedAmount = $remainingPayment > 0.001 ? $remainingPayment : $amount;
                    $ratio = $amount > 0 ? ($unallocatedAmount / $amount) : 1;

                    IdeaInvoicePayment::create([
                        'invoice_id'             => null,
                        'customer_name'          => $customerName,
                        'customer_phone'         => $customerPhone,
                        'payment_no'             => $payNo,
                        'payment_date'           => $paymentDate,
                        'amount'                 => $unallocatedAmount,
                        'net_amount'             => $netAmount * $ratio,
                        'vat_deduction_rate'     => $vatRate,
                        'vat_deduction_amount'   => $vatAmount * $ratio,
                        'tax_deduction_rate'     => $taxRate,
                        'tax_deduction_amount'   => $taxAmount * $ratio,
                        'other_deduction_amount' => $otherAmount * $ratio,
                        'deduction_challan_no'   => $challanNo,
                        'deduction_notes'        => $deductionNotes,
                        'payment_method'         => $paymentMethod,
                        'transaction_ref'        => $trxRef,
                        'note'                   => $note ? "{$note} [চলতি খাতা অগ্রিম/অতিরিক্ত জমা]" : "চলতি খাতা অগ্রিম জমা / কিস্তি",
                        'recorded_by'            => $userId,
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
    public function updateSettings(Request $request): RedirectResponse|JsonResponse
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
            'qr_code_size'                   => 'nullable|string|max:10',
            'default_creator_designation'    => 'nullable|string|max:150',
            'default_creator_name'           => 'nullable|string|max:150',
            'whatsapp_message_template'      => 'nullable|string|max:1000',
            'email_greeting_salutation'      => 'nullable|string|max:150',
            'email_intro_text'               => 'nullable|string|max:500',
            'quotation_default_validity_days'=> 'nullable|integer|min:1|max:365',
            'quotation_default_subject'      => 'nullable|string|max:255',
            'quotation_default_notes'        => 'nullable|string|max:1000',
            'quotation_default_terms'        => 'nullable|string|max:2000',
            'quotation_title_bn'             => 'nullable|string|max:150',
            'tender_title_bn'                => 'nullable|string|max:150',
            'invoice_title_bn'               => 'nullable|string|max:150',
            'challan_title_bn'               => 'nullable|string|max:150',
            'digit_language'                 => 'nullable|string|in:bn,en',
            'default_vat_rate'               => 'nullable|numeric|min:0|max:100',
            'default_tax_rate'               => 'nullable|numeric|min:0|max:100',
            'vat_presets'                    => 'nullable|string|max:100',
            'tax_presets'                    => 'nullable|string|max:100',
            'logo_base64'                    => 'nullable|string',
            'logo_file'                      => 'nullable|image|max:5120',
            'logo_url'                       => 'nullable|string|max:255',
            'payment_qr_file'                => 'nullable|image|max:5120',
            'mfs_qr_file'                    => 'nullable|image|max:5120',
            'bank_qr_file'                   => 'nullable|image|max:5120',
            'mfs_qr_note'                    => 'nullable|string|max:100',
            'bank_qr_note'                   => 'nullable|string|max:100',
            'remove_mfs_qr'                  => 'nullable',
            'remove_bank_qr'                 => 'nullable',
            'payment_qr_base64'              => 'nullable|string',
            'payment_qr_url'                 => 'nullable|string|max:255',
            'payment_qr_note'                => 'nullable|string|max:100',
            'bank_name'                      => 'nullable|string|max:150',
            'bank_account_name'              => 'nullable|string|max:150',
            'bank_account_no'                => 'nullable|string|max:100',
            'bank_branch'                    => 'nullable|string|max:150',
            'bank_routing_no'                => 'nullable|string|max:50',
            'receipt_primary_color'          => 'nullable|string|max:50',
            'receipt_stamp_color'            => 'nullable|string|max:50',
            'receipt_logo_height'            => 'nullable|string|max:20',
            'receipt_logo_width'             => 'nullable|string|max:20',
            'receipt_stamp_size'             => 'nullable|string|max:20',
            'receipt_stamp_rotation'         => 'nullable|string|max:20',
            'receipt_stamp_top'              => 'nullable|string|max:20',
            'receipt_stamp_right'            => 'nullable|string|max:20',
            'receipt_font_scale'             => 'nullable|string|max:20',
            'remove_payment_qr'              => 'nullable',
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
            $settings['qr_code_size'] = $validated['qr_code_size'] ?? '60px';
            $settings['default_creator_designation'] = $validated['default_creator_designation'] ?? '';
            $settings['default_creator_name'] = $validated['default_creator_name'] ?? '';
            $settings['whatsapp_message_template'] = $validated['whatsapp_message_template'] ?? '';
            $settings['email_greeting_salutation'] = $validated['email_greeting_salutation'] ?? 'সম্মানিত গ্রাহক';
            $settings['email_intro_text'] = $validated['email_intro_text'] ?? '';
            $settings['quotation_default_validity_days'] = $validated['quotation_default_validity_days'] ?? '30';
            $settings['quotation_default_subject'] = $validated['quotation_default_subject'] ?? '';
            $settings['quotation_default_notes'] = $validated['quotation_default_notes'] ?? '';
            $settings['quotation_default_terms'] = $validated['quotation_default_terms'] ?? '';
            $settings['quotation_title_bn'] = $validated['quotation_title_bn'] ?? 'মূল্য কোটেশন (PRICE QUOTATION)';
            $settings['tender_title_bn'] = $validated['tender_title_bn'] ?? 'দরপত্র প্রস্তাবনা (TENDER PROPOSAL)';
            $settings['invoice_title_bn'] = $validated['invoice_title_bn'] ?? 'ক্যাশ মেমো / বিল (INVOICE / BILL)';
            $settings['challan_title_bn'] = $validated['challan_title_bn'] ?? 'ডেলিভারি চালান (DELIVERY CHALLAN)';
            $settings['digit_language'] = $validated['digit_language'] ?? 'bn';
            $settings['default_vat_rate'] = $validated['default_vat_rate'] ?? '7.5';
            $settings['default_tax_rate'] = $validated['default_tax_rate'] ?? '5.0';
            $settings['vat_presets'] = $validated['vat_presets'] ?? '0, 5, 7.5, 10, 15';
            $settings['tax_presets'] = $validated['tax_presets'] ?? '0, 2, 3, 5, 7, 10';
            $settings['receipt_primary_color'] = $validated['receipt_primary_color'] ?? ($settings['receipt_primary_color'] ?? '#059669');
            $settings['receipt_stamp_color'] = $validated['receipt_stamp_color'] ?? ($settings['receipt_stamp_color'] ?? '#6b21a8');
            $settings['receipt_logo_height'] = $validated['receipt_logo_height'] ?? ($settings['receipt_logo_height'] ?? '58px');
            $settings['receipt_logo_width'] = $validated['receipt_logo_width'] ?? ($settings['receipt_logo_width'] ?? '155px');
            $settings['receipt_stamp_size'] = $validated['receipt_stamp_size'] ?? ($settings['receipt_stamp_size'] ?? '135px');
            $settings['receipt_stamp_rotation'] = $validated['receipt_stamp_rotation'] ?? ($settings['receipt_stamp_rotation'] ?? '-10');
            $settings['receipt_stamp_top'] = $validated['receipt_stamp_top'] ?? ($settings['receipt_stamp_top'] ?? '-30px');
            $settings['receipt_stamp_right'] = $validated['receipt_stamp_right'] ?? ($settings['receipt_stamp_right'] ?? '25px');
            $settings['receipt_font_scale'] = $validated['receipt_font_scale'] ?? ($settings['receipt_font_scale'] ?? '1');

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

                                    // Handle Banking Details
            $settings['bank_name'] = $request->input('bank_name') ?? ($settings['bank_name'] ?? '');
            $settings['bank_account_name'] = $request->input('bank_account_name') ?? ($settings['bank_account_name'] ?? '');
            $settings['bank_account_no'] = $request->input('bank_account_no') ?? ($settings['bank_account_no'] ?? '');
            $settings['bank_branch'] = $request->input('bank_branch') ?? ($settings['bank_branch'] ?? '');
            $settings['bank_routing_no'] = $request->input('bank_routing_no') ?? ($settings['bank_routing_no'] ?? '');

                        // Handle MFS QR (bKash / Nagad / Rocket)
            if ($request->has('mfs_qr_note')) {
                $settings['mfs_qr_note'] = $request->input('mfs_qr_note') ?: 'bKash / Nagad / Rocket';
            }
            if ($request->filled('remove_mfs_qr') && $request->input('remove_mfs_qr') == '1') {
                unset($settings['mfs_qr_image']);
            } elseif ($request->hasFile('mfs_qr_file')) {
                $file = $request->file('mfs_qr_file');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = 'mfs_qr_' . time() . '.' . $ext;
                $targetDir = public_path('images/settings');
                if (!is_dir($targetDir)) { @mkdir($targetDir, 0777, true); }
                $file->move($targetDir, $filename);
                $settings['mfs_qr_image'] = 'images/settings/' . $filename;
            }

            // Handle Bank QR (Bank Payment)
            if ($request->has('bank_qr_note')) {
                $settings['bank_qr_note'] = $request->input('bank_qr_note') ?: 'Bank Payment';
            }
            if ($request->filled('remove_bank_qr') && $request->input('remove_bank_qr') == '1') {
                unset($settings['bank_qr_image']);
            } elseif ($request->hasFile('bank_qr_file')) {
                $file = $request->file('bank_qr_file');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = 'bank_qr_' . time() . '.' . $ext;
                $targetDir = public_path('images/settings');
                if (!is_dir($targetDir)) { @mkdir($targetDir, 0777, true); }
                $file->move($targetDir, $filename);
                $settings['bank_qr_image'] = 'images/settings/' . $filename;
            }

            // Handle Payment QR (Bangla QR / bKash / Nagad / Bank)
            if ($request->has('payment_qr_note')) {
                $settings['payment_qr_note'] = $request->input('payment_qr_note') ?: 'বিকাশ / নগদ / রকেট';
            }

            if ($request->filled('remove_payment_qr') && $request->input('remove_payment_qr') == '1') {
                unset($settings['payment_qr_image']);
            } elseif ($request->hasFile('payment_qr_file')) {
                $file = $request->file('payment_qr_file');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = 'payment_qr_' . time() . '.' . $ext;

                $targetDir = public_path('images/settings');
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                $file->move($targetDir, $filename);
                $settings['payment_qr_image'] = 'images/settings/' . $filename;
            } elseif (!empty($request->input('payment_qr_base64')) && str_starts_with($request->input('payment_qr_base64'), 'data:image/')) {
                $base64 = $request->input('payment_qr_base64');
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $base64Data = substr($base64, strpos($base64, ',') + 1);
                    $decoded = base64_decode($base64Data);
                    if ($decoded !== false) {
                        $ext = strtolower($type[1] ?? 'png');
                        if ($ext === 'jpeg') $ext = 'jpg';
                        $filename = 'payment_qr_' . time() . '.' . $ext;

                        $targetDir = public_path('images/settings');
                        if (!is_dir($targetDir)) {
                            @mkdir($targetDir, 0777, true);
                        }
                        @file_put_contents($targetDir . '/' . $filename, $decoded);
                        $settings['payment_qr_image'] = 'images/settings/' . $filename;
                    }
                }
            } elseif (!empty($request->input('payment_qr_url'))) {
                $settings['payment_qr_image'] = $request->input('payment_qr_url');
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

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'কাস্টমার বার্তা ও সেটিংস সফলভাবে সংরক্ষিত ও কার্যকর করা হয়েছে!',
                    'settings' => $settings,
                ]);
            }

            return back()->with('success', 'বিল ও চালানের ডিজাইন, ফন্ট সাইজ এবং অফিশিয়াল তথ্য সফলভাবে আপডেট করা হয়েছে।');
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'তথ্য সংরক্ষণে সমস্যা হয়েছে: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'তথ্য সংরক্ষণে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Tax & VAT (TDS & VDS) Deduction Register & Monthly Statement (উৎসে কর ও মূসক কর্তন রেজিস্টার).
     */
    public function taxVatDeductions(Request $request): View
    {
        $selectedMonth = $request->input('month'); // e.g. 2026-09
        $selectedYear = $request->input('year');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $customer = $request->string('customer')->trim()->value();
        $search = $request->string('search')->trim()->value();
        $deductionType = $request->input('type'); // 'all', 'vat_only', 'tax_only'

        // If no date/month filter is requested, default to current month
        if (!$request->has('month') && !$request->has('date_from') && !$request->has('year') && !$request->has('all_time')) {
            $selectedMonth = date('Y-m');
        }

        $query = IdeaInvoicePayment::query()
            ->with(['invoice', 'recorder'])
            ->where(function ($q) {
                $q->where('vat_deduction_amount', '>', 0)
                  ->orWhere('tax_deduction_amount', '>', 0)
                  ->orWhere('other_deduction_amount', '>', 0);
            });

        if (!empty($selectedMonth)) {
            try {
                $dt = Carbon::parse($selectedMonth . '-01');
                $query->whereYear('payment_date', $dt->year)
                      ->whereMonth('payment_date', $dt->month);
            } catch (\Throwable $e) {}
        } elseif (!empty($selectedYear)) {
            $query->whereYear('payment_date', (int)$selectedYear);
        } elseif (!empty($dateFrom) || !empty($dateTo)) {
            if (!empty($dateFrom)) $query->whereDate('payment_date', '>=', $dateFrom);
            if (!empty($dateTo)) $query->whereDate('payment_date', '<=', $dateTo);
        }

        if (!empty($customer)) {
            $query->where(function ($q) use ($customer) {
                $q->where('customer_name', 'like', "%{$customer}%")
                  ->orWhere('customer_phone', 'like', "%{$customer}%");
            });
        }

        if (!empty($search)) {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('payment_no', 'like', $like)
                  ->orWhere('customer_name', 'like', $like)
                  ->orWhere('customer_phone', 'like', $like)
                  ->orWhere('deduction_challan_no', 'like', $like)
                  ->orWhere('transaction_ref', 'like', $like)
                  ->orWhere('note', 'like', $like)
                  ->orWhereHas('invoice', function ($w) use ($like) {
                      $w->where('invoice_no', 'like', $like)
                        ->orWhere('customer_org', 'like', $like);
                  });
            });
        }

        if ($deductionType === 'vat_only') {
            $query->where('vat_deduction_amount', '>', 0);
        } elseif ($deductionType === 'tax_only') {
            $query->where('tax_deduction_amount', '>', 0);
        }

        $deductions = $query->orderBy('payment_date', 'desc')->orderBy('id', 'desc')->get();

        // Metric Aggregations
        $totalSettledAmount = (float) $deductions->sum('amount');
        $totalNetCollected = (float) $deductions->sum(fn($p) => $p->effective_net_amount);
        $totalVatDeducted = (float) $deductions->sum('vat_deduction_amount');
        $totalTaxDeducted = (float) $deductions->sum('tax_deduction_amount');
        $totalOtherDeducted = (float) $deductions->sum('other_deduction_amount');
        $grandTotalDeductions = $totalVatDeducted + $totalTaxDeducted + $totalOtherDeducted;
        $totalClientsCount = $deductions->pluck('party_name')->unique()->count();

        // Month-by-month history (Last 12 distinct months with deduction activities)
        $monthlyDeductionSummaries = IdeaInvoicePayment::where(function ($q) {
                $q->where('vat_deduction_amount', '>', 0)
                  ->orWhere('tax_deduction_amount', '>', 0)
                  ->orWhere('other_deduction_amount', '>', 0);
            })
            ->select(
                DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as ym"),
                DB::raw("COUNT(*) as count"),
                DB::raw("SUM(amount) as sum_settled"),
                DB::raw("SUM(vat_deduction_amount) as sum_vat"),
                DB::raw("SUM(tax_deduction_amount) as sum_tax")
            )
            ->groupBy(DB::raw("DATE_FORMAT(payment_date, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(payment_date, '%Y-%m')"), 'desc')
            ->limit(12)
            ->get();

        $invoiceSettings = self::getInvoiceSettings();

        return view('admin.accounting.tax_vat_deductions.index', compact(
            'deductions',
            'totalSettledAmount',
            'totalNetCollected',
            'totalVatDeducted',
            'totalTaxDeducted',
            'totalOtherDeducted',
            'grandTotalDeductions',
            'totalClientsCount',
            'monthlyDeductionSummaries',
            'selectedMonth',
            'selectedYear',
            'dateFrom',
            'dateTo',
            'customer',
            'search',
            'deductionType',
            'invoiceSettings'
        ));
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
            'qr_code_size'                   => '60px',
            'default_creator_designation'    => '',
            'default_creator_name'           => '',
            'whatsapp_message_template'      => '{business_name} থেকে আপনার {doc_type} (#{invoice_no}) প্রস্তুত করা হয়েছে। সরাসরি দেখতে ভিজিট করুন: {invoice_url}',
            'email_greeting_salutation'      => 'সম্মানিত গ্রাহক',
            'email_intro_text'               => '',
            'quotation_default_validity_days'=> '30',
            'quotation_default_subject'      => 'বই প্রকাশনা, মুদ্রণ ও সরবরাহ প্রসঙ্গে',
            'quotation_default_notes'        => '১. ভ্যাট যুক্ত করা হয়নি। ২. কোটেশনের মেয়াদ ৩০ দিন পর্যন্ত কার্যকর থাকবে।',
            'quotation_default_terms'        => "১. কার্যাদেশ পাওয়ার পর নির্ধারিত সময়ের মধ্যে ডেলিভারি প্রদান করা হবে।\n২. কাজের পরিধি ও স্পেসিফিকেশন পরিবর্তন হলে দর সমন্বয়যোগ্য।",
            'quotation_title_bn'             => 'মূল্য কোটেশন (PRICE QUOTATION)',
            'tender_title_bn'                => 'দরপত্র প্রস্তাবনা (TENDER PROPOSAL)',
            'invoice_title_bn'               => 'ক্যাশ মেমো / বিল (INVOICE / BILL)',
            'challan_title_bn'               => 'ডেলিভারি চালান (DELIVERY CHALLAN)',
            'digit_language'                 => 'bn',
            'default_vat_rate'               => '7.5',
            'default_tax_rate'               => '5.0',
            'vat_presets'                    => '0, 5, 7.5, 10, 15',
            'tax_presets'                    => '0, 2, 3, 5, 7, 10',
            'receipt_primary_color'          => '#059669',
            'receipt_stamp_color'            => '#6b21a8',
            'receipt_logo_height'            => '58px',
            'receipt_logo_width'             => '155px',
            'receipt_stamp_size'             => '135px',
            'receipt_stamp_rotation'         => '-10',
            'receipt_stamp_top'              => '-30px',
            'receipt_stamp_right'            => '25px',
            'receipt_font_scale'             => '1',
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
        @set_time_limit(180);
        @ini_set('max_execution_time', '180');

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
            $senderEmail = config('mail.from.address') ?: 'ad@ideaabd.com';
            $sentLogs = $invoice->email_logs ?? [];
            if (!is_array($sentLogs)) {
                $sentLogs = [];
            }

            $successRecipients = [];
            $failedRecipients = [];

            $settings = self::getInvoiceSettings();
            $pdfData = null;
            try {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('emails.invoice-pdf', [
                    'invoice' => $invoice,
                    'invoiceSettings' => $settings,
                ])->setPaper('a4', 'portrait');
                $pdfData = $pdf->output();
            } catch (\Throwable $pdfEx) {
                Log::warning("Pre-rendering invoice PDF failed: " . $pdfEx->getMessage());
            }

            // Send individual direct emails to all valid recipients
            foreach ($validEmails as $singleRecipient) {
                try {
                    Mail::to($singleRecipient)->send(new CustomerInvoiceMail(
                        $invoice,
                        $customMsg,
                        $settings,
                        $pdfData
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
        /**
     * Delete an Email Dispatch Log entry.
     */
    public function deleteEmailLog(Request $request, IdeaInvoice $invoice, string $logId): RedirectResponse|JsonResponse
    {
        try {
            $logs = $invoice->email_logs ?? [];
            if (!is_array($logs)) {
                $logs = [];
            }

            $filteredLogs = array_values(array_filter($logs, function ($entry) use ($logId) {
                return ($entry['id'] ?? '') !== $logId;
            }));

            $invoice->email_logs = $filteredLogs;
            $invoice->save();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'ইমেইল লগ সফলভাবে মুছে ফেলা হয়েছে।'
                ]);
            }

            return back()->with('success', 'ইমেইল লগ সফলভাবে মুছে ফেলা হয়েছে।');
        } catch (\Throwable $e) {
            Log::error("Failed deleting email log {$logId} for invoice #{$invoice->id}: " . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'লগ মুছতে সমস্যা হয়েছে: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'লগ মুছতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

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
                $periodLabel = 'Today (' . $now->format('d M, Y') . ')';
                break;
            case 'weekly':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $periodLabel = 'Week (' . $startDate->format('d M') . ' - ' . $endDate->format('d M') . ')';
                break;
            case 'yearly':
                $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
                $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();
                $periodLabel = 'Year ' . $year;
                break;
            case 'custom':
                $startDate = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : $now->copy()->startOfMonth();
                $endDate = $dateTo ? Carbon::parse($dateTo)->endOfDay() : $now->copy()->endOfDay();
                $periodLabel = 'Custom';
                break;
            case 'monthly':
            default:
                $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
                $endDate = $startDate->copy()->endOfMonth()->endOfDay();
                $periodLabel = $startDate->format('F Y');
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

        // Monthly breakdown for yearly view, daily for monthly, or 7-day for weekly/daily
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
                $dProd = (float) IdeaAccountingEntry::whereDate('entry_date', $dDate)->where('type', 'expense')->whereIn('category', $prodCategories)->sum('amount');
                $trendDataset[] = [
                    'label' => (string) $d,
                    'income' => $dInc,
                    'expense' => $dExp,
                    'production' => $dProd,
                    'profit' => $dInc - $dExp,
                ];
            }
        } elseif ($period === 'weekly') {
            for ($w = 0; $w < 7; $w++) {
                $wDay = $startDate->copy()->addDays($w);
                $wDate = $wDay->toDateString();
                $wInc = (float) IdeaAccountingEntry::whereDate('entry_date', $wDate)->where('type', 'income')->sum('amount');
                $wExp = (float) IdeaAccountingEntry::whereDate('entry_date', $wDate)->where('type', 'expense')->sum('amount');
                $wProd = (float) IdeaAccountingEntry::whereDate('entry_date', $wDate)->where('type', 'expense')->whereIn('category', $prodCategories)->sum('amount');
                $trendDataset[] = [
                    'label' => $wDay->format('D (d M)'),
                    'income' => $wInc,
                    'expense' => $wExp,
                    'production' => $wProd,
                    'profit' => $wInc - $wExp,
                ];
            }
        } else {
            // Daily / Custom: 7 days trend
            for ($d = 6; $d >= 0; $d--) {
                $day = $endDate->copy()->subDays($d);
                $dDate = $day->toDateString();
                $dInc = (float) IdeaAccountingEntry::whereDate('entry_date', $dDate)->where('type', 'income')->sum('amount');
                $dExp = (float) IdeaAccountingEntry::whereDate('entry_date', $dDate)->where('type', 'expense')->sum('amount');
                $dProd = (float) IdeaAccountingEntry::whereDate('entry_date', $dDate)->where('type', 'expense')->whereIn('category', $prodCategories)->sum('amount');
                $trendDataset[] = [
                    'label' => $day->format('d M'),
                    'income' => $dInc,
                    'expense' => $dExp,
                    'production' => $dProd,
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
            ->when($department, function ($q, $dept) {
                $q->where(function ($sub) use ($dept) {
                    $sub->where('department', 'like', '%' . $dept . '%')
                        ->orWhere('designation', 'like', '%' . $dept . '%')
                        ->orWhere('skill_category', 'like', '%' . $dept . '%');
                });
            })
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
     * Universal Dynamic Staff & Artisan Work Log & Cash Ledger (যেকোনো স্টাফের কাজের খতিয়ান ও টাকা তোলার হিসাব).
     */
    public function employeeLedger($id, Request $request): View
    {
        $employee = IdeaEmployee::with(['workLogs' => fn($q) => $q->latest('log_date')->latest('id')])->findOrFail($id);
        
        $perPage = $request->input('per_page', 25);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $bookFilter = $request->input('book_title') ?: $request->input('search');
        $entryTypeFilter = $request->input('entry_type');

        $query = $employee->workLogs()->latest('log_date')->latest('id');
        if ($dateFrom) {
            $query->whereDate('log_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('log_date', '<=', $dateTo);
        }
        if ($bookFilter) {
            $query->where(function ($q) use ($bookFilter) {
                $q->where('book_title', 'like', '%' . $bookFilter . '%')
                  ->orWhere('notes', 'like', '%' . $bookFilter . '%')
                  ->orWhere('voucher_no', 'like', '%' . $bookFilter . '%');
            });
        }
        if ($entryTypeFilter && in_array($entryTypeFilter, ['work', 'payment'])) {
            $query->where('entry_type', $entryTypeFilter);
        }

        $workLogs = ($perPage === 'all' || $request->has('print'))
            ? $query->paginate(500)
            : $query->paginate((int) $perPage);

        $totalEarned = (float) $employee->totalWorkEarned();
        $totalPaid = (float) $employee->totalWorkPaid();
        $balanceDue = $totalEarned - $totalPaid;
        $totalWorkQuantity = (float) $employee->workLogs()->where('entry_type', 'work')->sum('quantity');

        // Chronological running balances
        $chronologicalLogs = $employee->workLogs()->orderBy('log_date', 'asc')->orderBy('id', 'asc')->get();
        $runningBalanceMap = [];
        $runningBal = 0;
        foreach ($chronologicalLogs as $clog) {
            if ($clog->entry_type === 'work') {
                $runningBal += (float) $clog->earned_amount;
            } else {
                $runningBal -= (float) $clog->paid_amount;
            }
            $runningBalanceMap[$clog->id] = $runningBal;
        }

        // Attach running balance to paginated logs
        foreach ($workLogs as $log) {
            $log->running_balance = $runningBalanceMap[$log->id] ?? 0;
        }

        // All Work Logs for Summaries
        $allWorkLogs = $employee->workLogs()->where('entry_type', 'work')->get();
        $bookSummaries = collect();
        $taskSummaries = collect();

        $roleCat = $employee->getRoleCategory();

        if ($roleCat === 'artisan') {
            // Multi-day Book Production Aggregation for Press / Binders
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
        } else {
            // Task-wise Aggregation for Computer Operator, Proofreader, Peon, Marketing, Designer, etc.
            $taskSummaries = $allWorkLogs
                ->groupBy(function ($l) {
                    return !empty($l->book_title) ? $l->book_title : 'সাধারণ কাজ ও দায়িত্ব (General Tasks)';
                })
                ->map(function ($logs, $taskTitle) {
                    $totalQty = (float) $logs->sum('quantity');
                    $totalEarned = (float) $logs->sum('earned_amount');
                    $lastLogDate = $logs->sortByDesc('log_date')->first()?->log_date;
                    $unitName = $logs->sortByDesc('id')->first()?->unit_name ?: 'Unit';
                    $lastRate = (float) ($logs->sortByDesc('id')->first()?->unit_rate ?: 0);

                    return [
                        'task_title'    => $taskTitle,
                        'total_qty'     => $totalQty,
                        'unit_name'     => $unitName,
                        'total_earned'  => $totalEarned,
                        'entries_count' => $logs->count(),
                        'last_log_date' => $lastLogDate ? $lastLogDate->format('d M, Y') : null,
                        'unit_rate'     => $lastRate,
                    ];
                })->values();
        }

        // All active employees for quick-switcher
        $allEmployees = IdeaEmployee::orderBy('name')->get(['id', 'name', 'designation', 'department', 'salary_rate_type']);

        $invoiceSettings = self::getInvoiceSettings();
        $roleConfig = $employee->getRoleConfig();

        return view('admin.accounting.employees.ledger', compact(
            'employee', 'workLogs', 'totalEarned', 'totalPaid', 'balanceDue', 'totalWorkQuantity',
            'bookSummaries', 'taskSummaries', 'allEmployees', 'invoiceSettings', 'roleConfig'
        ));
    }

    /**
     * Store Work Entry or Cash Withdrawal for any staff member.
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
            'expense_category'    => 'nullable|string|max:255',
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
     * Check if an invoice item qualifies as a Book in Bookshop inventory (/admin/books).
     * Non-book items (printing, stationery / educational materials, general products, services, etc.)
     * must never be registered as books.
     */
    public static function isBookItem(?string $itemType, string $salesCategory = 'books'): bool
    {
        return IdeaInvoiceItemService::isBookItem($itemType, $salesCategory);
    }
}

