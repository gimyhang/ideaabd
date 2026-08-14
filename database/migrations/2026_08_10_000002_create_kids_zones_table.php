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
        // Idempotent: a partially migrated database can re-run this safely.
        if (Schema::hasTable("kids_zones")) {
            return;
        }

        Schema::create('kids_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('age_group', 20); // 0-3, 3-6, 6-9, 9-12, 12-16, all
            $table->string('icon', 100)->nullable();
            $table->string('color', 7)->nullable();
            $table->string('banner_image')->nullable();
            $table->integer('featured_position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('is_active');
        });

        Schema::create('kids_zone_book', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('kids_zones')->onDelete('cascade');
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->unique(['zone_id', 'book_id']);
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kids_zone_book');
        Schema::dropIfExists('kids_zones');
    }
};
