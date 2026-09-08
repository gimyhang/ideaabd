<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create dynamic admin_roles table
        if (! Schema::hasTable('admin_roles')) {
            Schema::create('admin_roles', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('department')->default('General');
                $table->string('badge_color', 30)->default('#2563eb');
                $table->string('icon', 50)->default('fas fa-user-shield');
                $table->boolean('is_system')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Add custom role & IAM security columns to users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'custom_role_id')) {
                    $table->foreignId('custom_role_id')->nullable()->after('role')->constrained('admin_roles')->nullOnDelete();
                }
                if (! Schema::hasColumn('users', 'force_password_reset')) {
                    $table->boolean('force_password_reset')->default(false)->after('must_change_password');
                }
                if (! Schema::hasColumn('users', 'ip_whitelist')) {
                    $table->text('ip_whitelist')->nullable()->after('is_active');
                }
                if (! Schema::hasColumn('users', 'session_invalidated_at')) {
                    $table->timestamp('session_invalidated_at')->nullable()->after('remember_token');
                }
            });
        }

        // 3. User Direct Permission Overrides Table (Per-user granular grant/deny)
        if (! Schema::hasTable('user_has_permissions')) {
            Schema::create('user_has_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('admin_permissions')->cascadeOnDelete();
                $table->boolean('is_granted')->default(true); // true = Grant, false = Explicit Deny
                $table->timestamps();

                $table->unique(['user_id', 'permission_id']);
            });
        }

        // 4. Seed Granular Permissions across 10 Core Modules
        $permissions = [
            // Module 1: Dashboard & Analytics
            ['key' => 'dashboard.view',              'name' => 'ড্যাশবোর্ড পর্যবেক্ষণ',            'module' => 'dashboard',   'description' => 'মূল সিইও অ্যাডমিন ড্যাশবোর্ড ও কেপিআই দেখার অনুমতি', 'action_type' => 'view'],
            ['key' => 'dashboard.financial_metrics','name' => 'আর্থিক মেট্রিক্স ও রাজস্ব গ্রাফ',  'module' => 'dashboard',   'description' => 'মোট বিক্রয়, লাভ-ক্ষতি ও রেভিনিউ মেট্রিক্স দেখার অনুমতি', 'action_type' => 'view'],
            ['key' => 'dashboard.export',           'name' => 'ড্যাশবোর্ড রিপোর্ট এক্সপোর্ট',     'module' => 'dashboard',   'description' => 'ড্যাশবোর্ড ডেটা সিএসভি বা এক্সেলে নামানোর অনুমতি', 'action_type' => 'export'],

            // Module 2: Catalog & Books Management
            ['key' => 'catalog.view',                'name' => 'ক্যাটালগ ও বই দেখুন',             'module' => 'catalog',     'description' => 'বই, ক্যাটাগরি, লেখক ও প্রকাশকের তালিকা দেখা', 'action_type' => 'view'],
            ['key' => 'catalog.create',              'name' => 'নতুন বই ও বান্ডেল যোগ',           'module' => 'catalog',     'description' => 'নতুন মুদ্রিত বই, কম্বো ও বান্ডেল তৈরি করার অনুমতি', 'action_type' => 'create'],
            ['key' => 'catalog.edit',                'name' => 'বইয়ের তথ্য ও মূল্য সম্পাদনা',     'module' => 'catalog',     'description' => 'বইয়ের বিবরণ, প্রচ্ছদ ও মূল্য পরিবর্তন করার অনুমতি', 'action_type' => 'edit'],
            ['key' => 'catalog.delete',              'name' => 'বই ও ক্যাটালগ অপসারণ',          'module' => 'catalog',     'description' => 'ক্যাটালগ থেকে বই বা ক্যাটাগরি মুছে ফেলার অনুমতি', 'action_type' => 'delete'],
            ['key' => 'catalog.stock_update',        'name' => 'স্টক ও ইনভেন্টরি আপডেট',         'module' => 'catalog',     'description' => 'মুদ্রিত বইয়ের মজুত সংখ্যা পরিবর্তন করার অনুমতি', 'action_type' => 'edit'],

            // Module 3: Orders, POS & Sales
            ['key' => 'orders.view',                 'name' => 'অর্ডার ও বিক্রয় তালিকা দেখুন',     'module' => 'sales',       'description' => 'সকল ই-কমার্স অর্ডার ও সেলার বিল দেখার অনুমতি', 'action_type' => 'view'],
            ['key' => 'orders.manage',               'name' => 'অর্ডার প্রসেসিং ও স্ট্যাটাস বদল',   'module' => 'sales',       'description' => 'অর্ডার অনুমোদন, প্যাকেজিং ও ডেলিভারি স্ট্যাটাস আপডেট', 'action_type' => 'edit'],
            ['key' => 'orders.invoice_generate',     'name' => 'চালান ও ইনভয়েস প্রিন্ট',          'module' => 'sales',       'description' => 'অর্ডারের ক্যাশ মেমো ও চালান তৈরির অনুমতি', 'action_type' => 'manage'],
            ['key' => 'orders.cancel_refund',        'name' => 'অর্ডার বাতিল ও রিফান্ড',          'module' => 'sales',       'description' => 'গ্রাহকের অর্ডার বাতিল বা অর্থ ফেরত অনুমোদনের অনুমতি', 'action_type' => 'manage'],
            ['key' => 'pos.operate',                 'name' => 'বইমেলা ও আউটলেট POS পরিচালনা',    'module' => 'sales',       'description' => 'পয়েন্ট অব সেল (POS) ক্যাশ কাউন্টার ও সরাসরি বিক্রয়', 'action_type' => 'manage'],

            // Module 4: Accounting, Invoices & Ledger
            ['key' => 'accounting.view',             'name' => 'হিসাবরক্ষণ ও ক্যাশ খাতা দেখুন',   'module' => 'accounting',  'description' => 'দৈনিক আয়, ব্যয় ও একাউন্টিং লেজার দেখার অনুমতি', 'action_type' => 'view'],
            ['key' => 'accounting.ledger_manage',    'name' => 'আয়-ব্যয় ও ভাউচার এন্ট্রি',       'module' => 'accounting',  'description' => 'নতুন আয় বা ব্যয়ের ট্রানজাকশন ও ভাউচার পোস্টিং', 'action_type' => 'create'],
            ['key' => 'accounting.salary_disburse',  'name' => 'বেতন ও পে-রোল অনুমোদন',          'module' => 'accounting',  'description' => 'কর্মী ও স্টাফদের মাসিক স্যালারি শিট ও বেতন পরিশোধ', 'action_type' => 'manage'],
            ['key' => 'accounting.reports',          'name' => 'আর্থিক বিবরণী ও ব্যালেন্স শিট',   'module' => 'accounting',  'description' => 'মাসিক/বার্ষিক লাভ-ক্ষতি ও অডিট রিপোর্ট দেখা', 'action_type' => 'view'],
            ['key' => 'purchases.manage',            'name' => 'প্রেস ও ভেন্ডর ক্রয় ব্যবস্থাপনা', 'module' => 'accounting',  'description' => 'ছাপাখানা, কাগজ ও কাঁচামাল ক্রয়ের ইনভয়েস তৈরি', 'action_type' => 'manage'],

            // Module 5: HR, Staff & Team Management
            ['key' => 'staff.view',                  'name' => 'টিম ও কর্মী তালিকা দেখুন',        'module' => 'staff',       'description' => 'সকল বিভাগের কর্মকর্তা ও কারিগরদের তালিকা দেখা', 'action_type' => 'view'],
            ['key' => 'staff.manage',                'name' => 'নতুন কর্মী যোগ ও প্রোফাইল এডিট',  'module' => 'staff',       'description' => 'কর্মী নিয়োগ, পদবী নির্ধারণ ও বেতন স্কেল নির্ধারণ', 'action_type' => 'manage'],
            ['key' => 'staff.worklog_verify',        'name' => 'দৈনিক কাজের লগ ও হাজিরা যাচাই',  'module' => 'staff',       'description' => 'কারিগর ও কর্মীদের কাজের পরিমাণ ও প্রোডাকশন যাচাই', 'action_type' => 'edit'],

            // Module 6: Editorial, Blog & Webzines
            ['key' => 'editorial.view',              'name' => 'সম্পাদকীয় ও ব্লগ কনটেন্ট দেখুন',   'module' => 'editorial',   'description' => 'আইডিয়াপত্র ব্লগ, ওয়েবজিন ও পান্ডুলিপি তালিকা দেখা', 'action_type' => 'view'],
            ['key' => 'editorial.create',            'name' => 'ব্লগ ও আর্টিকেল রচনা',          'module' => 'editorial',   'description' => 'নতুন ব্লগ পোস্ট ও সম্পাদকীয় নিবন্ধ তৈরি', 'action_type' => 'create'],
            ['key' => 'editorial.publish',           'name' => 'কনটেন্ট প্রকাশ ও অনুমোদন',       'module' => 'editorial',   'description' => 'কনটেন্ট সরাসরি লাইভ প্রকাশ ও এপ্রুভাল দেওয়ার অনুমতি', 'action_type' => 'manage'],
            ['key' => 'editorial.honorarium',        'name' => 'লেখক সম্মানী ব্যবস্থাপনা',       'module' => 'editorial',   'description' => 'লেখক ও কন্ট্রিবিউটরদের রয়্যালটি ও সম্মানী নির্ধারণ', 'action_type' => 'manage'],

            // Module 7: Digital Marketing & Growth
            ['key' => 'marketing.view',              'name' => 'মার্কেটিং ও ক্যাম্পেইন দেখুন',    'module' => 'marketing',   'description' => 'ভিজিটর ট্রাফিক, অফার ও কুপনের রিপোর্ট দেখা', 'action_type' => 'view'],
            ['key' => 'marketing.coupons_manage',    'name' => 'ডিসকাউন্ট কুপন ও প্রমো কোড',      'module' => 'marketing',   'description' => 'কুপন কোড তৈরি, শতাংশ ছাড় ও শর্তাবলি নির্ধারণ', 'action_type' => 'manage'],
            ['key' => 'marketing.banners_manage',    'name' => 'হোম ব্যানার ও প্রোমো স্লাইডার',   'module' => 'marketing',   'description' => 'ওয়েবসাইটের হিরো ব্যানার ও প্রমোশনাল ব্যানার পরিবর্তন', 'action_type' => 'edit'],
            ['key' => 'marketing.affiliates_manage', 'name' => 'অ্যাফিলিয়েট ও ইনফ্লুয়েন্সার টিম',  'module' => 'marketing',   'description' => 'অ্যাফিলিয়েট পার্টনার অনুমোদন ও কমিশন পেআউট', 'action_type' => 'manage'],

            // Module 8: Customer Support & CRM
            ['key' => 'support.view',                'name' => 'গ্রাহক সাপোর্ট টিকেট দেখুন',      'module' => 'support',     'description' => 'গ্রাহকদের হেল্পডেস্ক টিকেট ও মেসেজ দেখার অনুমতি', 'action_type' => 'view'],
            ['key' => 'support.reply',               'name' => 'সাপোর্ট টিকেটে রিপ্লাই ও সমাধান', 'module' => 'support',     'description' => 'গ্রাহকের সমস্যার সমাধান ও মেসেজের উত্তর দেওয়া', 'action_type' => 'edit'],
            ['key' => 'support.broadcast',           'name' => 'গ্রাহক ব্রডকাস্ট ও নোটিফিকেশন',   'module' => 'support',     'description' => 'এসএমএস/ইমেইলে গ্রাহকদের নোটিশ বা অফার পাঠানো', 'action_type' => 'manage'],

            // Module 9: E-Books & Digital Editions
            ['key' => 'ebooks.view',                 'name' => 'ই-বুক ইনভেন্টরি ও রিডার দেখুন',   'module' => 'ebooks',      'description' => 'ডিজিটাল ই-বুক ক্যাটালগ ও রিডিং স্ট্যাটাস দেখা', 'action_type' => 'view'],
            ['key' => 'ebooks.manage',               'name' => 'ই-বুক ফাইল আপলোড ও মূল্য নির্ধারণ', 'module' => 'ebooks',  'description' => 'পিডিএফ/ইপাব আপলোড, ডিআরএম সেটিংস ও সাবস্ক্রিপশন প্ল্যান', 'action_type' => 'manage'],
            ['key' => 'royalties.manage',            'name' => 'ই-বুক ও বইয়ের রয়্যালটি পেআউট',    'module' => 'ebooks',      'description' => 'লেখকদের রয়্যালটি হিসাব ও পেমেন্ট রিকোয়েস্ট প্রসেসিং', 'action_type' => 'manage'],

            // Module 10: IAM, Roles & System Security
            ['key' => 'users.view',                  'name' => 'সকল ব্যবহারকারীর তালিকা দেখুন',    'module' => 'security',    'description' => 'রেজিস্ট্রিকৃত গ্রাহক, লেখক ও সাব-অ্যাডমিনদের তালিকা দেখা', 'action_type' => 'view'],
            ['key' => 'users.manage',                'name' => 'ইউজার অ্যাকাউন্ট কন্ট্রোল',       'module' => 'security',    'description' => 'ইউজার অনুমোদন, ব্লক, পাসওয়ার্ড রিসেট ও প্রোফাইল এডিট', 'action_type' => 'manage'],
            ['key' => 'roles.manage',                'name' => 'রোল ও পারমিশন ম্যাট্রিক্স নিয়ন্ত্রণ', 'module' => 'security', 'description' => 'নতুন কাস্টম রোল তৈরি ও কর্মীদের পারমিশন অ্যাসাইনমেন্ট', 'action_type' => 'manage'],
            ['key' => 'security.sessions',           'name' => 'সেশন টার্মিনেশন ও আইপি লক',      'module' => 'security',    'description' => 'কর্মীদের সব ডিভাইস থেকে ফোর্স লগআউট ও আইপি রেস্ট্রিকশন', 'action_type' => 'manage'],
            ['key' => 'activity_logs.view',          'name' => 'নিরাপত্তা অডিট লগ দেখুন',        'module' => 'security',    'description' => 'অ্যাডমিন ও কর্মীদের প্রতি মুহূর্তের কার্যকলাপের লগ দেখা', 'action_type' => 'view'],
            ['key' => 'settings.manage',             'name' => 'মূল সিস্টেম সেটিংস ও ক্যাশ',      'module' => 'system',      'description' => 'সাইট কনফিগারেশন, পেমেন্ট গেটওয়ে ও ক্যাশ ক্লিয়ার', 'action_type' => 'manage'],
        ];

        foreach ($permissions as $perm) {
            DB::table('admin_permissions')->updateOrInsert(
                ['key' => $perm['key']],
                [
                    'name'        => $perm['name'],
                    'module'      => $perm['module'],
                    'description' => $perm['description'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }

        // 5. Seed Enterprise Pre-Built Roles
        $defaultRoles = [
            [
                'slug'        => 'admin',
                'name'        => 'সাইট সুপার অ্যাডমিন (Super Admin)',
                'description' => 'প্ল্যাটফর্মের সর্বোচ্চ নীতি নির্ধারক ও পূর্ণ ক্ষমতাপ্রাপ্ত অ্যাক্সেস',
                'department'  => 'Executive',
                'badge_color' => '#1e293b',
                'icon'        => 'fas fa-crown',
                'is_system'   => true,
                'is_active'   => true,
            ],
            [
                'slug'        => 'sub_admin',
                'name'        => 'সাব-অ্যাডমিন (Executive Sub Admin)',
                'description' => 'অপারেশন্স ও ক্যাটালগের সার্বিক প্রশাসনিক দায়িত্বপ্রাপ্ত কর্মকর্তা',
                'department'  => 'Operations & Support',
                'badge_color' => '#2563eb',
                'icon'        => 'fas fa-user-shield',
                'is_system'   => true,
                'is_active'   => true,
            ],
            [
                'slug'        => 'operations_manager',
                'name'        => 'অপারেশন্স ও ফুলফিলমেন্ট ম্যানেজার',
                'description' => 'অর্ডার ডেলিভারি, ইনভেন্টরি মজুত ও গ্রাহক সেবা সমন্বয়কারী',
                'department'  => 'Operations & Support',
                'badge_color' => '#ea580c',
                'icon'        => 'fas fa-headset',
                'is_system'   => false,
                'is_active'   => true,
            ],
            [
                'slug'        => 'chief_editor',
                'name'        => 'প্রধান সম্পাদক ও কনটেন্ট লিড',
                'description' => 'বইয়ের পান্ডুলিপি, ব্লগ, আইডিয়াপত্র ও লেখক সম্মানী তদারককারী',
                'department'  => 'Content & Editorial',
                'badge_color' => '#ca8a04',
                'icon'        => 'fas fa-feather-pointed',
                'is_system'   => false,
                'is_active'   => true,
            ],
            [
                'slug'        => 'digital_marketer',
                'name'        => 'ডিজিটাল মার্কেটিং ও গ্রোথ লিড',
                'description' => 'ক্যাম্পেইন, ফেসবুক/গুগল এডস, কুপন ডিসকাউন্ট ও ট্রাফিক অ্যানালিটিক্স',
                'department'  => 'Digital Marketing',
                'badge_color' => '#0284c7',
                'icon'        => 'fas fa-bullhorn',
                'is_system'   => false,
                'is_active'   => true,
            ],
            [
                'slug'        => 'tech_admin',
                'name'        => 'টেকনিক্যাল ও আইটি সিস্টেম অ্যাডমিন',
                'description' => 'সার্ভার স্বাস্থ্য, ক্যাশ অপ্টিমাইজেশন, লগইন নিরাপত্তা ও ডেটাবেজ ব্যাকআপ',
                'department'  => 'Technical & IT',
                'badge_color' => '#16a34a',
                'icon'        => 'fas fa-laptop-code',
                'is_system'   => false,
                'is_active'   => true,
            ],
            [
                'slug'        => 'finance_controller',
                'name'        => 'হিসাবরক্ষক ও ফিন্যান্স কন্ট্রোলার',
                'description' => 'আয়-ব্যয়, ভাউচার পোস্টিং, পে-রোল স্যালারি ও ইনভয়েস তৈরি',
                'department'  => 'Operations & Support',
                'badge_color' => '#7c3aed',
                'icon'        => 'fas fa-money-check-dollar',
                'is_system'   => false,
                'is_active'   => true,
            ],
            [
                'slug'        => 'seller',
                'name'        => 'সেলার ও ডিলার (Seller / POS Operator)',
                'description' => 'অনুমোদিত পয়েন্ট অব সেল (POS) ও আউটলেটের সেলস বিলিং কাউন্টার',
                'department'  => 'Operations & Support',
                'badge_color' => '#059669',
                'icon'        => 'fas fa-store',
                'is_system'   => true,
                'is_active'   => true,
            ],
            [
                'slug'        => 'support_agent',
                'name'        => 'কাস্টমার সাপোর্ট ও হেল্পডেস্ক এক্সিকিউটিভ',
                'description' => 'গ্রাহকের ফোনকল, মেসেজ, টিকেট ও বইয়ের অনুরোধ সমাধানকারী',
                'department'  => 'Operations & Support',
                'badge_color' => '#0891b2',
                'icon'        => 'fas fa-life-ring',
                'is_system'   => false,
                'is_active'   => true,
            ],
        ];

        foreach ($defaultRoles as $roleData) {
            DB::table('admin_roles')->updateOrInsert(
                ['slug' => $roleData['slug']],
                array_merge($roleData, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // 6. Map Default Role Permissions
        $rolePermissionMappings = [
            'admin' => DB::table('admin_permissions')->pluck('id')->toArray(),
            'sub_admin' => DB::table('admin_permissions')->whereNotIn('key', ['settings.manage', 'security.sessions'])->pluck('id')->toArray(),
            'operations_manager' => DB::table('admin_permissions')->whereIn('module', ['dashboard', 'catalog', 'sales', 'support'])->pluck('id')->toArray(),
            'chief_editor' => DB::table('admin_permissions')->whereIn('module', ['dashboard', 'editorial', 'ebooks', 'catalog'])->pluck('id')->toArray(),
            'digital_marketer' => DB::table('admin_permissions')->whereIn('module', ['dashboard', 'marketing', 'catalog', 'sales'])->pluck('id')->toArray(),
            'tech_admin' => DB::table('admin_permissions')->whereIn('module', ['dashboard', 'security', 'system', 'staff'])->pluck('id')->toArray(),
            'finance_controller' => DB::table('admin_permissions')->whereIn('module', ['dashboard', 'accounting', 'sales'])->pluck('id')->toArray(),
            'seller' => DB::table('admin_permissions')->whereIn('key', ['dashboard.view', 'orders.view', 'pos.operate', 'orders.invoice_generate'])->pluck('id')->toArray(),
            'support_agent' => DB::table('admin_permissions')->whereIn('module', ['dashboard', 'support', 'sales'])->pluck('id')->toArray(),
        ];

        foreach ($rolePermissionMappings as $roleSlug => $permIds) {
            foreach ($permIds as $permId) {
                DB::table('role_has_permissions')->updateOrInsert(
                    ['role' => $roleSlug, 'permission_id' => $permId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_has_permissions');
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'custom_role_id')) {
                    $table->dropForeign(['custom_role_id']);
                    $table->dropColumn('custom_role_id');
                }
                if (Schema::hasColumn('users', 'force_password_reset')) {
                    $table->dropColumn('force_password_reset');
                }
                if (Schema::hasColumn('users', 'ip_whitelist')) {
                    $table->dropColumn('ip_whitelist');
                }
                if (Schema::hasColumn('users', 'session_invalidated_at')) {
                    $table->dropColumn('session_invalidated_at');
                }
            });
        }
        Schema::dropIfExists('admin_roles');
    }
};
