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
            if (!Schema::hasColumn('books', 'title_en')) {
                $table->string('title_en')->nullable()->after('title');
            }
            if (!Schema::hasColumn('books', 'rewriter_name')) {
                $table->string('rewriter_name')->nullable()->after('editor_name');
            }
            if (!Schema::hasColumn('books', 'sub_category_name')) {
                $table->string('sub_category_name')->nullable()->after('category_id');
            }
            if (!Schema::hasColumn('books', 'ekushey_category')) {
                $table->string('ekushey_category')->nullable()->after('sub_category_name');
            }
            if (!Schema::hasColumn('books', 'genre_category')) {
                $table->string('genre_category')->nullable()->after('ekushey_category');
            }
            if (!Schema::hasColumn('books', 'audience_category')) {
                $table->string('audience_category')->nullable()->after('genre_category');
            }
            if (!Schema::hasColumn('books', 'look_inside_type')) {
                $table->string('look_inside_type')->default('pdf')->nullable()->after('sample_pdf_path');
            }
            if (!Schema::hasColumn('books', 'look_inside_images')) {
                $table->json('look_inside_images')->nullable()->after('look_inside_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'title_en',
                'rewriter_name',
                'sub_category_name',
                'ekushey_category',
                'genre_category',
                'audience_category',
                'look_inside_type',
                'look_inside_images',
            ]);
        });
    }
};
