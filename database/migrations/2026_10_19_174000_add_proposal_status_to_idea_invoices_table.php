<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idea_invoice_number_sequences', function (Blueprint $table) {
            $table->string('sequence_key', 80)->primary();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        Schema::table('idea_invoices', function (Blueprint $table) {
            $table->string('proposal_status', 20)->nullable()->after('type')->index();
        });

        DB::table('idea_invoices')
            ->whereIn('type', ['quotation', 'tender'])
            ->update(['proposal_status' => 'draft']);
    }

    public function down(): void
    {
        Schema::table('idea_invoices', function (Blueprint $table) {
            $table->dropIndex(['proposal_status']);
            $table->dropColumn('proposal_status');
        });

        Schema::dropIfExists('idea_invoice_number_sequences');
    }
};
