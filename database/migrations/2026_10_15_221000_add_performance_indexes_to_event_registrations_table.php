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
        Schema::table('event_registrations', function (Blueprint $table) {
            // Index for campaign + status filtering
            $table->index(['event_campaign_id', 'status'], 'idx_er_campaign_status');
            // Index for campaign + payment status
            $table->index(['event_campaign_id', 'payment_status'], 'idx_er_campaign_payment');
            // Index for phone lookup & duplicate checks
            $table->index(['event_campaign_id', 'phone'], 'idx_er_campaign_phone');
            // Index for district queries
            $table->index('district', 'idx_er_district');
            // Index for updated_at (cache key invalidation speed)
            $table->index(['event_campaign_id', 'updated_at'], 'idx_er_campaign_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropIndex('idx_er_campaign_status');
            $table->dropIndex('idx_er_campaign_payment');
            $table->dropIndex('idx_er_campaign_phone');
            $table->dropIndex('idx_er_district');
            $table->dropIndex('idx_er_campaign_updated');
        });
    }
};
