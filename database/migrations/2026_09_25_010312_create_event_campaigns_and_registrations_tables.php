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
        // 1. Event / Campaign Form Definitions
        Schema::create('event_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique(); // e.g. rsutshab, joyeeshikkhabritti
            $table->string('type')->default('event'); // event, donation, scholarship, competition, workshop
            $table->string('badge_text')->nullable(); // e.g. "উৎসব ২০২৬", "বৃত্তি আবেদন"
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('banner_image')->nullable();
            
            // Donation / Fee options
            $table->boolean('has_fee_or_donation')->default(false);
            $table->decimal('fee_amount', 12, 2)->default(0.00);
            $table->boolean('is_donation_flexible')->default(false);
            $table->decimal('min_donation', 12, 2)->default(10.00);
            $table->string('payment_methods')->nullable(); // bkash,nagad,rocket,bank

            // Form configuration & custom fields
            $table->json('custom_fields')->nullable(); // Additional dynamic fields: institution, class, donation_cause, etc.
            
            // Scheduling & Status
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->integer('max_participants')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('success_message')->nullable();
            $table->string('redirect_url')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Registrations for Campaigns/Events
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_campaign_id')->constrained('event_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Linked customer account
            
            // Registrant snapshot
            $table->string('registration_number')->unique();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('district')->nullable();
            $table->string('thana')->nullable();
            $table->string('institution_or_org')->nullable();
            $table->string('designation_or_class')->nullable();

            // Donation / Fee details
            $table->decimal('amount_paid', 12, 2)->default(0.00);
            $table->string('payment_method')->nullable(); // bkash, nagad, rocket, free
            $table->string('transaction_id')->nullable();
            $table->string('payment_status')->default('pending'); // pending, verified, waived, refunded

            // Additional custom fields submission
            $table->json('form_data')->nullable(); // Custom answers, NID/Student ID, notes
            
            // Review & Approval status
            $table->string('status')->default('confirmed'); // confirmed, pending, rejected, attended
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('event_campaigns');
    }
};
