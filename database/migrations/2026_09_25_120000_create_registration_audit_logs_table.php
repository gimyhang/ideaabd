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
        if (!Schema::hasTable('registration_audit_logs')) {
            Schema::create('registration_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip_address', 45)->index();
                $table->string('phone', 30)->nullable()->index();
                $table->string('email', 255)->nullable()->index();
                $table->string('category', 50)->default('buyer')->index(); // buyer, author, publisher, seller
                $table->string('step', 50)->default('initial'); // details, captcha, email_otp, phone_otp, completed, rejected
                $table->boolean('is_success')->default(false)->index();
                $table->text('error_message')->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_audit_logs');
    }
};
