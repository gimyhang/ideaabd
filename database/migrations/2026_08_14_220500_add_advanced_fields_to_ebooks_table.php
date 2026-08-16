<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ebooks')) {
            Schema::table('ebooks', function (Blueprint $table) {
                if (!Schema::hasColumn('ebooks', 'epub_file_path')) {
                    $table->string('epub_file_path')->nullable()->after('file_path');
                }
                if (!Schema::hasColumn('ebooks', 'sample_file_path')) {
                    $table->string('sample_file_path')->nullable()->after('file_type');
                }
                if (!Schema::hasColumn('ebooks', 'preview_pages')) {
                    $table->unsignedInteger('preview_pages')->default(10)->after('pages');
                }
                if (!Schema::hasColumn('ebooks', 'format')) {
                    $table->string('format', 20)->nullable()->default('pdf')->after('preview_pages');
                }
                if (!Schema::hasColumn('ebooks', 'download_count')) {
                    $table->unsignedInteger('download_count')->default(0)->after('sales_count');
                }
                if (!Schema::hasColumn('ebooks', 'read_count')) {
                    $table->unsignedInteger('read_count')->default(0)->after('download_count');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ebooks')) {
            Schema::table('ebooks', function (Blueprint $table) {
                $columns = array_filter(
                    ['epub_file_path', 'sample_file_path', 'preview_pages', 'format', 'download_count', 'read_count'],
                    fn ($col) => Schema::hasColumn('ebooks', $col)
                );
                if ($columns) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
