<?php

declare(strict_types=1);

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
        // 1. Locations Table (Warehouses vs Showrooms)
        if (!Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 50)->unique();
                $table->enum('type', ['warehouse', 'showroom'])->default('showroom');
                $table->boolean('is_central')->default(false)->comment('Central warehouse receives Production In');
                $table->string('contact_person')->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['type', 'is_active']);
            });
        }

        // 2. Inventories Table (Pivot for Book ID, Location ID & Quantity)
        if (!Schema::hasTable('inventories')) {
            Schema::create('inventories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
                $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
                $table->integer('quantity')->default(0);
                $table->unsignedInteger('min_alert_quantity')->default(5)->comment('Threshold for Low Stock Alert');
                $table->string('shelf_location', 100)->nullable();
                $table->timestamps();

                $table->unique(['book_id', 'location_id']);
                $table->index(['location_id', 'quantity']);
            });
        }

        // 3. Stock Transfers Table (Challans between locations)
        if (!Schema::hasTable('stock_transfers')) {
            Schema::create('stock_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('challan_no', 60)->unique();
                $table->foreignId('from_location_id')->constrained('locations')->restrictOnDelete();
                $table->foreignId('to_location_id')->constrained('locations')->restrictOnDelete();
                $table->enum('status', ['pending', 'in_transit', 'received', 'cancelled'])->default('pending');
                $table->date('transfer_date');
                $table->dateTime('received_date')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['status', 'transfer_date']);
                $table->index(['from_location_id', 'to_location_id']);
            });
        }

        // 4. Stock Transfer Items Table (Line items inside challan)
        if (!Schema::hasTable('stock_transfer_items')) {
            Schema::create('stock_transfer_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
                $table->foreignId('book_id')->constrained('books')->restrictOnDelete();
                $table->unsignedInteger('quantity_sent');
                $table->unsignedInteger('quantity_received')->nullable();
                $table->decimal('unit_cost', 10, 2)->nullable();
                $table->string('remarks')->nullable();
                $table->timestamps();

                $table->index(['stock_transfer_id', 'book_id']);
            });
        }

        // 5. Stock Wastages / Damaged Log Table
        if (!Schema::hasTable('stock_wastages')) {
            Schema::create('stock_wastages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained('books')->restrictOnDelete();
                $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
                $table->unsignedInteger('quantity');
                $table->string('reason', 50)->comment('production_defect, binding_defect, transit_damage, showroom_damage, water_damage, lost, other');
                $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action_taken', 100)->nullable()->comment('disposed, returned_to_press, repaired, other');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['location_id', 'created_at']);
                $table->index(['book_id', 'reason']);
            });
        }

        // 6. Inventory Movement Logs Table (Comprehensive Audit Trail)
        if (!Schema::hasTable('inventory_movement_logs')) {
            Schema::create('inventory_movement_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
                $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
                $table->string('type', 40)->comment('production_in, transfer_out, transfer_in, sale_out, wastage_out, adjustment_in, adjustment_out');
                $table->integer('quantity_change');
                $table->integer('quantity_before');
                $table->integer('quantity_after');
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['book_id', 'location_id']);
                $table->index(['type', 'created_at']);
                $table->index(['reference_type', 'reference_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movement_logs');
        Schema::dropIfExists('stock_wastages');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('locations');
    }
};
