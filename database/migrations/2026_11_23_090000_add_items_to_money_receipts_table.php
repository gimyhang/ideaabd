<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Book-sale lines on an honorarium receipt: book, copies sold, price,
 * commission % — so the receipt shows how the writer's amount was worked out.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('money_receipts') && !Schema::hasColumn('money_receipts', 'items')) {
            Schema::table('money_receipts', function (Blueprint $table) {
                $table->json('items')->nullable()->after('amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('money_receipts', 'items')) {
            Schema::table('money_receipts', function (Blueprint $table) {
                $table->dropColumn('items');
            });
        }
    }
};
