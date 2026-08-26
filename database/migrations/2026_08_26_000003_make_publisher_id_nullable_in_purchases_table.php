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
                $table->foreignId('publisher_id')->nullable()->change();
                if (!Schema::hasColumn('publisher_purchases', 'vendor_name')) {
                    $table->string('vendor_name', 255)->nullable()->after('supplier_name');
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
                if (Schema::hasColumn('publisher_purchases', 'vendor_name')) {
                    $table->dropColumn('vendor_name');
                }
            });
        }
    }
};
