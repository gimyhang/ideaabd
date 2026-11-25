<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Raw-material stock book (admin/purchases/raw-materials): the materials the press buys (paper, plates, ink,
 * binding material…) and every movement of them — in from a purchase, out to a book / print job, adjustments.
 * The same tables are also created from a web request (App\Support\RawMaterialStock::ensureSchema), because the
 * production command line talks to another database.
 */
return new class extends Migration
{
    public function up(): void
    {
        \App\Support\RawMaterialStock::createTables();
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_material_moves');
        Schema::dropIfExists('raw_materials');
    }
};
