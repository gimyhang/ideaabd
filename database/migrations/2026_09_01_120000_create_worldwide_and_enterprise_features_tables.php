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
        // 1. Currency Rates Table (Multi-Currency & FX Engine)
        if (!Schema::hasTable('currency_rates')) {
            Schema::create('currency_rates', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10)->unique(); // USD, EUR, GBP, AED, SAR, INR, CAD, BDT
                $table->string('name', 50);
                $table->string('symbol', 10);
                $table->decimal('exchange_rate_to_bdt', 14, 4)->default(1.0000); // 1 USD = 120.50 BDT
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Subscription Plans & User Subscriptions (Kindle Unlimited Model)
        if (!Schema::hasTable('subscription_plans')) {
            Schema::create('subscription_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g. "Idea Unlimited Monthly", "Annual Scholar Club"
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->decimal('price_bdt', 10, 2)->default(0.00);
                $table->decimal('price_usd', 10, 2)->default(0.00);
                $table->integer('duration_days')->default(30); // 30, 90, 365
                $table->integer('max_devices')->default(3);
                $table->boolean('unlimited_ebooks')->default(true);
                $table->boolean('unlimited_audiobooks')->default(false);
                $table->boolean('unlimited_webzines')->default(true);
                $table->json('features')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_subscriptions')) {
            Schema::create('user_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
                $table->dateTime('starts_at');
                $table->dateTime('expires_at');
                $table->enum('status', ['active', 'expired', 'cancelled', 'pending'])->default('active');
                $table->string('payment_method')->nullable(); // bkash, nagad, stripe, paypal, manual
                $table->string('transaction_id')->nullable();
                $table->decimal('amount_paid', 10, 2)->default(0.00);
                $table->string('currency', 10)->default('BDT');
                $table->boolean('auto_renew')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ebook_reading_logs')) {
            Schema::create('ebook_reading_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedBigInteger('ebook_id');
                $table->integer('pages_read')->default(1);
                $table->integer('session_duration_sec')->default(0);
                $table->string('ip_address', 45)->nullable();
                $table->string('device_signature')->nullable();
                $table->date('read_date');
                $table->timestamps();

                $table->index(['ebook_id', 'read_date']);
            });
        }

        // 3. Affiliates & Influencer Referral Engine
        if (!Schema::hasTable('affiliates')) {
            Schema::create('affiliates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('affiliate_code', 30)->unique(); // e.g. "ROFIQ10", "BOOKTUBE"
                $table->decimal('commission_rate', 5, 2)->default(5.00); // 5.00%
                $table->decimal('balance', 12, 2)->default(0.00);
                $table->decimal('total_earned', 12, 2)->default(0.00);
                $table->decimal('total_paid', 12, 2)->default(0.00);
                $table->string('payout_method')->nullable(); // bkash, nagad, bank, paypal, wise
                $table->text('payout_details')->nullable();
                $table->enum('status', ['active', 'paused', 'banned'])->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('affiliate_referrals')) {
            Schema::create('affiliate_referrals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->decimal('order_amount', 12, 2)->default(0.00);
                $table->decimal('commission_amount', 12, 2)->default(0.00);
                $table->string('visitor_ip', 45)->nullable();
                $table->enum('status', ['pending', 'approved', 'paid', 'rejected'])->default('pending');
                $table->timestamps();
            });
        }

        // 4. POS (Point of Sale) & Boi Mela Offline Register
        if (!Schema::hasTable('pos_registers')) {
            Schema::create('pos_registers', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g. "Ekushey Boi Mela Stall #241", "Banglabazar Showroom"
                $table->string('location')->nullable();
                $table->decimal('opening_cash', 10, 2)->default(0.00);
                $table->decimal('current_cash', 10, 2)->default(0.00);
                $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('status', ['open', 'closed'])->default('open');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pos_sales')) {
            Schema::create('pos_sales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('register_id')->nullable()->constrained('pos_registers')->nullOnDelete();
                $table->string('receipt_no', 50)->unique();
                $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('customer_name')->nullable();
                $table->string('customer_phone')->nullable();
                $table->decimal('subtotal', 10, 2)->default(0.00);
                $table->decimal('discount', 10, 2)->default(0.00);
                $table->decimal('total', 10, 2)->default(0.00);
                $table->decimal('paid_cash', 10, 2)->default(0.00);
                $table->decimal('paid_online', 10, 2)->default(0.00); // bKash/Card
                $table->string('payment_method')->default('cash');
                $table->json('items_json')->nullable();
                $table->timestamps();
            });
        }

        // 5. Book Bundles, Combos & Pre-Orders
        if (!Schema::hasTable('book_bundles')) {
            Schema::create('book_bundles', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('banner_image')->nullable();
                $table->decimal('regular_price', 10, 2)->default(0.00);
                $table->decimal('bundle_price', 10, 2)->default(0.00);
                $table->decimal('discount_percent', 5, 2)->default(0.00);
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('bundle_items')) {
            Schema::create('bundle_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bundle_id')->constrained('book_bundles')->cascadeOnDelete();
                $table->unsignedBigInteger('book_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pre_orders')) {
            Schema::create('pre_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('book_id');
                $table->string('customer_name');
                $table->string('customer_phone');
                $table->string('customer_email')->nullable();
                $table->text('delivery_address')->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('total_amount', 10, 2)->default(0.00);
                $table->date('estimated_release_date')->nullable();
                $table->enum('status', ['registered', 'confirmed', 'converted_to_order', 'cancelled'])->default('registered');
                $table->unsignedBigInteger('order_id')->nullable();
                $table->timestamps();
            });
        }

        // 6. Support Tickets & Helpdesk (Customer 360)
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number', 30)->unique();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('customer_name');
                $table->string('customer_email');
                $table->string('customer_phone')->nullable();
                $table->string('subject');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->enum('department', ['orders', 'ebooks_drm', 'royalty_payouts', 'general'])->default('general');
                $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ticket_messages')) {
            Schema::create('ticket_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->text('message');
                $table->boolean('is_admin_reply')->default(false);
                $table->string('attachment_path')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('pre_orders');
        Schema::dropIfExists('bundle_items');
        Schema::dropIfExists('book_bundles');
        Schema::dropIfExists('pos_sales');
        Schema::dropIfExists('pos_registers');
        Schema::dropIfExists('affiliate_referrals');
        Schema::dropIfExists('affiliates');
        Schema::dropIfExists('ebook_reading_logs');
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('currency_rates');
    }
};
