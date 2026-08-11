<?php

declare(strict_types=1);

namespace Modules\BulkOrder\Http\Controllers\Frontend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BulkOrder\Models\BulkOrder;
use Modules\BulkOrder\Services\BulkOrderService;

class BulkOrderController
{
    private BulkOrderService $service;

    public function __construct(BulkOrderService $service)
    {
        $this->service = $service;
    }

    /**
     * Display bulk order form.
     *
     * @return View
     */
    public function create(): View
    {
        $discountInfo = [
            'quantities' => [
                ['min' => 20, 'max' => 49, 'discount' => 5],
                ['min' => 50, 'max' => 99, 'discount' => 10],
                ['min' => 100, 'max' => 199, 'discount' => 15],
                ['min' => 200, 'max' => 499, 'discount' => 20],
                ['min' => 500, 'max' => PHP_INT_MAX, 'discount' => 25],
            ],
            'educational_discounts' => [
                'school' => 20,
                'college' => 25,
                'university' => 30,
                'library' => 35,
            ],
        ];

        return view('bulkorder::frontend.create', compact('discountInfo'));
    }

    /**
     * Store a new bulk order.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_type' => 'required|in:educational,commercial,bulk_purchase',
            'institution_name' => 'required|string',
            'institution_type' => 'required|in:school,college,university,library,bookstore',
            'contact_person' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|exists:books,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_requirements' => 'nullable|string',
        ]);

        try {
            $validated['user_id'] = auth()->id();
            
            // Calculate totals
            $totalQuantity = array_sum(array_column($validated['items'], 'quantity'));
            $totalAmount = 0;
            
            foreach ($validated['items'] as $item) {
                $book = \Modules\Book\Models\Book::find($item['book_id']);
                $totalAmount += $book->discount_price * $item['quantity'];
            }

            // Apply discount
            $discount = $this->service->getEducationalDiscount($validated['institution_type']);
            
            $orderData = [
                'order_type' => $validated['order_type'],
                'institution_name' => $validated['institution_name'],
                'institution_type' => $validated['institution_type'],
                'contact_person' => $validated['contact_person'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'quantity' => $totalQuantity,
                'total_amount' => $totalAmount,
                'discount_percentage' => $discount,
                'special_requirements' => $validated['special_requirements'] ?? null,
                'user_id' => $validated['user_id'],
            ];

            $order = $this->service->create($orderData);
            
            // Add items
            $this->service->addItems($order, $validated['items']);

            return response()->json([
                'message' => 'Bulk order request submitted successfully',
                'order_id' => $order->id,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display my orders.
     *
     * @return View
     */
    public function myOrders(): View
    {
        $orders = auth()->user()->bulkOrders()->latest()->paginate(10);
        return view('bulkorder::frontend.my-orders', compact('orders'));
    }

    /**
     * Display order details.
     *
     * @param BulkOrder $order
     * @return View
     */
    public function show(BulkOrder $order): View
    {
        if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $order->load('items.book');
        return view('bulkorder::frontend.show', compact('order'));
    }
}
