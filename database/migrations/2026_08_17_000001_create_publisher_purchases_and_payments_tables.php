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
        if (! Schema::hasTable('publisher_purchases')) {
            Schema::create('publisher_purchases', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_no', 50)->unique();
                $table->foreignId('publisher_id')->constrained('publishers')->cascadeOnDelete();
                $table->date('purchase_date');
                $table->enum('payment_type', ['cash', 'credit', 'partial'])->default('cash'); // নগদ / বাকি / আংশিক
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->decimal('discount_amount', 12, 2)->default(0.00);
                $table->decimal('grand_total', 12, 2)->default(0.00);
                $table->decimal('paid_amount', 12, 2)->default(0.00);
                $table->decimal('due_amount', 12, 2)->default(0.00);
                $table->enum('payment_status', ['paid', 'partial', 'due'])->default('due'); // পরিশোধিত / আংশিক / বকেয়া
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('publisher_purchase_items')) {
            Schema::create('publisher_purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained('publisher_purchases')->cascadeOnDelete();
                $table->foreignId('book_id')->nullable()->constrained('books')->nullOnDelete();
                $table->string('book_title');
                $table->string('author_name')->nullable();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->integer('quantity')->default(1);
                $table->decimal('unit_cost_price', 10, 2)->default(0.00); // ক্রয়মূল্য
                $table->decimal('unit_sale_price', 10, 2)->default(0.00); // বিক্রয়মূল্য
                $table->decimal('subtotal', 12, 2)->default(0.00);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('publisher_payments')) {
            Schema::create('publisher_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->nullable()->constrained('publisher_purchases')->nullOnDelete();
                $table->foreignId('publisher_id')->constrained('publishers')->cascadeOnDelete();
                $table->string('payment_no', 50)->unique();
                $table->date('payment_date');
                $table->decimal('amount', 12, 2);
                $table->string('payment_method', 50)->default('cash'); // cash, bank, bkash, nagad, rocket, cheque, other
                $table->string('transaction_ref', 100)->nullable();
                $table->text('note')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publisher_payments');
        Schema::dropIfExists('publisher_purchase_items');
        Schema::dropIfExists('publisher_purchases');
    }
};
