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
        if (Schema::hasTable('idea_invoices')) {
            Schema::table('idea_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('idea_invoices', 'sales_category')) {
                    $table->string('sales_category', 50)->default('books')->after('type'); // books, stationery, printing_goods, other
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('idea_invoices')) {
            Schema::table('idea_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('idea_invoices', 'sales_category')) {
                    $table->dropColumn('sales_category');
                }
            });
        }
    }
};
