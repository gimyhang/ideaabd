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
        // 1. Add optional due_date and installment fields to idea_invoices if not exist
        if (Schema::hasTable('idea_invoices')) {
            Schema::table('idea_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('idea_invoices', 'due_date')) {
                    $table->date('due_date')->nullable()->after('invoice_date')->comment('পরিশোধের শেষ তারিখ / পরবর্তী কিস্তির তারিখ (ঐচ্ছিক)');
                }
                if (!Schema::hasColumn('idea_invoices', 'installment_count')) {
                    $table->integer('installment_count')->nullable()->default(1)->after('payment_method');
                }
                if (!Schema::hasColumn('idea_invoices', 'installment_notes')) {
                    $table->text('installment_notes')->nullable()->after('notes');
                }
            });
        }

        // 2. Create idea_invoice_payments table for dynamic installment tracking
        if (!Schema::hasTable('idea_invoice_payments')) {
            Schema::create('idea_invoice_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->nullable()->constrained('idea_invoices')->cascadeOnDelete();
                $table->string('customer_name', 255)->nullable();
                $table->string('customer_phone', 50)->nullable();
                $table->string('payment_no', 50)->unique();
                $table->date('payment_date');
                $table->decimal('amount', 12, 2)->default(0.00);
                $table->string('payment_method', 50)->default('cash');
                $table->string('transaction_ref', 100)->nullable()->comment('Voucher / Cheque / Trx ID');
                $table->text('note')->nullable()->comment('Payment details: advance, 1st installment, etc.');
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idea_invoice_payments');

        if (Schema::hasTable('idea_invoices')) {
            Schema::table('idea_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('idea_invoices', 'due_date')) {
                    $table->dropColumn('due_date');
                }
                if (Schema::hasColumn('idea_invoices', 'installment_count')) {
                    $table->dropColumn('installment_count');
                }
                if (Schema::hasColumn('idea_invoices', 'installment_notes')) {
                    $table->dropColumn('installment_notes');
                }
            });
        }
    }
};
