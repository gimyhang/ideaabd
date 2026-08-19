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
        Schema::table('idea_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('idea_invoices', 'customer_org')) {
                $table->string('customer_org', 255)->nullable()->after('customer_name')->comment('প্রতিষ্ঠান, লাইব্রেরি বা সংস্থার নাম');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('idea_invoices', function (Blueprint $table) {
            $table->dropColumn('customer_org');
        });
    }
};
