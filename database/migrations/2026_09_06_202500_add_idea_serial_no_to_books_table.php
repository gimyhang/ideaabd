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
        Schema::table('books', function (Blueprint $table) {
            if (!Schema::hasColumn('books', 'idea_serial_no')) {
                $table->string('idea_serial_no', 50)->nullable()->after('sku');
                $table->index('idea_serial_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'idea_serial_no')) {
                $table->dropIndex(['idea_serial_no']);
                $table->dropColumn('idea_serial_no');
            }
        });
    }
};
