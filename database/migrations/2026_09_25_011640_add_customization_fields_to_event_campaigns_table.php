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
            $table->string('theme_color', 30)->default('#0284c7')->after('banner_image');
            $table->text('payment_instructions')->nullable()->after('payment_methods');
            $table->string('contact_phone', 30)->nullable()->after('redirect_url');
            $table->string('contact_email', 100)->nullable()->after('contact_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_campaigns', function (Blueprint $table) {
            $table->dropColumn(['theme_color', 'payment_instructions', 'contact_phone', 'contact_email']);
        });
    }
};
