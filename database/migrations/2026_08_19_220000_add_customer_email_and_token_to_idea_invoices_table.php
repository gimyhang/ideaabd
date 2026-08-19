<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\IdeaInvoice;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('idea_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('idea_invoices', 'customer_email')) {
                $table->string('customer_email', 255)->nullable()->after('customer_phone');
            }
            if (!Schema::hasColumn('idea_invoices', 'customer_designation')) {
                $table->string('customer_designation', 150)->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('idea_invoices', 'access_token')) {
                $table->string('access_token', 64)->nullable()->unique()->after('invoice_no');
            }
            if (!Schema::hasColumn('idea_invoices', 'emailed_at')) {
                $table->timestamp('emailed_at')->nullable()->after('notes');
            }
        });

        // Populate access_token for existing invoices
        try {
            foreach (IdeaInvoice::whereNull('access_token')->get() as $inv) {
                $inv->access_token = Str::random(32);
                $inv->saveQuietly();
            }
        } catch (\Throwable $e) {
            // Ignore if DB not ready
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('idea_invoices', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'customer_designation', 'access_token', 'emailed_at']);
        });
    }
};
