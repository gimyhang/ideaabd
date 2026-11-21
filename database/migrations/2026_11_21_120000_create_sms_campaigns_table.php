<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bulk SMS campaigns, sent 100 numbers at a time.
 *
 * A campaign used to be one long PHP run after the page was returned. On shared hosting that run is often stopped
 * part-way (request time limit, LiteSpeed ending the process), so only the first numbers got the SMS and the rest
 * were silently dropped. The campaign and its position are now saved, and each slice of work continues from there.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sms_campaigns')) {
            return;
        }
        Schema::create('sms_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('mode', 12);                       // broadcast | many
            $table->string('title', 150);
            $table->string('target_group', 30);
            $table->text('message');                          // the text (broadcast) or the template (many)
            $table->longText('payload');                      // JSON: numbers (broadcast) or [{to, message}] (many)
            $table->string('status', 12)->default('running'); // running | paused | done | cancelled
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('position')->default(0);  // next index in payload
            $table->unsignedInteger('sent')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->unsignedInteger('refused')->default(0);   // fake / invalid numbers
            $table->unsignedInteger('unknown')->default(0);   // a slice was cut off mid-send: not re-sent, to avoid duplicates
            $table->unsignedInteger('in_flight')->default(0); // numbers of the slice being sent right now
            $table->unsignedInteger('segments')->default(1);  // SMS parts per message (largest, for many)
            $table->string('last_error', 255)->nullable();
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_beat_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('admin_name', 100)->nullable();
            $table->string('fingerprint', 64)->nullable();    // same text + same audience: stops a double click sending twice
            $table->timestamps();
            $table->index(['status', 'locked_until']);
            $table->index(['fingerprint', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_campaigns');
    }
};
