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
        if (Schema::hasTable('login_security_logs')) {
            Schema::table('login_security_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('login_security_logs', 'is_security_issue')) {
                    $table->boolean('is_security_issue')->default(false)->index()->after('is_blocked');
                }
                if (!Schema::hasColumn('login_security_logs', 'threat_level')) {
                    $table->string('threat_level', 30)->default('low')->index()->after('is_security_issue');
                }
                if (!Schema::hasColumn('login_security_logs', 'flagged_at')) {
                    $table->timestamp('flagged_at')->nullable()->after('threat_level');
                }
                if (!Schema::hasColumn('login_security_logs', 'human_challenge_passed_at')) {
                    $table->timestamp('human_challenge_passed_at')->nullable()->after('flagged_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('login_security_logs')) {
            Schema::table('login_security_logs', function (Blueprint $table) {
                $columns = ['is_security_issue', 'threat_level', 'flagged_at', 'human_challenge_passed_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('login_security_logs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
