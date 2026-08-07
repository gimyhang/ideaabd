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
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();

            // Core Publisher Info
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->year('established_year')->nullable();
            $table->string('website')->nullable();

            // Contact & Legal Verification
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('trade_license_no')->nullable(); // সিকিউরিটি ও ভেন্ডর ভেরিফিকেশনের জন্য

            // Multi-Vendor & Commission Business Logic
            $table->decimal('commission_rate', 5, 2)->default(0.00); // যেমন: 15.50%

            // SEO Metadata
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Display, Verification & Security Controls
            $table->boolean('is_verified')->default(false); // ভেরিফায়েড ব্যাজ দেখানোর জন্য
            $table->boolean('is_featured')->default(false); // পপুলার প্রকাশনী হাইলাইট করতে
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
        Schema::dropIfExists('publishers');
    }
};