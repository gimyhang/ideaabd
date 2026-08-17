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
                if (! Schema::hasColumn('publisher_purchases', 'publisher_memo_no')) {
                    $table->string('publisher_memo_no', 100)->nullable()->after('purchase_no')->comment('প্রকাশকের নিজস্ব মেমো / চালান নম্বর');
                }
            });
        }

        if (Schema::hasTable('publisher_purchase_items')) {
            Schema::table('publisher_purchase_items', function (Blueprint $table) {
                if (! Schema::hasColumn('publisher_purchase_items', 'mrp_price')) {
                    $table->decimal('mrp_price', 10, 2)->default(0.00)->after('quantity')->comment('বইয়ের গায়ের মূল্য / MRP');
                }
                if (! Schema::hasColumn('publisher_purchase_items', 'purchase_commission_percent')) {
                    $table->decimal('purchase_commission_percent', 5, 2)->default(0.00)->after('mrp_price')->comment('প্রকাশনী ক্রয় কমিশন %');
                }
                if (! Schema::hasColumn('publisher_purchase_items', 'shop_discount_percent')) {
                    $table->decimal('shop_discount_percent', 5, 2)->default(0.00)->after('unit_cost_price')->comment('বুকশপে প্রদর্শনীতে % ছাড়');
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
                if (Schema::hasColumn('publisher_purchases', 'publisher_memo_no')) {
                    $table->dropColumn('publisher_memo_no');
                }
            });
        }

        if (Schema::hasTable('publisher_purchase_items')) {
            Schema::table('publisher_purchase_items', function (Blueprint $table) {
                $columns = ['mrp_price', 'purchase_commission_percent', 'shop_discount_percent'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('publisher_purchase_items', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
