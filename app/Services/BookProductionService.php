<?php

namespace App\Services;

use App\Models\BookProductionEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Book\Models\Book;
use Modules\Inventory\Services\InventoryService;

/**
 * Book lifecycle, from the press to the reader:
 *
 *   printed ─┬─ bound ─┬─ received (store) ─┬─ in stock
 *            │         │                    ├─ sold / given out
 *            │         │                    └─ store waste      (stock_wastages)
 *            │         ├─ at binder  = bound - received - binder_waste
 *            │         └─ binder_waste
 *            ├─ at press = printed - bound - press_waste
 *            └─ press_waste
 *
 * Binding of one print run is often spread over 2–3 years, so every stage is booked on its own
 * date and may be split across many entries. Ageing is worked out first-in-first-out.
 */
class BookProductionService
{
    /**
     * Movement types that bring copies into the store (net). "received" production entries write a
     * production_in log (and production_reversal when removed), so they are counted here too, together
     * with direct stock-in, opening balances and purchases. Transfers net out; sales and store waste are outflows.
     */
    public const STORE_IN_TYPES = ['production_in', 'production_reversal', 'opening_balance', 'purchase_in', 'purchase_reversal'];

    public function __construct(protected InventoryService $inventory) {}

    /** 12,345 → ১২,৩৪৫ */
    public static function bn(int|float|string|null $n, int $decimals = 0): string
    {
        $s = is_numeric($n) ? number_format((float) $n, $decimals) : (string) $n;

        return strtr($s, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    }

    /** How long ago, in Bangla: "২ বছর ৩ মাস", "৫ মাস", "১২ দিন". */
    public static function age(?\DateTimeInterface $since): ?string
    {
        if (!$since) {
            return null;
        }
        $d = Carbon::parse($since)->startOfDay()->diff(now()->startOfDay());
        if ($d->y > 0) {
            return self::bn($d->y) . ' বছর' . ($d->m ? ' ' . self::bn($d->m) . ' মাস' : '');
        }
        if ($d->m > 0) {
            return self::bn($d->m) . ' মাস';
        }

        return $d->days === 0 ? 'আজ' : self::bn($d->days) . ' দিন';
    }

    /** Ageing tone for a waiting stage: older than a year is red, older than six months amber. */
    public static function ageTone(?\DateTimeInterface $since): string
    {
        if (!$since) {
            return '';
        }
        $days = Carbon::parse($since)->diffInDays(now());

        return $days > 365 ? 'is-old' : ($days > 180 ? 'is-aging' : 'is-fresh');
    }

    /**
     * Per-book stage totals. Date range limits what is *counted* (activity in the window);
     * balances (press / binder) are always lifetime so they never go negative by filtering.
     */
    public function summary(?string $from = null, ?string $to = null, ?string $search = null, bool $openOnly = false)
    {
        $rows = BookProductionEvent::query()
            ->select('book_id', 'stage', DB::raw('SUM(quantity) as qty'))
            ->groupBy('book_id', 'stage')
            ->get()
            ->groupBy('book_id');

        $windowed = BookProductionEvent::query()
            ->when($from, fn ($q) => $q->whereDate('event_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('event_date', '<=', $to))
            ->select('book_id', 'stage', DB::raw('SUM(quantity) as qty'))
            ->groupBy('book_id', 'stage')
            ->get()
            ->groupBy('book_id');

        $bookIds = $rows->keys();

        $books = Book::query()
            ->whereIn('id', $bookIds)
            ->when($search, function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('isbn', 'like', $like));
            })
            ->with('category:id,name')
            ->orderBy('title')
            ->get(['id', 'title', 'isbn', 'stock_quantity', 'category_id', 'author_name']);

        $out = $books->map(function ($book) use ($rows, $windowed) {
            $life = $this->stageTotals($rows[$book->id]->pluck('qty', 'stage'));
            $win = ($windowed[$book->id] ?? collect())->pluck('qty', 'stage');

            return (object) [
                'book'        => $book,
                'printed'     => (int) ($win['printed'] ?? 0),
                'bound'       => (int) ($win['bound'] ?? 0),
                'received'    => (int) ($win['received'] ?? 0),
                'wasted'      => (int) ($win['press_waste'] ?? 0) + (int) ($win['binder_waste'] ?? 0),
                'at_press'    => $this->atPress($life),
                'at_binder'   => $this->atBinder($life),
                'life'        => $life,
            ];
        });

        return $openOnly ? $out->filter(fn ($r) => $r->at_press > 0 || $r->at_binder > 0)->values() : $out->values();
    }

    public function totals(int $bookId): array
    {
        $t = BookProductionEvent::where('book_id', $bookId)
            ->select('stage', DB::raw('SUM(quantity) as qty'))->groupBy('stage')->pluck('qty', 'stage');

        return $this->stageTotals($t);
    }

    protected function stageTotals($t): array
    {
        $out = [];
        foreach (array_keys(BookProductionEvent::STAGES) as $stage) {
            $out[$stage] = (int) ($t[$stage] ?? 0);
        }

        return $out;
    }

    public function atPress(array $t): int
    {
        return max(0, $t['printed'] - $t['bound'] - $t['press_waste']);
    }

    public function atBinder(array $t): int
    {
        return max(0, $t['bound'] - $t['received'] - $t['binder_waste']);
    }

    /**
     * Full lifecycle per book, keyed by book id. Only books that have production entries,
     * store inflow logs or store wastage are returned; anything else is plain catalogue stock.
     */
    public function lifecycle(?array $bookIds = null): Collection
    {
        $scope = fn ($q) => $bookIds === null ? $q : $q->whereIn('book_id', $bookIds);

        $prod = $scope(BookProductionEvent::query())
            ->select('book_id', 'stage', DB::raw('SUM(quantity) as qty'))
            ->groupBy('book_id', 'stage')->get()->groupBy('book_id');

        $logIn = $scope(DB::table('inventory_movement_logs'))
            ->whereIn('type', self::STORE_IN_TYPES)
            ->selectRaw('book_id, SUM(quantity_change) as qty')
            ->groupBy('book_id')->pluck('qty', 'book_id');

        $storeWaste = $scope(DB::table('stock_wastages'))
            ->selectRaw('book_id, SUM(quantity) as qty')
            ->groupBy('book_id')->pluck('qty', 'book_id');

        $ids = $prod->keys()->merge($logIn->keys())->merge($storeWaste->keys())->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        $stock = Book::whereIn('id', $ids)->pluck('stock_quantity', 'id');

        // Dated runs for FIFO ageing (only books that still have copies at press or binder need them).
        $runs = BookProductionEvent::whereIn('book_id', $prod->keys())
            ->whereIn('stage', ['printed', 'bound'])
            ->orderBy('event_date')->orderBy('id')
            ->get(['book_id', 'stage', 'quantity', 'event_date'])
            ->groupBy(fn ($e) => $e->book_id . ':' . $e->stage);

        return $ids->mapWithKeys(function ($id) use ($prod, $logIn, $storeWaste, $stock, $runs) {
            $t = $this->stageTotals(($prod[$id] ?? collect())->pluck('qty', 'stage'));
            $atPress = $this->atPress($t);
            $atBinder = $this->atBinder($t);
            $sWaste = (int) ($storeWaste[$id] ?? 0);
            $inStore = max(0, (int) ($stock[$id] ?? 0));
            // Logged inflow, but never less than what the production ledger says was received.
            $storeIn = max((int) ($logIn[$id] ?? 0), $t['received']);
            $other = $storeIn - $t['received'];
            $sold = max(0, $storeIn - $sWaste - $inStore);
            $waste = $t['press_waste'] + $t['binder_waste'] + $sWaste;
            $total = $sold + $inStore + $atBinder + $atPress + $waste;

            $pressSince = $atPress > 0
                ? $this->oldestOpen($runs[$id . ':printed'] ?? collect(), $t['bound'] + $t['press_waste']) : null;
            $binderSince = $atBinder > 0
                ? $this->oldestOpen($runs[$id . ':bound'] ?? collect(), $t['received'] + $t['binder_waste']) : null;

            return [$id => (object) [
                'printed'      => $t['printed'],
                'bound'        => $t['bound'],
                'received'     => $t['received'],
                'other_in'     => $other,
                'store_in'     => $storeIn,
                'press_waste'  => $t['press_waste'],
                'binder_waste' => $t['binder_waste'],
                'store_waste'  => $sWaste,
                'waste'        => $waste,
                'at_press'     => $atPress,
                'at_binder'    => $atBinder,
                'in_store'     => $inStore,
                'sold'         => $sold,
                'total'        => $total,
                'sell_through' => $total > 0 ? round($sold / $total * 100, 1) : 0.0,
                'press_since'  => $pressSince,
                'binder_since' => $binderSince,
            ]];
        });
    }

    /** Date of the oldest entry that is not yet fully consumed (FIFO). */
    protected function oldestOpen(Collection $events, int $consumed): ?Carbon
    {
        foreach ($events as $e) {
            if ($consumed < $e->quantity) {
                return Carbon::parse($e->event_date);
            }
            $consumed -= $e->quantity;
        }

        return null;
    }

    /**
     * Print runs and binding lots of one book with what is still open in each (FIFO).
     *
     * @return array{printed: Collection, bound: Collection}
     */
    public function runs(int $bookId): array
    {
        $t = $this->totals($bookId);
        $events = BookProductionEvent::where('book_id', $bookId)->whereIn('stage', ['printed', 'bound'])
            ->orderBy('event_date')->orderBy('id')->get();

        $allocate = function (Collection $rows, int $consumed) {
            return $rows->map(function ($e) use (&$consumed) {
                $done = min((int) $e->quantity, $consumed);
                $consumed -= $done;

                return (object) ['event' => $e, 'done' => $done, 'open' => (int) $e->quantity - $done];
            });
        };

        return [
            'printed' => $allocate($events->where('stage', 'printed')->values(), $t['bound'] + $t['press_waste']),
            'bound'   => $allocate($events->where('stage', 'bound')->values(), $t['received'] + $t['binder_waste']),
        ];
    }

    public function record(int $bookId, string $stage, int $quantity, string $date, ?string $batch, ?string $vendor, ?string $notes, ?int $userId, ?int $locationId = null): BookProductionEvent
    {
        return DB::transaction(function () use ($bookId, $stage, $quantity, $date, $batch, $vendor, $notes, $userId, $locationId) {
            $book = Book::whereKey($bookId)->lockForUpdate()->firstOrFail();
            $t = $this->totals($book->id);

            if (in_array($stage, ['bound', 'press_waste'], true) && $quantity > $this->atPress($t)) {
                $room = $this->atPress($t);
                throw ValidationException::withMessages(['quantity' => "প্রেসে মাত্র " . self::bn($room) . " কপি বাঁধাইয়ের অপেক্ষায় আছে। (Only {$room} printed copies are waiting at the press.)"]);
            }
            if (in_array($stage, ['received', 'binder_waste'], true) && $quantity > $this->atBinder($t)) {
                $room = $this->atBinder($t);
                throw ValidationException::withMessages(['quantity' => "বাঁধাইখানায় মাত্র " . self::bn($room) . " কপি আছে। (Only {$room} bound copies are at the binder.)"]);
            }

            $event = BookProductionEvent::create([
                'book_id'    => $book->id,
                'stage'      => $stage,
                'quantity'   => $quantity,
                'event_date' => $date,
                'batch_no'   => $batch,
                'vendor'     => $vendor,
                'notes'      => $notes,
                'created_by' => $userId,
            ]);

            if ($stage === 'received') {
                // Adds to the book total and the chosen (or central) warehouse, and writes the movement log.
                $this->inventory->recordProductionIn($book->id, $quantity, $locationId, $batch, $notes ?: "Received {$date}", $userId);
            }

            return $event;
        });
    }

    public function remove(BookProductionEvent $event, ?int $userId): void
    {
        DB::transaction(function () use ($event, $userId) {
            $book = Book::whereKey($event->book_id)->lockForUpdate()->firstOrFail();
            $t = $this->totals($book->id);

            if ($event->stage === 'printed' && ($t['printed'] - $event->quantity) < $t['bound'] + $t['press_waste']) {
                throw ValidationException::withMessages(['event' => 'Copies from this run are already bound or written off. Remove those entries first.']);
            }
            if ($event->stage === 'bound' && ($t['bound'] - $event->quantity) < $t['received'] + $t['binder_waste']) {
                throw ValidationException::withMessages(['event' => 'Copies from this lot are already received or written off. Remove those entries first.']);
            }

            if ($event->stage === 'received') {
                $take = min((int) $event->quantity, (int) $book->stock_quantity);
                if ($take > 0) {
                    $book->decrement('stock_quantity', $take);
                }
                $this->inventory->mirrorBookStock($book->id, -$take, 'production_reversal', 'BookProductionEvent', $event->id, 'Received entry removed', $userId);
            }

            $event->delete();
        });
    }
}
