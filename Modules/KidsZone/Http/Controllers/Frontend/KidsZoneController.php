<?php

declare(strict_types=1);

namespace Modules\KidsZone\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\KidsZone\Models\KidsZone;
use Modules\KidsZone\Services\KidsZoneService;

class KidsZoneController
{
    private KidsZoneService $service;

    public function __construct(KidsZoneService $service)
    {
        $this->service = $service;
    }

    /**
     * Display all kids zones.
     *
     * @return View
     */
    public function index(): View
    {
        $zones = $this->service->getActive();
        return view('kidszones::frontend.index', compact('zones'));
    }

    /**
     * Display a specific zone with books.
     *
     * @param Request $request
     * @param KidsZone $zone
     * @return View
     */
    public function show(Request $request, KidsZone $zone): View
    {
        $sort = $request->query('sort', 'latest');
        $perPage = $request->query('per_page', 12);

        $books = $zone->books()
            ->when($sort === 'price_asc', function ($q) {
                $q->orderBy('discount_price', 'asc');
            })
            ->when($sort === 'price_desc', function ($q) {
                $q->orderBy('discount_price', 'desc');
            })
            ->when($sort === 'popular', function ($q) {
                $q->orderBy('sales_count', 'desc');
            })
            ->when($sort === 'latest', function ($q) {
                $q->latest();
            })
            ->paginate($perPage);

        return view('kidszones::frontend.show', compact('zone', 'books'));
    }

    /**
     * Get zone data as JSON.
     *
     * @param KidsZone $zone
     * @return \Illuminate\Http\JsonResponse
     */
    public function api(KidsZone $zone)
    {
        $zone->load('books');
        return response()->json($zone);
    }
}
