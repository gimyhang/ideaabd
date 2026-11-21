<?php

namespace App\Console\Commands;

use App\Services\VisitorStats;
use Illuminate\Console\Command;

/**
 * Summarise the visitor log into daily tables (see App\Services\VisitorStats).
 * Run once after deploying to summarise all past days: php artisan visitors:rollup --all
 */
class RollupVisitorStats extends Command
{
    protected $signature = 'visitors:rollup {--all : Keep going until every finished day is summarised} {--seconds=120 : Time budget per batch}';

    protected $description = 'Summarise finished days of visitor_logs for the admin dashboard';

    public function handle(): int
    {
        if (!VisitorStats::ready()) {
            $this->error('Visitor summary tables are missing. Run: php artisan migrate');

            return self::FAILURE;
        }

        $total = 0;
        do {
            $started = microtime(true);
            $r = VisitorStats::rollup((float) $this->option('seconds'));
            $total += $r['done'];
            $this->line(sprintf('  %d day(s) summarised in %.1fs, %d remaining', $r['done'], microtime(true) - $started, $r['remaining']));
        } while ($this->option('all') && $r['remaining'] > 0 && $r['done'] > 0);

        $this->info("Done: {$total} day(s).");

        return self::SUCCESS;
    }
}
