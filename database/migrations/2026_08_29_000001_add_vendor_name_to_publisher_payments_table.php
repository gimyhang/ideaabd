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
        if (Schema::hasTable('publisher_payments')) {
            Schema::table('publisher_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('publisher_payments', 'vendor_name')) {
                    $table->string('vendor_name', 255)->nullable()->after('publisher_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('publisher_payments')) {
            Schema::table('publisher_payments', function (Blueprint $table) {
                if (Schema::hasColumn('publisher_payments', 'vendor_name')) {
                    $table->dropColumn('vendor_name');
                }
            });
        }
    }
};
