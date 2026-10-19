<?php

declare(strict_types=1);

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\StockTransfer;
use Modules\Inventory\Services\InventoryService;

class StockTransferController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Transfer Challan History Log with Filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'challan_no' => $request->query('challan_no'),
            'status' => $request->query('status'),
            'from_location_id' => $request->query('from_location_id'),
            'to_location_id' => $request->query('to_location_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];
        $perPage = (int) $request->query('per_page', 15);

        $history = $this->inventoryService->getChallanHistory($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Create Stock Transfer Challan (Warehouse → Showroom)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id|different:from_location_id',
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|exists:books,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.remarks' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $transfer = $this->inventoryService->createTransfer(
                fromLocationId: (int) $validated['from_location_id'],
                toLocationId: (int) $validated['to_location_id'],
                items: $validated['items'],
                notes: $validated['notes'] ?? null,
                userId: $request->user()?->id,
            );

            return response()->json([
                'success' => true,
                'message' => "চালান নং '{$transfer->challan_no}' সফলভাবে তৈরি হয়েছে।",
                'data' => $transfer,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show single transfer challan with line items
     */
    public function show(int $id): JsonResponse
    {
        $transfer = StockTransfer::with([
            'fromLocation',
            'toLocation',
            'items.book',
            'creator',
            'receiver'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transfer,
        ]);
    }

    /**
     * Receive Transfer Challan (শোরুম রিসিভ করলে DB Transaction-এ স্টক ট্রান্সফার সম্পন্ন)
     */
    public function receive(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'received_items' => 'nullable|array',
            'received_items.*' => 'integer|min:0',
        ]);

        try {
            $transfer = $this->inventoryService->receiveTransfer(
                transferId: $id,
                receivedItemMap: $validated['received_items'] ?? null,
                userId: $request->user()?->id,
            );

            return response()->json([
                'success' => true,
                'message' => "চালান নং '{$transfer->challan_no}' সফলভাবে রিসিভ করা হয়েছে এবং গোডাউন থেকে শোরুমে স্টক স্থানান্তর হয়েছে।",
                'data' => $transfer,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel Transfer Challan
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $transfer = $this->inventoryService->cancelTransfer(
                transferId: $id,
                reason: $validated['reason'] ?? 'বাতিল করা হয়েছে',
                userId: $request->user()?->id,
            );

            return response()->json([
                'success' => true,
                'message' => "চালান নং '{$transfer->challan_no}' বাতিল করা হয়েছে।",
                'data' => $transfer,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
