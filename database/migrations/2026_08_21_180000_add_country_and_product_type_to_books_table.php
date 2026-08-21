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
            if (!Schema::hasColumn('books', 'country')) {
                $table->string('country', 100)->nullable()->default('Bangladesh')->after('language');
            }
            if (!Schema::hasColumn('books', 'product_type')) {
                $table->string('product_type', 50)->nullable()->default('book')->after('format');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn('books', 'product_type')) {
                $table->dropColumn('product_type');
            }
        });
    }
};
