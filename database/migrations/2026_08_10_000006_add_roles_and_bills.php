<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add extra columns to users table for role-based registration
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'reg_status')) {
                $table->string('reg_status')->default('approved')->after('role'); // pending/approved/rejected
            }
            if (!Schema::hasColumn('users', 'reg_type')) {
                $table->string('reg_type')->nullable()->after('reg_status'); // seller/publisher/author/buyer
            }
            if (!Schema::hasColumn('users', 'reg_data')) {
                $table->json('reg_data')->nullable()->after('reg_type'); // extra profile data
            }
            if (!Schema::hasColumn('users', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('reg_data');
            }
            if (!Schema::hasColumn('users', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('users', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_at');
            }
        });

        // Bills table – sub-admin/seller creates bills for customers
        if (Schema::hasTable('bills')) {
            return;
        }

        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no')->unique();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->json('items');          // [{book_id, title, qty, price}]
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('payment_method')->default('cash'); // cash/bkash/nagad/card
            $table->string('payment_status')->default('unpaid'); // unpaid/paid/partial
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'approved_by')) {
                $table->dropForeign(['approved_by']);
            }
            $table->dropColumn(['reg_status', 'reg_type', 'reg_data', 'approved_by', 'approved_at', 'rejection_reason']);
        });
    }
};
