<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasRegistrations = Schema::hasTable('event_registrations');
        $hasEntries = Schema::hasTable('entries');

        if ($hasRegistrations && $hasEntries) {
            throw new RuntimeException('Cannot rename event_registrations because entries already exists.');
        }

        if ($hasRegistrations) {
            Schema::rename('event_registrations', 'entries');
        }
    }

    public function down(): void
    {
        $hasEntries = Schema::hasTable('entries');
        $hasRegistrations = Schema::hasTable('event_registrations');

        if ($hasEntries && $hasRegistrations) {
            throw new RuntimeException('Cannot restore event_registrations because both tables already exist.');
        }

        if ($hasEntries) {
            Schema::rename('entries', 'event_registrations');
        }
    }
};
