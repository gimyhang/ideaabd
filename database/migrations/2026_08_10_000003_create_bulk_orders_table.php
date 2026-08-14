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
        if (Schema::hasTable("bulk_orders")) {
            return;
        }

        Schema::create('bulk_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('order_type', 30); // educational, commercial, bulk_purchase
            $table->string('institution_name');
            $table->string('institution_type', 30);
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone', 20);
            $table->text('address');
            $table->integer('quantity');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->text('special_requirements')->nullable();
            $table->string('status', 30)->default('pending'); // pending, approved, rejected, completed
            $table->text('notes')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->date('estimated_delivery_date')->nullable();
            $table->boolean('is_invoice_required')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('status');
            $table->index('order_type');
        });

        Schema::create('bulk_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_order_id')->constrained('bulk_orders')->onDelete('cascade');
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();

            $table->unique(['bulk_order_id', 'book_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_order_items');
        Schema::dropIfExists('bulk_orders');
    }
};
