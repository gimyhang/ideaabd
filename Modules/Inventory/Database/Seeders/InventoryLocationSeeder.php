<?php

declare(strict_types=1);

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Location;

if (!class_exists(__NAMESPACE__ . '\InventoryLocationSeeder')) {
    class InventoryLocationSeeder extends Seeder
    {
        /**
         * Seed initial central warehouse and key showrooms.
         */
        public function run(): void
        {
            $locations = [
                [
                    'name' => 'Central Warehouse (কেন্দ্রীয় গুদাম - বাংলাবাজার)',
                    'code' => 'WH-CENTRAL',
                    'type' => 'warehouse',
                    'is_central' => true,
                    'contact_person' => 'স্টক ইনচার্জ',
                    'phone' => '01700000001',
                    'email' => 'warehouse@ideaabd.com',
                    'address' => '৩৮/২-ক, বাংলাবাজার, ঢাকা-১১০০',
                    'is_active' => true,
                ],
                [
                    'name' => 'Mirpur Showroom (মিরপুর শোরুম ও সেলস সেন্টার)',
                    'code' => 'SR-MIRPUR',
                    'type' => 'showroom',
                    'is_central' => false,
                    'contact_person' => 'মিরপুর ব্রাঞ্চ ম্যানেজার',
                    'phone' => '01700000002',
                    'email' => 'mirpur.branch@ideaabd.com',
                    'address' => 'মিরপুর ১০, ঢাকা-১২১৬',
                    'is_active' => true,
                ],
                [
                    'name' => 'Banglabazar Showroom (বাংলাবাজার ডিসপ্লে ও সেলস সেন্টার)',
                    'code' => 'SR-BANGLABAZAR',
                    'type' => 'showroom',
                    'is_central' => false,
                    'contact_person' => 'শোরুম ইনচার্জ',
                    'phone' => '01700000003',
                    'email' => 'showroom@ideaabd.com',
                    'address' => 'বাংলাবাজার প্রধান সড়ক, ঢাকা',
                    'is_active' => true,
                ],
                [
                    'name' => 'Chattogram Showroom (চট্টগ্রাম আউটলেট)',
                    'code' => 'SR-CTG',
                    'type' => 'showroom',
                    'is_central' => false,
                    'contact_person' => 'চট্টগ্রাম শাখা ব্যবস্থাপক',
                    'phone' => '01700000004',
                    'email' => 'ctg.branch@ideaabd.com',
                    'address' => 'আন্দরকিল্লা, চট্টগ্রাম',
                    'is_active' => true,
                ],
            ];

            foreach ($locations as $data) {
                Location::firstOrCreate(
                    ['code' => $data['code']],
                    $data
                );
            }
        }
    }
}
