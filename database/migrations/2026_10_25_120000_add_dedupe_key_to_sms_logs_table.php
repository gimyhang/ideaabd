<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sms_logs') && !Schema::hasColumn('sms_logs', 'dedupe_key')) {
            Schema::table('sms_logs', function (Blueprint $table) {
                // "one order, one message": the same key (e.g. order-placed:IDP-1001) is sent to a number only once
                $table->string('dedupe_key', 120)->nullable()->after('campaign');
                $table->index(['phone', 'dedupe_key']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sms_logs', 'dedupe_key')) {
            Schema::table('sms_logs', function (Blueprint $table) {
                $table->dropIndex(['phone', 'dedupe_key']);
                $table->dropColumn('dedupe_key');
            });
        }
    }
};
