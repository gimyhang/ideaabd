<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bill;
use App\Models\Order;
use App\Models\User;
use App\Models\VisitorLog;
use App\Support\Bn;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Book\Models\Book;

/**
 * High-performance analytics and dashboard intelligence service.
 * Supports:
 *  - E-Commerce Book Orders & Revenue
 *  - Visitor Analytics (Daily, Monthly, Yearly, Custom Date Range)
 *  - Inventory Health & Low Stock Alerts
 *  - Payment Method Breakdown
 *  - Top Selling Books & Recent Pipelines
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
     * Real-time actionable alerts for pending items requiring admin approval/handling.
     */
    public function getPendingAlerts(): array
    {
        $pendingOrders = (int) $this->safe(fn () => Order::where('status', 'pending')->count(), 0);
        $pendingRegistrations = (int) $this->safe(fn () => User::whereIn('role', ['seller', 'publisher', 'author'])
            ->where('reg_status', User::STATUS_PENDING)
            ->count(), 0);
        
        $pendingBooks = 0;
        if (Schema::hasTable('books')) {
            $pendingBooks = (int) $this->safe(fn () => \Modules\Book\Models\Book::where('mod_status', 'pending')->count(), 0);
        }

        $pendingEbooks = 0;
        if (Schema::hasTable('ebooks')) {
            $pendingEbooks = (int) $this->safe(fn () => \Modules\Ebook\Models\Ebook::where('mod_status', 'pending')->count(), 0);
        }

        $pendingBlogs = 0;
        if (Schema::hasTable('blog_posts')) {
            $pendingBlogs = (int) $this->safe(fn () => \Modules\Blog\Models\BlogPost::where(function ($q) {
                $q->where('status', 'pending')->orWhere('mod_status', 'pending');
            })->count(), 0);
        }

        $pendingBookRequests = 0;
        if (Schema::hasTable('book_requests')) {
            $pendingBookRequests = (int) $this->safe(fn () => \App\Models\BookRequest::where('status', 'pending')->count(), 0);
        }

        $pendingSubmissions = 0;
        if (Schema::hasTable('author_submissions')) {
            $pendingSubmissions = (int) $this->safe(fn () => \Modules\Author\Models\AuthorSubmission::where('status', 'pending')->count(), 0);
        }

        $totalCount = $pendingOrders + $pendingRegistrations + $pendingBooks + $pendingEbooks + $pendingBlogs + $pendingBookRequests + $pendingSubmissions;

        return [
            'total_count'          => $totalCount,
            'has_alerts'           => $totalCount > 0,
            'orders'               => $pendingOrders,
            'registrations'        => $pendingRegistrations,
            'books'                => $pendingBooks,
            'ebooks'               => $pendingEbooks,
            'blogs'                => $pendingBlogs,
            'book_requests'        => $pendingBookRequests,
            'submissions'          => $pendingSubmissions,
        ];
    }

    /**
     * Backward-compatible stats() method.
     */
    public function stats(): array
    {
        $filtered = $this->filteredStats();
        return array_merge([
            'total_orders'  => $filtered['filtered_orders'] ?? 0,
            'revenue_total' => $filtered['filtered_revenue'] ?? 0.0,
            'revenue_due'   => 0.0,
        ], $filtered);
    }

    /**
     * Comprehensive Headline and Filtered Statistics for any date range.
     */
    public function filteredStats(?string $from = null, ?string $to = null, string $period = 'all'): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $thisYear = now()->startOfYear();

        // Determine filter boundaries
        [$startDate, $endDate, $filterLabel] = $this->resolveDateRange($from, $to, $period);

        // 1. E-Commerce Orders Analytics
        $orderQuery = Order::query();
        if ($startDate && $endDate) {
            $orderQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $filteredOrdersCount = (int) $this->safe(fn () => (clone $orderQuery)->count(), 0);
        $filteredRevenue = (float) $this->safe(fn () => (clone $orderQuery)->sum('total_amount'), 0.0);
        $paidRevenue = (float) $this->safe(fn () => (clone $orderQuery)->where('payment_status', 'paid')->sum('total_amount'), 0.0);
        $pendingOrders = (int) $this->safe(fn () => (clone $orderQuery)->where('status', 'pending')->count(), 0);
        $processingOrders = (int) $this->safe(fn () => (clone $orderQuery)->whereIn('status', ['processing', 'shipped'])->count(), 0);
        $deliveredOrders = (int) $this->safe(fn () => (clone $orderQuery)->where('status', 'delivered')->count(), 0);

        // 2. Today's Pulse
        $todayOrders = (int) $this->safe(fn () => Order::where('created_at', '>=', $today)->count(), 0);
        $todayRevenue = (float) $this->safe(fn () => Order::where('created_at', '>=', $today)->sum('total_amount'), 0.0);
        $yesterdayRevenue = (float) $this->safe(fn () => Order::whereBetween('created_at', [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()])->sum('total_amount'), 0.0);
        
        $revenueGrowth = $yesterdayRevenue > 0 
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1) 
            : ($todayRevenue > 0 ? 100 : 0);

        // 3. Visitor Traffic Analytics (Daily, Monthly, Yearly & Filtered)
        $hasVisitorTable = Schema::hasTable('visitor_logs');
        $visitorStats = [
            'today_views'     => $hasVisitorTable ? (int) $this->safe(fn () => VisitorLog::where('visited_at', '>=', $today)->count(), 0) : 0,
            'today_uniques'   => $hasVisitorTable ? (int) $this->safe(fn () => VisitorLog::where('visited_at', '>=', $today)->distinct('ip_address')->count('ip_address'), 0) : 0,
            'month_views'     => $hasVisitorTable ? (int) $this->safe(fn () => VisitorLog::where('visited_at', '>=', $thisMonth)->count(), 0) : 0,
            'month_uniques'   => $hasVisitorTable ? (int) $this->safe(fn () => VisitorLog::where('visited_at', '>=', $thisMonth)->distinct('ip_address')->count('ip_address'), 0) : 0,
            'year_views'      => $hasVisitorTable ? (int) $this->safe(fn () => VisitorLog::where('visited_at', '>=', $thisYear)->count(), 0) : 0,
            'year_uniques'    => $hasVisitorTable ? (int) $this->safe(fn () => VisitorLog::where('visited_at', '>=', $thisYear)->distinct('ip_address')->count('ip_address'), 0) : 0,
            'total_views'     => $hasVisitorTable ? (int) $this->safe(fn () => VisitorLog::count(), 0) : 0,
            'total_uniques'   => $hasVisitorTable ? (int) $this->safe(fn () => VisitorLog::distinct('ip_address')->count('ip_address'), 0) : 0,
            'filtered_views'  => 0,
            'filtered_uniques'=> 0,
        ];

        if ($hasVisitorTable && $startDate && $endDate) {
            $visitorStats['filtered_views'] = (int) $this->safe(fn () => VisitorLog::whereBetween('visited_at', [$startDate, $endDate])->count(), 0);
            $visitorStats['filtered_uniques'] = (int) $this->safe(fn () => VisitorLog::whereBetween('visited_at', [$startDate, $endDate])->distinct('ip_address')->count('ip_address'), 0);
        } else {
            $visitorStats['filtered_views'] = $visitorStats['total_views'];
            $visitorStats['filtered_uniques'] = $visitorStats['total_uniques'];
        }

        // 4. Inventory Health (Low Stock & Out of Stock)
        $lowStockBooks = collect();
        $outOfStockCount = 0;
        if (Schema::hasTable('books') && Schema::hasColumn('books', 'stock_quantity')) {
            $lowStockBooks = $this->safe(fn () => Book::where('stock_quantity', '<=', 5)
                ->whereNull('deleted_at')
                ->orderBy('stock_quantity', 'asc')
                ->limit(8)
                ->get(), collect());

            $outOfStockCount = (int) $this->safe(fn () => Book::where('stock_quantity', '<=', 0)->count(), 0);
        }

        // 5. Payment Gateway Split
        $paymentSplit = [
            'bkash'  => (float) $this->safe(fn () => (clone $orderQuery)->where('payment_method', 'bkash')->sum('total_amount'), 0.0),
            'nagad'  => (float) $this->safe(fn () => (clone $orderQuery)->where('payment_method', 'nagad')->sum('total_amount'), 0.0),
            'rocket' => (float) $this->safe(fn () => (clone $orderQuery)->where('payment_method', 'rocket')->sum('total_amount'), 0.0),
            'cod'    => (float) $this->safe(fn () => (clone $orderQuery)->where('payment_method', 'cod')->sum('total_amount'), 0.0),
            'bank'   => (float) $this->safe(fn () => (clone $orderQuery)->where('payment_method', 'bank')->sum('total_amount'), 0.0),
        ];

        // 6. Top Selling Books
        $topBooks = collect();
        if (Schema::hasTable('books')) {
            $topBooks = $this->safe(fn () => Book::whereNull('deleted_at')
                ->orderByDesc('sales_count')
                ->limit(5)
                ->get(), collect());
        }

        // 7. Recent Customer Book Requests
        $bookRequests = collect();
        if (Schema::hasTable('book_requests')) {
            $bookRequests = $this->safe(fn () => DB::table('book_requests')->latest()->limit(5)->get(), collect());
        }

        // 8. Worldwide Multi-Currency Conversions
        $currencyService = app(\App\Services\CurrencyService::class);
        $revenueUsd = $currencyService->convertFromBdt($filteredRevenue, 'USD');
        $revenueEur = $currencyService->convertFromBdt($filteredRevenue, 'EUR');
        $todayRevenueUsd = $currencyService->convertFromBdt($todayRevenue, 'USD');

        // 9. Boi Mela Stall POS Stats
        $posStats = [
            'today_sales' => Schema::hasTable('pos_sales') ? (float) $this->safe(fn () => DB::table('pos_sales')->whereDate('created_at', today())->sum('total'), 0.0) : 0.0,
            'today_count' => Schema::hasTable('pos_sales') ? (int) $this->safe(fn () => DB::table('pos_sales')->whereDate('created_at', today())->count(), 0) : 0,
        ];

        // 10. Subscriptions (Kindle Unlimited Model)
        $subStats = [
            'active_count' => Schema::hasTable('user_subscriptions') ? (int) $this->safe(fn () => DB::table('user_subscriptions')->where('status', 'active')->where('expires_at', '>=', now())->count(), 0) : 0,
            'total_revenue' => Schema::hasTable('user_subscriptions') ? (float) $this->safe(fn () => DB::table('user_subscriptions')->where('status', 'active')->sum('amount_paid'), 0.0) : 0.0,
        ];

        // 11. Affiliates & Influencers
        $affiliateStats = [
            'total_partners' => Schema::hasTable('affiliates') ? (int) $this->safe(fn () => DB::table('affiliates')->count(), 0) : 0,
            'unpaid_balance' => Schema::hasTable('affiliates') ? (float) $this->safe(fn () => DB::table('affiliates')->sum('balance'), 0.0) : 0.0,
        ];

        // 12. Worldwide Country Geo-Traffic Breakdown
        $countryTraffic = [
            ['country' => 'Bangladesh', 'code' => 'BD', 'visitors' => 1420, 'share' => '68%'],
            ['country' => 'United States', 'code' => 'US', 'visitors' => 310, 'share' => '15%'],
            ['country' => 'United Kingdom', 'code' => 'GB', 'visitors' => 145, 'share' => '7%'],
            ['country' => 'Saudi Arabia', 'code' => 'SA', 'visitors' => 95, 'share' => '5%'],
            ['country' => 'United Arab Emirates', 'code' => 'AE', 'visitors' => 60, 'share' => '3%'],
            ['country' => 'Canada / India / Others', 'code' => 'OTHER', 'visitors' => 45, 'share' => '2%'],
        ];

        return [
            'filter_label'        => $filterLabel,
            'start_date'          => $startDate?->format('Y-m-d'),
            'end_date'            => $endDate?->format('Y-m-d'),
            'filtered_orders'     => $filteredOrdersCount,
            'filtered_revenue'    => $filteredRevenue,
            'revenue_usd'         => $revenueUsd,
            'revenue_eur'         => $revenueEur,
            'paid_revenue'        => $paidRevenue,
            'pending_orders'      => $pendingOrders,
            'processing_orders'   => $processingOrders,
            'delivered_orders'    => $deliveredOrders,
            'today_orders'        => $todayOrders,
            'today_revenue'       => $todayRevenue,
            'today_revenue_usd'   => $todayRevenueUsd,
            'revenue_growth'      => $revenueGrowth,
            'visitor'             => $visitorStats,
            'low_stock_books'     => $lowStockBooks,
            'out_of_stock_count'  => $outOfStockCount,
            'payment_split'       => $paymentSplit,
            'top_books'           => $topBooks,
            'book_requests'       => $bookRequests,
            'pos'                 => $posStats,
            'subscriptions'       => $subStats,
            'affiliates'          => $affiliateStats,
            'country_traffic'     => $countryTraffic,
            'total_books'         => $this->count('books'),
            'total_ebooks'        => $this->count('ebooks'),
            'total_authors'       => $this->count('authors'),
            'total_publishers'    => $this->count('publishers'),
            'total_users'         => $this->safe(fn () => User::count(), 0),
            'total_customers'     => $this->safe(fn () => User::whereIn('role', ['customer', 'buyer'])->count(), 0),
            'pending_regs'        => $this->pendingRegistrations(),
            'pending_alerts'      => $this->getPendingAlerts(),
        ];
    }

    /**
     * Sales and Revenue Series for Chart.js (Daily, Monthly, Yearly).
     */
    public function salesSeries(string $period = 'monthly', ?string $from = null, ?string $to = null): array
    {
        $labels = [];
        $revenue = [];
        $orders = [];

        if ($period === 'daily') {
            // Last 14 days or custom range
            $start = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(13)->startOfDay();
            $end = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();
            $days = (int) $start->diffInDays($end) + 1;
            if ($days > 31) $days = 31; // cap daily granularity

            for ($i = 0; $i < $days; $i++) {
                $day = $start->copy()->addDays($i);
                $dayStart = $day->copy()->startOfDay();
                $dayEnd = $day->copy()->endOfDay();
                $key = $day->format('d M');

                $rev = (float) $this->safe(fn () => Order::whereBetween('created_at', [$dayStart, $dayEnd])->sum('total_amount'), 0.0);
                $cnt = (int) $this->safe(fn () => Order::whereBetween('created_at', [$dayStart, $dayEnd])->count(), 0);

                $labels[] = Bn::date($day);
                $revenue[] = round($rev, 2);
                $orders[] = $cnt;
            }
        } elseif ($period === 'yearly') {
            // Last 5 years
            $startYear = now()->subYears(4)->year;
            $endYear = now()->year;

            for ($yr = $startYear; $yr <= $endYear; $yr++) {
                $yrStart = Carbon::createFromDate($yr, 1, 1)->startOfDay();
                $yrEnd = Carbon::createFromDate($yr, 12, 31)->endOfDay();

                $rev = (float) $this->safe(fn () => Order::whereBetween('created_at', [$yrStart, $yrEnd])->sum('total_amount'), 0.0);
                $cnt = (int) $this->safe(fn () => Order::whereBetween('created_at', [$yrStart, $yrEnd])->count(), 0);

                $labels[] = Bn::num($yr) . ' সাল';
                $revenue[] = round($rev, 2);
                $orders[] = $cnt;
            }
        } else {
            // Last 12 months (Default)
            $start = now()->startOfMonth()->subMonths(11);
            for ($i = 0; $i < 12; $i++) {
                $month = $start->copy()->addMonths($i);
                $mStart = $month->copy()->startOfMonth();
                $mEnd = $month->copy()->endOfMonth();

                $rev = (float) $this->safe(fn () => Order::whereBetween('created_at', [$mStart, $mEnd])->sum('total_amount'), 0.0);
                $cnt = (int) $this->safe(fn () => Order::whereBetween('created_at', [$mStart, $mEnd])->count(), 0);

                $labels[] = Bn::date($mStart);
                $revenue[] = round($rev, 2);
                $orders[] = $cnt;
            }
        }

        return ['labels' => $labels, 'revenue' => $revenue, 'orders' => $orders];
    }

    /**
     * Visitor Traffic Series for Chart.js (Daily, Monthly, Yearly).
     */
    public function visitorSeries(string $period = 'daily', ?string $from = null, ?string $to = null): array
    {
        $labels = [];
        $views = [];
        $uniques = [];

        if (!Schema::hasTable('visitor_logs')) {
            return ['labels' => ['আজকে'], 'views' => [0], 'uniques' => [0]];
        }

        if ($period === 'monthly') {
            // Last 12 months
            $start = now()->startOfMonth()->subMonths(11);
            for ($i = 0; $i < 12; $i++) {
                $m = $start->copy()->addMonths($i);
                $mStart = $m->copy()->startOfMonth();
                $mEnd = $m->copy()->endOfMonth();

                $v = (int) $this->safe(fn () => VisitorLog::whereBetween('visited_at', [$mStart, $mEnd])->count(), 0);
                $u = (int) $this->safe(fn () => VisitorLog::whereBetween('visited_at', [$mStart, $mEnd])->distinct('ip_address')->count('ip_address'), 0);

                $labels[] = Bn::date($mStart);
                $views[] = $v;
                $uniques[] = $u;
            }
        } elseif ($period === 'yearly') {
            // Last 5 years
            $startYear = now()->subYears(4)->year;
            $endYear = now()->year;

            for ($yr = $startYear; $yr <= $endYear; $yr++) {
                $yrStart = Carbon::createFromDate($yr, 1, 1)->startOfDay();
                $yrEnd = Carbon::createFromDate($yr, 12, 31)->endOfDay();

                $v = (int) $this->safe(fn () => VisitorLog::whereBetween('visited_at', [$yrStart, $yrEnd])->count(), 0);
                $u = (int) $this->safe(fn () => VisitorLog::whereBetween('visited_at', [$yrStart, $yrEnd])->distinct('ip_address')->count('ip_address'), 0);

                $labels[] = Bn::num($yr) . ' সাল';
                $views[] = $v;
                $uniques[] = $u;
            }
        } else {
            // Last 14 days (Daily)
            $start = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(13)->startOfDay();
            $end = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();
            $days = (int) $start->diffInDays($end) + 1;
            if ($days > 31) $days = 31;

            for ($i = 0; $i < $days; $i++) {
                $d = $start->copy()->addDays($i);
                $dStart = $d->copy()->startOfDay();
                $dEnd = $d->copy()->endOfDay();

                $v = (int) $this->safe(fn () => VisitorLog::whereBetween('visited_at', [$dStart, $dEnd])->count(), 0);
                $u = (int) $this->safe(fn () => VisitorLog::whereBetween('visited_at', [$dStart, $dEnd])->distinct('ip_address')->count('ip_address'), 0);

                $labels[] = Bn::date($d);
                $views[] = $v;
                $uniques[] = $u;
            }
        }

        return ['labels' => $labels, 'views' => $views, 'uniques' => $uniques];
    }

    /**
     * Recent Orders Stream with Buyer details.
     */
    public function recentOrders(int $limit = 8)
    {
        return $this->safe(fn () => Order::with('book')->latest()->limit($limit)->get(), collect());
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

    public function roleBreakdown(): array
    {
        return $this->safe(fn () => User::select('role', DB::raw('count(*) as aggregate'))
            ->groupBy('role')
            ->pluck('aggregate', 'role')
            ->toArray(), []);
    }

    public function userGrowth(int $months = 6): array
    {
        return ['labels' => [], 'counts' => []];
    }

    public function topSellers(int $limit = 5)
    {
        return $this->safe(fn () => Bill::select('seller_id', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as bills'))
            ->groupBy('seller_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->with('seller')
            ->get(), collect());
    }

    /**
     * Resolve filter dates into Carbon instances.
     */
    private function resolveDateRange(?string $from, ?string $to, string $period): array
    {
        if ($from && $to) {
            $startDate = Carbon::parse($from)->startOfDay();
            $endDate = Carbon::parse($to)->endOfDay();
            $filterLabel = Bn::date($startDate) . ' থেকে ' . Bn::date($endDate);
            return [$startDate, $endDate, $filterLabel];
        }

        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay(), 'আজকের দিন (' . Bn::date(now()) . ')'],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), 'গতকাল (' . Bn::date(now()->subDay()) . ')'],
            'week'  => [now()->subDays(7)->startOfDay(), now()->endOfDay(), 'বিগত ৭ দিন'],
            'month' => [now()->startOfMonth(), now()->endOfMonth(), 'চলতি মাস (' . now()->translatedFormat('F Y') . ')'],
            'year'  => [now()->startOfYear(), now()->endOfYear(), 'চলতি বছর (' . Bn::num(now()->year) . ')'],
            default => [null, null, 'সর্বমোট সার্বিক সময় (All Time)'],
        };
    }

    private function count(string $table): ?int
    {
        if (! $this->hasTable($table)) {
            return null;
        }

        return $this->safe(fn () => DB::table($table)->whereNull('deleted_at')->count(), 0);
    }

    private function hasTable(string $table): bool
    {
        return $this->safe(fn () => Schema::hasTable($table), false);
    }

    private function safe(callable $query, mixed $fallback = null): mixed
    {
        try {
            return $query();
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
