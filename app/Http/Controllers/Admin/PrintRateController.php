<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrintRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Raw-material rate sheet behind the printing cost calculator.
 * One row per (group, item, kind): paper 80 GSM White, lamination Matt, binding Perfect, packing Poly …
 */
class PrintRateController extends Controller
{
    public function index(): JsonResponse
    {
        PrintRate::seedStarterIfEmpty();

        return response()->json($this->rows());
    }

    /** Add a row or change the rate of an existing one. */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $rate = PrintRate::query()->updateOrCreate(
            ['group' => $data['group'], 'item' => $data['item'], 'kind' => $data['kind']],
            ['rate' => $data['rate'], 'note' => $data['note'] ?? null, 'updated_by' => $request->user()?->id]
        );

        return response()->json(['ok' => true, 'row' => $this->present($rate), 'created' => $rate->wasRecentlyCreated]);
    }

    public function destroy(PrintRate $rate): JsonResponse
    {
        $rate->delete();

        return response()->json(['ok' => true]);
    }

    /** Raise or lower many rates at once, by a percentage or a fixed amount. Never below zero. */
    public function adjust(Request $request): JsonResponse
    {
        $data = $request->validate([
            'group' => ['nullable', Rule::in(PrintRate::GROUPS)],
            'mode'  => ['required', Rule::in(['percent', 'amount'])],
            'value' => ['required', 'numeric', 'between:-100000,100000'],
        ]);

        if ($data['mode'] === 'percent' && (float) $data['value'] <= -100) {
            return response()->json(['ok' => false, 'message' => 'Percent must be above -100.'], 422);
        }

        $changed = 0;
        DB::transaction(function () use ($data, $request, &$changed) {
            PrintRate::query()
                ->when($data['group'] ?? null, fn ($q, $g) => $q->where('group', $g))
                ->get()
                ->each(function (PrintRate $row) use ($data, $request, &$changed) {
                    $current = (float) $row->rate;
                    $next = $data['mode'] === 'percent'
                        ? $current * (1 + (float) $data['value'] / 100)
                        : $current + (float) $data['value'];
                    $next = round(max(0, $next), 2);

                    if ($next !== round($current, 2)) {
                        $row->update(['rate' => $next, 'updated_by' => $request->user()?->id]);
                        $changed++;
                    }
                });
        });

        return response()->json(['ok' => true, 'changed' => $changed, 'rows' => $this->rows()]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'group' => ['required', Rule::in(PrintRate::GROUPS)],
            'item'  => ['nullable', 'string', 'max:60'],
            'kind'  => ['nullable', 'string', 'max:60'],
            'rate'  => ['required', 'numeric', 'min:0', 'max:99999999'],
            'note'  => ['nullable', 'string', 'max:150'],
        ]);

        $data['item'] = trim((string) ($data['item'] ?? ''));
        $data['kind'] = trim((string) ($data['kind'] ?? ''));
        $data['rate'] = round((float) $data['rate'], 2);

        return $data;
    }

    private function rows(): array
    {
        $order = array_flip(PrintRate::GROUPS);

        return PrintRate::query()->get()
            ->sortBy(fn ($r) => [$order[$r->group] ?? 99, is_numeric($r->item) ? (float) $r->item : 9999, mb_strtolower($r->item), mb_strtolower($r->kind)])
            ->map(fn ($r) => $this->present($r))
            ->values()
            ->all();
    }

    private function present(PrintRate $r): array
    {
        return ['id' => $r->id, 'group' => $r->group, 'item' => $r->item, 'kind' => $r->kind, 'rate' => (float) $r->rate, 'note' => $r->note];
    }
}
