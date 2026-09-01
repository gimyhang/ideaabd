<?php

namespace App\Http\Controllers\SubAdmin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Book\Models\Book;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $isAdmin = auth()->user()->isAdmin();
        $sellerId = $request->input('seller_id');
        $status = $request->input('payment_status');
        $method = $request->input('payment_method');
        $datePreset = $request->input('date_preset');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $search = $request->string('search')->trim()->value();

        $query = Bill::query()
            ->with('seller')
            ->when(! $isAdmin, fn ($q) => $q->where('seller_id', auth()->id()))
            ->when($isAdmin && $sellerId, fn ($q) => $q->where('seller_id', $sellerId))
            ->when($status, fn ($q) => $q->where('payment_status', $status))
            ->when($method, fn ($q) => $q->where('payment_method', $method))
            ->when($datePreset, function ($q, $preset) {
                if ($preset === 'today') {
                    $q->whereDate('created_at', today());
                } elseif ($preset === 'yesterday') {
                    $q->whereDate('created_at', today()->subDay());
                } elseif ($preset === 'this_week') {
                    $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                } elseif ($preset === 'this_month') {
                    $q->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                }
            })
            ->when($fromDate, fn ($q) => $q->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn ($q) => $q->whereDate('created_at', '<=', $toDate))
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('bill_no', 'like', $like)
                      ->orWhere('customer_name', 'like', $like)
                      ->orWhere('customer_phone', 'like', $like)
                      ->orWhere('customer_email', 'like', $like);
                });
            })
            ->orderByDesc('created_at');

        $bills = $query->paginate(20)->withQueryString();

        $baseStatQuery = Bill::query()
            ->when(! $isAdmin, fn ($q) => $q->where('seller_id', auth()->id()))
            ->when($isAdmin && $sellerId, fn ($q) => $q->where('seller_id', $sellerId));

        $stats = [
            'total'     => (clone $baseStatQuery)->count(),
            'paid'      => (clone $baseStatQuery)->where('payment_status', 'paid')->count(),
            'unpaid'    => (clone $baseStatQuery)->where('payment_status', 'unpaid')->count(),
            'partial'   => (clone $baseStatQuery)->where('payment_status', 'partial')->count(),
            'revenue'   => (float) (clone $baseStatQuery)->where('payment_status', 'paid')->sum('total'),
            'due'       => (float) (clone $baseStatQuery)->where('payment_status', '!=', 'paid')->sum('total'),
        ];

        $sellers = $isAdmin ? User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUB_ADMIN, User::ROLE_SELLER])->orderBy('name')->pluck('name', 'id')->all() : [];

        return view('subadmin.billing.index', compact('bills', 'stats', 'sellers', 'isAdmin'));
    }

    /**
     * One-click instant payment mark for unpaid/partial bills.
     */
    public function quickPay(Bill $bill)
    {
        if (! auth()->user()->isAdmin()) {
            abort_unless($bill->seller_id === auth()->id(), 403);
        }

        $bill->update([
            'payment_status' => 'paid',
        ]);

        return back()->with('success', "বিল #{$bill->bill_no} পরিশোধিত (Paid) হিসেবে চিহ্নিত করা হয়েছে।");
    }

    /**
     * Thermal Receipt Printer View (58mm / 80mm).
     */
    public function receipt(Bill $bill)
    {
        if (! auth()->user()->isAdmin()) {
            abort_unless($bill->seller_id === auth()->id(), 403);
        }

        return view('subadmin.billing.receipt', compact('bill'));
    }

    /**
     * Export filtered bills to CSV.
     */
    public function exportCsv(Request $request)
    {
        $isAdmin = auth()->user()->isAdmin();
        $sellerId = $request->input('seller_id');
        $status = $request->input('payment_status');
        $method = $request->input('payment_method');
        $datePreset = $request->input('date_preset');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $search = $request->string('search')->trim()->value();

        $bills = Bill::query()
            ->with('seller')
            ->when(! $isAdmin, fn ($q) => $q->where('seller_id', auth()->id()))
            ->when($isAdmin && $sellerId, fn ($q) => $q->where('seller_id', $sellerId))
            ->when($status, fn ($q) => $q->where('payment_status', $status))
            ->when($method, fn ($q) => $q->where('payment_method', $method))
            ->when($datePreset, function ($q, $preset) {
                if ($preset === 'today') {
                    $q->whereDate('created_at', today());
                } elseif ($preset === 'yesterday') {
                    $q->whereDate('created_at', today()->subDay());
                } elseif ($preset === 'this_week') {
                    $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                } elseif ($preset === 'this_month') {
                    $q->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                }
            })
            ->when($fromDate, fn ($q) => $q->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn ($q) => $q->whereDate('created_at', '<=', $toDate))
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('bill_no', 'like', $like)
                      ->orWhere('customer_name', 'like', $like)
                      ->orWhere('customer_phone', 'like', $like)
                      ->orWhere('customer_email', 'like', $like);
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="seller_bills_' . date('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($bills) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Bengali support in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Bill No', 'Date', 'Customer Name', 'Phone', 'Seller', 'Items Count', 'Subtotal', 'Discount', 'Total Amount', 'Payment Method', 'Payment Status']);

            foreach ($bills as $b) {
                fputcsv($file, [
                    $b->bill_no,
                    $b->created_at->format('Y-m-d H:i:s'),
                    $b->customer_name,
                    $b->customer_phone,
                    $b->seller->name ?? 'N/A',
                    count($b->items ?? []),
                    $b->subtotal,
                    $b->discount,
                    $b->total,
                    strtoupper($b->payment_method ?? 'CASH'),
                    strtoupper($b->payment_status ?? 'PAID'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk Action on Bills (Mark as Paid, Delete).
     */
    public function bulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $billIds = (array) $request->input('bill_ids', []);

        if (empty($billIds)) {
            return back()->with('error', 'কোনো বিল নির্বাচন করা হয়নি।');
        }

        $isAdmin = auth()->user()->isAdmin();
        $query = Bill::whereIn('id', $billIds)
            ->when(! $isAdmin, fn ($q) => $q->where('seller_id', auth()->id()));

        if ($action === 'mark_paid') {
            $count = $query->update(['payment_status' => 'paid']);
            return back()->with('success', "{$count} টি বিলকে সফলভাবে পরিশোধিত (Paid) হিসেবে চিহ্নিত করা হয়েছে।");
        } elseif ($action === 'delete') {
            $count = $query->delete();
            return back()->with('success', "{$count} টি বিল মুছে ফেলা হয়েছে।");
        }

        return back();
    }

    public function create()
    {
        return view('subadmin.billing.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateBill($request);

        $billData = $this->calculateBillTotals($data);
        $billData['seller_id'] = auth()->id();

        $bill = Bill::create($billData);

        return redirect()->route('subadmin.bills.show', $bill)
            ->with('success', "বিল #{$bill->bill_no} সফলভাবে তৈরি হয়েছে।");
    }

    public function show(Bill $bill)
    {
        if (! auth()->user()->isAdmin()) {
            abort_unless($bill->seller_id === auth()->id(), 403);
        }

        return view('subadmin.billing.show', compact('bill'));
    }

    public function edit(Bill $bill)
    {
        if (! auth()->user()->isAdmin()) {
            abort_unless($bill->seller_id === auth()->id(), 403);
        }

        return view('subadmin.billing.edit', compact('bill'));
    }

    public function update(Request $request, Bill $bill)
    {
        if (! auth()->user()->isAdmin()) {
            abort_unless($bill->seller_id === auth()->id(), 403);
        }

        $data = $this->validateBill($request);
        $billData = $this->calculateBillTotals($data);

        $bill->update($billData);

        return redirect()->route('subadmin.bills.show', $bill)
            ->with('success', "বিল #{$bill->bill_no} সফলভাবে আপডেট করা হয়েছে।");
    }

    public function destroy(Bill $bill)
    {
        if (! auth()->user()->isAdmin()) {
            abort_unless($bill->seller_id === auth()->id(), 403);
        }

        $billNo = $bill->bill_no;
        $bill->delete();

        return redirect()->route('subadmin.bills.index')
            ->with('success', "বিল #{$billNo} সফলভাবে মুছে ফেলা হয়েছে।");
    }

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
                              ->orWhereHas('authors', fn($a) => $a->where('name', 'like', $like))
                              ->orWhereHas('publisher', fn($p) => $p->where('name', 'like', $like));

                        $lower = mb_strtolower($token);
                        if (str_contains($lower, 'idea') || str_contains($token, 'আইডিয়া') || str_contains($token, 'আইডিয়া')) {
                            $query->orWhereNull('publisher_id');
                        }
                    });
                }
            })
            ->limit(20)
            ->get(['id', 'title', 'price', 'discount_price', 'stock_quantity', 'cover_type', 'isbn', 'cover_image'])
            ->map(function ($book) {
                $regularPrice = (float) ($book->price ?? 0);
                $discountPrice = $book->discount_price !== null && (float) $book->discount_price > 0
                    ? (float) $book->discount_price
                    : null;
                $effectivePrice = $discountPrice ?? $regularPrice;

                $calculatedDiscountPct = ($regularPrice > 0 && $discountPrice !== null && $discountPrice < $regularPrice)
                    ? round((($regularPrice - $discountPrice) / $regularPrice) * 100, 1)
                    : 0;

                return [
                    'id'             => $book->id,
                    'title'          => $book->title,
                    'regular_price'  => $regularPrice,
                    'discount_price' => $discountPrice,
                    'selling_price'  => $effectivePrice,
                    'discount_pct'   => $calculatedDiscountPct,
                    'stock_quantity' => (int) ($book->stock_quantity ?? 0),
                    'cover_type'     => $book->cover_type ?? 'paperback',
                    'isbn'           => $book->isbn,
                    'cover_image'    => $book->cover_image ? asset('storage/' . ltrim($book->cover_image, '/')) : null,
                ];
            });

        return response()->json($books);
    }

    private function validateBill(Request $request): array
    {
        return $request->validate([
            'customer_name'          => 'required|string|max:255',
            'customer_phone'         => 'nullable|string|max:20',
            'customer_email'         => 'nullable|email|max:255',
            'items'                  => 'required|array|min:1',
            'items.*.book_id'        => 'nullable|integer',
            'items.*.title'          => 'required|string|max:500',
            'items.*.qty'            => 'required|integer|min:1',
            'items.*.price'          => 'required|numeric|min:0',
            'items.*.discount_pct'   => 'nullable|numeric|min:0|max:100',
            'special_discount_type'  => 'nullable|in:percent,fixed',
            'special_discount_value' => 'nullable|numeric|min:0',
            'discount'               => 'nullable|numeric|min:0',
            'payment_method'         => 'required|in:cash,bkash,nagad,card',
            'payment_status'         => 'required|in:unpaid,paid,partial',
            'notes'                  => 'nullable|string|max:2000',
        ]);
    }

    private function calculateBillTotals(array $data): array
    {
        $processedItems = [];
        $rawSubtotal = 0.0;
        $itemsDiscountTotal = 0.0;
        $itemsNetTotal = 0.0;

        foreach ($data['items'] as $item) {
            $qty = max(1, (int) $item['qty']);
            $unitPrice = max(0, (float) $item['price']);
            $itemDiscountPct = max(0, min(100, (float) ($item['discount_pct'] ?? 0)));

            $lineRawTotal = $qty * $unitPrice;
            $lineDiscountAmount = round($lineRawTotal * ($itemDiscountPct / 100), 2);
            $lineNetTotal = max(0, $lineRawTotal - $lineDiscountAmount);

            $rawSubtotal += $lineRawTotal;
            $itemsDiscountTotal += $lineDiscountAmount;
            $itemsNetTotal += $lineNetTotal;

            $processedItems[] = [
                'book_id'          => !empty($item['book_id']) ? (int)$item['book_id'] : null,
                'title'            => $item['title'],
                'qty'              => $qty,
                'price'            => $unitPrice,
                'discount_pct'     => $itemDiscountPct,
                'discount_amount'  => $lineDiscountAmount,
                'line_total'       => $lineNetTotal,
            ];
        }

        // Special / Overall Discount on Net Total
        $specialType = $data['special_discount_type'] ?? 'fixed';
        $specialVal = max(0, (float) ($data['special_discount_value'] ?? ($data['discount'] ?? 0)));
        $specialDiscountAmount = 0.0;

        if ($specialType === 'percent') {
            $specialPct = min(100, $specialVal);
            $specialDiscountAmount = round($itemsNetTotal * ($specialPct / 100), 2);
        } else {
            $specialDiscountAmount = min($itemsNetTotal, $specialVal);
        }

        $totalDiscount = $itemsDiscountTotal + $specialDiscountAmount;
        $grandTotal = max(0, $rawSubtotal - $totalDiscount);

        return [
            'customer_name'          => $data['customer_name'],
            'customer_phone'         => $data['customer_phone'] ?? null,
            'customer_email'         => $data['customer_email'] ?? null,
            'items'                  => $processedItems,
            'subtotal'               => $rawSubtotal,
            'discount'               => $totalDiscount,
            'tax'                    => 0,
            'total'                  => $grandTotal,
            'payment_method'         => $data['payment_method'],
            'payment_status'         => $data['payment_status'],
            'notes'                  => $data['notes'] ?? null,
        ];
    }

    /**
     * Seller Account Statement & Cash in Hand Ledger.
     */
    public function sellerAccounts(Request $request)
    {
        $isAdmin = auth()->user()->isAdmin();
        $targetSellerId = $isAdmin && $request->filled('seller_id') ? (int) $request->seller_id : auth()->id();

        $seller = User::find($targetSellerId) ?? auth()->user();

        $billsQuery = Bill::where('seller_id', $targetSellerId);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $billsQuery->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $totalSales = (float) (clone $billsQuery)->sum('total');
        $paidSales = (float) (clone $billsQuery)->where('payment_status', 'paid')->sum('total');
        $unpaidDue = (float) (clone $billsQuery)->where('payment_status', 'unpaid')->sum('total');

        $cashCollection = (float) (clone $billsQuery)->where('payment_status', 'paid')->where('payment_method', 'cash')->sum('total');
        $bkashCollection = (float) (clone $billsQuery)->where('payment_status', 'paid')->where('payment_method', 'bkash')->sum('total');
        $nagadCollection = (float) (clone $billsQuery)->where('payment_status', 'paid')->where('payment_method', 'nagad')->sum('total');
        $cardCollection = (float) (clone $billsQuery)->where('payment_status', 'paid')->where('payment_method', 'card')->sum('total');

        $recentBills = $billsQuery->latest()->take(25)->get();

        $allSellers = $isAdmin ? User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUB_ADMIN, User::ROLE_SELLER])->get() : collect([$seller]);

        return view('subadmin.billing.accounts', compact(
            'seller',
            'totalSales',
            'paidSales',
            'unpaidDue',
            'cashCollection',
            'bkashCollection',
            'nagadCollection',
            'cardCollection',
            'recentBills',
            'allSellers',
            'isAdmin',
            'targetSellerId'
        ));
    }
}
