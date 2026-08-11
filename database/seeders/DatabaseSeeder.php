<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Default entry point for `php artisan db:seed`.
 *
 * Only idempotent seeders belong here so the command is safe to re-run on an
 * existing database. Demo/sample content stays opt-in via:
 *
 *     php artisan db:seed --class=DemoContentSeeder
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
        ]);
    }
}
