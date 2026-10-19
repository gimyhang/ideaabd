<?php

declare(strict_types=1);

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\StockWastage;
use Modules\Inventory\Services\InventoryService;

class StockWastageController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * List all damaged/defective book logs
     */
    public function index(Request $request): JsonResponse
    {
        $query = StockWastage::with(['book', 'location', 'reporter'])->latest('id');

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->query('location_id'));
        }

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->query('book_id'));
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->query('reason'));
        }

        $perPage = (int) $request->query('per_page', 15);
        $wastages = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $wastages,
        ]);
    }

    /**
     * Record wastage / damaged books
     * ছাপা, বাঁধাই বা পরিবহনের ত্রুটিপূর্ণ বই আলাদা অ্যাডজাস্ট করার অপশন
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|in:production_defect,binding_defect,transit_damage,showroom_damage,water_damage,lost,other',
            'action_taken' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $wastage = $this->inventoryService->recordWastage(
                bookId: (int) $validated['book_id'],
                locationId: (int) $validated['location_id'],
                quantity: (int) $validated['quantity'],
                reason: $validated['reason'],
                actionTaken: $validated['action_taken'] ?? 'disposed',
                notes: $validated['notes'] ?? null,
                userId: $request->user()?->id,
            );

            return response()->json([
                'success' => true,
                'message' => 'ত্রুটিপূর্ণ/নষ্ট বইয়ের রেকর্ড সংরক্ষিত হয়েছে এবং স্টক সমন্বয় করা হয়েছে।',
                'data' => $wastage,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
