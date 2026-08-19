<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('idea_invoices', function (Blueprint $table) {
            // Change type to varchar to flexibly accommodate quotation & tender
            $table->string('type', 50)->default('invoice')->change();
            
            // Quotation & Tender specific fields
            if (!Schema::hasColumn('idea_invoices', 'subject')) {
                $table->string('subject', 255)->nullable()->after('type')->comment('দরপত্র বা কোটেশনের বিষয়');
            }
            if (!Schema::hasColumn('idea_invoices', 'reference_no')) {
                $table->string('reference_no', 100)->nullable()->after('subject')->comment('দরপত্র / স্মারক রেফারেন্স নম্বর');
            }
            if (!Schema::hasColumn('idea_invoices', 'valid_until')) {
                $table->date('valid_until')->nullable()->after('invoice_date')->comment('কোটেশন বা দরপত্রের মেয়াদের শেষ তারিখ');
            }
            if (!Schema::hasColumn('idea_invoices', 'terms_conditions')) {
                $table->text('terms_conditions')->nullable()->after('notes')->comment('দরপত্র ও কোটেশনের বিশেষ শর্তাবলী');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('idea_invoices', function (Blueprint $table) {
            $table->dropColumn(['subject', 'reference_no', 'valid_until', 'terms_conditions']);
        });
    }
};
