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
        if (!Schema::hasTable('visitor_logs')) {
            Schema::create('visitor_logs', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45)->nullable();
                $table->string('url', 1000);
                $table->string('page_title')->nullable();
                $table->string('route_name')->nullable();
                $table->string('device', 20)->default('desktop');
                $table->string('browser', 50)->nullable();
                $table->string('os', 50)->nullable();
                $table->string('referer', 1000)->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('visited_at')->useCurrent();
                $table->timestamps();

                $table->index(['visited_at', 'device']);
                $table->index('ip_address');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
