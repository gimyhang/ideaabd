<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Moderation + "posted on behalf of" columns for every user-submittable content
 * table.
 *
 * `mod_status` defaults to 'approved' so existing rows stay visible; only new
 * submissions from non-admin users start out as 'pending'.
 *
 * `owner_name` / `owner_phone` cover the offline case the platform was asked
 * for: someone who cannot register online, whose book/author/publisher entry an
 * admin creates for them. The row is credited to that person by name while
 * `submitted_by` records the admin who actually typed it in.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'books',
        'ebooks',
        'authors',
        'publishers',
        'webzines',
        'blog_posts',
    ];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            if (! Schema::hasTable($name)) {
                continue;
            }

            Schema::table($name, function (Blueprint $table) use ($name) {
                if (! Schema::hasColumn($name, 'mod_status')) {
                    $table->string('mod_status', 20)->default('approved')->index();
                }
                if (! Schema::hasColumn($name, 'submitted_by')) {
                    $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn($name, 'owner_name')) {
                    $table->string('owner_name')->nullable();
                }
                if (! Schema::hasColumn($name, 'owner_phone')) {
                    $table->string('owner_phone', 30)->nullable();
                }
                if (! Schema::hasColumn($name, 'reviewed_by')) {
                    $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn($name, 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable();
                }
                if (! Schema::hasColumn($name, 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable();
                }
            });
        }

        // Webzines and ebooks are soft-deletable via the model but the ebooks
        // table already has deleted_at; webzine_articles does not need it.
        if (Schema::hasTable('blog_posts') && ! Schema::hasColumn('blog_posts', 'deleted_at')) {
            Schema::table('blog_posts', fn (Blueprint $t) => $t->softDeletes());
        }
    }

    public function down(): void
    {
        $columns = [
            'mod_status', 'owner_name', 'owner_phone', 'reviewed_at', 'rejection_reason',
        ];

        foreach ($this->tables as $name) {
            if (! Schema::hasTable($name)) {
                continue;
            }

            Schema::table($name, function (Blueprint $table) use ($name, $columns) {
                foreach (['submitted_by', 'reviewed_by'] as $fk) {
                    if (Schema::hasColumn($name, $fk)) {
                        $table->dropConstrainedForeignId($fk);
                    }
                }
                foreach ($columns as $column) {
                    if (Schema::hasColumn($name, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
