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
        // 1. Login Security Logs & IP Blocklist Table
        if (!Schema::hasTable('login_security_logs')) {
            Schema::create('login_security_logs', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45)->index();
                $table->string('last_username', 255)->nullable();
                $table->integer('attempt_count')->default(0);
                $table->timestamp('locked_until')->nullable();
                $table->boolean('is_blocked')->default(false)->index();
                $table->timestamp('blocked_at')->nullable();
                $table->string('block_reason', 255)->nullable();
                $table->timestamp('unblocked_at')->nullable();
                $table->foreignId('unblocked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 2. User Password Reset Help Requests Table
        if (!Schema::hasTable('password_reset_requests')) {
            Schema::create('password_reset_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('identity', 255)->index();
                $table->string('user_name', 255)->nullable();
                $table->string('user_ip', 45)->nullable();
                $table->text('reason_notes')->nullable();
                $table->string('status', 30)->default('pending')->index(); // pending, resolved, rejected
                $table->string('otp_code', 50)->nullable();
                $table->timestamp('otp_expires_at')->nullable();
                $table->text('admin_notes')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. User Table columns for One-Time Password / Force Password Change
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'must_change_password')) {
                    $table->boolean('must_change_password')->default(false)->after('password');
                }
                if (!Schema::hasColumn('users', 'otp_expires_at')) {
                    $table->timestamp('otp_expires_at')->nullable()->after('must_change_password');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'must_change_password')) {
                    $table->dropColumn('must_change_password');
                }
                if (Schema::hasColumn('users', 'otp_expires_at')) {
                    $table->dropColumn('otp_expires_at');
                }
            });
        }

        Schema::dropIfExists('password_reset_requests');
        Schema::dropIfExists('login_security_logs');
    }
};
