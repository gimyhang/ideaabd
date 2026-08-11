<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds for demo content.
     * Seeds authors, books, ebooks, webzines, and other content.
     * Created by: Masud Rana Shakil
     * For: ideaabd Platform
     */
    public function run()
    {
        // Demo Authors - বাংলা সাহিত্যের শীর্ষস্থানীয় লেখকগণ
        $authors = [
            [
                'name' => 'মুহম্মদ জাফর ইকবাল',
                'bio' => 'বিখ্যাত বাংলা লেখক, বিজ্ঞানী এবং শিক্ষা ব্যক্তিত্ব। তিনি শিশু সাহিত্যে অসাধারণ অবদান রেখেছেন।',
                'email' => 'zafor.ikbal@example.com',
                'slug' => 'jafar-ikbal'
            ],
            [
                'name' => 'আখতারুজ্জামান ইলিয়াস',
                'bio' => 'খ্যাত উপন্যাসিক ও গল্পকার। তার রচনাগুলি সামাজিক বাস্তবতার গভীর চিত্র তুলে ধরে।',
                'email' => 'ilias.akhtar@example.com',
                'slug' => 'akhtar-ilias'
            ],
            [
                'name' => 'হুমায়ুন আহমেদ',
                'bio' => 'বাঙালি লেখক, নাটক পরিচালক ও চলচ্চিত্র নির্মাতা। তার লেখা বাংলা সাহিত্যে মাইলফলক সৃষ্টি করেছে।',
                'email' => 'humayun.ahmed@example.com',
                'slug' => 'humayun-ahmed'
            ],
            [
                'name' => 'শরৎচন্দ্র চট্টোপাধ্যায়',
                'bio' => 'বাঙালি সাহিত্যের অন্যতম শ্রেষ্ঠ লেখক। তার উপন্যাসগুলি মানব জীবনের গভীর বিশ্লেষণ প্রদান করে।',
                'email' => 'sharat.chandra@example.com',
                'slug' => 'sharat-chandra'
            ],
            [
                'name' => 'রবীন্দ্রনাথ ঠাকুর',
                'bio' => 'নোবেল পুরস্কার বিজয়ী কবি, লেখক এবং দার্শনিক। বাংলা সাহিত্যের সর্বকালের মহান ব্যক্তিত্ব।',
                'email' => 'rabindranath@example.com',
                'slug' => 'rabindranath-tagore'
            ],
            [
                'name' => 'নাজমুল হোসেন',
                'bio' => 'জনপ্রিয় কথাসাহিত্যিক ও উপন্যাসকার। তার রোমাঞ্চকর গল্পগুলি পাঠকদের মুগ্ধ করে।',
                'email' => 'nazmul.hosen@example.com',
                'slug' => 'nazmul-hosen'
            ]
        ];

        foreach ($authors as $author) {
            DB::table('authors')->updateOrInsert(
                ['email' => $author['email']],
                [
                    'name' => $author['name'],
                    'bio' => $author['bio'],
                    'email' => $author['email'],
                    'slug' => $author['slug'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Demo Publishers - প্রকাশনী প্রতিষ্ঠানসমূহ
        $publishers = [
            [
                'name' => 'আদিত্য প্রকাশনী',
                'email' => 'aditya@publisher.bd',
                'phone' => '01700000001',
                'city' => 'ঢাকা'
            ],
            [
                'name' => 'মেরিল পাবলিশার্স',
                'email' => 'meril@publisher.bd',
                'phone' => '01700000002',
                'city' => 'ঢাকা'
            ],
            [
                'name' => 'বাংলা একাডেমি',
                'email' => 'bangla@publisher.bd',
                'phone' => '01700000003',
                'city' => 'ঢাকা'
            ],
            [
                'name' => 'দি ইউনিভার্সিটি প্রেস',
                'email' => 'university@publisher.bd',
                'phone' => '01700000004',
                'city' => 'ঢাকা'
            ],
            [
                'name' => 'ঐতিহ্য পাবলিকেশনস',
                'email' => 'aitihasya@publisher.bd',
                'phone' => '01700000005',
                'city' => 'ঢাকা'
            ]
        ];

        foreach ($publishers as $publisher) {
            DB::table('publishers')->updateOrInsert(
                ['email' => $publisher['email']],
                array_merge($publisher, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Demo Books - নতুন বই সংযোজন
        $books = [
            [
                'title' => 'রোবোটিক্স এবং কৃত্রিম বুদ্ধিমত্তা',
                'author_id' => 1,
                'publisher_id' => 1,
                'isbn' => '978-1234567890',
                'price' => 599,
                'discount' => 15,
                'description' => 'ভবিষ্যতের প্রযুক্তি এবং কৃত্রিম বুদ্ধিমত্তার উপর একটি বিস্তারিত গাইড যা সকল স্তরের পাঠকদের জন্য উপযুক্ত।',
                'pages' => 320,
                'publication_date' => '2022-01-15'
            ],
            [
                'title' => 'চিলেকোঠার সেপাই',
                'author_id' => 2,
                'publisher_id' => 2,
                'isbn' => '978-1234567891',
                'price' => 399,
                'discount' => 10,
                'description' => 'স্বাধীনতা যুদ্ধের পটভূমিতে রচিত একটি মহান উপন্যাস যা মানুষের সাহস এবং ত্যাগের গল্প বলে।',
                'pages' => 256,
                'publication_date' => '2020-06-10'
            ],
            [
                'title' => 'বসন্তের এক নিশিথে',
                'author_id' => 5,
                'publisher_id' => 3,
                'isbn' => '978-1234567892',
                'price' => 299,
                'discount' => 20,
                'description' => 'প্রেম এবং আবেগের গভীরতম অনুভূতি নিয়ে লেখা কবিতা সংকলন যা হৃদয় স্পর্শ করে।',
                'pages' => 180,
                'publication_date' => '2019-03-20'
            ],
            [
                'title' => 'অগ্নিশ্বর',
                'author_id' => 4,
                'publisher_id' => 4,
                'isbn' => '978-1234567893',
                'price' => 450,
                'discount' => 12,
                'description' => 'ইতিহাস এবং পৌরাণিক কাহিনীর মেলবন্ধনে তৈরি রোমাঞ্চকর উপন্যাস যা পাঠকদের মুগ্ধ করবে।',
                'pages' => 408,
                'publication_date' => '2021-07-05'
            ],
            [
                'title' => 'পালক এবং মাটি',
                'author_id' => 3,
                'publisher_id' => 5,
                'isbn' => '978-1234567894',
                'price' => 399,
                'discount' => 8,
                'description' => 'গ্রামীণ জীবনের সুন্দর এবং করুণ চিত্রাবলী যা পরিবার এবং মানবিক সম্পর্কের গল্প বলে।',
                'pages' => 288,
                'publication_date' => '2020-11-30'
            ]
        ];

        foreach ($books as $book) {
            DB::table('books')->updateOrInsert(
                ['isbn' => $book['isbn']],
                array_merge($book, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✓ ডেমো ডেটা সফলভাবে সিড করা হয়েছে!');
        $this->command->info('✓ ৬ জন লেখক যুক্ত করা হয়েছে');
        $this->command->info('✓ ৫টি প্রকাশনী যুক্ত করা হয়েছে');
        $this->command->info('✓ ৫টি বই যুক্ত করা হয়েছে');
        $this->command->info('সৃষ্টি করেছেন: Masud Rana Shakil');
    }
}
