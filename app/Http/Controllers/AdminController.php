<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\User;
use App\Services\AdminDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function __construct(private readonly AdminDashboardService $dashboard)
    {
    }

    public function index(): View
    {
        return $this->dashboard();
    }

    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'stats'         => $this->dashboard->stats(),
            'sales'         => $this->dashboard->salesSeries(12),
            'growth'        => $this->dashboard->userGrowth(6),
            'roles'         => $this->dashboard->roleBreakdown(),
            'recentBills'   => $this->dashboard->recentBills(6),
            'pendingRegs'   => $this->dashboard->recentRegistrations(5),
            'topSellers'    => $this->dashboard->topSellers(5),
        ]);
    }

    // ─── Users ──────────────────────────────────────────────────────────

    public function users(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->string('search')->trim() . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term));
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', [
            'users'      => $users,
            'roleCounts' => $this->dashboard->roleBreakdown(),
        ]);
    }

    // ─── Orders / bills ─────────────────────────────────────────────────

    public function orders(Request $request): View
    {
        $bills = $this->paginateSafely(
            fn () => Bill::query()
                ->with('seller')
                ->when($request->filled('search'), function ($q) use ($request) {
                    $term = '%' . $request->string('search')->trim() . '%';
                    $q->where(fn ($w) => $w->where('bill_no', 'like', $term)
                        ->orWhere('customer_name', 'like', $term)
                        ->orWhere('customer_phone', 'like', $term));
                })
                ->when($request->filled('status'), fn ($q) => $q->where('payment_status', $request->string('status')))
                ->latest(),
            'bills'
        );

        return view('admin.orders', [
            'bills'   => $bills,
            'summary' => [
                'count'   => $this->dashboard->stats()['total_orders'],
                'revenue' => $this->dashboard->stats()['revenue_total'],
                'due'     => $this->dashboard->stats()['revenue_due'],
            ],
        ]);
    }

    // ─── Catalog & content lists ────────────────────────────────────────

    public function books(Request $request): View
    {
        return view('admin.books', [
            'books' => $this->listing('books', $request, ['title', 'slug']),
        ]);
    }

    public function ebooks(Request $request): View
    {
        return view('admin.ebooks', [
            'ebooks' => $this->listing('ebooks', $request, ['title', 'slug']),
        ]);
    }

    public function blog(Request $request): View
    {
        return view('admin.blog', [
            'posts' => $this->listing('blog_posts', $request, ['title', 'slug']),
        ]);
    }

    public function webzines(Request $request): View
    {
        return view('admin.webzines', [
            'webzines' => $this->listing('webzines', $request, ['title', 'slug']),
        ]);
    }

    public function authors(Request $request): View
    {
        return view('admin.authors', [
            'authors' => $this->listing('authors', $request, ['name', 'slug']),
        ]);
    }

    public function publishers(Request $request): View
    {
        return view('admin.publishers', [
            'publishers' => $this->listing('publishers', $request, ['name', 'slug']),
        ]);
    }

    // ─── internals ──────────────────────────────────────────────────────

    /**
     * Generic paginated listing straight off the query builder.
     *
     * The catalog modules are optional on a given deployment, so this reads the
     * table directly (no model class required) and returns an empty paginator
     * when the table has not been migrated.
     *
     * @param  list<string>  $searchable  columns the search box filters on
     */
    private function listing(string $table, Request $request, array $searchable): mixed
    {
        return $this->paginateSafely(function () use ($table, $request, $searchable) {
            $query = DB::table($table);

            if ($request->filled('search') && $searchable !== []) {
                $term = '%' . $request->string('search')->trim() . '%';
                $query->where(function ($w) use ($searchable, $term, $table) {
                    foreach ($searchable as $column) {
                        if (Schema::hasColumn($table, $column)) {
                            $w->orWhere($column, 'like', $term);
                        }
                    }
                });
            }

            return Schema::hasColumn($table, 'created_at')
                ? $query->orderByDesc('created_at')
                : $query->orderByDesc('id');
        }, $table);
    }

    /**
     * Paginate a query, degrading to an empty paginator if the table is missing
     * or the driver rejects the query — an admin list page should render either
     * way rather than throwing a 500.
     */
    private function paginateSafely(callable $builder, string $table, int $perPage = 20): mixed
    {
        try {
            if (! Schema::hasTable($table)) {
                return $this->emptyPaginator($perPage);
            }

            return $builder()->paginate($perPage)->withQueryString();
        } catch (\Throwable $e) {
            report($e);

            return $this->emptyPaginator($perPage);
        }
    }

    private function emptyPaginator(int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: $perPage,
            currentPage: 1,
            options: ['path' => request()->url(), 'query' => request()->query()],
        );
    }
}
