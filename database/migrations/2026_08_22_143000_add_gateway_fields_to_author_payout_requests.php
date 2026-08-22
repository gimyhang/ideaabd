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
        Schema::table('author_payout_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('author_payout_requests', 'gateway_channel')) {
                $table->string('gateway_channel', 50)->default('manual')->after('payment_method');
            }
            if (!Schema::hasColumn('author_payout_requests', 'gateway_fee')) {
                $table->decimal('gateway_fee', 10, 2)->default(0.00)->after('tax_deduction_amount');
            }
            if (!Schema::hasColumn('author_payout_requests', 'gateway_response')) {
                $table->json('gateway_response')->nullable()->after('transaction_ref');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('author_payout_requests', function (Blueprint $table) {
            $table->dropColumn(['gateway_channel', 'gateway_fee', 'gateway_response']);
        });
    }
};
