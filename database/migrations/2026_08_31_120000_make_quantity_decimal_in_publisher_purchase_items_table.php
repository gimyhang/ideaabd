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
        if (Schema::hasTable('publisher_purchase_items')) {
            Schema::table('publisher_purchase_items', function (Blueprint $table) {
                $table->decimal('quantity', 10, 3)->default(1.000)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('publisher_purchase_items')) {
            Schema::table('publisher_purchase_items', function (Blueprint $table) {
                $table->integer('quantity')->default(1)->change();
            });
        }
    }
};
