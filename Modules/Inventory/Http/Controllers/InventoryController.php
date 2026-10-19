<?php

declare(strict_types=1);

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Services\InventoryService;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Stock overview across all locations
     */
    public function overview(Request $request): JsonResponse
    {
        $bookId = $request->query('book_id') ? (int) $request->query('book_id') : null;
        $categoryId = $request->query('category_id') ? (int) $request->query('category_id') : null;
        $perPage = (int) $request->query('per_page', 15);

        $overview = $this->inventoryService->getInventoryOverview($bookId, $categoryId, $perPage);

        return response()->json([
            'success' => true,
            'data' => $overview,
        ]);
    }

    /**
     * Production In:
     * ছাপা/বাঁধাই শেষে সরাসরি Central Warehouse স্টকে Stock In হওয়া
     */
    public function productionIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
            'location_id' => 'nullable|exists:locations,id',
            'batch_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $inventory = $this->inventoryService->recordProductionIn(
                bookId: (int) $validated['book_id'],
                quantity: (int) $validated['quantity'],
                warehouseLocationId: !empty($validated['location_id']) ? (int) $validated['location_id'] : null,
                batchNo: $validated['batch_no'] ?? null,
                notes: $validated['notes'] ?? null,
                userId: $request->user()?->id,
            );

            return response()->json([
                'success' => true,
                'message' => "উৎপাদন/মুদ্রণ শেষে {$validated['quantity']} কপি বই সফলভাবে সেন্ট্রাল ওয়্যারহাউসে স্টক ইন হয়েছে।",
                'data' => $inventory,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Sales Out:
     * শোরুম বা পস (POS) থেকে বিক্রি হলে শুধুমাত্র শোরুমের স্টক থেকে decrement হবে
     */
    public function salesOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|exists:books,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reference_no' => 'nullable|string|max:100',
        ]);

        try {
            $results = $this->inventoryService->recordSaleOut(
                locationId: (int) $validated['location_id'],
                items: $validated['items'],
                referenceNo: $validated['reference_no'] ?? null,
                userId: $request->user()?->id,
            );

            return response()->json([
                'success' => true,
                'message' => 'শোরুম স্টক থেকে সফলভাবে বিক্রয় হ্রাস (Sales Out) রেকর্ড হয়েছে।',
                'data' => $results,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Low Stock Alert:
     * Showroom-এ Low Stock Alert
     */
    public function lowStockAlerts(Request $request): JsonResponse
    {
        $locationId = $request->query('location_id') ? (int) $request->query('location_id') : null;
        $locationType = $request->query('type', 'showroom');

        $alerts = $this->inventoryService->getLowStockAlerts($locationId, $locationType);

        return response()->json([
            'success' => true,
            'total_alerts' => $alerts->count(),
            'data' => $alerts,
        ]);
    }

    /**
     * Inventory Movement Audit Log
     */
    public function movementLogs(Request $request): JsonResponse
    {
        $filters = [
            'book_id' => $request->query('book_id'),
            'location_id' => $request->query('location_id'),
            'type' => $request->query('type'),
        ];
        $perPage = (int) $request->query('per_page', 20);

        $logs = $this->inventoryService->getMovementLogs($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
