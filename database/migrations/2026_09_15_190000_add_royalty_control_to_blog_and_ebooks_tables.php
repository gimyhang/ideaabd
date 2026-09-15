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
        // 1. Add royalty control columns to blog_posts
        if (Schema::hasTable('blog_posts')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                if (!Schema::hasColumn('blog_posts', 'is_royalty_free')) {
                    $table->boolean('is_royalty_free')->default(false)->after('status');
                }
                if (!Schema::hasColumn('blog_posts', 'royalty_percentage')) {
                    $table->decimal('royalty_percentage', 5, 2)->default(70.00)->after('is_royalty_free');
                }
            });
        }

        // 2. Add is_royalty_free to ebooks
        if (Schema::hasTable('ebooks')) {
            Schema::table('ebooks', function (Blueprint $table) {
                if (!Schema::hasColumn('ebooks', 'is_royalty_free')) {
                    $table->boolean('is_royalty_free')->default(false)->after('royalty_percentage');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('blog_posts')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                if (Schema::hasColumn('blog_posts', 'is_royalty_free')) {
                    $table->dropColumn('is_royalty_free');
                }
                if (Schema::hasColumn('blog_posts', 'royalty_percentage')) {
                    $table->dropColumn('royalty_percentage');
                }
            });
        }

        if (Schema::hasTable('ebooks')) {
            Schema::table('ebooks', function (Blueprint $table) {
                if (Schema::hasColumn('ebooks', 'is_royalty_free')) {
                    $table->dropColumn('is_royalty_free');
                }
            });
        }
    }
};
