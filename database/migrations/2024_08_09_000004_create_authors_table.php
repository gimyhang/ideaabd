<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique()->index();
            $table->longText('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index('email');
        });

        Schema::create('author_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('authors')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->index();
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('author_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_submissions');
        Schema::dropIfExists('authors');
    }
};
