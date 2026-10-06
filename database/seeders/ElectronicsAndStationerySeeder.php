<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ElectronicsAndStationerySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Electronics Categories
        $elecCategories = [
            [
                'name' => 'রিডিং লাইট ও ল্যাম্প',
                'slug' => 'reading-lights',
                'type' => 'electronics',
                'description' => 'চোখের সুরক্ষায় রিচার্জেবল ও ফ্লেক্সিবল রিডিং ল্যাম্প',
                'icon' => 'fas fa-lightbulb',
                'sort_order' => 1,
            ],
            [
                'name' => 'স্মার্ট স্টাডি ডিভাইস',
                'slug' => 'smart-study-devices',
                'type' => 'electronics',
                'description' => 'ডিজিটাল নোটপ্যাড, স্মার্ট পেন ও টাইমার ডিভাইস',
                'icon' => 'fas fa-tablet-alt',
                'sort_order' => 2,
            ],
            [
                'name' => 'অডিও ও হেডফোন',
                'slug' => 'audio-headphones',
                'type' => 'electronics',
                'description' => 'নয়েজ ক্যানসেলিং হেডফোন ও অডিওবুক প্লেয়ার',
                'icon' => 'fas fa-headphones',
                'sort_order' => 3,
            ],
            [
                'name' => 'পাওয়ার ও চার্জার',
                'slug' => 'power-chargers',
                'type' => 'electronics',
                'description' => 'ফাস্ট চার্জিং পাওয়ার ব্যাংক ও অ্যাডাপ্টার',
                'icon' => 'fas fa-bolt',
                'sort_order' => 4,
            ],
            [
                'name' => 'ডেস্ক গ্যাজেটস',
                'slug' => 'desk-gadgets',
                'type' => 'electronics',
                'description' => 'ডিজিটাল ক্লক, মিনি ফ্যান ও ক্লিন ক্লিনার',
                'icon' => 'fas fa-microchip',
                'sort_order' => 5,
            ],
        ];

        $elecCatModels = [];
        foreach ($elecCategories as $cat) {
            $elecCatModels[$cat['slug']] = ProductCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        // 2. Stationery Categories & Products (Delegated to dedicated seeder)
        $this->call(StationeryProductDatabaseSeeder::class);

        // 3. Electronics Products
        $electronicsProducts = [
            [
                'title' => 'আইডিয়া প্রো ফ্লেক্সিবল রিচার্জেবল রিডিং ল্যাম্প',
                'slug' => 'idea-pro-flexible-rechargeable-reading-lamp',
                'type' => 'electronics',
                'category_id' => $elecCatModels['reading-lights']->id,
                'brand' => 'Baseus',
                'model' => 'Comfort Pro Eye-Care',
                'sku' => 'ELC-LMP-01',
                'summary' => 'চোখের সুরক্ষায় ৩ রঙের আলোর মোড এবং টাচ কন্ট্রোল সুবিধা সম্বলিত এলইডি ক্লিপ-অন রিডিং ল্যাম্প।',
                'description' => 'দীর্ঘ সময় বই পড়ার জন্য উপযুক্ত ফ্লেক্সিবল এলইডি ল্যাম্প। এটি চোখের ওপর বাড়তি চাপ পড়তে দেয় না। ৩৬০ ডিগ্রি সহজে ঘোরানো যায় এবং ক্লিপের সাহায্যে টেবিল বা বইয়ের মলাটে সহজে আটকানো যায়। ১৮০০ মিলিঅ্যাম্পিয়ার ব্যাটারি একবার চার্জে প্রায় ১২ ঘন্টা নিরবচ্ছিন্ন আলো দেয়।',
                'price' => 1250,
                'discount_price' => 950,
                'stock' => 45,
                'warranty' => '৬ মাসের অফিসিয়াল রিপ্লেসমেন্ট ওয়ারেন্টি',
                'badge' => 'হট ডিল',
                'rating' => 4.9,
                'reviews_count' => 38,
                'is_featured' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=600&auto=format&fit=crop&q=80',
                'specifications' => [
                    'ব্যাটারি ক্যাপাসিটি' => '1800mAh Li-ion',
                    'চার্জিং পোর্ট' => 'Type-C Fast Charging',
                    'কালার মোড' => 'Warm, Natural, Cool White',
                    'ব্রাইটনেস লেভেল' => '৩টি স্টেপ টাচ অ্যাডজাস্টেবল',
                    'বডি মেটেরিয়াল' => 'ABS + Flexible Silicone',
                ],
            ],
            [
                'title' => 'স্মার্ট ডিজিটাল এলসিডি রাইটিং প্যাড ও নোটবুক ১২ ইঞ্চি',
                'slug' => 'smart-digital-lcd-writing-pad-12-inch',
                'type' => 'electronics',
                'category_id' => $elecCatModels['smart-study-devices']->id,
                'brand' => 'Xiaomi',
                'model' => 'Mijia LCD Blackboard 12"',
                'sku' => 'ELC-NTP-02',
                'summary' => 'কাগজ ছাড়া নোট নেওয়া, অঙ্ক কষা ও ড্রয়িংয়ের জন্য আধুনিক চোখের সুরক্ষাযুক্ত ডিজিটাল প্যাড।',
                'description' => 'কাগজের অপচয় রোধ করতে স্টাডি ও অফিসের জন্য অত্যন্ত উপযোগী ১২ ইঞ্চি ডিজিটাল এলসিডি রাইটিং ট্যাবলেট। এতে রয়েছে ওয়ান-ক্লিক ক্লিয়ার বাটন এবং স্ক্রিন লক সুইচ যেন গুরুত্বপূর্ণ ড্রয়িং বা নোট মুছে না যায়।',
                'price' => 1650,
                'discount_price' => 1350,
                'stock' => 30,
                'warranty' => '১ বছরের ওয়ারেন্টি',
                'badge' => 'জনপ্রিয়',
                'rating' => 4.8,
                'reviews_count' => 52,
                'is_featured' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&auto=format&fit=crop&q=80',
                'specifications' => [
                    'স্ক্রিন সাইজ' => '১২ ইঞ্চি প্রেসার সেনসিটিভ এলসিডি',
                    'ব্যাটারি' => 'CR2025 বাটন সেল (১ বছর ব্যাকআপ)',
                    'পেন স্টাইলাস' => 'ম্যাগনেটিক আল্ট্রা-লাইট পেন অন্তর্ভুক্ত',
                    'ওজন' => '২১৫ গ্রাম',
                ],
            ],
            [
                'title' => 'অ্যানকার সাউন্ডকোর অ্যাক্টিভ নয়েজ ক্যানসেলিং হেডফোন',
                'slug' => 'anker-soundcore-life-q30-anc-headphone',
                'type' => 'electronics',
                'category_id' => $elecCatModels['audio-headphones']->id,
                'brand' => 'Anker',
                'model' => 'Soundcore Life Q30',
                'sku' => 'ELC-HDP-03',
                'summary' => 'পড়াশোনায় পূর্ণ মনোযোগ ও অডিওবুক শোনার জন্য হাই-রেজ অডিও ও এআই হাইব্রিড নয়েজ ক্যানসলেশন।',
                'description' => 'পারিপার্শ্বিক সকল আওয়াজ ব্লক করে পড়ালেখা ও গবেষণায় শতভাগ মনোযোগ ধরে রাখতে অনবদ্য হাইব্রিড অ্যাক্টিভ নয়েজ ক্যানসেলিং হেডফোন। ৪০ ঘন্টা প্লে-টাইম এবং নরম মেমরি ফোম ইয়ারপ্যাড দীর্ঘ সময় আরামদায়ক ব্যবহার নিশ্চিত করে।',
                'price' => 8500,
                'discount_price' => 7490,
                'stock' => 18,
                'warranty' => '১৮ মাসের অফিশিয়াল ওয়ারেন্টি',
                'badge' => 'প্রিমিয়াম',
                'rating' => 4.95,
                'reviews_count' => 64,
                'is_featured' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80',
                'specifications' => [
                    'কানেক্টিভিটি' => 'Bluetooth 5.0 + AUX 3.5mm',
                    'ব্যাটারি ব্যাকআপ' => '৪০ ঘন্টা (ANC অন), ৬০ ঘন্টা (ANC অফ)',
                    'চার্জিং' => 'USB-C ফাস্ট চার্জিং (৫ মিনিটে ৪ ঘন্টা প্লেব্যাক)',
                    'অডিও কোডেক' => 'Hi-Res Audio Certified, AAC, SBC',
                ],
            ],
            [
                'title' => 'বেসাস পোর্টেবল ২০,০০০ এমএএইচ ফাস্ট চার্জিং পাওয়ার ব্যাংক',
                'slug' => 'baseus-portable-20000mah-fast-charging-power-bank',
                'type' => 'electronics',
                'category_id' => $elecCatModels['power-chargers']->id,
                'brand' => 'Baseus',
                'model' => 'Blade 22.5W Digital Display',
                'sku' => 'ELC-PWR-04',
                'summary' => 'এলইডি ডিজিটাল ডিসপ্লে ও মাল্টি-পোর্ট ২২.৫ ওয়াট আল্ট্রা-স্লিম পোর্টেবল পাওয়ার ব্যাংক।',
                'description' => 'ল্যাপটপ, ট্যাবলেট বা স্মার্টফোনের চার্জিং নিয়ে আর চিন্তা নেই। আধুনিক ব্যাটারি সুরক্ষাযুক্ত সলিড অ্যালুমিনিয়াম বডির প্রিমিয়াম পাওয়ার ব্যাংক। এক সাথে ৩টি ডিভাইস চার্জ দেওয়া সম্ভব।',
                'price' => 3200,
                'discount_price' => 2650,
                'stock' => 25,
                'warranty' => '১ বছরের ওয়ারেন্টি',
                'badge' => 'সেরা অফার',
                'rating' => 4.85,
                'reviews_count' => 29,
                'is_featured' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80',
                'specifications' => [
                    'ক্যাপাসিটি' => '20,000mAh / 3.7V (74Wh)',
                    'সর্বোচ্চ আউটপুট' => '22.5W Quick Charge 3.0 & PD 3.0',
                    'পোর্ট' => '২টি USB-A, ১টি Type-C ইন/আউট',
                    'ডিসপ্লে' => 'স্মার্ট ডিজিটাল পার্সেন্টেজ ডিসপ্লে',
                ],
            ],
            [
                'title' => 'স্মার্ট পমোডোরো স্টাডি টাইমার ও অ্যালার্ম ক্লক',
                'slug' => 'smart-pomodoro-study-timer-alarm-clock',
                'type' => 'electronics',
                'category_id' => $elecCatModels['desk-gadgets']->id,
                'brand' => 'Idea Smart',
                'model' => 'Cube Focus Timer Pro',
                'sku' => 'ELC-TMR-05',
                'summary' => 'পড়ার টেবিলে মনোযোগ ধরে রাখার জন্য কিউব রোটেশনাল পমোডোরো টাইমার ও সাইলেন্ট কাউন্টডাউন।',
                'description' => 'পমোডোরো টেকনিক অনুযায়ী পড়ালেখা বা কাজের সময়কে ২৫ মিনিট ফোকাস ও ৫ মিনিট ব্রেকে ভাগ করার জন্য আদর্শ স্টাডি কিউব। উল্টে রাখলেই স্বয়ংক্রিয়ভাবে টাইমার চালু হয়। ভাইব্রেশন ও মিউট নোটিফিকেশন মোড রয়েছে।',
                'price' => 1100,
                'discount_price' => 850,
                'stock' => 40,
                'warranty' => '৬ মাসের ওয়ারেন্টি',
                'badge' => 'নতুন',
                'rating' => 4.75,
                'reviews_count' => 21,
                'is_featured' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1563861826100-9cb868fdbe1c?w=600&auto=format&fit=crop&q=80',
                'specifications' => [
                    'টাইম প্রিসেট' => '৫, ১৫, ২৫, ৪৫ মিনিট কিউব ফ্লিপ',
                    'চার্জিং' => 'Type-C রিচার্জেবল লিথিয়াম ব্যাটারি',
                    'মোড' => 'সাউন্ড ও ভাইব্রেশন ডুয়াল মোড',
                ],
            ],
            [
                'title' => 'ইউগ্রিন এরগনোমিক ভার্টিকাল রিচার্জেবল ব্লুটুথ মাউস',
                'slug' => 'ugreen-ergonomic-vertical-bluetooth-mouse',
                'type' => 'electronics',
                'category_id' => $elecCatModels['desk-gadgets']->id,
                'brand' => 'Ugreen',
                'model' => 'ErgoPro Wireless 4000 DPI',
                'sku' => 'ELC-MOU-06',
                'summary' => 'হাতের কব্জির ব্যথা প্রতিরোধে ন্যাচারাল হ্যান্ডশেক পজিশন ভার্টিকাল ওয়্যারলেস মাউস।',
                'description' => 'দীর্ঘক্ষণ কম্পিউটারে বই লেখা, এডিটিং বা অফিসের কাজের জন্য চিকিৎসকদের দ্বারা প্রশংসিত এরগনোমিক মাউস। এর ৫৭ ডিগ্রি কোণ হাতের স্বাভাবিক অবস্থান ধরে রাখে। সাইলেন্ট ক্লিক সিস্টেম।',
                'price' => 2400,
                'discount_price' => 1950,
                'stock' => 22,
                'warranty' => '১ বছর রিপ্লেসমেন্ট ওয়ারেন্টি',
                'badge' => 'হট ডিল',
                'rating' => 4.9,
                'reviews_count' => 19,
                'is_featured' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=600&auto=format&fit=crop&q=80',
                'specifications' => [
                    'ডিপিআই' => '1000 - 1600 - 2400 - 4000 Adjustable',
                    'কানেক্টিভিটি' => 'Bluetooth 5.0 + 2.4G USB ডঙ্গেল',
                    'ব্যাটারি' => 'রিচার্জেবল ৫০০ এমএএইচ (৩ মাস ব্যাকআপ)',
                ],
            ],
            [
                'title' => 'ডেস্কটপ মিনি এয়ার পিউরিফায়ার ও নয়েজলেস রিডিং ফ্যান',
                'slug' => 'desktop-mini-air-purifier-reading-fan',
                'type' => 'electronics',
                'category_id' => $elecCatModels['reading-lights']->id,
                'brand' => 'Baseus',
                'model' => 'Ocean Desk Whisper',
                'sku' => 'ELC-FAN-07',
                'summary' => 'পড়ার টেবিল শান্ত ও ঠাণ্ডা রাখার জন্য আল্ট্রা-সাইলেন্ট রিচার্জেবল ফ্যান উইথ সফট নাইট লাইট।',
                'description' => 'স্টাডি টেবিল ও বেডসাইডের জন্য উপযুক্ত মিনি রিচার্জেবল টেবিল ফ্যান। কোন রকম শব্দ তৈরি না করে শান্ত হাওয়া দেয়। ৪টি স্পিড মোড এবং বিল্ট-ইন সফট ওয়ার্ম রিডিং লাইট রয়েছে।',
                'price' => 1850,
                'discount_price' => 1450,
                'stock' => 35,
                'warranty' => '৬ মাসের ওয়ারেন্টি',
                'badge' => 'জনপ্রিয়',
                'rating' => 4.8,
                'reviews_count' => 17,
                'is_featured' => true,
                'cover_image' => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=600&auto=format&fit=crop&q=80',
                'specifications' => [
                    'ব্যাটারি' => '4000mAh Li-ion (১৬ ঘন্টা ব্যাকআপ)',
                    'শব্দের মাত্রা' => '২০ ডেসিবেলের নিচে (অত্যন্ত শান্ত)',
                    'চার্জিং' => 'USB Type-C',
                ],
            ],
        ];

        foreach ($electronicsProducts as $prod) {
            Product::updateOrCreate(
                ['slug' => $prod['slug']],
                $prod
            );
        }

        // 4. Stationery Products are managed by StationeryProductDatabaseSeeder called above
        return;
    }
}
