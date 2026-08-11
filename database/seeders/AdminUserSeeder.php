<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Ensures the site admin account exists.
 *
 * Credentials come from .env so no password is ever committed:
 *
 *     ADMIN_NAME="Site Admin"
 *     ADMIN_EMAIL=admin@example.com
 *     ADMIN_PASSWORD=<strong password>
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (empty($email) || empty($password)) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD .env-এ নেই — অ্যাডমিন তৈরি এড়িয়ে যাওয়া হলো।');

            return;
        }

        $user = User::withTrashed()->updateOrCreate(
            ['email' => $email],
            [
                'name'       => env('ADMIN_NAME', 'Site Admin'),
                'password'   => Hash::make($password),
                'role'       => User::ROLE_ADMIN,
                'is_active'  => true,
                'reg_status' => User::STATUS_APPROVED,
                'reg_type'   => User::ROLE_ADMIN,
                'deleted_at' => null,
            ]
        );

        $this->command?->info("অ্যাডমিন প্রস্তুত: {$user->email}");
    }
}
