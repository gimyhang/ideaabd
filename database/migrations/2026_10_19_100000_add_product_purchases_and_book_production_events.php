<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stationery / electronics purchases point at catalogue products.
        if (Schema::hasTable('publisher_purchase_items') && !Schema::hasColumn('publisher_purchase_items', 'product_id')) {
            Schema::table('publisher_purchase_items', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id')->nullable()->index();
            });
        }

        // Book production ledger: printed → bound → received at office, each on its own date.
        if (!Schema::hasTable('book_production_events')) {
            Schema::create('book_production_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
                $table->string('stage', 20); // printed | bound | received
                $table->unsignedInteger('quantity');
                $table->date('event_date');
                $table->string('batch_no', 100)->nullable();
                $table->string('vendor', 255)->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['book_id', 'stage', 'event_date']);
                $table->index(['stage', 'event_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('book_production_events');

        if (Schema::hasTable('publisher_purchase_items') && Schema::hasColumn('publisher_purchase_items', 'product_id')) {
            Schema::table('publisher_purchase_items', function (Blueprint $table) {
                $table->dropColumn('product_id');
            });
        }
    }
};
