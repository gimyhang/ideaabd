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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_address');
            $table->string('district')->nullable();
            
            // Gift Fields
            $table->boolean('is_gift')->default(false);
            $table->string('gift_recipient_name')->nullable();
            $table->string('gift_recipient_phone')->nullable();
            $table->string('gift_recipient_address')->nullable();
            $table->text('gift_message')->nullable();
            
            // Order details
            $table->foreignId('book_id')->nullable(); // Since no cart, order is tied to a single book for now
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
