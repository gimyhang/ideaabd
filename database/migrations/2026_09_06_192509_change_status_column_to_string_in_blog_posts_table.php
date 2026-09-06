<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('blog_posts')) {
            try {
                DB::statement("ALTER TABLE `blog_posts` MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'draft'");
            } catch (\Throwable $e) {
                // Fallback for non-MySQL or drivers
                Schema::table('blog_posts', function (Blueprint $table) {
                    $table->string('status', 30)->default('draft')->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('blog_posts')) {
            try {
                DB::statement("ALTER TABLE `blog_posts` MODIFY COLUMN `status` ENUM('draft', 'pending', 'published', 'archived', 'hold', 'rejected') NOT NULL DEFAULT 'draft'");
            } catch (\Throwable $e) {}
        }
    }
};
