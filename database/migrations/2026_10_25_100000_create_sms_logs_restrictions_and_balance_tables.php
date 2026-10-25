<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per number per message: the transaction history and the source for the per-number daily limit.
        if (!Schema::hasTable('sms_logs')) {
            Schema::create('sms_logs', function (Blueprint $table) {
                $table->id();
                $table->string('batch', 36)->nullable()->index();
                $table->string('phone', 20);
                $table->string('purpose', 20)->default('notification'); // otp | notification | campaign | test
                $table->string('source', 10)->default('system');         // system | admin
                $table->string('status', 12)->default('pending');        // pending | sent | failed | blocked | rejected
                $table->string('reason', 40)->nullable();
                $table->text('message')->nullable();
                $table->unsignedTinyInteger('segments')->default(1);
                $table->decimal('cost', 8, 2)->default(0);
                $table->string('provider', 30)->nullable();
                $table->string('response_code', 10)->nullable();
                $table->string('gateway_response', 500)->nullable();
                $table->string('campaign', 100)->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('ip', 45)->nullable();
                $table->timestamps();

                $table->index(['phone', 'created_at']);
                $table->index(['status', 'created_at']);
                $table->index('created_at');
            });
        }

        // Numbers that hit the daily limit: temporarily restricted, or blocked for OTP until an admin releases them.
        if (!Schema::hasTable('sms_number_restrictions')) {
            Schema::create('sms_number_restrictions', function (Blueprint $table) {
                $table->id();
                $table->string('phone', 20)->unique();
                $table->timestamp('restricted_until')->nullable();
                $table->timestamp('blocked_at')->nullable();
                $table->unsignedBigInteger('count_after_id')->default(0); // an admin released the number: sms_logs rows up to this id no longer count
                $table->json('violation_days')->nullable();
                $table->unsignedInteger('violations')->default(0);
                $table->string('reason', 150)->nullable();
                $table->timestamp('last_attempt_at')->nullable();
                $table->unsignedBigInteger('blocked_by')->nullable();
                $table->timestamps();
            });
        }

        // Balance seen at the gateway over time, so top-ups and usage show up as history.
        if (!Schema::hasTable('sms_balance_snapshots')) {
            Schema::create('sms_balance_snapshots', function (Blueprint $table) {
                $table->id();
                $table->decimal('balance', 12, 2);
                $table->decimal('delta', 12, 2)->default(0);
                $table->string('type', 10)->default('usage'); // initial | topup | usage
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_balance_snapshots');
        Schema::dropIfExists('sms_number_restrictions');
        Schema::dropIfExists('sms_logs');
    }
};
