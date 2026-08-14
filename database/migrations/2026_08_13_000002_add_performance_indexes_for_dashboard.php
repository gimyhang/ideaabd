<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add indexes for maximum query speed and sub-second load times.
     */
    public function up(): void
    {
        // 1. Users table indexes
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['role', 'reg_status'], 'idx_users_role_status');
                $table->index('created_at', 'idx_users_created_at');
            });
        }

        // 2. Bills table indexes
        if (Schema::hasTable('bills')) {
            Schema::table('bills', function (Blueprint $table) {
                $table->index(['created_at', 'total'], 'idx_bills_created_total');
                $table->index(['payment_status', 'total'], 'idx_bills_payment_total');
                $table->index(['seller_id', 'total'], 'idx_bills_seller_total');
            });
        }

        // 3. Content tables indexes (books, ebooks, blog_posts, webzines)
        foreach (['books', 'ebooks', 'blog_posts', 'webzines'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'status')) {
                        $table->index(['status', 'created_at'], "idx_{$tableName}_status_created");
                    } else {
                        $table->index('created_at', "idx_{$tableName}_created_at");
                    }
                });
            }
        }

        // 4. Admin activity logs indexes
        if (Schema::hasTable('admin_activity_logs')) {
            Schema::table('admin_activity_logs', function (Blueprint $table) {
                $table->index('created_at', 'idx_logs_created_at');
                $table->index('action_type', 'idx_logs_action_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $t) {
                $t->dropIndex('idx_users_role_status');
                $t->dropIndex('idx_users_created_at');
            });
        }
        if (Schema::hasTable('bills')) {
            Schema::table('bills', function (Blueprint $t) {
                $t->dropIndex('idx_bills_created_total');
                $t->dropIndex('idx_bills_payment_total');
                $t->dropIndex('idx_bills_seller_total');
            });
        }
        foreach (['books', 'ebooks', 'blog_posts', 'webzines'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $t) use ($tableName) {
                    try {
                        if (Schema::hasColumn($tableName, 'status')) {
                            $t->dropIndex("idx_{$tableName}_status_created");
                        } else {
                            $t->dropIndex("idx_{$tableName}_created_at");
                        }
                    } catch (\Throwable) {}
                });
            }
        }
        if (Schema::hasTable('admin_activity_logs')) {
            Schema::table('admin_activity_logs', function (Blueprint $t) {
                $t->dropIndex('idx_logs_created_at');
                $t->dropIndex('idx_logs_action_type');
            });
        }
    }
};
