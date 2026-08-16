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
            if (!Schema::hasColumn('books', 'summary')) {
                $table->text('summary')->nullable()->after('description');
            }
            if (!Schema::hasColumn('books', 'published_at')) {
                $table->date('published_at')->nullable()->after('summary');
            }
            if (!Schema::hasColumn('books', 'edition')) {
                $table->string('edition', 100)->nullable()->after('published_at');
            }
            if (!Schema::hasColumn('books', 'stock_status')) {
                $table->string('stock_status', 30)->default('in_stock')->after('stock_quantity');
            }
            if (!Schema::hasColumn('books', 'cover_type')) {
                $table->string('cover_type', 30)->default('paperback')->after('format');
            }
            if (!Schema::hasColumn('books', 'hardcover_price')) {
                $table->decimal('hardcover_price', 10, 2)->nullable()->after('discount_price');
            }
            if (!Schema::hasColumn('books', 'hardcover_discount_price')) {
                $table->decimal('hardcover_discount_price', 10, 2)->nullable()->after('hardcover_price');
            }
            if (!Schema::hasColumn('books', 'page_count')) {
                $table->unsignedInteger('page_count')->nullable()->after('preview_pages');
            }
            if (!Schema::hasColumn('books', 'language')) {
                $table->string('language', 50)->default('বাংলা')->after('page_count');
            }
            if (!Schema::hasColumn('books', 'author_bio')) {
                $table->text('author_bio')->nullable()->after('author_link_id');
            }
            if (!Schema::hasColumn('books', 'author_image')) {
                $table->string('author_image')->nullable()->after('author_bio');
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
                'summary',
                'published_at',
                'edition',
                'stock_status',
                'cover_type',
                'hardcover_price',
                'hardcover_discount_price',
                'page_count',
                'language',
                'author_bio',
                'author_image',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('books', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
