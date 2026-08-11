<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tables the catalog models already reference but that had no migration:
 *  - reviews       (Book::reviews(), Ebook::reviews())
 *  - ebook_author  (Ebook::authors())
 *
 * Also adds the publisher link to ebooks so Ebook::publisher() can resolve.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('book_id')->nullable()->constrained('books')->cascadeOnDelete();
                $table->foreignId('ebook_id')->nullable()->constrained('ebooks')->cascadeOnDelete();
                $table->unsignedTinyInteger('rating')->default(5);
                $table->string('title')->nullable();
                $table->text('comment')->nullable();
                $table->boolean('is_approved')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['book_id', 'is_approved']);
                $table->index(['ebook_id', 'is_approved']);
            });
        }

        if (! Schema::hasTable('ebook_author')) {
            Schema::create('ebook_author', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ebook_id')->constrained('ebooks')->cascadeOnDelete();
                $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['ebook_id', 'author_id']);
            });
        }

        if (Schema::hasTable('ebooks') && ! Schema::hasColumn('ebooks', 'publisher_id')) {
            Schema::table('ebooks', function (Blueprint $table) {
                $table->foreignId('publisher_id')->nullable()->after('author_id')
                    ->constrained('publishers')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ebook_author');
        Schema::dropIfExists('reviews');

        if (Schema::hasTable('ebooks') && Schema::hasColumn('ebooks', 'publisher_id')) {
            Schema::table('ebooks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('publisher_id');
            });
        }
    }
};
