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
            $table->string('has_previous_books', 10)->nullable()->after('designation_or_class'); // হ্যাঁ / না
            $table->string('previous_books_year', 20)->nullable()->after('has_previous_books');
            $table->unsignedInteger('previous_books_count')->nullable()->after('previous_books_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['has_previous_books', 'previous_books_year', 'previous_books_count']);
        });
    }
};
