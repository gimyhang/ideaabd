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
        if (!Schema::hasTable('idea_employee_work_logs')) {
            Schema::create('idea_employee_work_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('idea_employees')->onDelete('cascade');
                $table->string('entry_type', 30)->default('work'); // 'work' (কাজ জমা/বই বাঁধাই) or 'payment' (টাকা তোলা/উত্তোলন)
                $table->date('log_date');
                $table->string('book_title')->nullable(); // বইয়ের নাম / কাজের নাম
                $table->decimal('quantity', 12, 2)->default(0); // বাঁধাইকৃত বইয়ের পরিমাণ
                $table->decimal('unit_rate', 12, 2)->default(0); // প্রতি বইয়ের বাঁধাই দর
                $table->string('unit_name', 50)->nullable(); // যেমন: বই বাঁধাই, ফর্মা, দিন
                $table->decimal('earned_amount', 12, 2)->default(0); // কাজের মোট বিল / পাওনা (quantity * unit_rate)
                $table->decimal('paid_amount', 12, 2)->default(0); // উত্তোলিত / প্রদত্ত টাকা
                $table->string('payment_method', 50)->default('cash');
                $table->string('voucher_no', 60)->nullable();
                $table->unsignedBigInteger('accounting_entry_id')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idea_employee_work_logs');
    }
};
