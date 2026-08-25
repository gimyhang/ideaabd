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
        if (!Schema::hasTable('author_honorariums')) {
            Schema::create('author_honorariums', function (Blueprint $table) {
                $table->id();
                $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
                $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('blog_post_id')->nullable()->constrained('blog_posts')->nullOnDelete();
                $table->foreignId('donor_user_id')->nullable()->constrained('users')->nullOnDelete();
                
                $table->string('donor_name', 150)->default('সম্মানিত পাঠক');
                $table->string('donor_phone', 50)->nullable();
                $table->string('donor_email', 150)->nullable();
                $table->text('message')->nullable(); // শুভেচ্ছা বার্তা / অনুভূতি
                
                $table->decimal('amount', 10, 2);
                $table->decimal('platform_fee', 10, 2)->default(0.00);
                $table->decimal('author_amount', 10, 2);
                
                $table->string('payment_method', 30)->default('bkash'); // bkash, nagad, rocket, card, sslcommerz, manual
                $table->string('payment_channel', 50)->nullable(); // personal, merchant, online_gateway
                $table->string('sender_account_number', 50)->nullable();
                $table->string('trx_id', 100)->nullable();
                
                $table->string('payment_status', 30)->default('completed'); // completed, pending, rejected, refunded
                $table->boolean('is_anonymous')->default(false);
                $table->text('admin_notes')->nullable();
                
                $table->timestamps();

                $table->index(['author_id', 'payment_status']);
                $table->index(['author_user_id', 'payment_status']);
                $table->index('blog_post_id');
                $table->index('trx_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('author_honorariums');
    }
};
