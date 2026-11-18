<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** VAT / Tax mode and rates per invoice: {mode, vat_rate, tax_rate, vat_amount, tax_amount, net}. */
    public function up(): void
    {
        if (Schema::hasTable('idea_invoices') && !Schema::hasColumn('idea_invoices', 'tax_meta')) {
            Schema::table('idea_invoices', function (Blueprint $table) {
                $table->json('tax_meta')->nullable()->after('tax');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('idea_invoices', 'tax_meta')) {
            Schema::table('idea_invoices', function (Blueprint $table) {
                $table->dropColumn('tax_meta');
            });
        }
    }
};
