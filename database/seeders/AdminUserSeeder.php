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
        $phone    = env('ADMIN_PHONE') ?: '01726976982';

        $existing = User::withTrashed()->where('email', $email)->orWhere('role', User::ROLE_ADMIN)->first();

        if ($existing) {
            $existing->email             = $email;
            $existing->password          = Hash::make($password);
            $existing->role              = User::ROLE_ADMIN;
            $existing->is_active         = true;
            $existing->reg_status        = User::STATUS_APPROVED;
            $existing->reg_type          = User::ROLE_ADMIN;
            $existing->email_verified_at = now();
            $existing->deleted_at        = null;
            if (empty($existing->phone) || env('ADMIN_PHONE')) {
                $existing->phone = $phone;
            }
            if (env('ADMIN_NAME')) {
                $existing->name = env('ADMIN_NAME');
            } elseif (empty($existing->name)) {
                $existing->name = $name;
            }
            $existing->save();
            $user = $existing;
        } else {
            $user = User::create([
                'name'              => $name,
                'email'             => $email,
                'phone'             => $phone,
                'password'          => Hash::make($password),
                'role'              => User::ROLE_ADMIN,
                'is_active'         => true,
                'reg_status'        => User::STATUS_APPROVED,
                'reg_type'          => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]);
        }

        if ($this->command) {
            $this->command->info("অ্যাডমিন প্রস্তুত: {$user->name} ({$user->email})");
        }
    }
}
