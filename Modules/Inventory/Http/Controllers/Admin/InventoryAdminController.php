<?php

declare(strict_types=1);

namespace Modules\Inventory\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookProductionEvent;
use App\Services\BookProductionService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Book\Models\Book;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\InventoryMovementLog;
use Modules\Inventory\Models\Location;
use Modules\Inventory\Models\StockTransfer;
use Modules\Inventory\Models\StockWastage;
use Modules\Inventory\Services\InventoryService;

class InventoryAdminController extends Controller
{
    public const WASTAGE_REASONS = [
        'production_defect' => 'Production defect',
        'binding_defect'    => 'Binding defect',
        'transit_damage'    => 'Transit damage',
        'showroom_damage'   => 'Showroom damage',
        'water_damage'      => 'Water damage',
        'lost'              => 'Lost',
        'other'             => 'Other',
    ];

    public const MOVEMENT_TYPES = [
        'opening_balance'   => 'Opening',
        'production_in'     => 'Production in',
        'purchase_in'       => 'Purchase in',
        'purchase_reversal' => 'Purchase reversal',
        'transfer_out'      => 'Transfer out',
        'transfer_in'       => 'Transfer in',
        'sale_out'          => 'Sale out',
        'wastage_out'       => 'Wastage',
        'adjustment_in'     => 'Adjustment in',
        'adjustment_out'    => 'Adjustment out',
    ];

    /** Lifecycle filters on the stock page: key => [label, hint]. */
    public const STAGE_FILTERS = [
        'press'       => 'প্রেসে বাকি',
        'binder'      => 'বাঁধাইখানায়',
        'low'         => 'স্টক কম',
        'empty'       => 'স্টক শূন্য',
        'soldout'     => 'বিক্রি শেষ',
        'unallocated' => 'লোকেশন ছাড়া',
    ];

    public function __construct(protected InventoryService $service, protected BookProductionService $production) {}

    /** Lifecycle of every tracked book, computed once per request. */
    protected ?Collection $lifeCache = null;

    protected function life(): Collection
    {
        return $this->lifeCache ??= $this->production->lifecycle();
    }

    /** Shared query for the stock grid and its CSV export. */
    protected function stockQuery(Request $request)
    {
        $search = $request->string('q')->trim()->value();
        $sort = $request->input('sort', 'title');
        $dir = $request->input('dir') === 'desc' ? 'desc' : 'asc';
        $stage = (string) $request->input('stage', '');

        // Stage filters that depend on the computed lifecycle: pick ids, oldest-waiting first.
        $pick = match ($stage) {
            'press'   => $this->life()->filter(fn ($l) => $l->at_press > 0)->sortBy(fn ($l) => $l->press_since?->timestamp ?? PHP_INT_MAX),
            'binder'  => $this->life()->filter(fn ($l) => $l->at_binder > 0)->sortBy(fn ($l) => $l->binder_since?->timestamp ?? PHP_INT_MAX),
            'soldout' => $this->life()->filter(fn ($l) => $l->in_store <= 0 && $l->at_press === 0 && $l->at_binder === 0 && $l->sold > 0),
            default   => null,
        };
        $ids = $pick?->keys()->map(fn ($id) => (int) $id)->values()->all();

        return Book::query()
            ->when($ids !== null, function ($q) use ($ids, $request) {
                $q->whereIn('books.id', $ids ?: [0]);
                if ($ids && !$request->filled('sort')) {
                    $case = collect($ids)->map(fn ($id, $i) => 'WHEN ' . $id . ' THEN ' . $i)->implode(' ');
                    $q->orderByRaw('CASE books.id ' . $case . ' END');
                }
            })
            ->with(['inventories', 'category:id,name'])
            ->withSum('inventories as located_qty', 'quantity')
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('isbn', 'like', $like)->orWhere('sku', 'like', $like)->orWhere('author_name', 'like', $like));
            })
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->boolean('unallocated') || $stage === 'unallocated', fn ($q) => $q->whereRaw('stock_quantity > COALESCE((select sum(quantity) from inventories where inventories.book_id = books.id), 0)'))
            ->when($request->boolean('low') || $stage === 'low', fn ($q) => $q->whereHas('inventories', fn ($i) => $i->lowStock()))
            ->when($request->boolean('empty') || $stage === 'empty', fn ($q) => $q->where('stock_quantity', '<=', 0))
            ->orderBy(in_array($sort, ['title', 'stock_quantity'], true) ? $sort : 'title', $dir);
    }

    public function export(Request $request)
    {
        $locations = Location::active()->orderByDesc('is_central')->orderBy('type')->orderBy('name')->get();
        $books = $this->stockQuery($request)->get();
        $life = $this->life();

        return response()->streamDownload(function () use ($books, $locations, $life) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_merge(
                ['Title', 'Author', 'Category', 'ISBN', 'Printed', 'At press', 'At press since', 'At binder', 'At binder since', 'Store in', 'In stock', 'Sold / out', 'Press waste', 'Binding waste', 'Store waste', 'Sell-through %'],
                $locations->pluck('name')->all(),
                ['Unallocated']
            ));
            foreach ($books as $b) {
                $by = $b->inventories->keyBy('location_id');
                $l = $life[$b->id] ?? null;
                fputcsv($out, array_merge(
                    [
                        $b->title, $b->author_name, $b->category?->name, $b->isbn,
                        $l->printed ?? 0, $l->at_press ?? 0, $l?->press_since?->format('Y-m-d'), $l->at_binder ?? 0, $l?->binder_since?->format('Y-m-d'),
                        $l->store_in ?? 0, $b->stock_quantity, $l->sold ?? 0,
                        $l->press_waste ?? 0, $l->binder_waste ?? 0, $l->store_waste ?? 0, $l->sell_through ?? 0,
                    ],
                    $locations->map(fn ($loc) => (int) ($by[$loc->id]->quantity ?? 0))->all(),
                    [(int) $b->stock_quantity - (int) ($b->located_qty ?? 0)]
                ));
            }
            fclose($out);
        }, 'book-lifecycle-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function overview(Request $request): View
    {
        $search = $request->string('q')->trim()->value();
        $locations = Location::active()->orderByDesc('is_central')->orderBy('type')->orderBy('name')->get();
        $categories = \Modules\Book\Models\Category::orderBy('name')->get(['id', 'name']);
        $stage = (string) $request->input('stage', '');

        $books = $this->stockQuery($request)->paginate(20)->withQueryString();
        $life = $this->life();

        $stats = [
            'locations'   => Location::active()->count(),
            'located'     => (int) Inventory::sum('quantity'),
            'catalogue'   => (int) Book::sum('stock_quantity'),
            'low'         => Inventory::lowStock()->count(),
            'transit'     => StockTransfer::whereIn('status', ['pending', 'in_transit'])->count(),
            'printed'     => (int) $life->sum('printed'),
            'at_press'    => (int) $life->sum('at_press'),
            'at_binder'   => (int) $life->sum('at_binder'),
            'sold'        => (int) $life->sum('sold'),
            'press_waste' => (int) $life->sum('press_waste'),
            'binder_waste'=> (int) $life->sum('binder_waste'),
            'store_waste' => (int) $life->sum('store_waste'),
            'press_titles'  => $life->where('at_press', '>', 0)->count(),
            'binder_titles' => $life->where('at_binder', '>', 0)->count(),
            'soldout'     => $life->filter(fn ($l) => $l->in_store <= 0 && $l->at_press === 0 && $l->at_binder === 0 && $l->sold > 0)->count(),
            'press_oldest'  => $life->pluck('press_since')->filter()->min(),
            'binder_oldest' => $life->pluck('binder_since')->filter()->min(),
            'empty'       => Book::where('stock_quantity', '<=', 0)->count(),
        ];
        $stats['unallocated'] = max(0, $stats['catalogue'] - $stats['located']);
        $stats['waste'] = $stats['press_waste'] + $stats['binder_waste'] + $stats['store_waste'];

        $stageFilters = self::STAGE_FILTERS;

        return view('inventory::admin.index', compact('books', 'locations', 'stats', 'search', 'categories', 'life', 'stage', 'stageFilters'));
    }

    /** One title's full journey: print runs, binding lots, where copies sit now and every movement. */
    public function bookLedger(int $id): View
    {
        $book = Book::with(['category:id,name', 'inventories.location'])->findOrFail($id);
        $life = $this->production->lifecycle([$book->id])[$book->id] ?? null;
        $runs = $this->production->runs($book->id);

        $production = BookProductionEvent::with('creator:id,name')->where('book_id', $book->id)->get()
            ->map(fn ($e) => (object) [
                'at'    => $e->event_date->copy()->setTimeFrom($e->created_at ?? now()),
                'kind'  => $e->stage,
                'label' => BookProductionEvent::STAGES_BN[$e->stage] ?? $e->stage,
                'qty'   => (int) $e->quantity,
                'place' => $e->vendor,
                'ref'   => $e->batch_no,
                'note'  => $e->notes,
                'by'    => $e->creator?->name,
                'id'    => $e->id,
            ]);

        $wastes = StockWastage::with(['location:id,name', 'reporter:id,name'])->where('book_id', $book->id)->get()
            ->map(fn ($w) => (object) [
                'at'    => $w->created_at,
                'kind'  => 'store_waste',
                'label' => 'নষ্ট · স্টোরে',
                'qty'   => (int) $w->quantity,
                'place' => $w->location?->name,
                'ref'   => self::WASTAGE_REASONS[$w->reason] ?? $w->reason,
                'note'  => $w->notes,
                'by'    => $w->reporter?->name,
                'id'    => null,
            ]);

        // Store movements other than the ones already shown above (received → production_in, wastage_out).
        $moves = InventoryMovementLog::with(['location:id,name', 'user:id,name'])->where('book_id', $book->id)
            ->whereNotIn('type', ['production_in', 'wastage_out'])
            ->latest('id')->limit(300)->get()
            ->map(fn ($m) => (object) [
                'at'    => $m->created_at,
                'kind'  => $m->type,
                'label' => self::MOVEMENT_TYPES[$m->type] ?? ucfirst(str_replace('_', ' ', $m->type)),
                'qty'   => (int) $m->quantity_change,
                'place' => $m->location?->name,
                'ref'   => null,
                'note'  => $m->notes,
                'by'    => $m->user?->name,
                'id'    => null,
            ]);

        $timeline = $production->concat($wastes)->concat($moves)->sortByDesc('at')->values();
        $locations = Location::active()->orderByDesc('is_central')->orderBy('type')->orderBy('name')->get();

        return view('inventory::admin.book', compact('book', 'life', 'runs', 'timeline', 'locations'));
    }

    public function searchBooks(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = $request->string('q')->trim()->value();
        $location = $request->integer('location_id') ?: null;

        $books = Book::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('isbn', 'like', $like)->orWhere('sku', 'like', $like));
            })
            ->orderBy('title')->limit(12)->get(['id', 'title', 'isbn', 'stock_quantity']);

        $here = $location
            ? Inventory::where('location_id', $location)->whereIn('book_id', $books->pluck('id'))->pluck('quantity', 'book_id')
            : collect();

        $life = $books->isEmpty() ? collect() : $this->production->lifecycle($books->pluck('id')->all());

        return response()->json($books->map(fn ($b) => [
            'id'     => $b->id,
            'title'  => $b->title,
            'isbn'   => $b->isbn,
            'stock'  => (int) $b->stock_quantity,
            'here'   => $location ? (int) ($here[$b->id] ?? 0) : null,
            'press'  => (int) ($life[$b->id]->at_press ?? 0),
            'binder' => (int) ($life[$b->id]->at_binder ?? 0),
        ])->values());
    }

    public function stockIn(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'book_id'     => 'required|exists:books,id',
            'quantity'    => 'required|integer|min:1|max:1000000',
            'location_id' => 'nullable|exists:locations,id',
            'batch_no'    => 'nullable|string|max:100',
            'notes'       => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->recordProductionIn(
                bookId: (int) $data['book_id'],
                quantity: (int) $data['quantity'],
                warehouseLocationId: !empty($data['location_id']) ? (int) $data['location_id'] : null,
                batchNo: $data['batch_no'] ?? null,
                notes: $data['notes'] ?? null,
                userId: $request->user()?->id,
            );
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Stock added.');
    }

    public function openingBalance(Request $request): RedirectResponse
    {
        $count = $this->service->backfillOpeningStock($request->user()?->id);

        return back()->with($count ? 'success' : 'info', $count ? "{$count} books placed in the central warehouse." : 'Nothing to allocate.');
    }

    // ── Locations ────────────────────────────────────────────

    public function locations(): View
    {
        $locations = Location::withCount(['outgoingTransfers', 'incomingTransfers'])
            ->withSum('inventories as units', 'quantity')
            ->withCount('inventories as titles')
            ->orderByDesc('is_central')->orderBy('type')->orderBy('name')->get();

        return view('inventory::admin.locations', compact('locations'));
    }

    public function storeLocation(Request $request): RedirectResponse
    {
        $data = $this->locationData($request);
        if (!empty($data['is_central'])) {
            Location::where('is_central', true)->update(['is_central' => false]);
        }
        Location::create($data);

        return back()->with('success', 'Location saved.');
    }

    public function updateLocation(Request $request, int $id): RedirectResponse
    {
        $location = Location::findOrFail($id);
        $data = $this->locationData($request, $location->id);
        if (!empty($data['is_central'])) {
            Location::where('id', '!=', $location->id)->update(['is_central' => false]);
        }
        $location->update($data);

        return back()->with('success', 'Location updated.');
    }

    private function locationData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => ['required', 'string', 'max:50', Rule::unique('locations', 'code')->ignore($ignoreId)],
            'type'           => 'required|in:warehouse,showroom',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
        ]);
        $data['is_central'] = $request->boolean('is_central') && $data['type'] === 'warehouse';
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    // ── Transfers ────────────────────────────────────────────

    public function transfers(Request $request): View
    {
        $transfers = $this->service->getChallanHistory([
            'challan_no'       => $request->query('challan_no'),
            'status'           => $request->query('status'),
            'from_location_id' => $request->query('from_location_id'),
            'to_location_id'   => $request->query('to_location_id'),
            'date_from'        => $request->query('date_from'),
            'date_to'          => $request->query('date_to'),
        ], 15)->withQueryString();

        $locations = Location::active()->orderBy('name')->get();

        return view('inventory::admin.transfers', compact('transfers', 'locations'));
    }

    public function showTransfer(int $id): View
    {
        $transfer = StockTransfer::with(['fromLocation', 'toLocation', 'items.book', 'creator', 'receiver'])->findOrFail($id);

        return view('inventory::admin.transfer-show', compact('transfer'));
    }

    public function storeTransfer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_location_id'  => 'required|exists:locations,id',
            'to_location_id'    => 'required|exists:locations,id|different:from_location_id',
            'items'             => 'required|array|min:1',
            'items.*.book_id'   => 'required|exists:books,id',
            'items.*.quantity'  => 'required|integer|min:1',
            'notes'             => 'nullable|string|max:1000',
        ]);

        try {
            $transfer = $this->service->createTransfer(
                fromLocationId: (int) $data['from_location_id'],
                toLocationId: (int) $data['to_location_id'],
                items: array_values($data['items']),
                notes: $data['notes'] ?? null,
                userId: $request->user()?->id,
            );
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.inventory.transfers.show', $transfer->id)->with('success', "Challan {$transfer->challan_no} created.");
    }

    public function receiveTransfer(Request $request, int $id): RedirectResponse
    {
        try {
            $this->service->receiveTransfer(transferId: $id, receivedItemMap: null, userId: $request->user()?->id);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Transfer received.');
    }

    public function cancelTransfer(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate(['reason' => 'nullable|string|max:500']);

        try {
            $this->service->cancelTransfer(transferId: $id, reason: $data['reason'] ?? null, userId: $request->user()?->id);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Transfer cancelled.');
    }

    // ── Wastage ──────────────────────────────────────────────

    public function wastages(Request $request): View
    {
        $wastages = StockWastage::with(['book', 'location', 'reporter'])
            ->when($request->filled('location_id'), fn ($q) => $q->where('location_id', $request->query('location_id')))
            ->when($request->filled('reason'), fn ($q) => $q->where('reason', $request->query('reason')))
            ->latest('id')->paginate(15)->withQueryString();

        $locations = Location::active()->orderBy('name')->get();
        $reasons = self::WASTAGE_REASONS;

        return view('inventory::admin.wastages', compact('wastages', 'locations', 'reasons'));
    }

    public function storeWastage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'book_id'      => 'required|exists:books,id',
            'location_id'  => 'required|exists:locations,id',
            'quantity'     => 'required|integer|min:1',
            'reason'       => ['required', Rule::in(array_keys(self::WASTAGE_REASONS))],
            'action_taken' => 'nullable|string|max:100',
            'notes'        => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->recordWastage(
                bookId: (int) $data['book_id'],
                locationId: (int) $data['location_id'],
                quantity: (int) $data['quantity'],
                reason: $data['reason'],
                actionTaken: $data['action_taken'] ?? 'disposed',
                notes: $data['notes'] ?? null,
                userId: $request->user()?->id,
            );
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Wastage recorded.');
    }

    // ── Alerts & logs ────────────────────────────────────────

    public function alerts(Request $request): View
    {
        $type = $request->query('type', '');
        $alerts = $this->service->getLowStockAlerts(
            $request->filled('location_id') ? (int) $request->query('location_id') : null,
            $type !== '' ? $type : null
        );
        $locations = Location::active()->orderBy('name')->get();

        return view('inventory::admin.alerts', compact('alerts', 'locations', 'type'));
    }

    public function updateThreshold(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate(['min_alert_quantity' => 'required|integer|min:0|max:1000000']);
        Inventory::findOrFail($id)->update($data);

        return back()->with('success', 'Threshold updated.');
    }

    public function logs(Request $request): View
    {
        $logs = $this->service->getMovementLogs([
            'book_id'     => $request->query('book_id'),
            'location_id' => $request->query('location_id'),
            'type'        => $request->query('type'),
        ], 25)->withQueryString();

        $locations = Location::orderBy('name')->get();
        $types = self::MOVEMENT_TYPES;

        return view('inventory::admin.logs', compact('logs', 'locations', 'types'));
    }
}
