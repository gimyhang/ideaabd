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
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'edit_request_status')) {
                $table->string('edit_request_status', 30)->nullable()->default(null)->after('rejection_reason')->index();
            }
            if (!Schema::hasColumn('blog_posts', 'edit_request_data')) {
                $table->longText('edit_request_data')->nullable()->after('edit_request_status');
            }
            if (!Schema::hasColumn('blog_posts', 'edit_requested_at')) {
                $table->timestamp('edit_requested_at')->nullable()->after('edit_request_data');
            }
            if (!Schema::hasColumn('blog_posts', 'edit_request_notes')) {
                $table->text('edit_request_notes')->nullable()->after('edit_requested_at');
            }
            if (!Schema::hasColumn('blog_posts', 'edit_request_reviewed_at')) {
                $table->timestamp('edit_request_reviewed_at')->nullable()->after('edit_request_notes');
            }
            if (!Schema::hasColumn('blog_posts', 'edit_request_rejection_reason')) {
                $table->text('edit_request_rejection_reason')->nullable()->after('edit_request_reviewed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $columns = [
                'edit_request_status',
                'edit_request_data',
                'edit_requested_at',
                'edit_request_notes',
                'edit_request_reviewed_at',
                'edit_request_rejection_reason',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('blog_posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
