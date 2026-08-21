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
            if (!Schema::hasColumn('books', 'book_height_cm')) {
                $table->decimal('book_height_cm', 8, 2)->nullable()->after('book_size')
                      ->comment('বইয়ের উচ্চতা (cm)');
            }
            if (!Schema::hasColumn('books', 'book_width_cm')) {
                $table->decimal('book_width_cm', 8, 2)->nullable()->after('book_height_cm')
                      ->comment('বইয়ের প্রস্থ (cm)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $columns = ['book_height_cm', 'book_width_cm'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('books', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
