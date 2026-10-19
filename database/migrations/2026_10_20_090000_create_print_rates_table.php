<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Raw-material rate sheet used by the printing cost calculator
     * (paper per ream, plates, press, lamination, binding, packing, transport …).
     */
    public function up(): void
    {
        if (Schema::hasTable('print_rates')) {
            return;
        }

        Schema::create('print_rates', function (Blueprint $table) {
            $table->id();
            $table->string('group', 30);          // Paper, CTP, Press, Lamination, Cutting, Binding, Packing, Transport, Design, Other
            $table->string('item', 60)->default('');  // GSM for paper (80), method / material for the rest (Matt, Perfect, Poly …)
            $table->string('kind', 60)->default('');  // paper kind (White), or the basis (Hundred) where the rate depends on it
            $table->decimal('rate', 12, 2)->default(0);
            $table->string('note', 150)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['group', 'item', 'kind']);
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_rates');
    }
};
