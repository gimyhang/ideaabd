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
            if (!Schema::hasColumn('books', 'sku')) {
                $table->string('sku', 50)->nullable()->after('isbn');
            }
            if (!Schema::hasColumn('books', 'translator_name')) {
                $table->string('translator_name', 255)->nullable()->after('author_name');
            }
            if (!Schema::hasColumn('books', 'editor_name')) {
                $table->string('editor_name', 255)->nullable()->after('translator_name');
            }
            if (!Schema::hasColumn('books', 'cover_artist')) {
                $table->string('cover_artist', 255)->nullable()->after('editor_name');
            }
            if (!Schema::hasColumn('books', 'paper_type')) {
                $table->string('paper_type', 100)->nullable()->after('edition');
            }
            if (!Schema::hasColumn('books', 'weight')) {
                $table->unsignedInteger('weight')->nullable()->after('page_count');
            }
            if (!Schema::hasColumn('books', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->nullable()->after('discount_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $columns = [
                'sku',
                'translator_name',
                'editor_name',
                'cover_artist',
                'paper_type',
                'weight',
                'cost_price',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('books', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
