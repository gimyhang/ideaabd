<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CustomerInvoiceMail;
use App\Models\IdeaAccountingEntry;
use App\Models\IdeaInvoice;
use App\Models\IdeaEmployee;
use App\Models\IdeaSalaryPayment;
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
     * Create Bill, Delivery Challan, Quotation or Tender.
     */
    public function createInvoice(Request $request): View
    {
        $books = Book::where('is_active', true)
            ->select('id', 'title', 'subtitle', 'author_name', 'cover_type', 'format', 'price', 'discount_price', 'hardcover_price', 'hardcover_discount_price', 'stock_quantity')
            ->orderBy('title')
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

        return view('admin.accounting.invoices.create', compact('books', 'suggestedNo', 'selectedType', 'salesCategory'));
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
            'valid_until'          => 'nullable|date',
            'discount'             => 'nullable|numeric|min:0',
            'tax'                  => 'nullable|numeric|min:0',
            'paid_amount'          => 'nullable|numeric|min:0',
            'payment_method'       => 'required|string|max:50',
            'notes'                => 'nullable|string|max:1000',
            'terms_conditions'     => 'nullable|string|max:2000',
            'items'                => 'required|array|min:1',
            'items.*.title'            => 'required|string|max:255',
            'items.*.author_name'      => 'nullable|string|max:255',
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

        try {
            return DB::transaction(function () use ($validated, $request) {
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

                    $itemsProcessed[] = [
                        'title'            => $item['title'],
                        'author_name'      => !empty($item['author_name']) ? trim((string)$item['author_name']) : null,
                        'item_type'        => $item['item_type'] ?? 'বই (Book)',
                        'unit'             => !empty($item['unit']) ? trim((string)$item['unit']) : 'কপি',
                        'book_id'          => !empty($item['book_id']) ? (int)$item['book_id'] : null,
                        'quantity'         => $qty,
                        'regular_price'    => $regularPrice,
                        'discount_percent' => $discPct,
                        'unit_price'       => $price,
                        'subtotal'         => $lineTotal,
                    ];
                }

                $discount = (float) ($validated['discount'] ?? 0);
                $tax = (float) ($validated['tax'] ?? 0);
                $grandTotal = max(0, $subtotal - discount + $tax);
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

                // Auto record payment in Accounting entries if paid amount > 0 and type is invoice/challan
                if ($paid > 0 && in_array($validated['type'], ['invoice', 'challan'])) {
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
        $invoiceSettings = self::getInvoiceSettings();
        return view('admin.accounting.invoices.show', compact('invoice', 'invoiceSettings'));
    }

    /**
     * Edit Bill / Challan / Quotation / Tender.
     */
    public function editInvoice(IdeaInvoice $invoice): View
    {
        $books = Book::where('is_active', true)
            ->select('id', 'title', 'subtitle', 'author_name', 'cover_type', 'format', 'price', 'discount_price', 'hardcover_price', 'hardcover_discount_price', 'stock_quantity')
            ->orderBy('title')
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
            'valid_until'          => 'nullable|date',
            'discount'             => 'nullable|numeric|min:0',
            'tax'                  => 'nullable|numeric|min:0',
            'paid_amount'          => 'nullable|numeric|min:0',
            'payment_method'       => 'required|string|max:50',
            'notes'                => 'nullable|string|max:1000',
            'terms_conditions'     => 'nullable|string|max:2000',
            'items'                => 'required|array|min:1',
            'items.*.title'            => 'required|string|max:255',
            'items.*.author_name'      => 'nullable|string|max:255',
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

                    $itemsProcessed[] = [
                        'title'            => $item['title'],
                        'author_name'      => !empty($item['author_name']) ? trim((string)$item['author_name']) : null,
                        'item_type'        => $item['item_type'] ?? 'বই (Book)',
                        'unit'             => !empty($item['unit']) ? trim((string)$item['unit']) : 'কপি',
                        'book_id'          => !empty($item['book_id']) ? (int)$item['book_id'] : null,
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
                ]);

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
                    ->with('success', "{$typeLabel} #{$invoice->invoice_no} সফলভাবে আপডেট করা হয়েছে।");
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'বিল ও চালান আপডেটে সমস্যা হয়েছে: ' . $e->getMessage());
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
     * Update Memo / Invoice Header Business Settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'tagline'       => 'nullable|string|max:255',
            'address'       => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:100',
            'email'         => 'nullable|string|max:100',
            'logo_base64'   => 'nullable|string',
            'logo_file'     => 'nullable|image|max:5120',
            'logo_url'      => 'nullable|string|max:255',
        ]);

        try {
            $settings = self::getInvoiceSettings();
            $settings['business_name'] = $validated['business_name'];
            $settings['tagline'] = $validated['tagline'] ?? '';
            $settings['address'] = $validated['address'] ?? '';
            $settings['phone'] = $validated['phone'] ?? '';
            $settings['email'] = $validated['email'] ?? '';

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

            return back()->with('success', 'বিল ও মেমোর অফিশিয়াল তথ্য এবং লোগো সফলভাবে আপডেট করা হয়েছে।');
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
            'business_name' => \App\Support\SiteSetting::name(),
            'tagline'       => \App\Support\SiteSetting::tagline(),
            'address'       => 'ঢাকা, বাংলাদেশ',
            'phone'         => '018XXXXXXXX',
            'email'         => 'info@ideaabd.com',
            'logo'          => '/images/logo.png',
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
     * Send Invoice & Delivery Challan to Customer Email.
     */
    public function sendInvoiceEmail(Request $request, IdeaInvoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'email'          => 'required|email|max:255',
            'custom_message' => 'nullable|string|max:1000',
        ], [
            'email.required' => 'গ্রাহকের ইমেইল ঠিকানা প্রদান করুন।',
            'email.email'    => 'সঠিক ইমেইল ঠিকানা লিখুন।',
        ]);

        try {
            IdeaInvoice::ensureColumnsExist();

            $invoice->customer_email = $validated['email'];
            if (empty($invoice->access_token)) {
                $invoice->access_token = Str::random(32);
            }
            $invoice->emailed_at = now();
            $invoice->save();

            Mail::to($validated['email'])
                ->send(new CustomerInvoiceMail(
                    $invoice,
                    $validated['custom_message'] ?? null,
                    self::getInvoiceSettings()
                ));

            return back()->with('success', "গ্রাহকের ইমেইল ({$validated['email']}) এ সফলভাবে বিল ও চালানের লিংক পাঠানো হয়েছে।");
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
        $status = $request->input('status');

        $query = IdeaEmployee::query()
            ->withCount('salaryPayments')
            ->withSum('salaryPayments', 'net_paid')
            ->when($department, fn($q) => $q->where('department', $department))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                      ->orWhere('phone', 'like', $like)
                      ->orWhere('designation', 'like', $like);
                });
            })
            ->latest('id');

        $employees = $query->paginate(20)->withQueryString();
        $departments = IdeaEmployee::departments();
        $totalEmployees = IdeaEmployee::count();
        $activeEmployees = IdeaEmployee::where('status', 'active')->count();
        $monthlyPayroll = (float) IdeaEmployee::where('status', 'active')->sum('basic_salary');

        return view('admin.accounting.employees.index', compact(
            'employees', 'departments', 'search', 'department', 'status',
            'totalEmployees', 'activeEmployees', 'monthlyPayroll'
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
            'phone'             => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:255',
            'basic_salary'      => 'required|numeric|min:0',
            'joining_date'      => 'nullable|date',
            'status'            => 'required|in:active,inactive,on_leave',
            'address'           => 'nullable|string|max:500',
            'nid_passport'      => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:1000',
        ]);

        IdeaEmployee::create($validated);

        return back()->with('success', 'নতুন কর্মচারী সফলভাবে যুক্ত করা হয়েছে।');
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
            'phone'             => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:255',
            'basic_salary'      => 'required|numeric|min:0',
            'joining_date'      => 'nullable|date',
            'status'            => 'required|in:active,inactive,on_leave',
            'address'           => 'nullable|string|max:500',
            'nid_passport'      => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:1000',
        ]);

        $employee->update($validated);

        return back()->with('success', 'কর্মচারীর তথ্য সফলভাবে আপডেট করা হয়েছে।');
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
                       ->orWhere('designation', 'like', $like);
                })->orWhere('slip_no', 'like', $like);
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
        $entry = IdeaAccountingEntry::create([
            'entry_no'       => $entryNo,
            'type'           => 'expense',
            'category'       => 'কর্মচারী মূল বেতন (Staff Basic Salary)',
            'title'          => "বেতন প্রদান: {$employee->name} ({$employee->designation}) — {$monthFormatted}",
            'amount'         => $netPaid,
            'entry_date'     => $validated['payment_date'],
            'voucher_no'     => $slipNo,
            'payment_method' => $validated['payment_method'],
            'party_name'     => $employee->name,
            'notes'          => "মূল বেতন: ৳{$basic}, বোনাস: ৳{$bonus}, কর্তন: ৳{$deduction}. " . ($validated['notes'] ?? ''),
            'created_by'     => auth()->id(),
        ]);

        // 2. Create Salary Payment Record
        IdeaSalaryPayment::create([
            'employee_id'         => $employee->id,
            'salary_month'        => $validated['salary_month'],
            'payment_date'        => $validated['payment_date'],
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

        return back()->with('success', "{$employee->name} এর {$monthFormatted} মাসের বেতন (৳" . number_format($netPaid, 2) . ") সফলভাবে প্রদান ও হিসাব খতিয়ানে যুক্ত হয়েছে।");
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
