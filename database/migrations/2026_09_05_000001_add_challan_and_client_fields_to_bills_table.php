<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            if (!Schema::hasColumn('bills', 'type')) {
                $table->string('type', 30)->default('invoice')->after('bill_no'); // invoice, challan, quotation
            }
            if (!Schema::hasColumn('bills', 'subject')) {
                $table->string('subject', 255)->nullable()->after('type');
            }
            if (!Schema::hasColumn('bills', 'reference_no')) {
                $table->string('reference_no', 100)->nullable()->after('subject');
            }
            if (!Schema::hasColumn('bills', 'customer_org')) {
                $table->string('customer_org', 255)->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('bills', 'customer_designation')) {
                $table->string('customer_designation', 150)->nullable()->after('customer_org');
            }
            if (!Schema::hasColumn('bills', 'customer_address')) {
                $table->text('customer_address')->nullable()->after('customer_email');
            }
            if (!Schema::hasColumn('bills', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('bills', 'due_amount')) {
                $table->decimal('due_amount', 12, 2)->default(0)->after('paid_amount');
            }
            if (!Schema::hasColumn('bills', 'bill_date')) {
                $table->date('bill_date')->nullable()->after('due_amount');
            }
            if (!Schema::hasColumn('bills', 'terms_conditions')) {
                $table->text('terms_conditions')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $columns = [
                'type', 'subject', 'reference_no', 'customer_org', 'customer_designation',
                'customer_address', 'paid_amount', 'due_amount', 'bill_date', 'terms_conditions'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('bills', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
