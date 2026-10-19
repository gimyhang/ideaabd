<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PublisherPayment;
use App\Models\PublisherPurchase;
use App\Models\PublisherPurchaseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Purchase entry for stationery and electronics. Items are catalogue products and every
 * received unit is added to the product stock.
 */
class ItemPurchaseController extends Controller
{
    public const TYPES = ['stationery' => 'Stationery', 'electronics' => 'Electronics'];
    private const PREFIX = ['stationery' => 'STN-', 'electronics' => 'ELC-'];

    public function stationery(): View
    {
        return $this->form('stationery');
    }

    public function electronics(): View
    {
        return $this->form('electronics');
    }

    private function form(string $type): View
    {
        $purchaseNo = $this->nextNumber($type);
        $vendors = PublisherPurchase::whereIn('purchase_category', array_keys(self::TYPES))
            ->whereNotNull('vendor_name')->select('vendor_name', 'vendor_phone')->distinct()->orderBy('vendor_name')->get();
        $label = self::TYPES[$type];

        $categories = ProductCategory::where('type', $type)->orderBy('name')->get(['id', 'name']);

        $stockBlock = $this->stockData(request()->duplicate(['page' => 1]), $type) + ['types' => self::TYPES, 'type' => $type, 'withBuy' => true];

        return view('admin.purchases.item-create', compact('type', 'label', 'purchaseNo', 'vendors', 'categories', 'stockBlock'));
    }

    public function products(Request $request): JsonResponse
    {
        $type = $request->input('type');
        $q = $request->string('q')->trim()->value();

        $rows = Product::query()
            ->when(in_array($type, array_keys(self::TYPES), true), fn ($w) => $w->where('type', $type))
            ->when($q !== '', function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where(fn ($x) => $x->where('title', 'like', $like)->orWhere('sku', 'like', $like)->orWhere('brand', 'like', $like));
            })
            ->orderBy('title')->limit(12)->get(['id', 'title', 'sku', 'stock', 'price']);

        // Most recent buying price per product, so the cost field can be pre-filled.
        $last = PublisherPurchaseItem::whereIn('product_id', $rows->pluck('id'))
            ->orderByDesc('id')->get(['product_id', 'unit_cost_price'])
            ->unique('product_id')->pluck('unit_cost_price', 'product_id');

        return response()->json($rows->map(fn ($p) => [
            'id' => $p->id, 'title' => $p->title, 'sku' => $p->sku, 'stock' => (int) $p->stock, 'price' => (float) $p->price,
            'last_cost' => isset($last[$p->id]) ? (float) $last[$p->id] : null,
        ])->values());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'          => 'required|in:stationery,electronics',
            'purchase_no'   => 'required|string|max:50|unique:publisher_purchases,purchase_no',
            'purchase_date' => 'required|date',
            'due_date'      => 'nullable|date|after_or_equal:purchase_date',
            'vendor_name'   => 'required|string|max:255',
            'vendor_phone'  => 'nullable|string|max:50',
            'memo_no'       => 'nullable|string|max:100',
            'discount'      => 'nullable|numeric|min:0',
            'paid_amount'   => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'transaction_ref' => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:1000',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.title' => 'nullable|string|max:255',
            'items.*.quantity'   => 'required|integer|min:1|max:10000000',
            'items.*.cost_price' => 'required|numeric|min:0',
            'items.*.sale_price' => 'nullable|numeric|min:0',
            'items.*.category_id' => 'nullable|integer|exists:product_categories,id',
        ], [
            'vendor_name.required' => 'Vendor is required.',
            'items.required' => 'Add at least one item.',
        ]);

        foreach ($data['items'] as $i => $row) {
            if (empty($row['product_id']) && trim((string) ($row['title'] ?? '')) === '') {
                throw ValidationException::withMessages(["items.$i.title" => 'Choose an item or enter a name.']);
            }
        }

        $type = $data['type'];
        $subtotal = collect($data['items'])->sum(fn ($r) => (int) $r['quantity'] * (float) $r['cost_price']);
        $discount = round((float) ($data['discount'] ?? 0), 2);
        $paid = round((float) ($data['paid_amount'] ?? 0), 2);
        $grand = round($subtotal - $discount, 2);

        if ($discount > $subtotal + 0.004) {
            throw ValidationException::withMessages(['discount' => 'Discount exceeds the subtotal.']);
        }
        if ($paid > $grand + 0.004) {
            throw ValidationException::withMessages(['paid_amount' => 'Paid exceeds the total.']);
        }

        $purchase = DB::transaction(function () use ($data, $type, $discount, $paid, $request) {
            $purchase = new PublisherPurchase();
            $purchase->purchase_no = $data['purchase_no'];
            $purchase->purchase_category = $type;
            $purchase->publisher_memo_no = $data['memo_no'] ?? null;
            $purchase->supplier_name = $data['vendor_name'];
            $purchase->vendor_name = $data['vendor_name'];
            $purchase->vendor_phone = $data['vendor_phone'] ?? null;
            $purchase->purchase_date = $data['purchase_date'];
            $purchase->due_date = $data['due_date'] ?? null;
            $purchase->payment_type = $paid <= 0 ? 'credit' : 'partial';
            $purchase->installment_count = 1;
            $purchase->discount_amount = $discount;
            $purchase->notes = $data['notes'] ?? null;
            $purchase->created_by = auth()->id();
            $purchase->save();

            foreach ($data['items'] as $row) {
                $qty = (int) $row['quantity'];
                $cost = round((float) $row['cost_price'], 2);
                $sale = isset($row['sale_price']) && $row['sale_price'] !== '' ? round((float) $row['sale_price'], 2) : null;

                $product = !empty($row['product_id']) ? Product::whereKey($row['product_id'])->lockForUpdate()->first() : null;

                // A typed name that already exists is the same product, not a new one.
                if (!$product && trim((string) ($row['title'] ?? '')) !== '') {
                    $product = Product::where('type', $type)
                        ->whereRaw('LOWER(TRIM(title)) = ?', [mb_strtolower(trim((string) $row['title']))])
                        ->lockForUpdate()->first();
                }

                if (!$product) {
                    $title = trim((string) $row['title']);
                    $slug = Str::slug($title) ?: 'item';
                    $base = $slug;
                    $n = 1;
                    while (Product::where('slug', $slug)->exists()) {
                        $slug = $base . '-' . (++$n);
                    }
                    $product = Product::create([
                        'type'     => $type,
                        'category_id' => !empty($row['category_id']) ? (int) $row['category_id'] : null,
                        'title'    => $title,
                        'slug'     => $slug,
                        'sku'      => (Product::SKU_PREFIX[$type] ?? 'PRD') . '-' . strtoupper(Str::random(6)),
                        'price'    => $sale ?? $cost,
                        'stock'    => 0,
                        'stock_status' => 'in_stock',
                        'is_active' => true,
                    ]);
                }

                $product->stock = (int) $product->stock + $qty;
                $product->stock_status = 'in_stock';
                if ($sale !== null) {
                    $product->price = $sale;
                }
                $product->save();

                $item = new PublisherPurchaseItem();
                $item->purchase_id = $purchase->id;
                $item->item_type = 'product';
                $item->item_name = $product->title;
                $item->book_title = $product->title;
                $item->unit = 'Pcs';
                $item->product_id = $product->id;
                $item->quantity = $qty;
                $item->mrp_price = $sale ?? 0;
                $item->unit_cost_price = $cost;
                $item->unit_sale_price = $sale ?? $cost;
                $item->subtotal = round($qty * $cost, 2);
                $item->save();
            }

            if ($paid > 0) {
                PublisherPayment::create([
                    'purchase_id'     => $purchase->id,
                    'publisher_id'    => null,
                    'vendor_name'     => $purchase->vendor_name,
                    'payment_no'      => PublisherPayment::nextPaymentNo(),
                    'payment_date'    => $purchase->purchase_date,
                    'amount'          => $paid,
                    'payment_method'  => $data['payment_method'] ?? 'cash',
                    'transaction_ref' => $data['transaction_ref'] ?? null,
                    'note'            => 'Advance',
                    'recorded_by'     => auth()->id(),
                ]);
            }

            $purchase->recalculate();

            return $purchase;
        });

        return redirect()->route('admin.purchases.show', $purchase->id)->with('success', "Purchase {$purchase->purchase_no} saved.");
    }
    /** Stock list for stationery and electronics. */
    public function stock(Request $request): View
    {
        $type = $request->input('type');
        $types = self::TYPES;
        $search = $request->string('q')->trim()->value();
        $data = $this->stockData($request, $type);

        return view('admin.purchases.item-stock', $data + compact('types', 'type', 'search') + ['withBuy' => false]);
    }

    /** Live stock block embedded in the purchase form (HTML fragment, refreshed by JS). */
    public function stockList(Request $request): View
    {
        $type = array_key_exists($request->input('type'), self::TYPES) ? $request->input('type') : null;

        return view('admin.purchases.partials.item-stock-list', $this->stockData($request, $type) + [
            'types' => self::TYPES, 'type' => $type, 'withBuy' => $request->boolean('buy'),
        ]);
    }

    /** Merge duplicate products (same type) into one: stock is summed and purchase lines are re-pointed. */
    public function merge(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'keep_id' => 'required|integer|exists:products,id',
            'merge_ids' => 'required|array|min:1',
            'merge_ids.*' => 'integer|exists:products,id|different:keep_id',
        ]);

        $merged = DB::transaction(function () use ($data) {
            $keep = Product::whereKey($data['keep_id'])->lockForUpdate()->firstOrFail();
            $others = Product::whereIn('id', $data['merge_ids'])->lockForUpdate()->get();

            if ($others->contains(fn ($p) => $p->type !== $keep->type)) {
                throw ValidationException::withMessages(['merge_ids' => 'Only items of the same type can be merged.']);
            }

            foreach ($others as $other) {
                $keep->stock = (int) $keep->stock + (int) $other->stock;
                $keep->category_id = $keep->category_id ?: $other->category_id;
                $keep->brand = $keep->brand ?: $other->brand;
                PublisherPurchaseItem::where('product_id', $other->id)->update(['product_id' => $keep->id]);
                $other->delete();
            }
            $keep->stock_status = $keep->stock > 0 ? 'in_stock' : $keep->stock_status;
            $keep->save();

            return $others->count();
        });

        $msg = "{$merged} duplicate(s) merged.";

        return $request->wantsJson() ? response()->json(['message' => $msg]) : back()->with('success', $msg);
    }

    private function stockData(Request $request, ?string $type): array
    {
        $scoped = in_array($type, array_keys(self::TYPES), true);
        $search = $request->string('q')->trim()->value();
        $threshold = 5;

        $purchased = PublisherPurchaseItem::query()
            ->whereNotNull('product_id')
            ->select('product_id', DB::raw('SUM(quantity) as qty'), DB::raw('MAX(created_at) as last_at'))
            ->groupBy('product_id');

        $products = Product::query()
            ->leftJoinSub($purchased, 'pp', 'pp.product_id', '=', 'products.id')
            ->whereIn('products.type', array_keys(self::TYPES))
            ->when($scoped, fn ($q) => $q->where('products.type', $type))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(fn ($w) => $w->where('products.title', 'like', $like)->orWhere('products.sku', 'like', $like)->orWhere('products.brand', 'like', $like));
            })
            ->when($request->boolean('low'), fn ($q) => $q->where('products.stock', '<=', $threshold))
            ->when($request->input('category_id') === 'none', fn ($q) => $q->whereNull('products.category_id'))
            ->when($request->filled('category_id') && $request->input('category_id') !== 'none', fn ($q) => $q->where('products.category_id', $request->integer('category_id')))
            ->leftJoin('product_categories as pc', 'pc.id', '=', 'products.category_id')
            ->with('category:id,name')
            ->select('products.*', DB::raw('COALESCE(pp.qty, 0) as purchased_qty'), 'pp.last_at')
            ->orderBy('products.type')->orderByRaw('pc.name is null')->orderBy('pc.name')->orderBy('products.title')
            ->paginate(50)->withQueryString();

        $categories = ProductCategory::whereIn('type', array_keys(self::TYPES))
            ->when($scoped, fn ($q) => $q->where('type', $type))
            ->orderBy('type')->orderBy('name')->get(['id', 'name', 'type']);

        $base = Product::whereIn('type', $scoped ? [$type] : array_keys(self::TYPES));
        $kpis = [
            'items' => (clone $base)->count(),
            'units' => (int) (clone $base)->sum('stock'),
            'low'   => (clone $base)->where('stock', '<=', $threshold)->count(),
            'out'   => (clone $base)->where('stock', '<=', 0)->count(),
            'value' => (float) (clone $base)->sum(DB::raw('stock * price')),
        ];

        // Category menu: one tab per category with its item/unit counts (independent of the category filter).
        $counts = (clone $base)->select('category_id', DB::raw('COUNT(*) as items'), DB::raw('SUM(stock) as units'))->groupBy('category_id')->get()->keyBy(fn ($r) => $r->category_id ?? 'none');
        $tabs = $categories->map(fn ($c) => [
            'key' => (string) $c->id, 'name' => $c->name, 'type' => $c->type,
            'items' => (int) ($counts[$c->id]->items ?? 0), 'units' => (int) ($counts[$c->id]->units ?? 0),
        ])->filter(fn ($t) => $t['items'] > 0)->values();
        if (isset($counts['none'])) {
            $tabs->push(['key' => 'none', 'name' => 'Uncategorized', 'type' => null, 'items' => (int) $counts['none']->items, 'units' => (int) $counts['none']->units]);
        }

        // Probable duplicates: same type and same title once case, spaces and punctuation are ignored.
        $dupes = (clone $base)->get(['id', 'type', 'title', 'sku', 'stock', 'category_id'])
            ->groupBy(fn ($p) => $p->type . '|' . preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($p->title)))
            ->filter(fn ($g) => $g->count() > 1)->values();
        if ($dupes->isNotEmpty()) {
            $used = PublisherPurchaseItem::whereIn('product_id', $dupes->flatten()->pluck('id'))->select('product_id', DB::raw('COUNT(*) as n'))->groupBy('product_id')->pluck('n', 'product_id');
            $dupes = $dupes->map(fn ($g) => $g->sortByDesc(fn ($p) => [(int) ($used[$p->id] ?? 0), (int) $p->stock, -$p->id])->values()
                ->each(fn ($p) => $p->used = (int) ($used[$p->id] ?? 0)));
        }

        return compact('products', 'kpis', 'categories', 'tabs', 'dupes');
    }

    private function nextNumber(string $type): string
    {
        $base = self::PREFIX[$type] . date('Ymd') . '-';
        $last = PublisherPurchase::where('purchase_no', 'like', $base . '%')->pluck('purchase_no')
            ->map(fn ($n) => (int) substr($n, strlen($base)))->max() ?? 0;

        do {
            $candidate = $base . str_pad((string) (++$last), 3, '0', STR_PAD_LEFT);
        } while (PublisherPurchase::where('purchase_no', $candidate)->exists());

        return $candidate;
    }
}
