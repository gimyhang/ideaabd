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
        if (Schema::hasTable('pos_sales')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('pos_sales', 'status')) {
                    $table->string('status', 20)->default('completed')->index()->after('payment_method');
                }
                if (!Schema::hasColumn('pos_sales', 'trx_id')) {
                    $table->string('trx_id', 100)->nullable()->after('payment_method');
                }
                if (!Schema::hasColumn('pos_sales', 'discount_percent')) {
                    $table->decimal('discount_percent', 5, 2)->default(0.00)->after('discount');
                }
                if (!Schema::hasColumn('pos_sales', 'tendered_amount')) {
                    $table->decimal('tendered_amount', 10, 2)->nullable()->after('paid_online');
                }
                if (!Schema::hasColumn('pos_sales', 'change_amount')) {
                    $table->decimal('change_amount', 10, 2)->default(0.00)->after('tendered_amount');
                }
                if (!Schema::hasColumn('pos_sales', 'notes')) {
                    $table->text('notes')->nullable()->after('items_json');
                }
                if (!Schema::hasColumn('pos_sales', 'voided_by')) {
                    $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete()->after('notes');
                }
                if (!Schema::hasColumn('pos_sales', 'voided_at')) {
                    $table->timestamp('voided_at')->nullable()->after('voided_by');
                }
                if (!Schema::hasColumn('pos_sales', 'void_reason')) {
                    $table->string('void_reason')->nullable()->after('voided_at');
                }
            });
        }

        if (Schema::hasTable('pos_registers')) {
            Schema::table('pos_registers', function (Blueprint $table) {
                if (!Schema::hasColumn('pos_registers', 'closing_cash')) {
                    $table->decimal('closing_cash', 10, 2)->nullable()->after('current_cash');
                }
                if (!Schema::hasColumn('pos_registers', 'closed_by')) {
                    $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete()->after('opened_by');
                }
                if (!Schema::hasColumn('pos_registers', 'closed_at')) {
                    $table->timestamp('closed_at')->nullable()->after('closed_by');
                }
                if (!Schema::hasColumn('pos_registers', 'notes')) {
                    $table->text('notes')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pos_sales')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                $cols = ['status', 'trx_id', 'discount_percent', 'tendered_amount', 'change_amount', 'notes', 'void_reason', 'voided_at'];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('pos_sales', $c)) {
                        $table->dropColumn($c);
                    }
                }
                if (Schema::hasColumn('pos_sales', 'voided_by')) {
                    $table->dropConstrainedForeignId('voided_by');
                }
            });
        }

        if (Schema::hasTable('pos_registers')) {
            Schema::table('pos_registers', function (Blueprint $table) {
                $cols = ['closing_cash', 'closed_at', 'notes'];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('pos_registers', $c)) {
                        $table->dropColumn($c);
                    }
                }
                if (Schema::hasColumn('pos_registers', 'closed_by')) {
                    $table->dropConstrainedForeignId('closed_by');
                }
            });
        }
    }
};
