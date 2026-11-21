<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daily visitor summaries for the admin dashboard.
 *
 * The dashboard used to count the whole visitor_logs table (all-time totals, distinct IPs, countries,
 * devices) on every cache refresh, which timed out once the log grew large. Each finished day is now
 * summarised once into these small tables; only today's rows are read from visitor_logs.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('visitor_daily_stats')) {
            Schema::create('visitor_daily_stats', function (Blueprint $table) {
                $table->date('date')->primary();
                $table->unsignedInteger('views')->default(0);
                $table->unsignedInteger('uniques')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('visitor_daily_breakdowns')) {
            Schema::create('visitor_daily_breakdowns', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->string('dimension', 16); // country | device
                $table->string('name', 100);
                $table->string('code', 10)->nullable();
                $table->unsignedInteger('views')->default(0);
                $table->unique(['date', 'dimension', 'name']);
                $table->index(['dimension', 'date']);
            });
        }

        // Which IPs were seen in a period ("ALL", "Y2026", "M2026-11"): exact unique-visitor counts without
        // re-scanning the raw log. One row per IP per period.
        if (!Schema::hasTable('visitor_period_ips')) {
            Schema::create('visitor_period_ips', function (Blueprint $table) {
                $table->string('period', 10);
                $table->string('ip_address', 45);
                $table->primary(['period', 'ip_address']);
            });
        }

        // Covering indexes: "distinct IPs in a date range" and "visits per country in a date range" are answered
        // from the index alone. Without them a day's country count read every row (34 s per day on 2 M rows).
        if (Schema::hasTable('visitor_logs')) {
            $names = collect(\Illuminate\Support\Facades\DB::select('SHOW INDEX FROM visitor_logs'))->pluck('Key_name')->all();
            Schema::table('visitor_logs', function (Blueprint $table) use ($names) {
                if (!in_array('visitor_logs_visited_at_ip_index', $names, true)) {
                    $table->index(['visited_at', 'ip_address'], 'visitor_logs_visited_at_ip_index');
                }
                if (!in_array('visitor_logs_visited_at_country_index', $names, true)) {
                    $table->index(['visited_at', 'country', 'country_code'], 'visitor_logs_visited_at_country_index');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_period_ips');
        Schema::dropIfExists('visitor_daily_breakdowns');
        Schema::dropIfExists('visitor_daily_stats');
        if (Schema::hasTable('visitor_logs')) {
            foreach (['visitor_logs_visited_at_ip_index', 'visitor_logs_visited_at_country_index'] as $ix) {
                try {
                    Schema::table('visitor_logs', fn (Blueprint $t) => $t->dropIndex($ix));
                } catch (\Throwable) {
                }
            }
        }
    }
};
