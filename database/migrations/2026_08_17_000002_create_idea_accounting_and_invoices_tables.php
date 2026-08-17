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
        // 1. Idea Prokashon Invoices & Challans (বিল ও চালান)
        Schema::create('idea_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 50)->unique();
            $table->enum('type', ['invoice', 'challan'])->default('invoice')->comment('invoice: বিল/ক্যাশ মেমো, challan: ডেলিভারি চালান');
            $table->string('customer_name');
            $table->string('customer_phone', 50)->nullable();
            $table->string('customer_address', 255)->nullable();
            $table->date('invoice_date');
            $table->json('items')->comment('Array of bill/challan items: title, book_id, type, qty, price, subtotal');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('tax', 12, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00);
            $table->string('payment_method', 50)->default('cash');
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Idea Prokashon Accounting Ledger & Expenses (আয়-ব্যয় ও ক্রয় হিসাব খাত)
        Schema::create('idea_accounting_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_no', 50)->unique();
            $table->enum('type', ['income', 'expense'])->default('expense')->comment('income: আয়, expense: ব্যয়');
            $table->string('category', 100)->comment('কাগজ ক্রয়, কালি ও প্লেট, মুদ্রণ ও বাঁধাই, অফিস খরচ, পরিবহন, বই বিক্রয় ইত্যাদি');
            $table->string('title', 255);
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->date('entry_date');
            $table->string('voucher_no', 100)->nullable();
            $table->string('payment_method', 50)->default('cash');
            $table->string('party_name', 255)->nullable()->comment('সরবরাহকারী / ভেন্ডর / গ্রাহক');
            $table->foreignId('invoice_id')->nullable()->constrained('idea_invoices')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idea_accounting_entries');
        Schema::dropIfExists('idea_invoices');
    }
};
