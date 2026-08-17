<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('blog_posts') && !Schema::hasColumn('blog_posts', 'subtitle')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                $table->string('subtitle', 500)->nullable()->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('blog_posts') && Schema::hasColumn('blog_posts', 'subtitle')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                $table->dropColumn('subtitle');
            });
        }
    }
};
