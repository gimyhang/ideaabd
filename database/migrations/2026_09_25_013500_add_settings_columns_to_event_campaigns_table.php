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
        Schema::table('event_campaigns', function (Blueprint $table) {
            $table->json('form_settings')->nullable()->after('custom_fields');
            $table->json('table_settings')->nullable()->after('form_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_campaigns', function (Blueprint $table) {
            $table->dropColumn(['form_settings', 'table_settings']);
        });
    }
};
