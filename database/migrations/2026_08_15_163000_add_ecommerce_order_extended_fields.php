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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('orders', 'quantity')) {
                $table->integer('quantity')->default(1)->after('book_id');
            }
            if (!Schema::hasColumn('orders', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 10, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('shipping_cost');
            }
            if (!Schema::hasColumn('orders', 'gift_wrap_fee')) {
                $table->decimal('gift_wrap_fee', 10, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->default('cod')->after('total_amount'); // cod, bkash, nagad, rocket, card
            }
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('payment_method'); // pending, paid, partial
            }
            if (!Schema::hasColumn('orders', 'thana')) {
                $table->string('thana')->nullable()->after('district');
            }
            if (!Schema::hasColumn('orders', 'post_code')) {
                $table->string('post_code')->nullable()->after('thana');
            }
            if (!Schema::hasColumn('orders', 'house_road')) {
                $table->string('house_road')->nullable()->after('post_code');
            }
            if (!Schema::hasColumn('orders', 'courier_name')) {
                $table->string('courier_name')->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'tracking_code')) {
                $table->string('tracking_code')->nullable()->after('courier_name');
            }
            if (!Schema::hasColumn('orders', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('tracking_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'order_number', 'quantity', 'unit_price', 'shipping_cost', 
                'discount_amount', 'gift_wrap_fee', 'payment_method', 
                'payment_status', 'thana', 'post_code', 'house_road', 
                'courier_name', 'tracking_code', 'admin_notes'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
