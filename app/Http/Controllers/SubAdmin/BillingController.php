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
        $search = $request->string('search')->trim()->value();

        $query = Bill::query()
            ->with('seller')
            ->when(! $isAdmin, fn ($q) => $q->where('seller_id', auth()->id()))
            ->when($isAdmin && $sellerId, fn ($q) => $q->where('seller_id', $sellerId))
            ->when($status, fn ($q) => $q->where('payment_status', $status))
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
            ->when(! $isAdmin, fn ($q) => $q->where('seller_id', auth()->id()));

        $stats = [
            'total'   => (clone $baseStatQuery)->count(),
            'paid'    => (clone $baseStatQuery)->where('payment_status', 'paid')->count(),
            'unpaid'  => (clone $baseStatQuery)->where('payment_status', 'unpaid')->count(),
            'revenue' => (float) (clone $baseStatQuery)->where('payment_status', 'paid')->sum('total'),
        ];

        $sellers = $isAdmin ? User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUB_ADMIN, User::ROLE_SELLER])->orderBy('name')->pluck('name', 'id')->all() : [];

        return view('subadmin.billing.index', compact('bills', 'stats', 'sellers', 'isAdmin'));
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

        $books = Book::query()
            ->where('is_active', true)
            ->where(function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where('title', 'like', $like)
                      ->orWhere('isbn', 'like', $like)
                      ->orWhere('author_name', 'like', $like);
            })
            ->limit(15)
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

    public function sellerAccounts()
    {
        $sellers = User::whereIn('role', [User::ROLE_SELLER, User::ROLE_SUB_ADMIN])
            ->withCount('bills')
            ->with(['bills' => fn($q) => $q->select('seller_id', DB::raw('SUM(total) as revenue'))->groupBy('seller_id')])
            ->paginate(20);

        return view('subadmin.seller-accounts', compact('sellers'));
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
}
