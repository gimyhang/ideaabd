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

            // Unique Order Identifier (Tracking ID)
            $table->string('order_number')->unique()->index();

            // Customer Association (Guest Checkout Support)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Customer Contact & Shipping Information
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone');
            $table->text('shipping_address');
            $table->string('district')->nullable();
            $table->string('division')->nullable();
            $table->string('postal_code')->nullable();

            // Pricing Breakdown
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('shipping_cost', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency')->default('BDT');

            // Payment Details
            $table->string('payment_method')->default('cod'); // cod, bkash, sslcommerz, nagad
            $table->string('payment_status')->default('pending')->index(); // pending, paid, failed, refunded
            $table->string('transaction_id')->nullable()->unique(); // Bkash / SSLCommerz Transaction ID

            // Order Status & Fulfillment
            $table->string('status')->default('pending')->index(); // pending, processing, shipped, delivered, cancelled, returned
            $table->string('coupon_code')->nullable();

            // Logistics & Tracking
            $table->string('courier_name')->nullable(); // Steadfast, RedX, Sundarban, Pathao
            $table->string('tracking_number')->nullable();
            $table->text('note')->nullable(); // Customer special instructions

            $table->timestamps();
            $table->softDeletes();
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