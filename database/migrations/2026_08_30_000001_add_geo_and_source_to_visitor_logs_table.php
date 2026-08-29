<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('visitor_logs')) {
            Schema::table('visitor_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('visitor_logs', 'country')) {
                    $table->string('country', 100)->nullable()->after('os');
                }
                if (!Schema::hasColumn('visitor_logs', 'country_code')) {
                    $table->string('country_code', 10)->nullable()->after('country');
                }
                if (!Schema::hasColumn('visitor_logs', 'city')) {
                    $table->string('city', 100)->nullable()->after('country_code');
                }
                if (!Schema::hasColumn('visitor_logs', 'traffic_source')) {
                    $table->string('traffic_source', 100)->nullable()->after('referer');
                }
                if (!Schema::hasColumn('visitor_logs', 'utm_source')) {
                    $table->string('utm_source', 100)->nullable()->after('traffic_source');
                }

                $table->index('country_code');
                $table->index('traffic_source');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('visitor_logs')) {
            Schema::table('visitor_logs', function (Blueprint $table) {
                $columns = ['country', 'country_code', 'city', 'traffic_source', 'utm_source'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('visitor_logs', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
