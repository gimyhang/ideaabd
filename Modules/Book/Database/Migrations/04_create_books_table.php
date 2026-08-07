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
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->nullOnDelete(); 

            // Basic Info & SKU
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('sku')->nullable()->unique(); // Inventory Stock Keeping Unit
            $table->string('isbn')->nullable()->unique();
            $table->string('edition')->nullable();
            $table->integer('pages')->nullable();
            $table->string('cover_type')->default('Paperback'); // Hardcover/Paperback
            $table->string('language')->default('bn');

            // Descriptions & Media
            $table->longText('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('sample_pdf')->nullable(); // 'একটু পড়ে দেখুন'

            // Digital E-Book Support
            $table->boolean('is_ebook')->default(false);
            $table->string('ebook_file')->nullable();

            // Pricing, Weight & Stock
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable(); // ছাড়ের পর ফাইনাল দাম
            
            // Dynamic Product-Specific Offers
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable(); // 'fixed' (টাকা) নাকি 'percentage' (%)
            $table->decimal('discount_value', 8, 2)->nullable(); // ছাড়ের পরিমাণ (যেমন: ২০% বা ৫০ টাকা)
            $table->string('offer_badge')->nullable(); // যেমন: "ঈদ অফার", "বইমেলা ধামাকা", "Flash Sale"
            $table->timestamp('offer_start_at')->nullable(); // অফার শুরু হওয়ার তারিখ ও সময়
            $table->timestamp('offer_end_at')->nullable(); // অফার শেষ হওয়ার সময় (কাউন্টডাউন টাইমারের জন্য)

            $table->integer('stock_qty')->default(0);
            $table->integer('weight_grams')->nullable(); // কুরিয়ার চার্জ হিসাবের জন্য
            $table->unsignedBigInteger('sales_count')->default(0);

            // Rating Cache
            $table->decimal('rating_cache', 3, 2)->default(0.00);
            $table->unsignedInteger('reviews_count')->default(0);

            // Status & Flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('is_pre_order')->default(false);

            // SEO Metadata
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};