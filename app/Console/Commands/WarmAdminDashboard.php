<?php

namespace App\Console\Commands;

use App\Services\AdminDashboardService;
use Illuminate\Console\Command;

/**
 * Keeps the admin dashboard's cached figures fresh in the background.
 *
 * The dashboard serves everything from short-lived caches (see AdminDashboardService::swr). Those caches
 * are recomputed by whoever opens the page first once they have gone stale for long enough, which is
 * exactly when the admin opens it: dozens of queries on the biggest tables while they wait. This command
 * does that work on the schedule instead, so opening the dashboard only ever reads warm caches.
 *
 * It asks for the same figures, with the same arguments, as AdminController::dashboard() does by default.
 */
class WarmAdminDashboard extends Command
{
    protected $signature = 'dashboard:warm';

    protected $description = 'Pre-compute the admin dashboard caches so opening the dashboard is instant';

    public function handle(AdminDashboardService $dashboard): int
    {
        $steps = [
            // First: summarise finished visitor-log days, so the figures below read summaries, not the raw log.
            'visitor rollup'  => fn () => \App\Services\VisitorStats::rollup(60.0),
            'stats'           => fn () => $dashboard->filteredStats(null, null, 'all'),
            'sales chart'     => fn () => $dashboard->salesSeries('monthly', null, null),
            'visitor chart'   => fn () => $dashboard->visitorSeries('daily', null, null),
            'recent orders'   => fn () => $dashboard->recentOrders(8),
            'sellers'         => fn () => $dashboard->sellersSummary(8),
            'pending records' => fn () => $dashboard->getPendingRecordsData(15),
            'pending alerts'  => fn () => $dashboard->getPendingAlerts(),
            'pending signups' => fn () => $dashboard->pendingRegistrations(),
        ];

        $failed = 0;
        foreach ($steps as $name => $step) {
            $started = microtime(true);
            try {
                $step();
                $this->line(sprintf('  %-16s %5.0f ms', $name, (microtime(true) - $started) * 1000));
            } catch (\Throwable $e) {
                // One broken figure must not stop the others being warmed (the dashboard shows its own fallback).
                $failed++;
                $this->warn("  {$name}: " . $e->getMessage());
            }
        }

        return $failed === count($steps) ? self::FAILURE : self::SUCCESS;
    }
}
