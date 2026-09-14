<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('idea_invoices')) {
            Schema::table('idea_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('idea_invoices', 'previous_due')) {
                    $table->decimal('previous_due', 12, 2)->default(0)->nullable()->after('tax');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('idea_invoices')) {
            Schema::table('idea_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('idea_invoices', 'previous_due')) {
                    $table->dropColumn('previous_due');
                }
            });
        }
    }
};
