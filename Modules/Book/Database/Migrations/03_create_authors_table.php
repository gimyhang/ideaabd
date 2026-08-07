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
        Schema::create('authors', function (Blueprint $table) {
            $table->id();

            // Core Author Details
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('designation')->nullable(); // উদাহরণ: কবি, কথাসাহিত্যিক ও অনুবাদক
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();

            // Personal & Historical Info
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('born_date')->nullable();
            $table->date('death_date')->nullable(); // প্রখ্যাত বা প্রয়াত লেখকদের জন্য

            // Social & External Profiles (JSON Structure)
            $table->json('social_links')->nullable(); // Facebook, Wikipedia, Goodreads ইত্যাদি

            // SEO Optimization
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Display & Status Controls
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false); // সেরা/জনপ্রিয় লেখক হাইলাইট করতে
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};