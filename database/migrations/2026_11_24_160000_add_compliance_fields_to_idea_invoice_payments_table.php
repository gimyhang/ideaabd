<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * VDS / TDS compliance fields for the deduction register (admin/accounting/tax-vat-deductions).
 *
 *  VDS (উৎসে মূসক): the buyer deducts VAT, deposits it and gives us a Mushak-6.6 certificate; we take that amount
 *  as a decreasing adjustment in our Mushak-9.1 return. TDS (উৎসে আয়কর): the buyer deducts income tax and gives a
 *  certificate; we claim it as tax credit in the income-year return.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('idea_invoice_payments')) {
            return;
        }
        Schema::table('idea_invoice_payments', function (Blueprint $table) {
            $add = fn (string $c) => !Schema::hasColumn('idea_invoice_payments', $c);
            if ($add('deductor_bin')) $table->string('deductor_bin', 20)->nullable();
            if ($add('deductor_tin')) $table->string('deductor_tin', 20)->nullable();
            if ($add('vds_certificate_no')) $table->string('vds_certificate_no', 100)->nullable();
            if ($add('vds_certificate_date')) $table->date('vds_certificate_date')->nullable();
            if ($add('vds_adjusted_period')) $table->string('vds_adjusted_period', 7)->nullable();   // YYYY-MM of the 9.1 return
            if ($add('tds_certificate_no')) $table->string('tds_certificate_no', 100)->nullable();
            if ($add('tds_certificate_date')) $table->date('tds_certificate_date')->nullable();
            if ($add('tds_claimed_year')) $table->string('tds_claimed_year', 9)->nullable();         // e.g. 2025-26
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('idea_invoice_payments')) {
            return;
        }
        foreach (['deductor_bin', 'deductor_tin', 'vds_certificate_no', 'vds_certificate_date', 'vds_adjusted_period', 'tds_certificate_no', 'tds_certificate_date', 'tds_claimed_year'] as $c) {
            if (Schema::hasColumn('idea_invoice_payments', $c)) {
                Schema::table('idea_invoice_payments', fn (Blueprint $t) => $t->dropColumn($c));
            }
        }
    }
};
