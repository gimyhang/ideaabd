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
        if (Schema::hasTable('idea_invoice_payments')) {
            Schema::table('idea_invoice_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('idea_invoice_payments', 'net_amount')) {
                    $table->decimal('net_amount', 12, 2)->default(0)->nullable()->after('amount')->comment('গ্রাহকের নিকট হতে গৃহীত নিট অর্থ (চেক/ক্যাশ)');
                }
                if (!Schema::hasColumn('idea_invoice_payments', 'vat_deduction_rate')) {
                    $table->decimal('vat_deduction_rate', 5, 2)->default(0)->nullable()->after('net_amount')->comment('উৎসে মূসক/ভ্যাট কর্তন হার (%)');
                }
                if (!Schema::hasColumn('idea_invoice_payments', 'vat_deduction_amount')) {
                    $table->decimal('vat_deduction_amount', 12, 2)->default(0)->nullable()->after('vat_deduction_rate')->comment('উৎসে কর্তনকৃত মূসক/ভ্যাট (টাকা)');
                }
                if (!Schema::hasColumn('idea_invoice_payments', 'tax_deduction_rate')) {
                    $table->decimal('tax_deduction_rate', 5, 2)->default(0)->nullable()->after('vat_deduction_amount')->comment('উৎসে আয়কর/ট্যাক্স কর্তন হার (%)');
                }
                if (!Schema::hasColumn('idea_invoice_payments', 'tax_deduction_amount')) {
                    $table->decimal('tax_deduction_amount', 12, 2)->default(0)->nullable()->after('tax_deduction_rate')->comment('উৎসে কর্তনকৃত আয়কর/ট্যাক্স (টাকা)');
                }
                if (!Schema::hasColumn('idea_invoice_payments', 'other_deduction_amount')) {
                    $table->decimal('other_deduction_amount', 12, 2)->default(0)->nullable()->after('tax_deduction_amount')->comment('অন্যান্য কর্তন (টাকা)');
                }
                if (!Schema::hasColumn('idea_invoice_payments', 'deduction_challan_no')) {
                    $table->string('deduction_challan_no', 100)->nullable()->after('other_deduction_amount')->comment('ট্রেজারি চালান নং / মূসক-৬.৬ প্রত্যয়নপত্র নং');
                }
                if (!Schema::hasColumn('idea_invoice_payments', 'deduction_notes')) {
                    $table->text('deduction_notes')->nullable()->after('deduction_challan_no')->comment('ভ্যাট-ট্যাক্স কর্তন সংক্রান্ত অতিরিক্ত নোট');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('idea_invoice_payments')) {
            Schema::table('idea_invoice_payments', function (Blueprint $table) {
                $columns = [
                    'net_amount',
                    'vat_deduction_rate',
                    'vat_deduction_amount',
                    'tax_deduction_rate',
                    'tax_deduction_amount',
                    'other_deduction_amount',
                    'deduction_challan_no',
                    'deduction_notes',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('idea_invoice_payments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
