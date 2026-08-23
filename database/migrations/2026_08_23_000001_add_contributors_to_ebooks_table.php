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
        Schema::table('ebooks', function (Blueprint $table) {
            if (!Schema::hasColumn('ebooks', 'editor_name')) {
                $table->string('editor_name', 255)->nullable()->after('author_name');
            }
            if (!Schema::hasColumn('ebooks', 'rewriter_name')) {
                $table->string('rewriter_name', 255)->nullable()->after('editor_name');
            }
            if (!Schema::hasColumn('ebooks', 'translator_name')) {
                $table->string('translator_name', 255)->nullable()->after('rewriter_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropColumn(['editor_name', 'rewriter_name', 'translator_name']);
        });
    }
};
