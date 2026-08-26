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
                if (!Schema::hasColumn('publisher_purchase_items', 'item_type')) {
                    $table->string('item_type', 30)->default('book')->after('book_id'); // book, raw_material, service, other
                }
                if (!Schema::hasColumn('publisher_purchase_items', 'item_name')) {
                    $table->string('item_name', 255)->nullable()->after('item_type');
                }
                if (!Schema::hasColumn('publisher_purchase_items', 'size_spec')) {
                    $table->string('size_spec', 150)->nullable()->after('item_name');
                }
                if (!Schema::hasColumn('publisher_purchase_items', 'unit')) {
                    $table->string('unit', 50)->nullable()->after('size_spec');
                }
                if (!Schema::hasColumn('publisher_purchase_items', 'quality_spec')) {
                    $table->string('quality_spec', 150)->nullable()->after('unit');
                }
                if (!Schema::hasColumn('publisher_purchase_items', 'item_notes')) {
                    $table->text('item_notes')->nullable()->after('subtotal');
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
                $columns = ['item_type', 'item_name', 'size_spec', 'unit', 'quality_spec', 'item_notes'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('publisher_purchase_items', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
