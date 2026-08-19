<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdeaAccountingEntry;
use App\Models\IdeaInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'category'       => 'required|string|max:100',
            'title'          => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'entry_date'     => 'required|date',
            'voucher_no'     => 'nullable|string|max:100',
            'payment_method' => 'required|string|max:50',
            'party_name'     => 'nullable|string|max:255',
            'notes'          => 'nullable|string|max:1000',
        ], [
            'type.required'     => 'লেনদেনের ধরন (আয় বা ব্যয়) নির্বাচন করুন।',
            'category.required' => 'খাত বা ক্যাটাগরি নির্বাচন করুন।',
            'title.required'    => 'বিবরণ লিখুন।',
            'amount.required'   => 'টাকার পরিমাণ দিন।',
        ]);

        $prefix = $validated['type'] === 'income' ? 'INC-' : 'EXP-';
        $dateStr = date('Ymd', strtotime($validated['entry_date']));
        $entryNo = $prefix . $dateStr . '-' . rand(1000, 9999);

        IdeaAccountingEntry::create([
            'entry_no'       => $entryNo,
            'type'           => $validated['type'],
            'category'       => $validated['category'],
            'title'          => $validated['title'],
            'amount'         => (float) $validated['amount'],
            'entry_date'     => $validated['entry_date'],
            'voucher_no'     => $validated['voucher_no'] ?? null,
            'payment_method' => $validated['payment_method'],
            'party_name'     => $validated['party_name'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'created_by'     => auth()->id(),
        ]);

        $msg = $validated['type'] === 'income' ? 'নতুন আয় এন্ট্রি সংরক্ষিত হয়েছে।' : 'নতুন ব্যয় / ক্রয়ের হিসাব সংরক্ষিত হয়েছে।';

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
        $status = $request->input('payment_status');
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = IdeaInvoice::query()
            ->with('creator')
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($status, fn($q) => $q->where('payment_status', $status))
            ->when($dateFrom, fn($q) => $q->whereDate('invoice_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('invoice_date', '<=', $dateTo))
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('invoice_no', 'like', $like)
                      ->orWhere('customer_name', 'like', $like)
                      ->orWhere('customer_phone', 'like', $like);
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
            'total_amount'     => (float) IdeaInvoice::whereIn('type', ['invoice', 'challan'])->sum('grand_total'),
            'total_paid'       => (float) IdeaInvoice::whereIn('type', ['invoice', 'challan'])->sum('paid_amount'),
            'total_due'        => (float) IdeaInvoice::whereIn('type', ['invoice', 'challan'])->sum('due_amount'),
        ];

        return view('admin.accounting.invoices.index', compact(
            'invoices', 'stats', 'type', 'status', 'search', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Create Bill, Delivery Challan, Quotation or Tender.
     */
    public function createInvoice(Request $request): View
    {
        $books = Book::where('is_active', true)->select('id', 'title', 'price', 'stock_quantity', 'author_name')->orderBy('title')->get();
        
        $selectedType = $request->query('type', 'invoice');
        if (!in_array($selectedType, ['invoice', 'challan', 'quotation', 'tender'])) {
            $selectedType = 'invoice';
        }

        $prefix = match($selectedType) {
            'challan'   => 'IDEA-CHL-',
            'quotation' => 'IDEA-QUO-',
            'tender'    => 'IDEA-TND-',
            default     => 'IDEA-INV-',
        };

        $dateStr = date('Ymd');
        $countToday = IdeaInvoice::whereDate('created_at', today())->count() + 1;
        $suggestedNo = $prefix . $dateStr . '-' . str_pad((string)$countToday, 3, '0', STR_PAD_LEFT);

        return view('admin.accounting.invoices.create', compact('books', 'suggestedNo', 'selectedType'));
    }

    /**
     * Store Bill / Challan / Quotation / Tender.
     */
    public function storeInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'             => 'required|in:invoice,challan,quotation,tender',
            'invoice_no'       => 'required|string|max:50|unique:idea_invoices,invoice_no',
            'subject'          => 'nullable|string|max:255',
            'reference_no'     => 'nullable|string|max:100',
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'nullable|string|max:50',
            'customer_address' => 'nullable|string|max:255',
            'invoice_date'     => 'required|date',
            'valid_until'      => 'nullable|date',
            'discount'         => 'nullable|numeric|min:0',
            'tax'              => 'nullable|numeric|min:0',
            'paid_amount'      => 'nullable|numeric|min:0',
            'payment_method'   => 'required|string|max:50',
            'notes'            => 'nullable|string|max:1000',
            'terms_conditions' => 'nullable|string|max:2000',
            'items'            => 'required|array|min:1',
            'items.*.title'    => 'required|string|max:255',
            'items.*.item_type'=> 'nullable|string|max:50',
            'items.*.book_id'  => 'nullable|integer',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price'    => 'required|numeric|min:0',
        ], [
            'customer_name.required' => 'গ্রাহক বা প্রতিষ্ঠানের নাম লিখুন।',
            'items.required'         => 'কমপক্ষে একটি আইটেম বা বিবরণ যোগ করুন।',
        ]);

        return DB::transaction(function () use ($validated) {
            $subtotal = 0.0;
            $itemsProcessed = [];

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['price'];
                $lineTotal = $qty * $price;
                $subtotal += $lineTotal;

                $itemsProcessed[] = [
                    'title'      => $item['title'],
                    'item_type'  => $item['item_type'] ?? 'product',
                    'book_id'    => !empty($item['book_id']) ? (int)$item['book_id'] : null,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                    'subtotal'   => $lineTotal,
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

            $invoice = IdeaInvoice::create([
                'invoice_no'       => $validated['invoice_no'],
                'type'             => $validated['type'],
                'subject'          => $validated['subject'] ?? null,
                'reference_no'     => $validated['reference_no'] ?? null,
                'customer_name'    => $validated['customer_name'],
                'customer_phone'   => $validated['customer_phone'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'invoice_date'     => $validated['invoice_date'],
                'valid_until'      => $validated['valid_until'] ?? null,
                'items'            => $itemsProcessed,
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'tax'              => $tax,
                'grand_total'      => $grandTotal,
                'paid_amount'      => $paid,
                'due_amount'       => $due,
                'payment_method'   => $validated['payment_method'],
                'payment_status'   => $paymentStatus,
                'notes'            => $validated['notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'created_by'       => auth()->id(),
            ]);

            // Auto record payment in Accounting entries if paid amount > 0 and type is invoice/challan
            if ($paid > 0 && in_array($validated['type'], ['invoice', 'challan'])) {
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
                    'created_by'     => auth()->id(),
                ]);
            }

            $typeLabel = $invoice->type_label;

            return redirect()->route('admin.accounting.invoices.show', $invoice->id)
                ->with('success', "{$typeLabel} #{$invoice->invoice_no} সফলভাবে তৈরি হয়েছে।");
        });
    }

    /**
     * Show & Print Bill / Challan / Quotation / Tender.
     */
    public function showInvoice(IdeaInvoice $invoice): View
    {
        return view('admin.accounting.invoices.show', compact('invoice'));
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
     * Delete Bill / Challan / Quotation / Tender.
     */
    public function destroyInvoice(IdeaInvoice $invoice): RedirectResponse
    {
        $invoice->delete();
        return redirect()->route('admin.accounting.invoices.index')
            ->with('success', 'ডকুমেন্টটি সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
