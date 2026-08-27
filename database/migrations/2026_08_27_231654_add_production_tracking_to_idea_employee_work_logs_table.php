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
        if (Schema::hasTable('idea_employee_work_logs')) {
            Schema::table('idea_employee_work_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('idea_employee_work_logs', 'print_date')) {
                    $table->date('print_date')->nullable()->after('log_date');
                }
                if (!Schema::hasColumn('idea_employee_work_logs', 'printed_quantity')) {
                    $table->decimal('printed_quantity', 12, 2)->default(0)->after('book_title');
                }
                if (!Schema::hasColumn('idea_employee_work_logs', 'received_quantity')) {
                    $table->decimal('received_quantity', 12, 2)->default(0)->after('printed_quantity');
                }
                if (!Schema::hasColumn('idea_employee_work_logs', 'delivered_quantity')) {
                    $table->decimal('delivered_quantity', 12, 2)->default(0)->after('received_quantity');
                }
                if (!Schema::hasColumn('idea_employee_work_logs', 'incomplete_quantity')) {
                    $table->decimal('incomplete_quantity', 12, 2)->default(0)->after('delivered_quantity');
                }
                if (!Schema::hasColumn('idea_employee_work_logs', 'wastage_quantity')) {
                    $table->decimal('wastage_quantity', 12, 2)->default(0)->after('incomplete_quantity');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('idea_employee_work_logs')) {
            Schema::table('idea_employee_work_logs', function (Blueprint $table) {
                $columns = ['print_date', 'printed_quantity', 'received_quantity', 'delivered_quantity', 'incomplete_quantity', 'wastage_quantity'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('idea_employee_work_logs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
