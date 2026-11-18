<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Line items of a web order: [{type, id, title, format, qty, price}, …].
     * Until now they were only written into admin_notes as one line of text.
     */
    public function up(): void
    {
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'items')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->json('items')->nullable()->after('book_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'items')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('items');
            });
        }
    }
};
