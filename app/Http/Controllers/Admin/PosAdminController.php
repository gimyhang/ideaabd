<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosRegister;
use App\Models\PosSale;
use App\Models\User;
use App\Services\AdminAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Book\Models\Book;
use Modules\Book\Models\Category;

class PosAdminController extends Controller
{
    public function __construct(
        protected AdminAccessService $accessService
    ) {}

    public function index(): View
    {
        // Get active register or create default Boi Mela register
        $activeRegister = PosRegister::firstOrCreate(
            ['status' => 'open'],
            [
                'name'         => 'অমর একুশে বইমেলা স্টল (Boi Mela Stall)',
                'location'     => 'সোহরাওয়ার্দী উদ্যান, ঢাকা',
                'opening_cash' => 2000.00,
                'current_cash' => 2000.00,
                'opened_by'    => auth()->id(),
                'status'       => 'open',
            ]
        );

        // Fetch Today's Transactions
        $recentSales = PosSale::with(['cashier', 'voidedByUser'])
            ->whereDate('created_at', today())
            ->latest()
            ->take(50)
            ->get();

        // Calculate Today's Live Sales Analytics
        $todayActiveQuery = PosSale::whereDate('created_at', today())->where('status', '!=', 'voided');
        
        $todayTotalSales = (float) (clone $todayActiveQuery)->sum('total');
        $todayCash = (float) (clone $todayActiveQuery)->sum('paid_cash');
        $todayOnline = (float) (clone $todayActiveQuery)->sum('paid_online');
        $todayDiscount = (float) (clone $todayActiveQuery)->sum('discount');
        $todayOrdersCount = (int) (clone $todayActiveQuery)->count();
        $todayVoidCount = (int) PosSale::whereDate('created_at', today())->where('status', 'voided')->count();

        // Calculate total books sold today
        $todaySalesItems = (clone $todayActiveQuery)->pluck('items_json');
        $todayBooksSold = 0;
        foreach ($todaySalesItems as $items) {
            if (is_array($items)) {
                $todayBooksSold += array_sum(array_column($items, 'quantity'));
            }
        }

        // Available Categories for Genre Filter Pills
        $categories = Category::has('books')
            ->select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();

        // Books prioritized: Idea Prokashon (publisher_id = 2) first, then alphabetical
        $books = Book::with('category', 'authorLink')
            ->where('stock_quantity', '>', 0)
            ->select('id', 'title', 'price', 'discount_price', 'stock_quantity', 'isbn', 'sku', 'cover_image', 'category_id', 'author_name', 'author_link_id', 'publisher_id')
            ->orderByRaw("CASE WHEN publisher_id = 2 THEN 0 ELSE 1 END")
            ->orderBy('title')
            ->take(120)
            ->get()
            ->map(function ($b) {
                return [
                    'id'             => $b->id,
                    'title'          => $b->title,
                    'author_name'    => $b->author_name ?: ($b->authorLink?->name ?? '—'),
                    'category_id'    => $b->category_id,
                    'category_name'  => $b->category?->name ?? 'General',
                    'is_idea'        => ($b->publisher_id == 2),
                    'price'          => (float)$b->price,
                    'final_price'    => (float)($b->discount_price ?? $b->price),
                    'stock_quantity' => (int)$b->stock_quantity,
                    'isbn'           => $b->isbn,
                    'sku'            => $b->sku,
                    'cover_url'      => $b->cover_image ? (str_starts_with($b->cover_image, 'http') ? $b->cover_image : asset('storage/' . ltrim($b->cover_image, '/'))) : null,
                ];
            });

        return view('admin.pos.index', compact(
            'activeRegister',
            'recentSales',
            'todayTotalSales',
            'todayCash',
            'todayOnline',
            'todayDiscount',
            'todayOrdersCount',
            'todayVoidCount',
            'todayBooksSold',
            'categories',
            'books'
        ));
    }

    /**
     * Complete Offline Catalog for browser pre-caching.
     */
    public function offlineCatalog(): JsonResponse
    {
        $books = Book::with('category', 'authorLink')
            ->where('stock_quantity', '>', 0)
            ->select('id', 'title', 'price', 'discount_price', 'stock_quantity', 'isbn', 'sku', 'cover_image', 'category_id', 'author_name', 'author_link_id', 'publisher_id')
            ->orderByRaw("CASE WHEN publisher_id = 2 THEN 0 ELSE 1 END")
            ->orderBy('title')
            ->get()
            ->map(function ($b) {
                return [
                    'id'             => $b->id,
                    'title'          => $b->title,
                    'author_name'    => $b->author_name ?: ($b->authorLink?->name ?? '—'),
                    'category_id'    => $b->category_id,
                    'category_name'  => $b->category?->name ?? 'General',
                    'is_idea'        => ($b->publisher_id == 2),
                    'price'          => (float)$b->price,
                    'final_price'    => (float)($b->discount_price ?? $b->price),
                    'stock_quantity' => (int)$b->stock_quantity,
                    'isbn'           => $b->isbn,
                    'sku'            => $b->sku,
                    'cover_url'      => $b->cover_image ? (str_starts_with($b->cover_image, 'http') ? $b->cover_image : asset('storage/' . ltrim($b->cover_image, '/'))) : null,
                ];
            });

        return response()->json([
            'success' => true,
            'books'   => $books,
            'count'   => $books->count(),
        ]);
    }

    /**
     * Search books by Title, ISBN, Barcode, or SKU with optional Category / Publisher filter.
     */
    public function searchBooks(Request $request): JsonResponse
    {
        $q = trim((string)$request->input('q', ''));
        $categoryId = $request->input('category_id');
        $onlyIdea = $request->boolean('only_idea');

        $query = Book::with('category', 'authorLink')
            ->where('stock_quantity', '>', 0);

        if ($onlyIdea) {
            $query->where('publisher_id', 2);
        } elseif ($categoryId && $categoryId !== 'all') {
            $query->where('category_id', $categoryId);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('author_name', 'like', "%{$q}%")
                  ->orWhere('isbn', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%")
                  ->orWhereHas('authorLink', fn($a) => $a->where('name', 'like', "%{$q}%"));
            });
        }

        $books = $query->select('id', 'title', 'price', 'discount_price', 'stock_quantity', 'isbn', 'sku', 'cover_image', 'category_id', 'author_name', 'author_link_id', 'publisher_id')
            ->orderByRaw("CASE WHEN publisher_id = 2 THEN 0 ELSE 1 END")
            ->orderBy('title')
            ->take(60)
            ->get()
            ->map(function ($b) {
                return [
                    'id'             => $b->id,
                    'title'          => $b->title,
                    'author_name'    => $b->author_name ?: ($b->authorLink?->name ?? '—'),
                    'category_id'    => $b->category_id,
                    'category_name'  => $b->category?->name ?? 'General',
                    'is_idea'        => ($b->publisher_id == 2),
                    'price'          => (float)$b->price,
                    'final_price'    => (float)($b->discount_price ?? $b->price),
                    'stock_quantity' => (int)$b->stock_quantity,
                    'isbn'           => $b->isbn,
                    'sku'            => $b->sku,
                    'cover_url'      => $b->cover_image ? (str_starts_with($b->cover_image, 'http') ? $b->cover_image : asset('storage/' . ltrim($b->cover_image, '/'))) : null,
                ];
            });

        return response()->json($books);
    }

    /**
     * Search existing customers by phone or name.
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        $q = trim((string)$request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $customers = User::where(function ($w) use ($q) {
            $w->where('phone', 'like', "%{$q}%")
              ->orWhere('name', 'like', "%{$q}%")
              ->orWhere('email', 'like', "%{$q}%");
        })
        ->select('id', 'name', 'phone', 'email')
        ->take(10)
        ->get();

        return response()->json($customers);
    }

    /**
     * Process instant POS sale.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'required|exists:books,id',
            'items.*.qty'      => 'required|integer|min:1',
            'items.*.price'    => 'required|numeric|min:0',
            'subtotal'         => 'required|numeric|min:0',
            'discount'         => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'total'            => 'required|numeric|min:0',
            'paid_cash'        => 'nullable|numeric|min:0',
            'paid_online'      => 'nullable|numeric|min:0',
            'tendered_amount'  => 'nullable|numeric|min:0',
            'change_amount'    => 'nullable|numeric|min:0',
            'payment_method'   => 'required|string',
            'trx_id'           => 'nullable|string|max:100',
            'customer_name'    => 'nullable|string|max:150',
            'customer_phone'   => 'nullable|string|max:50',
            'notes'            => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($validated) {
            $receiptNo = 'POS-' . date('ymd') . '-' . rand(1000, 9999);
            $activeRegister = PosRegister::where('status', 'open')->first();

            // 1. Validate & Deduct Stock
            $itemsData = [];
            foreach ($validated['items'] as $item) {
                $book = Book::lockForUpdate()->find($item['id']);
                if (!$book) {
                    continue;
                }

                $qty = (int)$item['qty'];
                if ($book->stock_quantity < $qty) {
                    return response()->json([
                        'success' => false,
                        'message' => "‘{$book->title}’ has insufficient stock ({$book->stock_quantity} available).",
                    ], 422);
                }

                $book->decrement('stock_quantity', $qty);

                $itemsData[] = [
                    'id'       => $book->id,
                    'title'    => $book->title,
                    'isbn'     => $book->isbn,
                    'sku'      => $book->sku,
                    'price'    => (float)$item['price'],
                    'quantity' => $qty,
                    'total'    => (float)$item['price'] * $qty,
                ];
            }

            if (empty($itemsData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid books found in cart.',
                ], 422);
            }

            $paidCash = (float)($validated['paid_cash'] ?? 0);
            $paidOnline = (float)($validated['paid_online'] ?? 0);

            // 2. Create POS Sale Record
            $sale = PosSale::create([
                'register_id'      => $activeRegister?->id,
                'receipt_no'       => $receiptNo,
                'cashier_id'       => auth()->id(),
                'customer_name'    => $validated['customer_name'] ?: 'Walk-in Customer',
                'customer_phone'   => $validated['customer_phone'] ?: null,
                'subtotal'         => (float)$validated['subtotal'],
                'discount'         => (float)($validated['discount'] ?? 0.00),
                'discount_percent' => (float)($validated['discount_percent'] ?? 0.00),
                'total'            => (float)$validated['total'],
                'paid_cash'        => $paidCash,
                'paid_online'      => $paidOnline,
                'tendered_amount'  => $validated['tendered_amount'] ? (float)$validated['tendered_amount'] : null,
                'change_amount'    => (float)($validated['change_amount'] ?? 0.00),
                'payment_method'   => $validated['payment_method'],
                'trx_id'           => $validated['trx_id'] ?? null,
                'items_json'       => $itemsData,
                'notes'            => $validated['notes'] ?? null,
                'status'           => 'completed',
            ]);

            // 3. Update Register Cash
            if ($activeRegister && $paidCash > 0) {
                $activeRegister->increment('current_cash', $paidCash);
            }

            $this->accessService->log('pos_sale', "POS receipt #{$receiptNo} generated (Total: ৳{$sale->total})");

            return response()->json([
                'success'     => true,
                'message'     => "Bill #{$receiptNo} generated & stock updated successfully.",
                'receipt_no'  => $receiptNo,
                'sale_id'     => $sale->id,
                'total'       => $sale->total,
                'change'      => $sale->change_amount,
                'receipt_url' => route('admin.pos.receipt', $sale->id),
            ]);
        });
    }

    /**
     * Batch Sync Offline Sales created without internet connection.
     */
    public function syncOfflineSales(Request $request): JsonResponse
    {
        $salesList = $request->input('sales', []);
        if (empty($salesList) || !is_array($salesList)) {
            return response()->json(['success' => false, 'message' => 'No offline sales to sync.'], 422);
        }

        $activeRegister = PosRegister::where('status', 'open')->first();
        $syncedCount = 0;
        $syncedReceipts = [];

        foreach ($salesList as $saleData) {
            try {
                DB::transaction(function () use ($saleData, $activeRegister, &$syncedCount, &$syncedReceipts) {
                    $receiptNo = $saleData['receipt_no'] ?? ('POS-OFF-' . date('ymd') . '-' . rand(1000, 9999));
                    
                    // Deduct stock for all items
                    $itemsData = [];
                    foreach ($saleData['items'] ?? [] as $item) {
                        $book = Book::find($item['id']);
                        $qty = (int)($item['qty'] ?? $item['quantity'] ?? 1);
                        if ($book) {
                            $book->decrement('stock_quantity', min($qty, (int)$book->stock_quantity));
                        }
                        $itemsData[] = [
                            'id'       => $item['id'],
                            'title'    => $item['title'] ?? ($book?->title ?? 'Book'),
                            'price'    => (float)$item['price'],
                            'quantity' => $qty,
                            'total'    => (float)$item['price'] * $qty,
                        ];
                    }

                    $paidCash = (float)($saleData['paid_cash'] ?? 0);
                    $paidOnline = (float)($saleData['paid_online'] ?? 0);

                    $sale = PosSale::create([
                        'register_id'      => $activeRegister?->id,
                        'receipt_no'       => $receiptNo,
                        'cashier_id'       => auth()->id(),
                        'customer_name'    => $saleData['customer_name'] ?: 'Walk-in Customer (Offline)',
                        'customer_phone'   => $saleData['customer_phone'] ?: null,
                        'subtotal'         => (float)$saleData['subtotal'],
                        'discount'         => (float)($saleData['discount'] ?? 0.00),
                        'discount_percent' => (float)($saleData['discount_percent'] ?? 0.00),
                        'total'            => (float)$saleData['total'],
                        'paid_cash'        => $paidCash,
                        'paid_online'      => $paidOnline,
                        'payment_method'   => $saleData['payment_method'] ?? 'cash',
                        'trx_id'           => $saleData['trx_id'] ?? null,
                        'items_json'       => $itemsData,
                        'notes'            => 'Synced from offline queue at ' . now()->toDateTimeString(),
                        'status'           => 'completed',
                        'created_at'       => !empty($saleData['timestamp']) ? \Carbon\Carbon::parse($saleData['timestamp']) : now(),
                    ]);

                    if ($activeRegister && $paidCash > 0) {
                        $activeRegister->increment('current_cash', $paidCash);
                    }

                    $syncedCount++;
                    $syncedReceipts[] = $receiptNo;
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Offline POS Sync error: " . $e->getMessage());
            }
        }

        $this->accessService->log('pos_offline_sync', "Synced {$syncedCount} offline POS sales to server database");

        return response()->json([
            'success'      => true,
            'message'      => "{$syncedCount} offline transactions synced successfully.",
            'synced_count' => $syncedCount,
            'receipts'     => $syncedReceipts,
        ]);
    }

    /**
     * Thermal Receipt Print View.
     */
    public function receipt(int $id): View
    {
        $sale = PosSale::with(['cashier', 'register', 'voidedByUser'])->findOrFail($id);
        return view('admin.pos.receipt', compact('sale'));
    }

    /**
     * Void / Cancel Sale.
     */
    public function voidSale(Request $request, int $id): JsonResponse
    {
        $sale = PosSale::findOrFail($id);

        if ($sale->isVoided()) {
            return response()->json([
                'success' => false,
                'message' => 'This bill is already voided.',
            ], 422);
        }

        $reason = trim((string)$request->input('reason', 'Customer refund / void'));

        DB::transaction(function () use ($sale, $reason) {
            // Restore book stocks
            if (is_array($sale->items_json)) {
                foreach ($sale->items_json as $item) {
                    if (!empty($item['id'])) {
                        Book::where('id', $item['id'])->increment('stock_quantity', (int)($item['quantity'] ?? 1));
                    }
                }
            }

            // Adjust Cash Register
            if ($sale->register && $sale->paid_cash > 0) {
                $sale->register->decrement('current_cash', min((float)$sale->paid_cash, (float)$sale->register->current_cash));
            }

            // Mark Sale as Voided
            $sale->update([
                'status'      => 'voided',
                'voided_by'   => auth()->id(),
                'voided_at'   => now(),
                'void_reason' => $reason,
            ]);

            $this->accessService->log('pos_sale_void', "Bill #{$sale->receipt_no} voided and stock restored. Reason: {$reason}");
        });

        return response()->json([
            'success' => true,
            'message' => "Bill #{$sale->receipt_no} voided and book inventory restored.",
        ]);
    }

    /**
     * Register Shift Summary & Z-Report.
     */
    public function shiftReport(): JsonResponse
    {
        $activeRegister = PosRegister::where('status', 'open')->first();
        $todayActiveQuery = PosSale::whereDate('created_at', today())->where('status', 'completed');

        $grossSales = (float) (clone $todayActiveQuery)->sum('subtotal');
        $totalDiscount = (float) (clone $todayActiveQuery)->sum('discount');
        $netSales = (float) (clone $todayActiveQuery)->sum('total');

        $cashSales = (float) (clone $todayActiveQuery)->where('payment_method', 'cash')->sum('paid_cash');
        $bkashSales = (float) (clone $todayActiveQuery)->where('payment_method', 'bkash')->sum('paid_online');
        $nagadSales = (float) (clone $todayActiveQuery)->where('payment_method', 'nagad')->sum('paid_online');
        $rocketSales = (float) (clone $todayActiveQuery)->where('payment_method', 'rocket')->sum('paid_online');
        $cardSales = (float) (clone $todayActiveQuery)->where('payment_method', 'card')->sum('paid_online');
        $splitSalesCash = (float) (clone $todayActiveQuery)->where('payment_method', 'split')->sum('paid_cash');
        $splitSalesOnline = (float) (clone $todayActiveQuery)->where('payment_method', 'split')->sum('paid_online');

        $totalCashCollected = $cashSales + $splitSalesCash;
        $totalOnlineCollected = $bkashSales + $nagadSales + $rocketSales + $cardSales + $splitSalesOnline;

        $openingCash = (float) ($activeRegister?->opening_cash ?? 0.00);
        $expectedDrawerCash = $openingCash + $totalCashCollected;

        $totalBills = (int) (clone $todayActiveQuery)->count();
        $voidBills = (int) PosSale::whereDate('created_at', today())->where('status', 'voided')->count();

        $items = (clone $todayActiveQuery)->pluck('items_json');
        $totalQty = 0;
        foreach ($items as $itemGroup) {
            if (is_array($itemGroup)) {
                $totalQty += array_sum(array_column($itemGroup, 'quantity'));
            }
        }

        return response()->json([
            'success'              => true,
            'register_name'        => $activeRegister?->name ?? 'Stall Register',
            'location'             => $activeRegister?->location ?? 'Boi Mela Ground, Dhaka',
            'opening_cash'         => $openingCash,
            'gross_sales'          => $grossSales,
            'total_discount'       => $totalDiscount,
            'net_sales'            => $netSales,
            'total_cash_collected' => $totalCashCollected,
            'total_online'         => $totalOnlineCollected,
            'bkash_sales'          => $bkashSales,
            'nagad_sales'          => $nagadSales,
            'card_sales'           => $cardSales,
            'expected_drawer_cash' => $expectedDrawerCash,
            'current_cash'         => (float) ($activeRegister?->current_cash ?? $expectedDrawerCash),
            'total_bills'          => $totalBills,
            'void_bills'           => $voidBills,
            'total_books_sold'     => $totalQty,
            'date'                 => today()->format('d M, Y'),
            'cashier_name'         => auth()->user()?->name ?? 'Stall Staff',
        ]);
    }

    /**
     * Close Active Register.
     */
    public function closeRegister(Request $request): JsonResponse
    {
        $activeRegister = PosRegister::where('status', 'open')->first();
        if (!$activeRegister) {
            return response()->json(['success' => false, 'message' => 'No active register open.'], 422);
        }

        $closingCash = (float) $request->input('closing_cash', $activeRegister->current_cash);
        $notes = $request->input('notes');

        $activeRegister->update([
            'status'       => 'closed',
            'closing_cash' => $closingCash,
            'closed_by'    => auth()->id(),
            'closed_at'    => now(),
            'notes'        => $notes,
        ]);

        $this->accessService->log('pos_register_close', "Register '{$activeRegister->name}' closed (Cash: ৳{$closingCash})");

        return response()->json([
            'success' => true,
            'message' => 'Cash register closed successfully.',
        ]);
    }

    /**
     * Open a New Register.
     */
    public function openRegister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:150',
            'location'     => 'nullable|string|max:150',
            'opening_cash' => 'required|numeric|min:0',
        ]);

        PosRegister::where('status', 'open')->update(['status' => 'closed', 'closed_at' => now(), 'closed_by' => auth()->id()]);

        $register = PosRegister::create([
            'name'         => $validated['name'],
            'location'     => $validated['location'] ?? 'Amar Ekushey Boi Mela, Dhaka',
            'opening_cash' => (float)$validated['opening_cash'],
            'current_cash' => (float)$validated['opening_cash'],
            'opened_by'    => auth()->id(),
            'status'       => 'open',
        ]);

        $this->accessService->log('pos_register_open', "Opened register '{$register->name}' (Opening: ৳{$register->opening_cash})");

        return response()->json([
            'success'  => true,
            'message'  => 'New register opened successfully!',
            'register' => $register,
        ]);
    }
}
