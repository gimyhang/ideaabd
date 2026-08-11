<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core catalog schema: categories, publishers, authors, books, ebooks.
 *
 * Columns mirror the $fillable lists on the corresponding module models
 * (Modules\Book\Models\Book, Modules\Ebook\Models\Ebook, etc.).
 * Each table is created only if absent so this can run on an existing database.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon_or_image')->nullable();
                $table->string('meta_title')->nullable();
                $table->string('meta_description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_featured')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_active', 'sort_order']);
            });
        }

        if (! Schema::hasTable('publishers')) {
            Schema::create('publishers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('logo')->nullable();
                $table->string('email')->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('website')->nullable();
                $table->string('address')->nullable();
                $table->string('country', 100)->nullable();
                $table->json('social_links')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('authors')) {
            Schema::create('authors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('bio')->nullable();
                $table->string('avatar')->nullable();
                $table->string('email')->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('website')->nullable();
                $table->json('social_links')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->boolean('is_active')->default(true);
                // Legacy flag: some views list publishers out of this table.
                $table->boolean('is_publisher')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_active', 'is_publisher']);
            });
        }

        if (! Schema::hasTable('books')) {
            Schema::create('books', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_id')->nullable();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('publisher_id')->nullable()->constrained('publishers')->nullOnDelete();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('isbn', 30)->nullable();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('discount_price', 10, 2)->nullable();
                $table->string('cover_image')->nullable();
                $table->string('sample_pdf_path')->nullable();
                $table->unsignedInteger('preview_pages')->default(0);
                $table->unsignedInteger('stock_quantity')->default(0);
                $table->unsignedInteger('sales_count')->default(0);
                $table->enum('format', ['printed', 'ebook', 'both'])->default('printed');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_active', 'created_at']);
                $table->index('category_id');
            });
        }

        if (! Schema::hasTable('book_author')) {
            Schema::create('book_author', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
                $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['book_id', 'author_id']);
            });
        }

        if (! Schema::hasTable('ebooks')) {
            Schema::create('ebooks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_id')->nullable();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('isbn', 30)->nullable();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('discount_price', 10, 2)->nullable();
                $table->string('cover_image')->nullable();
                $table->string('file_path')->nullable();
                $table->string('file_type', 20)->nullable();
                $table->string('file_size', 30)->nullable();
                $table->unsignedInteger('pages')->default(0);
                $table->unsignedInteger('sales_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_active', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ebooks');
        Schema::dropIfExists('book_author');
        Schema::dropIfExists('books');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('categories');
    }
};
