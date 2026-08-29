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
        if (Schema::hasTable('publisher_purchases')) {
            Schema::table('publisher_purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('publisher_purchases', 'vendor_phone')) {
                    $table->string('vendor_phone', 50)->nullable()->after('vendor_name');
                }
                if (!Schema::hasColumn('publisher_purchases', 'vendor_address')) {
                    $table->text('vendor_address')->nullable()->after('vendor_phone');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('publisher_purchases')) {
            Schema::table('publisher_purchases', function (Blueprint $table) {
                if (Schema::hasColumn('publisher_purchases', 'vendor_address')) {
                    $table->dropColumn('vendor_address');
                }
                if (Schema::hasColumn('publisher_purchases', 'vendor_phone')) {
                    $table->dropColumn('vendor_phone');
                }
            });
        }
    }
};
