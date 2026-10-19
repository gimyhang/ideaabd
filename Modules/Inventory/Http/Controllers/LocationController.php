<?php

declare(strict_types=1);

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Location;

class LocationController extends Controller
{
    /**
     * List all locations (Warehouses & Showrooms)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Location::withCount(['inventories', 'outgoingTransfers', 'incomingTransfers']);

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $locations = $query->orderBy('type')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    /**
     * Store a new Location
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:locations,code',
            'type' => 'required|in:warehouse,showroom',
            'is_central' => 'boolean',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['is_central']) && $validated['is_central']) {
            // Unset previous central warehouse
            Location::where('is_central', true)->update(['is_central' => false]);
        }

        $location = Location::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'লোকেশন সফলভাবে তৈরি হয়েছে।',
            'data' => $location,
        ], 201);
    }

    /**
     * Show location details with current book stock
     */
    public function show(int $id): JsonResponse
    {
        $location = Location::with(['inventories.book'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $location,
        ]);
    }

    /**
     * Update Location
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $location = Location::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:locations,code,' . $location->id,
            'type' => 'sometimes|required|in:warehouse,showroom',
            'is_central' => 'boolean',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['is_central']) && $validated['is_central']) {
            Location::where('id', '!=', $location->id)->update(['is_central' => false]);
        }

        $location->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'লোকেশন আপডেট হয়েছে।',
            'data' => $location,
        ]);
    }
}
