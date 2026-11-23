<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** One row per share of a post, e-book, webzine, author profile or site page (see App\Support\Share). */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('content_shares')) {
            return;
        }
        Schema::create('content_shares', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('item_key', 100);
            $table->string('channel', 20);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->char('visitor_hash', 64);
            $table->timestamp('created_at')->nullable();
            $table->index(['type', 'item_key']);
            $table->index(['visitor_hash', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_shares');
    }
};
