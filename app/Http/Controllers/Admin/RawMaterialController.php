<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\RawMaterialMove;
use App\Support\RawMaterialStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Book\Models\Book;

/**
 * Raw-material stock book: what the press bought (from raw-material purchases), what it used for which
 * book / job, what is left and what it is worth.
 */
class RawMaterialController extends Controller
{
    public function index(Request $request): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        RawMaterialStock::ensureSchema();

        $group = $request->string('group')->trim()->value();
        $search = $request->string('search')->trim()->value();
        $status = $request->string('status')->trim()->value(); // low | out | in | inactive

        $materials = RawMaterial::query()
            ->when($group && isset(RawMaterial::GROUPS[$group]), fn ($q) => $q->where('group', $group))
            ->when($search, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('spec', 'like', $like)->orWhere('notes', 'like', $like));
            })
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false), fn ($q) => $q->where('is_active', true))
            ->orderBy('group')->orderBy('name')
            ->get();

        $totals = RawMaterialStock::totals();
        $empty = RawMaterialStock::emptyTotals();
        $materials->each(function ($m) use ($totals, $empty) {
            $m->t = $totals[$m->id] ?? $empty;
            $bal = $m->t['balance'];
            $m->state = !$m->isStocked() ? 'service'
                : ($bal <= 0 ? 'out' : ((float) $m->reorder_level > 0 && $bal <= (float) $m->reorder_level ? 'low' : 'in'));
        });
        if (in_array($status, ['low', 'out', 'in'], true)) {
            $materials = $materials->filter(fn ($m) => $status === 'low' ? in_array($m->state, ['low', 'out'], true) : $m->state === $status)->values();
        }

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($materials);
        }

        // KPIs over all active materials (not only the filtered ones)
        $all = RawMaterial::where('is_active', true)->get(['id', 'group', 'reorder_level']);
        $stocked = $all->filter(fn ($m) => $m->isStocked());
        $monthStart = now()->startOfMonth()->toDateString();
        $month = DB::table('raw_material_moves')->where('moved_at', '>=', $monthStart)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END), 0) AS bought,
                         COALESCE(SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END), 0) AS used")->first();
        $kpi = [
            'materials' => $stocked->count(),
            'value'     => $stocked->sum(fn ($m) => $totals[$m->id]['value'] ?? 0),
            'bought'    => (float) $month->bought,
            'used'      => (float) $month->used,
            'low'       => $stocked->filter(function ($m) use ($totals) {
                $bal = $totals[$m->id]['balance'] ?? 0;
                return $bal <= 0 ? ($totals[$m->id]['in_qty'] ?? 0) > 0 : ((float) $m->reorder_level > 0 && $bal <= (float) $m->reorder_level);
            })->count(),
            'services'  => $all->reject(fn ($m) => $m->isStocked())->sum(fn ($m) => $totals[$m->id]['in_amount'] ?? 0),
        ];

        // last 6 months: bought vs used (value)
        $from = now()->subMonths(5)->startOfMonth();
        $monthly = [];
        for ($i = 0; $i < 6; $i++) {
            $monthly[$from->copy()->addMonths($i)->format('Y-m')] = ['in' => 0.0, 'out' => 0.0];
        }
        DB::table('raw_material_moves')->where('moved_at', '>=', $from->toDateString())->whereIn('type', ['in', 'out'])
            ->get(['moved_at', 'type', 'amount'])
            ->each(function ($r) use (&$monthly) {
                $k = substr((string) $r->moved_at, 0, 7);
                if (isset($monthly[$k])) {
                    $monthly[$k][$r->type] += (float) $r->amount;
                }
            });

        $groupCounts = RawMaterial::where('is_active', true)->selectRaw('`group` AS g, COUNT(*) AS n')->groupBy('group')->pluck('n', 'g');
        $recent = RawMaterialMove::with(['material:id,name,spec,unit', 'purchase:id,purchase_no', 'book:id,title'])
            ->latest('moved_at')->latest('id')->limit(12)->get();
        $books = Book::query()->where('is_active', true)->latest('id')->limit(400)->pluck('title')->unique()->values();

        return view('admin.purchases.raw-materials', compact('materials', 'kpi', 'monthly', 'groupCounts', 'recent', 'books', 'group', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        RawMaterialStock::ensureSchema();
        $data = $request->validate([
            'name'          => 'required|string|max:200',
            'group'         => ['required', Rule::in(array_keys(RawMaterial::GROUPS))],
            'unit'          => 'required|string|max:30',
            'spec'          => 'nullable|string|max:200',
            'reorder_level' => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string|max:1000',
            'opening_qty'   => 'nullable|numeric|min:0',
            'opening_rate'  => 'nullable|numeric|min:0',
            'opening_date'  => 'nullable|date',
        ]);
        $key = RawMaterialStock::key($data['name'], $data['spec'] ?? null, $data['unit']);
        if (RawMaterial::where('key', $key)->exists()) {
            return back()->withInput()->with('error', 'এই নাম, মাপ ও এককের উপকরণ আগে থেকেই আছে।');
        }

        DB::transaction(function () use ($data, $key) {
            $m = RawMaterial::create([
                'key' => $key, 'name' => trim($data['name']), 'group' => $data['group'], 'unit' => trim($data['unit']),
                'spec' => trim((string) ($data['spec'] ?? '')) ?: null, 'reorder_level' => (float) ($data['reorder_level'] ?? 0),
                'notes' => $data['notes'] ?? null,
            ]);
            if ((float) ($data['opening_qty'] ?? 0) > 0) {
                $qty = (float) $data['opening_qty'];
                $rate = (float) ($data['opening_rate'] ?? 0);
                RawMaterialMove::create([
                    'raw_material_id' => $m->id, 'type' => 'opening', 'qty' => $qty, 'unit_cost' => $rate,
                    'amount' => round($qty * $rate, 2), 'note' => 'শুরুর মজুদ',
                    'moved_at' => $data['opening_date'] ?? now()->toDateString(), 'created_by' => auth()->id(),
                ]);
            }
        });

        return back()->with('success', 'নতুন উপকরণ যোগ হয়েছে।');
    }

    public function update(Request $request, RawMaterial $material): RedirectResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:200',
            'group'         => ['required', Rule::in(array_keys(RawMaterial::GROUPS))],
            'unit'          => 'required|string|max:30',
            'spec'          => 'nullable|string|max:200',
            'reorder_level' => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string|max:1000',
            'is_active'     => 'nullable|boolean',
        ]);
        // the matching key stays as it was, so later purchase lines with the old wording still land here
        $material->fill([
            'name' => trim($data['name']), 'group' => $data['group'], 'unit' => trim($data['unit']),
            'spec' => trim((string) ($data['spec'] ?? '')) ?: null, 'reorder_level' => (float) ($data['reorder_level'] ?? 0),
            'notes' => $data['notes'] ?? null, 'is_active' => $request->boolean('is_active', true),
        ])->save();

        return back()->with('success', "“{$material->name}” হালনাগাদ হয়েছে।");
    }

    /** Use for a book / job, count correction, or opening stock. */
    public function storeMove(Request $request): RedirectResponse|JsonResponse
    {
        RawMaterialStock::ensureSchema();
        $data = $request->validate([
            'raw_material_id' => 'required|integer|exists:raw_materials,id',
            'type'            => 'required|in:out,adjust,opening',
            'direction'       => 'nullable|in:plus,minus',
            'qty'             => 'required|numeric|gt:0|max:99999999',
            'unit_cost'       => 'nullable|numeric|min:0',
            'moved_at'        => 'required|date|before_or_equal:' . now()->addDay()->toDateString(),
            'job'             => 'nullable|string|max:250',
            'note'            => 'nullable|string|max:500',
        ]);
        $m = RawMaterial::findOrFail($data['raw_material_id']);
        if (!$m->isStocked()) {
            return $this->fail($request, 'সেবা / বিলের কোনো মজুদ নেই — ব্যবহার বা সমন্বয় লাগে না।');
        }
        $qty = round((float) $data['qty'], 3);
        $date = \Carbon\Carbon::parse($data['moved_at'])->toDateString();
        $t = RawMaterialStock::totals([$m->id])[$m->id] ?? RawMaterialStock::emptyTotals();
        $avg = (float) $t['avg_cost'];

        $minus = $data['type'] === 'out' || ($data['type'] === 'adjust' && ($data['direction'] ?? 'minus') === 'minus');
        if ($minus && $qty > $t['balance'] + 0.0005) {
            return $this->fail($request, 'মজুদে আছে ' . rtrim(rtrim(number_format($t['balance'], 3), '0'), '.') . ' ' . $m->unit . ' — এর বেশি বাদ দেওয়া যাবে না।');
        }

        // book from the catalogue when the title matches, else kept as the job name
        $job = trim((string) ($data['job'] ?? ''));
        $bookId = $job !== '' ? Book::where('title', $job)->value('id') : null;

        $rate = $data['type'] === 'opening' ? (float) ($data['unit_cost'] ?? 0) : ($data['type'] === 'adjust' && !$minus && isset($data['unit_cost']) && $data['unit_cost'] !== null && $data['unit_cost'] !== '' ? (float) $data['unit_cost'] : $avg);
        $signed = $data['type'] === 'adjust' && $minus ? -$qty : $qty;

        $move = RawMaterialMove::create([
            'raw_material_id' => $m->id,
            'type'            => $data['type'],
            'qty'             => $signed,
            'unit_cost'       => round($rate, 4),
            'amount'          => round($signed * $rate, 2),
            'book_id'         => $bookId,
            'job'             => $job !== '' ? mb_substr($job, 0, 250) : null,
            'note'            => $data['note'] ?? null,
            'moved_at'        => $date,
            'created_by'      => auth()->id(),
        ]);

        $label = RawMaterialMove::TYPES[$move->type];
        if ($request->expectsJson()) {
            $nt = RawMaterialStock::totals([$m->id])[$m->id];
            return response()->json(['ok' => true, 'message' => "{$label} সংরক্ষিত।", 'balance' => $nt['balance'], 'value' => $nt['value']]);
        }

        return back()->with('success', "{$m->name}: {$label} " . rtrim(rtrim(number_format($qty, 3), '0'), '.') . " {$m->unit} সংরক্ষিত।");
    }

    public function destroyMove(RawMaterialMove $move): RedirectResponse
    {
        if ($move->purchase_id) {
            return back()->with('error', 'ক্রয়ের এন্ট্রি এখান থেকে মোছা যায় না — ক্রয় ইনভয়েসটি সম্পাদনা করুন।');
        }
        $move->delete();

        return back()->with('success', 'এন্ট্রি মুছে ফেলা হয়েছে।');
    }

    /** Ledger of one material (modal body). */
    public function ledger(Request $request, RawMaterial $material): View
    {
        $from = $request->input('from') && strtotime($request->input('from')) ? date('Y-m-d', strtotime($request->input('from'))) : null;
        $to = $request->input('to') && strtotime($request->input('to')) ? date('Y-m-d', strtotime($request->input('to'))) : null;
        $ledger = RawMaterialStock::ledger($material, $from, $to);
        $t = RawMaterialStock::totals([$material->id])[$material->id] ?? RawMaterialStock::emptyTotals();

        return view('admin.purchases.partials.raw-ledger', compact('material', 'ledger', 't', 'from', 'to'));
    }

    /** Re-reads all raw-material purchases into the stock book. */
    public function resync(): RedirectResponse
    {
        RawMaterialStock::ensureSchema();
        $n = RawMaterialStock::backfill();

        return back()->with('success', "{$n}টি কাঁচামাল ক্রয় ইনভয়েস থেকে মজুদ হিসাব মিলিয়ে নেওয়া হয়েছে।");
    }

    private function fail(Request $request, string $msg): RedirectResponse|JsonResponse
    {
        return $request->expectsJson()
            ? response()->json(['ok' => false, 'message' => $msg], 422)
            : back()->withInput()->with('error', $msg);
    }

    private function exportCsv($materials): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($materials) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Material', 'Spec', 'Group', 'Unit', 'In', 'Used', 'Adjust', 'Balance', 'Avg cost', 'Value', 'Reorder level', 'Bought (Tk)', 'Used (Tk)']);
            foreach ($materials as $m) {
                $t = $m->t;
                fputcsv($out, [$m->name, $m->spec, $m->groupLabel(), $m->unit, $t['in_qty'], $t['out_qty'], $t['adj_qty'], $t['balance'],
                    number_format($t['avg_cost'], 2, '.', ''), number_format($t['value'], 2, '.', ''), (float) $m->reorder_level,
                    number_format($t['in_amount'], 2, '.', ''), number_format($t['out_amount'], 2, '.', '')]);
            }
            fclose($out);
        }, 'raw-material-stock-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
