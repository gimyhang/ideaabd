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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained('books')->nullOnDelete();

            // Data Snapshots (অর্ডারের সময় বইয়ের নাম বা দাম পরিবর্তন হলে যেন পুরানো অর্ডারে প্রভাব না পড়ে)
            $table->string('book_title');
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);

            // Digital Product Flag
            $table->boolean('is_ebook')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};