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
        if (Schema::hasTable('publisher_purchase_items')) {
            Schema::table('publisher_purchase_items', function (Blueprint $table) {
                if (!Schema::hasColumn('publisher_purchase_items', 'reams_quantity')) {
                    $table->decimal('reams_quantity', 10, 3)->nullable()->after('quantity');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('publisher_purchase_items')) {
            Schema::table('publisher_purchase_items', function (Blueprint $table) {
                if (Schema::hasColumn('publisher_purchase_items', 'reams_quantity')) {
                    $table->dropColumn('reams_quantity');
                }
            });
        }
    }
};
