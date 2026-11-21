<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Visitor numbers for the admin dashboard, read from daily summaries.
 *
 * Finished days are summarised once by rollup() into visitor_daily_stats / visitor_daily_breakdowns /
 * visitor_period_ips. Only today's rows are read from visitor_logs, through the visited_at index, so no
 * query ever scans the whole log (that is what made /admin/dashboard time out).
 */
class VisitorStats
{
    /** Ranges up to this many days get an exact distinct-IP count from the raw log (index-bounded). */
    private const EXACT_RANGE_DAYS = 31;

    public static function ready(): bool
    {
        return self::has('visitor_logs') && self::has('visitor_daily_stats') && self::has('visitor_period_ips') && self::has('visitor_daily_breakdowns');
    }

    /**
     * visitor_logs for per-country counts, read through the (visited_at, country, country_code) covering
     * index. Without it MariaDB picks the country_code index and walks the whole table (31–34 s for one day
     * on 2 M rows).
     */
    private static function logs(): \Illuminate\Database\Query\Builder
    {
        static $hint = null;
        if ($hint === null) {
            try {
                // Index hints are MySQL/MariaDB syntax; SQLite's planner picks the visited_at range by itself.
                $hint = in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)
                    && Schema::hasIndex('visitor_logs', 'visitor_logs_visited_at_country_index')
                    ? ' FORCE INDEX (`visitor_logs_visited_at_country_index`)' : '';
            } catch (\Throwable) {
                $hint = '';
            }
        }

        return DB::table(DB::raw('`visitor_logs`' . $hint));
    }

    private static function has(string $t): bool
    {
        try {
            return \App\Support\SchemaCache::has($t);
        } catch (\Throwable) {
            return Schema::hasTable($t);
        }
    }

    // ── Rollup ───────────────────────────────────────────────

    /**
     * Summarise finished days that are not summarised yet, oldest first, within a time budget.
     * Safe to call often: one run at a time (lock), each day is idempotent.
     *
     * @return array{done:int, remaining:int}
     */
    public static function rollup(float $budgetSeconds = 20.0, ?int $maxDays = null): array
    {
        if (!self::ready()) {
            return ['done' => 0, 'remaining' => 0];
        }
        $lock = Cache::lock('visitor-stats-rollup', (int) ceil($budgetSeconds) + 60);
        if (!$lock->get()) {
            return ['done' => 0, 'remaining' => self::pendingDays()];
        }

        $done = 0;
        $started = microtime(true);
        try {
            foreach (self::daysToRoll() as $day) {
                if ((microtime(true) - $started) > $budgetSeconds || ($maxDays !== null && $done >= $maxDays)) {
                    break;
                }
                self::rollDay(Carbon::parse($day));
                $done++;
            }
        } catch (\Throwable $e) {
            Log::warning('Visitor rollup stopped: ' . $e->getMessage());
        } finally {
            $lock->release();
        }

        return ['done' => $done, 'remaining' => self::pendingDays()];
    }

    /** Finished days (before today) that have visits but no summary yet. */
    public static function daysToRoll(): array
    {
        $first = DB::table('visitor_logs')->min('visited_at');
        if (!$first) {
            return [];
        }
        $last = DB::table('visitor_daily_stats')->max('date');
        $from = $last ? Carbon::parse($last)->addDay() : Carbon::parse($first)->startOfDay();
        $until = now()->startOfDay()->subDay();
        $days = [];
        for ($d = $from->copy(); $d->lte($until); $d->addDay()) {
            $days[] = $d->toDateString();
        }

        return $days;
    }

    public static function pendingDays(): int
    {
        try {
            return count(self::daysToRoll());
        } catch (\Throwable) {
            return 0;
        }
    }

    private static function rollDay(Carbon $day): void
    {
        $from = $day->copy()->startOfDay();
        $to = $day->copy()->addDay()->startOfDay();
        $date = $day->toDateString();
        $range = fn ($q) => $q->where('visited_at', '>=', $from)->where('visited_at', '<', $to);

        $row = $range(DB::table('visitor_logs'))->selectRaw('COUNT(*) AS v, COUNT(DISTINCT ip_address) AS u')->first();

        DB::transaction(function () use ($range, $date, $row, $day) {
            foreach ([
                'country' => $range(self::logs())->whereNotNull('country')->where('country', '!=', '')
                    ->selectRaw('country AS name, MAX(country_code) AS code, COUNT(*) AS c')->groupBy('country')->get(),
                'device' => $range(DB::table('visitor_logs'))
                    ->selectRaw("COALESCE(NULLIF(device, ''), 'desktop') AS name, NULL AS code, COUNT(*) AS c")->groupBy('name')->get(),
            ] as $dimension => $rows) {
                foreach ($rows as $r) {
                    DB::table('visitor_daily_breakdowns')->updateOrInsert(
                        ['date' => $date, 'dimension' => $dimension, 'name' => mb_substr((string) $r->name, 0, 100)],
                        ['code' => $r->code ? mb_substr((string) $r->code, 0, 10) : null, 'views' => (int) $r->c]
                    );
                }
            }

            // Mark every IP of the day as seen in its month, its year and all-time.
            // "Insert unless already there": INSERT IGNORE on MySQL/MariaDB, INSERT OR IGNORE on SQLite.
            $insertIgnore = DB::getDriverName() === 'sqlite' ? 'INSERT OR IGNORE' : 'INSERT IGNORE';
            foreach (['ALL', 'Y' . $day->format('Y'), 'M' . $day->format('Y-m')] as $period) {
                DB::statement(
                    $insertIgnore . ' INTO visitor_period_ips (period, ip_address)
                     SELECT DISTINCT ?, ip_address FROM visitor_logs
                     WHERE visited_at >= ? AND visited_at < ? AND ip_address IS NOT NULL AND ip_address <> ?',
                    [$period, $day->copy()->startOfDay(), $day->copy()->addDay()->startOfDay(), '']
                );
            }

            // Written last: a day only counts as done once everything above succeeded.
            DB::table('visitor_daily_stats')->updateOrInsert(
                ['date' => $date],
                ['views' => (int) ($row->v ?? 0), 'uniques' => (int) ($row->u ?? 0), 'updated_at' => now(), 'created_at' => now()]
            );
        });
    }

    // ── Reading ──────────────────────────────────────────────

    /** Today's views and distinct IPs, straight from the log (index range on visited_at). */
    private static function today(): object
    {
        return once(fn () => DB::table('visitor_logs')->where('visited_at', '>=', now()->startOfDay())
            ->selectRaw('COUNT(*) AS v, COUNT(DISTINCT ip_address) AS u')->first() ?: (object) ['v' => 0, 'u' => 0]);
    }

    /** IPs seen today that the period's list does not contain yet (today is not rolled up). */
    private static function todayNewIps(string $period): int
    {
        return (int) DB::table('visitor_logs AS v')
            ->leftJoin('visitor_period_ips AS p', function ($j) use ($period) {
                $j->on('p.ip_address', '=', 'v.ip_address')->where('p.period', '=', $period);
            })
            ->where('v.visited_at', '>=', now()->startOfDay())
            ->whereNotNull('v.ip_address')
            ->whereNull('p.ip_address')
            ->distinct()
            ->count('v.ip_address');
    }

    private static function periodCount(string $period): int
    {
        return (int) DB::table('visitor_period_ips')->where('period', $period)->count();
    }

    private static function sumViews(?Carbon $from = null, ?Carbon $to = null): int
    {
        return (int) DB::table('visitor_daily_stats')
            ->when($from, fn ($q) => $q->where('date', '>=', $from->toDateString()))
            ->when($to, fn ($q) => $q->where('date', '<=', $to->toDateString()))
            ->sum('views');
    }

    /**
     * Headline numbers: today / this month / this year / all time, plus an optional filtered range.
     */
    public static function headline(?Carbon $start = null, ?Carbon $end = null, ?string $period = null): array
    {
        $today = self::today();
        $monthStart = now()->startOfMonth();
        $yearStart = now()->startOfYear();
        $mKey = 'M' . now()->format('Y-m');
        $yKey = 'Y' . now()->format('Y');

        $out = [
            'today_views'   => (int) $today->v,
            'today_uniques' => (int) $today->u,
            'month_views'   => self::sumViews($monthStart) + (int) $today->v,
            'month_uniques' => self::periodCount($mKey) + self::todayNewIps($mKey),
            'year_views'    => self::sumViews($yearStart) + (int) $today->v,
            'year_uniques'  => self::periodCount($yKey) + self::todayNewIps($yKey),
            'total_views'   => self::sumViews() + (int) $today->v,
            'total_uniques' => self::periodCount('ALL') + self::todayNewIps('ALL'),
        ];

        // Dashboard period buttons that match a figure above reuse it instead of re-counting the raw log.
        $reuse = ['today' => 'today', 'month' => 'month', 'year' => 'year', 'all' => 'total'][$period ?? ''] ?? null;
        if ($reuse && ($reuse !== 'total' || !$start)) {
            $out['filtered_views'] = $out[$reuse . '_views'];
            $out['filtered_uniques'] = $out[$reuse . '_uniques'];
        } elseif ($start && $end) {
            [$views, $uniques] = self::range($start, $end);
            $out['filtered_views'] = $views;
            $out['filtered_uniques'] = $uniques;
            // Longer than EXACT_RANGE_DAYS: daily uniques were added up (a returning visitor counts once per day).
            $out['filtered_uniques_approx'] = ($start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1) > self::EXACT_RANGE_DAYS;
        } else {
            $out['filtered_views'] = $out['total_views'];
            $out['filtered_uniques'] = $out['total_uniques'];
        }

        return $out;
    }

    /** [views, uniques] for a date range. Short ranges are exact; long ones add up daily uniques. */
    public static function range(Carbon $start, Carbon $end): array
    {
        $days = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        if ($days <= self::EXACT_RANGE_DAYS) {
            $r = DB::table('visitor_logs')->whereBetween('visited_at', [$start, $end])
                ->selectRaw('COUNT(*) AS v, COUNT(DISTINCT ip_address) AS u')->first();

            return [(int) ($r->v ?? 0), (int) ($r->u ?? 0)];
        }
        $r = DB::table('visitor_daily_stats')->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('COALESCE(SUM(views),0) AS v, COALESCE(SUM(uniques),0) AS u')->first();
        $views = (int) $r->v;
        $uniques = (int) $r->u;
        if ($end->gte(now()->startOfDay())) {
            $views += (int) self::today()->v;
            $uniques += (int) self::today()->u;
        }

        return [$views, $uniques];
    }

    /** Chart series. $period: daily (last 14 days or the given range, max 31), monthly (12), yearly (5). */
    public static function series(string $period, ?Carbon $from = null, ?Carbon $to = null, ?callable $label = null): array
    {
        $label ??= fn (Carbon $d, string $kind) => $d->format($kind === 'yearly' ? 'Y' : ($kind === 'monthly' ? 'M Y' : 'd M'));
        $today = self::today();
        $labels = $views = $uniques = [];

        if ($period === 'monthly') {
            $start = now()->startOfMonth()->subMonths(11);
            $rows = DB::table('visitor_daily_stats')->where('date', '>=', $start->toDateString())
                ->selectRaw('SUBSTR(date, 1, 7) AS k, SUM(views) AS v')->groupBy('k')->pluck('v', 'k');
            $periods = [];
            for ($i = 0; $i < 12; $i++) {
                $periods[] = 'M' . $start->copy()->addMonths($i)->format('Y-m');
            }
            $u = DB::table('visitor_period_ips')->whereIn('period', $periods)->selectRaw('period, COUNT(*) AS c')->groupBy('period')->pluck('c', 'period');
            for ($i = 0; $i < 12; $i++) {
                $m = $start->copy()->addMonths($i);
                $isNow = $m->isSameMonth(now());
                $labels[] = $label($m, 'monthly');
                $views[] = (int) ($rows[$m->format('Y-m')] ?? 0) + ($isNow ? (int) $today->v : 0);
                $uniques[] = (int) ($u['M' . $m->format('Y-m')] ?? 0) + ($isNow ? self::todayNewIps('M' . $m->format('Y-m')) : 0);
            }
        } elseif ($period === 'yearly') {
            $startYear = now()->year - 4;
            $rows = DB::table('visitor_daily_stats')->where('date', '>=', $startYear . '-01-01')
                ->selectRaw('SUBSTR(date, 1, 4) AS k, SUM(views) AS v')->groupBy('k')->pluck('v', 'k');
            $u = DB::table('visitor_period_ips')->whereIn('period', array_map(fn ($y) => 'Y' . $y, range($startYear, now()->year)))
                ->selectRaw('period, COUNT(*) AS c')->groupBy('period')->pluck('c', 'period');
            for ($y = $startYear; $y <= now()->year; $y++) {
                $isNow = $y === now()->year;
                $labels[] = $label(Carbon::create($y, 1, 1), 'yearly');
                $views[] = (int) ($rows[$y] ?? 0) + ($isNow ? (int) $today->v : 0);
                $uniques[] = (int) ($u['Y' . $y] ?? 0) + ($isNow ? self::todayNewIps('Y' . $y) : 0);
            }
        } else {
            $start = ($from ? $from->copy() : now()->subDays(13))->startOfDay();
            $end = ($to ? $to->copy() : now())->startOfDay();
            if ($start->diffInDays($end) > 30) {
                $start = $end->copy()->subDays(30);
            }
            $rows = DB::table('visitor_daily_stats')->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get(['date', 'views', 'uniques'])->keyBy(fn ($r) => substr((string) $r->date, 0, 10));
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $k = $d->toDateString();
                $isToday = $d->isToday();
                $labels[] = $label($d->copy(), 'daily');
                $views[] = $isToday ? (int) $today->v : (int) ($rows[$k]->views ?? 0);
                $uniques[] = $isToday ? (int) $today->u : (int) ($rows[$k]->uniques ?? 0);
            }
        }

        return ['labels' => $labels, 'views' => $views, 'uniques' => $uniques];
    }

    /** Top countries (all time): [['country','code','visitors'], …] */
    public static function topCountries(int $limit = 6): array
    {
        $rows = DB::table('visitor_daily_breakdowns')->where('dimension', 'country')
            ->selectRaw('name, MAX(code) AS code, SUM(views) AS v')->groupBy('name')->get()
            ->mapWithKeys(fn ($r) => [$r->name => ['country' => $r->name, 'code' => $r->code, 'visitors' => (int) $r->v]])->all();

        $todayRows = self::logs()->where('visited_at', '>=', now()->startOfDay())
            ->whereNotNull('country')->where('country', '!=', '')
            ->selectRaw('country AS name, MAX(country_code) AS code, COUNT(*) AS c')->groupBy('country')->get();
        foreach ($todayRows as $r) {
            $rows[$r->name] ??= ['country' => $r->name, 'code' => $r->code, 'visitors' => 0];
            $rows[$r->name]['visitors'] += (int) $r->c;
        }
        usort($rows, fn ($a, $b) => $b['visitors'] <=> $a['visitors']);

        return array_slice(array_values($rows), 0, $limit);
    }

    /** Device split (all time) in visits: ['mobile' => n, 'desktop' => n, 'tablet' => n]. */
    public static function devices(): array
    {
        $out = DB::table('visitor_daily_breakdowns')->where('dimension', 'device')
            ->selectRaw('name, SUM(views) AS v')->groupBy('name')->pluck('v', 'name')->map(fn ($v) => (int) $v)->all();
        $today = DB::table('visitor_logs')->where('visited_at', '>=', now()->startOfDay())
            ->selectRaw("COALESCE(NULLIF(device, ''), 'desktop') AS name, COUNT(*) AS c")->groupBy('name')->pluck('c', 'name');
        foreach ($today as $k => $c) {
            $out[$k] = ($out[$k] ?? 0) + (int) $c;
        }

        return $out;
    }
}
