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
        if (!Schema::hasTable('idea_employees')) {
            Schema::create('idea_employees', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('designation');
                $table->string('department')->default('সাধারণ (General)');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->decimal('basic_salary', 12, 2)->default(0);
                $table->date('joining_date')->nullable();
                $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
                $table->text('address')->nullable();
                $table->string('nid_passport')->nullable();
                $table->string('emergency_contact')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('idea_salary_payments')) {
            Schema::create('idea_salary_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('idea_employees')->onDelete('cascade');
                $table->string('salary_month', 7); // e.g. '2026-08'
                $table->date('payment_date');
                $table->decimal('basic_amount', 12, 2)->default(0);
                $table->decimal('bonus_amount', 12, 2)->default(0);
                $table->decimal('overtime_amount', 12, 2)->default(0);
                $table->decimal('deduction_amount', 12, 2)->default(0);
                $table->decimal('net_paid', 12, 2)->default(0);
                $table->string('payment_method', 50)->default('cash');
                $table->string('trx_reference', 100)->nullable();
                $table->string('slip_no', 60)->nullable();
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
        Schema::dropIfExists('idea_salary_payments');
        Schema::dropIfExists('idea_employees');
    }
};
