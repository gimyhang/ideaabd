<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookProductionEvent;
use App\Services\BookProductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Book\Models\Book;

class BookProductionController extends Controller
{
    public function __construct(protected BookProductionService $service) {}

    public function index(Request $request): View
    {
        $from = $request->input('date_from');
        $to = $request->input('date_to');
        $search = $request->string('q')->trim()->value();
        $openOnly = $request->boolean('open');

        $summary = $this->service->summary($from, $to, $search ?: null, $openOnly);

        $totals = [
            'printed'   => $summary->sum('printed'),
            'bound'     => $summary->sum('bound'),
            'received'  => $summary->sum('received'),
            'at_press'  => $summary->sum('at_press'),
            'at_binder' => $summary->sum('at_binder'),
        ];

        $events = BookProductionEvent::with(['book:id,title', 'creator:id,name'])
            ->when($request->filled('stage'), fn ($q) => $q->where('stage', $request->input('stage')))
            ->when($request->filled('book_id'), fn ($q) => $q->where('book_id', $request->integer('book_id')))
            ->when($from, fn ($q) => $q->whereDate('event_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('event_date', '<=', $to))
            ->orderByDesc('event_date')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        $stages = BookProductionEvent::STAGES;

        return view('admin.production.index', compact('summary', 'totals', 'events', 'stages', 'from', 'to', 'search', 'openOnly'));
    }

    public function books(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim()->value();

        $books = Book::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('isbn', 'like', $like));
            })
            ->orderBy('title')->limit(12)->get(['id', 'title', 'isbn', 'stock_quantity']);

        return response()->json($books->map(function ($b) {
            $t = $this->service->totals($b->id);

            return [
                'id'       => $b->id,
                'title'    => $b->title,
                'isbn'     => $b->isbn,
                'stock'    => (int) $b->stock_quantity,
                'at_press' => max(0, $t['printed'] - $t['bound']),
                'at_binder' => max(0, $t['bound'] - $t['received']),
            ];
        })->values());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'book_id'    => 'required|exists:books,id',
            'stage'      => ['required', Rule::in(array_keys(BookProductionEvent::STAGES))],
            'quantity'   => 'required|integer|min:1|max:10000000',
            'event_date' => 'required|date|before_or_equal:today',
            'batch_no'   => 'nullable|string|max:100',
            'vendor'     => 'nullable|string|max:255',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $this->service->record(
            (int) $data['book_id'], $data['stage'], (int) $data['quantity'], $data['event_date'],
            $data['batch_no'] ?? null, $data['vendor'] ?? null, $data['notes'] ?? null, $request->user()?->id
        );

        return back()->with('success', BookProductionEvent::STAGES[$data['stage']] . ' saved.');
    }

    public function destroy(Request $request, BookProductionEvent $event): RedirectResponse
    {
        $this->service->remove($event, $request->user()?->id);

        return back()->with('success', 'Entry removed.');
    }
}
