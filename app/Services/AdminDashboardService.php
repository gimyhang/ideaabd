<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Every aggregate the admin panel needs, in one place.
 *
 * This project's book/author/publisher modules can be deployed without their
 * migrations having run yet, so every read is guarded by a table check and each
 * metric degrades to null ("data unavailable") instead of throwing.
 */
class AdminDashboardService
{
    /** Count of seller/publisher/author signups still awaiting a decision. */
    public function pendingRegistrations(): int
    {
        return $this->safe(fn () => User::whereIn('role', ['seller', 'publisher', 'author'])
            ->where('reg_status', User::STATUS_PENDING)
            ->count(), 0);
    }

    /**
     * Headline KPI tiles. A null value means the underlying table is missing,
     * which the view renders as "—" rather than a misleading zero.
     */
    public function stats(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('admin_dashboard_stats', 20, function () {
            $thisMonth = now()->startOfMonth();

            return [
                'total_books'    => $this->count('books'),
                'total_ebooks'   => $this->count('ebooks'),
                'total_authors'  => $this->count('authors'),
                'total_blog'     => $this->count('blog_posts'),
                'total_webzines' => $this->count('webzines'),
                'total_research' => $this->count('research_papers'),
                'total_tags'     => $this->count('tags'),
                'total_wishlist' => $this->count('wishlists'),

                'total_users'    => $this->safe(fn () => User::count()),
                'new_users_month' => $this->safe(fn () => User::where('created_at', '>=', $thisMonth)->count()),
                'total_sellers'  => $this->safe(fn () => User::where('role', User::ROLE_SELLER)
                    ->where('reg_status', User::STATUS_APPROVED)->count()),
                'total_sub_admins' => $this->safe(fn () => User::where('role', User::ROLE_SUB_ADMIN)->count()),
                'pending_regs'   => $this->pendingRegistrations(),

                'total_orders'   => $this->count('bills'),
                'orders_month'   => $this->safe(fn () => Bill::where('created_at', '>=', $thisMonth)->count()),
                'revenue_total'  => $this->safe(fn () => (float) Bill::sum('total')),
                'revenue_month'  => $this->safe(fn () => (float) Bill::where('created_at', '>=', $thisMonth)->sum('total')),
                'revenue_due'    => $this->safe(fn () => (float) Bill::where('payment_status', '!=', 'paid')->sum('total')),

                'bulk_orders'         => $this->count('bulk_orders'),
                'pending_moderation'  => $this->safe(fn () => (Schema::hasTable('books') ? DB::table('books')->where('mod_status', 'pending')->count() : 0) + (Schema::hasTable('ebooks') ? DB::table('ebooks')->where('mod_status', 'pending')->count() : 0), 0),
            ];
        });
    }

    /**
     * Month-by-month revenue and order count for the dashboard chart.
     * Fast aggregated query using indexed created_at column.
     */
    public function salesSeries(int $months = 12): array
    {
        return \Illuminate\Support\Facades\Cache::remember('admin_sales_series_'.$months, 20, function () use ($months) {
            $labels = $revenue = $orders = [];
            $start  = now()->startOfMonth()->subMonths($months - 1);

            $rows = $this->safe(fn () => Bill::where('created_at', '>=', $start)
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month_key, SUM(total) as rev_sum, COUNT(*) as order_count')
                ->groupBy('month_key')
                ->pluck('rev_sum', 'month_key')
                ->toArray(), []);

            $orderRows = $this->safe(fn () => Bill::where('created_at', '>=', $start)
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month_key, COUNT(*) as order_count')
                ->groupBy('month_key')
                ->pluck('order_count', 'month_key')
                ->toArray(), []);

            for ($i = 0; $i < $months; $i++) {
                $month    = $start->copy()->addMonths($i);
                $key      = $month->format('Y-m');
                $labels[] = \App\Support\Bn::date($month->startOfMonth());
                $revenue[] = round((float) ($rows[$key] ?? 0), 2);
                $orders[]  = (int) ($orderRows[$key] ?? 0);
            }

            return ['labels' => $labels, 'revenue' => $revenue, 'orders' => $orders];
        });
    }

    /** Signups per month, split by role, for the growth chart. */
    public function userGrowth(int $months = 6): array
    {
        return \Illuminate\Support\Facades\Cache::remember('admin_user_growth_'.$months, 20, function () use ($months) {
            $labels = $counts = [];
            $start  = now()->startOfMonth()->subMonths($months - 1);

            $rows = $this->safe(fn () => User::where('created_at', '>=', $start)
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month_key, COUNT(*) as user_count')
                ->groupBy('month_key')
                ->pluck('user_count', 'month_key')
                ->toArray(), []);

            for ($i = 0; $i < $months; $i++) {
                $month    = $start->copy()->addMonths($i);
                $key      = $month->format('Y-m');
                $labels[] = \App\Support\Bn::date($month->startOfMonth());
                $counts[] = (int) ($rows[$key] ?? 0);
            }

            return ['labels' => $labels, 'counts' => $counts];
        });
    }

    /** Users grouped by role, for the role breakdown doughnut. */
    public function roleBreakdown(): array
    {
        return $this->safe(fn () => User::select('role', DB::raw('count(*) as aggregate'))
            ->groupBy('role')
            ->pluck('aggregate', 'role')
            ->toArray(), []);
    }

    public function recentBills(int $limit = 8)
    {
        return $this->safe(fn () => Bill::with('seller')->latest()->limit($limit)->get(), collect());
    }

    public function recentRegistrations(int $limit = 6)
    {
        return $this->safe(fn () => User::whereIn('role', ['seller', 'publisher', 'author'])
            ->where('reg_status', User::STATUS_PENDING)
            ->latest()->limit($limit)->get(), collect());
    }

    /** Top sellers by billed revenue. */
    public function topSellers(int $limit = 5)
    {
        return $this->safe(fn () => Bill::select('seller_id', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as bills'))
            ->groupBy('seller_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->with('seller')
            ->get(), collect());
    }

    // ─── internals ──────────────────────────────────────────────────────

    /** Row count for a table, or null when the table has not been migrated. */
    private function count(string $table): ?int
    {
        if (! $this->hasTable($table)) {
            return null;
        }

        return $this->safe(fn () => DB::table($table)->count());
    }

    private function hasTable(string $table): bool
    {
        return $this->safe(fn () => Schema::hasTable($table), false);
    }

    /** Run a query, swallowing any driver/schema failure. */
    private function safe(callable $query, mixed $fallback = null): mixed
    {
        try {
            return $query();
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
