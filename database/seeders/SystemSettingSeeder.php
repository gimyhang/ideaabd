<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ট্রানজ্যাকশন ব্যবহার করা হয়েছে যেন যেকোনো এররে আংশিক ডেটা ইনসার্ট না হয়
        DB::transaction(function () {
            $settings = [
                // General Settings
                [
                    'group'     => 'general',
                    'key'       => 'site_name',
                    'value'     => 'আইডিয়া প্রকাশন',
                    'type'      => 'text',
                    'is_public' => true,
                ],
                [
                    'group'     => 'general',
                    'key'       => 'site_tagline',
                    'value'     => 'জ্ঞান ও সাহিত্যের বাতিঘর',
                    'type'      => 'text',
                    'is_public' => true,
                ],
                [
                    'group'     => 'general',
                    'key'       => 'site_logo',
                    'value'     => 'settings/logo.png',
                    'type'      => 'image',
                    'is_public' => true,
                ],

                // Contact & Location Info
                [
                    'group'     => 'contact',
                    'key'       => 'contact_email',
                    'value'     => 'info@ideaabd.com',
                    'type'      => 'text',
                    'is_public' => true,
                ],
                [
                    'group'     => 'contact',
                    'key'       => 'contact_phone',
                    'value'     => '+8801700000000',
                    'type'      => 'text',
                    'is_public' => true,
                ],
                [
                    'group'     => 'contact',
                    'key'       => 'store_address',
                    'value'     => 'রংপুর, বাংলাদেশ',
                    'type'      => 'textarea',
                    'is_public' => true,
                ],

                // E-commerce & Logistics Settings
                [
                    'group'     => 'ecommerce',
                    'key'       => 'currency_symbol',
                    'value'     => '৳',
                    'type'      => 'text',
                    'is_public' => true,
                ],
                [
                    'group'     => 'ecommerce',
                    'key'       => 'currency_code',
                    'value'     => 'BDT',
                    'type'      => 'text',
                    'is_public' => true,
                ],
                [
                    'group'     => 'ecommerce',
                    'key'       => 'inside_dhaka_shipping_fee',
                    'value'     => '60',
                    'type'      => 'number',
                    'is_public' => true,
                ],
                [
                    'group'     => 'ecommerce',
                    'key'       => 'outside_dhaka_shipping_fee',
                    'value'     => '120',
                    'type'      => 'number',
                    'is_public' => true,
                ],
                [
                    'group'     => 'ecommerce',
                    'key'       => 'free_shipping_min_amount',
                    'value'     => '1000',
                    'type'      => 'number',
                    'is_public' => true,
                ],
                [
                    'group'     => 'ecommerce',
                    'key'       => 'order_prefix',
                    'value'     => 'IDEA-',
                    'type'      => 'text',
                    'is_public' => false,
                ],

                // SEO & Analytics Integration
                [
                    'group'     => 'seo',
                    'key'       => 'meta_title',
                    'value'     => 'আইডিয়া প্রকাশন - বইয়ের অনলাইন শপ',
                    'type'      => 'text',
                    'is_public' => true,
                ],
                [
                    'group'     => 'seo',
                    'key'       => 'meta_description',
                    'value'     => 'আইডিয়া প্রকাশনের সব ধরণের বই অনলাইনে অর্ডার করুন সহজে ও দ্রুত সময়ে।',
                    'type'      => 'textarea',
                    'is_public' => true,
                ],
                [
                    'group'     => 'seo',
                    'key'       => 'google_analytics_id',
                    'value'     => env('GOOGLE_ANALYTICS_ID', ''),
                    'type'      => 'text',
                    'is_public' => false,
                ],

                // Security & System Status Controls
                [
                    'group'     => 'system',
                    'key'       => 'maintenance_mode',
                    'value'     => '0',
                    'type'      => 'boolean',
                    'is_public' => true,
                ],
                [
                    'group'     => 'system',
                    'key'       => 'allow_customer_registration',
                    'value'     => '1',
                    'type'      => 'boolean',
                    'is_public' => true,
                ],

                // Social Links (JSON format)
                [
                    'group'     => 'social',
                    'key'       => 'social_links',
                    'value'     => json_encode([
                        'facebook'  => 'https://facebook.com/ideaabd',
                        'youtube'   => 'https://youtube.com',
                        'instagram' => 'https://instagram.com',
                    ]),
                    'type'      => 'json',
                    'is_public' => true,
                ],
            ];

            foreach ($settings as $setting) {
                // updateOrCreate ব্যবহার করার ফলে বারবার সীড চালালেও ডেটা ডুপ্লিকেট হবে না বা বিদ্যমান পরিবর্তন নষ্ট হবে না
                DB::table('system_settings')->updateOrCreate(
                    ['key' => $setting['key']],
                    [
                        'group'      => $setting['group'],
                        'value'      => $setting['value'],
                        'type'       => $setting['type'],
                        'is_public'  => $setting['is_public'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }
}