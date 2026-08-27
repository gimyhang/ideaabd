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
        Schema::table('publisher_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('publisher_purchases', 'due_date')) {
                $table->date('due_date')->nullable()->after('purchase_date');
            }
            if (!Schema::hasColumn('publisher_purchases', 'installment_count')) {
                $table->integer('installment_count')->nullable()->default(1)->after('payment_type');
            }
            if (!Schema::hasColumn('publisher_purchases', 'installment_notes')) {
                $table->text('installment_notes')->nullable()->after('notes');
            }
        });

        // Ensure payment_type column allows 'installment'
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE publisher_purchases MODIFY COLUMN payment_type VARCHAR(50) NOT NULL DEFAULT 'cash'");
        } catch (\Throwable $e) {
            // fallback
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publisher_purchases', function (Blueprint $table) {
            if (Schema::hasColumn('publisher_purchases', 'due_date')) {
                $table->dropColumn('due_date');
            }
            if (Schema::hasColumn('publisher_purchases', 'installment_count')) {
                $table->dropColumn('installment_count');
            }
            if (Schema::hasColumn('publisher_purchases', 'installment_notes')) {
                $table->dropColumn('installment_notes');
            }
        });
    }
};
