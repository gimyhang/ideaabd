<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Admin Access Control, Activity Logs, and Dashboard Settings.
     */
    public function up(): void
    {
        // 1. Admin Permissions table
        if (! Schema::hasTable('admin_permissions')) {
            Schema::create('admin_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('name');
                $table->string('module')->default('general');
                $table->text('description')->nullable();
                $table->timestamps();
            });

            // Seed default system permissions
            $defaultPermissions = [
                ['key' => 'dashboard.view',         'name' => 'ড্যাশবোর্ড দর্শন',          'module' => 'general',     'description' => 'অ্যাডমিন ড্যাশবোর্ড দেখার অনুমতি'],
                ['key' => 'users.view',             'name' => 'ব্যবহারকারী দেখুন',       'module' => 'users',       'description' => 'ব্যবহারকারীদের তালিকা দেখার অনুমতি'],
                ['key' => 'users.manage',           'name' => 'ব্যবহারকারী ব্যবস্থাপনা',  'module' => 'users',       'description' => 'ব্যবহারকারী অনুমোদন, সম্পাদনা ও স্ট্যাটাস পরিবর্তনের অনুমতি'],
                ['key' => 'content.moderate',       'name' => 'কনটেন্ট মডারেশন',       'module' => 'content',     'description' => 'বই, ব্লগ, ই-বুক অনুমোদন বা বাতিল'],
                ['key' => 'orders.view',            'name' => 'অর্ডার ও বিল দেখুন',      'module' => 'sales',       'description' => 'অর্ডার ও সেলার বিল দেখার অনুমতি'],
                ['key' => 'orders.manage',          'name' => 'অর্ডার ও বিল তৈরি',      'module' => 'sales',       'description' => 'নতুন বিল তৈরি বা পরিশোধ স্ট্যাটাস আপডেটের অনুমতি'],
                ['key' => 'roles.manage',           'name' => 'রোল ও পারমিশন এক্সেস',   'module' => 'access',      'description' => 'সাব-অ্যাডমিন রোল ও পারমিশন অ্যাসাইনমেন্টের অনুমতি'],
                ['key' => 'activity_logs.view',     'name' => 'অ্যাক্টিভিটি লগ দেখুন',  'module' => 'audit',       'description' => 'অ্যাডমিন একশন লগ দেখার অনুমতি'],
                ['key' => 'settings.manage',        'name' => 'সিস্টেম সেটিংস',        'module' => 'system',      'description' => 'সিস্টেম সেটিংস ও মেইনটেনেন্স মোড পরিবর্তনের অনুমতি'],
            ];

            foreach ($defaultPermissions as $perm) {
                DB::table('admin_permissions')->insert(array_merge($perm, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 2. Role Permissions table
        if (! Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('role');
                $table->foreignId('permission_id')->constrained('admin_permissions')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['role', 'permission_id']);
            });

            // Assign all default permissions to admin & sub_admin by default
            $allPermIds = DB::table('admin_permissions')->pluck('id');
            foreach ($allPermIds as $permId) {
                DB::table('role_has_permissions')->insert([
                    'role'          => 'admin',
                    'permission_id' => $permId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                DB::table('role_has_permissions')->insert([
                    'role'          => 'sub_admin',
                    'permission_id' => $permId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        // 3. Admin Activity Logs table
        if (! Schema::hasTable('admin_activity_logs')) {
            Schema::create('admin_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action_type'); // e.g., 'approve_user', 'moderate_content', 'create_bill'
                $table->text('description');
                $table->string('target_type')->nullable(); // Model class or type
                $table->unsignedBigInteger('target_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }

        // 4. Admin Dashboard Settings table
        if (! Schema::hasTable('admin_dashboard_settings')) {
            Schema::create('admin_dashboard_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->json('value')->nullable();
                $table->string('description')->nullable();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });

            // Seed initial settings
            DB::table('admin_dashboard_settings')->insert([
                [
                    'key'         => 'system_notice',
                    'value'       => json_encode(['text' => 'স্বাগতম নতুন মাল্টিফাংশনাল অ্যাডমিন ড্যাশবোর্ডে!', 'active' => true, 'type' => 'info']),
                    'description' => 'ড্যাশবোর্ড নোটিশ ব্যানার',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'key'         => 'maintenance_mode',
                    'value'       => json_encode(['enabled' => false, 'reason' => '']),
                    'description' => 'সিস্টেম মেইনটেনেন্স মোড ফ্ল্যাগ',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_dashboard_settings');
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('admin_permissions');
    }
};
