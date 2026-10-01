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
        if (!Schema::hasTable('seo_metas')) {
            Schema::create('seo_metas', function (Blueprint $table) {
                $table->id();
                
                // Polymorphic relation (App\Models\Book, BlogPost, Author, etc.)
                $table->nullableMorphs('seoable');
                
                // Custom URL identifier (for static pages like '/about', '/contact', '/books')
                $table->string('url_path', 255)->nullable()->index();
                
                // Standard Core Meta Tags
                $table->string('meta_title', 255)->nullable();
                $table->text('meta_description')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->string('canonical_url', 500)->nullable();
                $table->string('robots', 50)->default('index, follow');
                $table->string('focus_keyword', 150)->nullable();
                
                // OpenGraph / Facebook Tags
                $table->string('og_title', 255)->nullable();
                $table->text('og_description')->nullable();
                $table->string('og_image', 500)->nullable();
                $table->string('og_type', 50)->default('website');
                
                // Twitter Card Tags
                $table->string('twitter_card', 50)->default('summary_large_image');
                $table->string('twitter_title', 255)->nullable();
                $table->text('twitter_description')->nullable();
                $table->string('twitter_image', 500)->nullable();
                
                // Structured Data / Schema.org
                $table->string('schema_type', 80)->nullable();
                $table->json('schema_json')->nullable();
                
                // Intelligent Quality & Scoring Metrics
                $table->unsignedTinyInteger('seo_score')->default(0); // 0-100
                $table->json('seo_analysis')->nullable(); // Audits: issues, warnings, passed checks
                $table->boolean('is_auto_generated')->default(true);
                $table->timestamp('last_scanned_at')->nullable();
                
                $table->timestamps();
                
                $table->unique(['seoable_type', 'seoable_id'], 'seo_metas_polymorphic_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
