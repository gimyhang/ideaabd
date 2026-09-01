<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosRegister;
use App\Models\PosSale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Book\Models\Book;

class PosAdminController extends Controller
{
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

        $recentSales = PosSale::with('cashier')->latest()->take(15)->get();
        $todayTotalSales = (float) PosSale::whereDate('created_at', today())->sum('total');
        $todayCash = (float) PosSale::whereDate('created_at', today())->sum('paid_cash');
        $todayOnline = (float) PosSale::whereDate('created_at', today())->sum('paid_online');

        $books = Book::where('stock_quantity', '>', 0)
            ->select('id', 'title', 'price', 'discount_price', 'stock_quantity', 'isbn', 'sku', 'cover_image')
            ->take(100)
            ->get();

        return view('admin.pos.index', compact(
            'activeRegister',
            'recentSales',
            'todayTotalSales',
            'todayCash',
            'todayOnline',
            'books'
        ));
    }

    /**
     * Search books by Title, ISBN or SKU for fast barcode scanner input.
     */
    public function searchBooks(Request $request): JsonResponse
    {
        $q = trim((string)$request->input('q', ''));
        if (!$q) {
            return response()->json([]);
        }

        $books = Book::query()
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('isbn', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            })
            ->select('id', 'title', 'price', 'discount_price', 'stock_quantity', 'isbn', 'sku', 'cover_image')
            ->take(20)
            ->get();

        return response()->json($books);
    }

    /**
     * Process instant POS sale and automatically deduct physical stock.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:books,id',
            'items.*.qty'    => 'required|integer|min:1',
            'items.*.price'  => 'required|numeric|min:0',
            'subtotal'       => 'required|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0',
            'total'          => 'required|numeric|min:0',
            'paid_cash'      => 'nullable|numeric|min:0',
            'paid_online'    => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'customer_name'  => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:50',
        ]);

        $receiptNo = 'POS-' . date('ymd') . '-' . rand(1000, 9999);
        $activeRegister = PosRegister::where('status', 'open')->first();

        // 1. Deduct Stock for all items
        $itemsData = [];
        foreach ($validated['items'] as $item) {
            $book = Book::find($item['id']);
            if ($book) {
                $qty = (int)$item['qty'];
                $book->decrement('stock_quantity', $qty);
                $itemsData[] = [
                    'id'       => $book->id,
                    'title'    => $book->title,
                    'price'    => (float)$item['price'],
                    'quantity' => $qty,
                    'total'    => (float)$item['price'] * $qty,
                ];
            }
        }

        // 2. Create POS Sale Record
        $sale = PosSale::create([
            'register_id'    => $activeRegister?->id,
            'receipt_no'     => $receiptNo,
            'cashier_id'     => auth()->id(),
            'customer_name'  => $validated['customer_name'] ?? 'Walk-in Customer',
            'customer_phone' => $validated['customer_phone'] ?? null,
            'subtotal'       => $validated['subtotal'],
            'discount'       => $validated['discount'] ?? 0.00,
            'total'          => $validated['total'],
            'paid_cash'      => $validated['paid_cash'] ?? $validated['total'],
            'paid_online'    => $validated['paid_online'] ?? 0.00,
            'payment_method' => $validated['payment_method'],
            'items_json'     => $itemsData,
        ]);

        // 3. Update Register Cash
        if ($activeRegister && ($validated['paid_cash'] ?? 0) > 0) {
            $activeRegister->increment('current_cash', (float)$validated['paid_cash']);
        }

        return response()->json([
            'success'    => true,
            'message'    => "বিল #{$receiptNo} সফলভাবে তৈরি ও স্টক আপডেট হয়েছে।",
            'receipt_no' => $receiptNo,
            'sale_id'    => $sale->id,
        ]);
    }

    /**
     * Thermal Receipt Print View.
     */
    public function receipt(int $id): View
    {
        $sale = PosSale::with('cashier', 'register')->findOrFail($id);
        return view('admin.pos.receipt', compact('sale'));
    }
}
