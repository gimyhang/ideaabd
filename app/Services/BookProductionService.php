<?php

namespace App\Services;

use App\Models\BookProductionEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Book\Models\Book;
use Modules\Inventory\Services\InventoryService;

/**
 * Printed → Bound → Received (office). Each stage is booked on its own date and may be
 * split across many dates for the same title:
 *   at press   = printed - bound
 *   at binder  = bound   - received
 *   at office  = received   (this is what enters stock)
 */
class BookProductionService
{
    public function __construct(protected InventoryService $inventory) {}

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
            ->orderBy('title')
            ->get(['id', 'title', 'isbn', 'stock_quantity']);

        $out = $books->map(function ($book) use ($rows, $windowed) {
            $life = $rows[$book->id]->pluck('qty', 'stage');
            $win = ($windowed[$book->id] ?? collect())->pluck('qty', 'stage');

            $printed = (int) ($life['printed'] ?? 0);
            $bound = (int) ($life['bound'] ?? 0);
            $received = (int) ($life['received'] ?? 0);

            return (object) [
                'book'        => $book,
                'printed'     => (int) ($win['printed'] ?? 0),
                'bound'       => (int) ($win['bound'] ?? 0),
                'received'    => (int) ($win['received'] ?? 0),
                'at_press'    => max(0, $printed - $bound),
                'at_binder'   => max(0, $bound - $received),
                'life'        => compact('printed', 'bound', 'received'),
            ];
        });

        return $openOnly ? $out->filter(fn ($r) => $r->at_press > 0 || $r->at_binder > 0)->values() : $out->values();
    }

    public function totals(int $bookId): array
    {
        $t = BookProductionEvent::where('book_id', $bookId)
            ->select('stage', DB::raw('SUM(quantity) as qty'))->groupBy('stage')->pluck('qty', 'stage');

        return ['printed' => (int) ($t['printed'] ?? 0), 'bound' => (int) ($t['bound'] ?? 0), 'received' => (int) ($t['received'] ?? 0)];
    }

    public function record(int $bookId, string $stage, int $quantity, string $date, ?string $batch, ?string $vendor, ?string $notes, ?int $userId): BookProductionEvent
    {
        return DB::transaction(function () use ($bookId, $stage, $quantity, $date, $batch, $vendor, $notes, $userId) {
            $book = Book::whereKey($bookId)->lockForUpdate()->firstOrFail();
            $t = $this->totals($book->id);

            if ($stage === 'bound' && $t['bound'] + $quantity > $t['printed']) {
                $room = max(0, $t['printed'] - $t['bound']);
                throw ValidationException::withMessages(['quantity' => "Only {$room} printed copies are waiting to be bound."]);
            }
            if ($stage === 'received' && $t['received'] + $quantity > $t['bound']) {
                $room = max(0, $t['bound'] - $t['received']);
                throw ValidationException::withMessages(['quantity' => "Only {$room} bound copies are waiting to be received."]);
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
                // Adds to the book total and the central warehouse, and writes the movement log.
                $this->inventory->recordProductionIn($book->id, $quantity, null, $batch, $notes ?: "Received {$date}", $userId);
            }

            return $event;
        });
    }

    public function remove(BookProductionEvent $event, ?int $userId): void
    {
        DB::transaction(function () use ($event, $userId) {
            $book = Book::whereKey($event->book_id)->lockForUpdate()->firstOrFail();
            $t = $this->totals($book->id);

            if ($event->stage === 'printed' && ($t['printed'] - $event->quantity) < $t['bound']) {
                throw ValidationException::withMessages(['event' => 'Copies from this run are already bound. Remove the bound entries first.']);
            }
            if ($event->stage === 'bound' && ($t['bound'] - $event->quantity) < $t['received']) {
                throw ValidationException::withMessages(['event' => 'Copies from this run are already received. Remove the received entries first.']);
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
