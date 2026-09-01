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
        // 1. Site Translations Table (Multi-Language i18n & L10n)
        if (!Schema::hasTable('site_translations')) {
            Schema::create('site_translations', function (Blueprint $table) {
                $table->id();
                $table->string('group', 50)->default('site'); // site, auth, checkout, reader, bookstore
                $table->string('key');
                $table->text('text_bn')->nullable();
                $table->text('text_en')->nullable();
                $table->text('text_ar')->nullable();
                $table->timestamps();

                $table->unique(['group', 'key']);
            });
        }

        // 2. Global Communication Hub & Transactional Email/WhatsApp Templates
        if (!Schema::hasTable('communication_templates')) {
            Schema::create('communication_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g. "Order Confirmation", "Abandoned Cart 24h", "WhatsApp Tracking"
                $table->string('trigger_event', 50)->unique(); // order_placed, abandoned_cart, ebook_licensed, pre_order_release
                $table->enum('channel', ['email', 'whatsapp', 'sms', 'push'])->default('email');
                $table->string('subject')->nullable();
                $table->text('content_template');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('communication_logs')) {
            Schema::create('communication_logs', function (Blueprint $table) {
                $table->id();
                $table->string('channel', 20); // email, whatsapp, sms, push
                $table->string('recipient');
                $table->string('trigger_event', 50);
                $table->string('subject')->nullable();
                $table->enum('status', ['sent', 'delivered', 'failed'])->default('sent');
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
        Schema::dropIfExists('communication_templates');
        Schema::dropIfExists('site_translations');
    }
};
