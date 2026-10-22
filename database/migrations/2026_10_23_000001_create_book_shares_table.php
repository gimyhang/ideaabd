<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('book_shares')) {
            return;
        }

        Schema::create('book_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->string('channel', 20);                     // whatsapp, facebook, copy, native, ...
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('visitor_hash', 64);                // sha256(ip + user agent + app key): no raw IP is stored
            $table->timestamp('created_at')->useCurrent();

            $table->index(['book_id', 'channel']);
            $table->index(['book_id', 'visitor_hash', 'channel', 'created_at'], 'book_shares_dedupe_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_shares');
    }
};
