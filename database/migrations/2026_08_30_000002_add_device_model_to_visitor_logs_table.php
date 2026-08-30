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
                if (!Schema::hasColumn('visitor_logs', 'device_name')) {
                    $table->string('device_name', 100)->nullable()->after('device');
                }
                if (!Schema::hasColumn('visitor_logs', 'user_agent')) {
                    $table->text('user_agent')->nullable()->after('route_name');
                }
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
                if (Schema::hasColumn('visitor_logs', 'device_name')) {
                    $table->dropColumn('device_name');
                }
                if (Schema::hasColumn('visitor_logs', 'user_agent')) {
                    $table->dropColumn('user_agent');
                }
            });
        }
    }
};
