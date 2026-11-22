<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Money receipts register: honorarium paid to writers (লেখক সম্মানী প্রদান)
 * and bill money received (বিলের অর্থ গ্রহণ). Each receipt also has its
 * accounting entry; a bill receipt for an invoice also has the invoice payment.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('money_receipts')) {
            return;
        }

        Schema::create('money_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 40)->unique();
            $table->string('kind', 20)->index();               // honorarium | bill
            $table->date('receipt_date')->index();
            $table->unsignedBigInteger('author_id')->nullable()->index();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->unsignedBigInteger('invoice_payment_id')->nullable();
            $table->unsignedBigInteger('accounting_entry_id')->nullable();
            $table->string('party_name', 191);
            $table->string('party_phone', 40)->nullable();
            $table->string('party_address', 255)->nullable();
            $table->string('purpose', 255);                    // what the money is for
            $table->string('category', 100)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->default('cash');
            $table->string('reference', 120)->nullable();      // TrxID / cheque no.
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('money_receipts');
    }
};
