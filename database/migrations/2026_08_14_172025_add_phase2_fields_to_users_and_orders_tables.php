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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'loyalty_points')) {
                $table->integer('loyalty_points')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('users', 'affiliate_balance')) {
                $table->decimal('affiliate_balance', 10, 2)->default(0)->after('loyalty_points');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'points_earned')) {
                $table->integer('points_earned')->default(0)->after('is_gift');
            }
            if (!Schema::hasColumn('orders', 'points_used')) {
                $table->integer('points_used')->default(0)->after('points_earned');
            }
            if (!Schema::hasColumn('orders', 'affiliate_id')) {
                $table->foreignId('affiliate_id')->nullable()->constrained('users')->nullOnDelete()->after('points_used');
            }
            if (!Schema::hasColumn('orders', 'commission_amount')) {
                $table->decimal('commission_amount', 10, 2)->default(0)->after('affiliate_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'affiliate_id')) {
                $table->dropForeign(['affiliate_id']);
            }
            $table->dropColumn(['points_earned', 'points_used', 'affiliate_id', 'commission_amount']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points', 'affiliate_balance']);
        });
    }
};
