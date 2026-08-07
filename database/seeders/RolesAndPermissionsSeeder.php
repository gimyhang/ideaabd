<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ১. ইন-মেমোরি ও ক্যাশ ক্লিয়ার (Cache Poisoning ও পারমিশন ওভারল্যাপ এড়াতে)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ২. ডাটাবেজ ট্রানজ্যাকশন ব্যবহার (মাইগ্রেশনের মাঝে এরর এলে অটো রোলব্যাক হবে)
        DB::transaction(function () {
            $guard = 'web';

            // মডিউল ভিত্তিক পারমিশন
            $permissions = [
                // Category Module
                'view-categories', 'create-categories', 'edit-categories', 'delete-categories',

                // Author Module
                'view-authors', 'create-authors', 'edit-authors', 'delete-authors',

                // Publisher Module
                'view-publishers', 'create-publishers', 'edit-publishers', 'delete-publishers',

                // Book Module
                'view-books', 'create-books', 'edit-books', 'delete-books',

                // Order Module
                'view-orders', 'manage-orders', 'delete-orders',

                // User & Access Control Management
                'view-users', 'create-users', 'edit-users', 'delete-users',
                'manage-roles', 'manage-permissions',

                // System Settings
                'manage-settings',
            ];

            // ৩. নির্দিষ্ট গার্ডসহ পারমিশন তৈরি
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard,
                ]);
            }

            // ৪. নির্দিষ্ট গার্ড ব্যবহার করে রোল অ্যাসাইনমেন্ট
            
            // Super Admin Role
            Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => $guard]);

            // Admin Role (সকল পারমিশন দেওয়া হলো)
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
            $adminRole->syncPermissions($permissions);

            // Manager Role (সীমিত পারমিশন)
            $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => $guard]);
            $managerRole->syncPermissions([
                'view-categories', 'create-categories', 'edit-categories',
                'view-authors', 'create-authors', 'edit-authors',
                'view-publishers', 'create-publishers', 'edit-publishers',
                'view-books', 'create-books', 'edit-books',
                'view-orders', 'manage-orders',
            ]);

            // Customer Role (কোনো ব্যাকএন্ড পারমিশন ছাড়াই তৈরি)
            Role::firstOrCreate(['name' => 'customer', 'guard_name' => $guard]);
        });
    }
}