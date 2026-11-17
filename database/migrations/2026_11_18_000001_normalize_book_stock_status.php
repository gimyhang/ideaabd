<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The quick tools used to save "out" while the full book form saved "out_of_stock". One spelling now
 * (see Book::STOCK_STATUSES), so older rows are brought in line.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('books') && Schema::hasColumn('books', 'stock_status')) {
            DB::table('books')->where('stock_status', 'out')->update(['stock_status' => 'out_of_stock']);
        }
    }

    public function down(): void
    {
        // The two spellings meant the same thing; nothing to restore.
    }
};
