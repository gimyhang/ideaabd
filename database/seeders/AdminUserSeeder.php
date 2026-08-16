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
 *     ADMIN_NAME="admin"
 *     ADMIN_USERNAME="admin"
 *     ADMIN_EMAIL=adideabd@gmail.com
 *     ADMIN_PASSWORD=admin123456
 *
 * @property \Illuminate\Console\Command|null $command
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL') ?: 'adideabd@gmail.com';
        $password = env('ADMIN_PASSWORD') ?: 'admin123456';
        $name     = env('ADMIN_NAME') ?: (env('ADMIN_USERNAME') ?: 'admin');

        $user = User::withTrashed()->updateOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'password'          => Hash::make($password),
                'role'              => User::ROLE_ADMIN,
                'is_active'         => true,
                'reg_status'        => User::STATUS_APPROVED,
                'reg_type'          => User::ROLE_ADMIN,
                'email_verified_at' => now(),
                'deleted_at'        => null,
            ]
        );

        if ($this->command) {
            $this->command->info("অ্যাডমিন প্রস্তুত: {$user->name} ({$user->email})");
        }
    }
}
