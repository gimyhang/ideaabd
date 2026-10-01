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
        // 1. 301 / 302 SEO URL Redirects Table
        if (!Schema::hasTable('seo_redirects')) {
            Schema::create('seo_redirects', function (Blueprint $table) {
                $table->id();
                $table->string('source_url', 500)->index();
                $table->string('target_url', 500);
                $table->unsignedSmallInteger('status_code')->default(301); // 301 Permanent or 302 Temporary
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('hits_count')->default(0);
                $table->timestamp('last_hit_at')->nullable();
                $table->string('notes', 255)->nullable();
                $table->timestamps();
            });
        }

        // 2. 404 Broken Link Monitor Table
        if (!Schema::hasTable('seo_broken_links')) {
            Schema::create('seo_broken_links', function (Blueprint $table) {
                $table->id();
                $table->string('url', 500)->index();
                $table->string('referer', 500)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->unsignedBigInteger('hits_count')->default(1);
                $table->boolean('is_resolved')->default(false);
                $table->timestamp('last_hit_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_broken_links');
        Schema::dropIfExists('seo_redirects');
    }
};
