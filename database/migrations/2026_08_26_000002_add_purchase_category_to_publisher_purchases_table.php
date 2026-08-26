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
                if (!Schema::hasColumn('publisher_purchases', 'purchase_category')) {
                    $table->string('purchase_category', 50)->default('books')->after('purchase_no'); // 'books' or 'raw_materials'
                }
                if (!Schema::hasColumn('publisher_purchases', 'supplier_name')) {
                    $table->string('supplier_name', 255)->nullable()->after('publisher_id');
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
                if (Schema::hasColumn('publisher_purchases', 'purchase_category')) {
                    $table->dropColumn('purchase_category');
                }
                if (Schema::hasColumn('publisher_purchases', 'supplier_name')) {
                    $table->dropColumn('supplier_name');
                }
            });
        }
    }
};
