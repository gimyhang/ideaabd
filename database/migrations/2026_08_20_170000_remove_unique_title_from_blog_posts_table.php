<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drops UNIQUE index/constraint on blog_posts.title so duplicate poem/article titles are allowed.
     */
    public function up(): void
    {
        if (!Schema::hasTable('blog_posts')) {
            return;
        }

        try {
            Schema::table('blog_posts', function (Blueprint $table) {
                $table->dropUnique('blog_posts_title_unique');
            });
        } catch (\Throwable $e) {
            try {
                Schema::table('blog_posts', function (Blueprint $table) {
                    $table->dropUnique(['title']);
                });
            } catch (\Throwable $e2) {
                try {
                    DB::statement('DROP INDEX IF EXISTS blog_posts_title_unique');
                } catch (\Throwable $e3) {}
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
