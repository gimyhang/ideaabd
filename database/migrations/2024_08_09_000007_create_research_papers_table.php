<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotent: a partially migrated database can re-run this safely.
        if (Schema::hasTable("research_papers")) {
            return;
        }

        Schema::create('research_papers', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('slug')->unique()->index();
            $table->longText('abstract')->nullable();
            $table->longText('content');
            $table->string('pdf_file_path')->nullable();
            $table->json('keywords')->nullable();
            $table->string('category')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('authors')->onDelete('set null');
            $table->date('publication_date')->nullable();
            $table->string('doi')->unique()->nullable();
            $table->unsignedInteger('citations_count')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('download_count')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_published');
            $table->index('published_at');
            $table->index('category');
            $table->index('author_id');
        });

        Schema::create('research_co_authors', function (Blueprint $table) {
            $table->foreignId('paper_id')->constrained('research_papers')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('authors')->onDelete('cascade');
            $table->primary(['paper_id', 'author_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_co_authors');
        Schema::dropIfExists('research_papers');
    }
};
