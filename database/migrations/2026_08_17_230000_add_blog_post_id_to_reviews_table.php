<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reviews') && !Schema::hasColumn('reviews', 'blog_post_id')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->unsignedBigInteger('blog_post_id')->nullable()->after('ebook_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'blog_post_id')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropColumn('blog_post_id');
            });
        }
    }
};
