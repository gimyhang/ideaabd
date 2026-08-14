<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotent: a partially migrated database can re-run this safely.
        if (Schema::hasTable("webzines")) {
            return;
        }

        Schema::create('webzines', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('slug')->unique()->index();
            $table->longText('description')->nullable();
            $table->string('epub_file_path')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('issue_number');
            $table->date('publication_date')->nullable();
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->onDelete('set null');
            $table->string('category')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_published');
            $table->index('published_at');
            $table->index('category');
        });

        Schema::create('webzine_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webzine_id')->constrained('webzines')->onDelete('cascade');
            $table->string('title');
            $table->longText('content');
            $table->foreignId('author_id')->nullable()->constrained('authors')->onDelete('set null');
            $table->integer('page_number')->nullable();
            $table->string('featured_image')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('webzine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webzine_articles');
        Schema::dropIfExists('webzines');
    }
};
