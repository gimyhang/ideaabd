<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogTag;
use Modules\Author\Models\Author;
use Modules\Publisher\Models\Publisher;
use Modules\Webzine\Models\Webzine;
use Modules\Research\Models\ResearchPaper;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        // Create Blog Categories
        $blogCategories = [
            ['name' => 'প্রযুক্তি', 'slug' => 'technology', 'description' => 'প্রযুক্তি সম্পর্কিত নিবন্ধ'],
            ['name' => 'শিক্ষা', 'slug' => 'education', 'description' => 'শিক্ষামূলক বিষয়বস্তু'],
            ['name' => 'সাহিত্য', 'slug' => 'literature', 'description' => 'সাহিত্যিক রচনা'],
            ['name' => 'ব্যবসা', 'slug' => 'business', 'description' => 'ব্যবসায়িক পরামর্শ'],
        ];

        foreach ($blogCategories as $category) {
            BlogCategory::firstOrCreate(['slug' => $category['slug']], $category);
        }

        // Create Authors
        $authors = [
            ['name' => 'করিম সাহেব', 'slug' => 'karim-saheb', 'email' => 'karim@example.com', 'bio' => 'প্রখ্যাত লেখক এবং বিশিষ্ট বুদ্ধিজীবী'],
            ['name' => 'ফাতেমা বেগম', 'slug' => 'fatema-begum', 'email' => 'fatema@example.com', 'bio' => 'গল্পকার এবং সাহিত্য সমালোচক'],
        ];

        foreach ($authors as $author) {
            Author::firstOrCreate(['email' => $author['email']], array_merge($author, ['is_verified' => true, 'is_active' => true]));
        }

        // Create Blog Posts (if auth user exists)
        $user = \App\Models\User::first();
        if ($user) {
            $category = BlogCategory::first();
            BlogPost::firstOrCreate(
                ['slug' => 'first-blog-post'],
                [
                    'title' => 'বাংলা ব্লগিং এর ভবিষ্যৎ',
                    'content' => 'বাংলা ভাষায় ডিজিটাল কন্টেন্ট তৈরি এখন একটি বড় প্রবণতা। এই নিবন্ধে আমরা আলোচনা করব...',
                    'excerpt' => 'বাংলা ব্লগিং এর আগামী দিনগুলি কেমন হবে তা নিয়ে চিন্তাভাবনা।',
                    'category_id' => $category?->id,
                    'author_id' => $user->id,
                    'status' => 'published',
                    'published_at' => now(),
                    'is_featured' => true,
                ]
            );
        }

        // Create Publishers
        $publishers = [
            ['name' => 'অগ্রণী প্রকাশনী', 'slug' => 'agroni-prakashoni', 'email' => 'agroni@example.com', 'description' => 'বাংলাদেশের শীর্ষ প্রকাশনীগুলির মধ্যে একটি'],
            ['name' => 'বাংলা বই ঘর', 'slug' => 'bangla-boi-ghor', 'email' => 'banglabook@example.com', 'description' => 'আধুনিক এবং ঐতিহ্যবাহী বাংলা সাহিত্য প্রকাশনা'],
        ];

        foreach ($publishers as $publisher) {
            Publisher::firstOrCreate(['email' => $publisher['email']], array_merge($publisher, ['is_verified' => true, 'is_active' => true]));
        }

        // Create Webzines
        Webzine::firstOrCreate(
            ['slug' => 'demo-magazine'],
            [
                'title' => 'আইডিয়া ম্যাগাজিন - প্রথম সংখ্যা',
                'description' => 'আমাদের প্রথম ডিজিটাল ম্যাগাজিন যা সাহিত্য, প্রযুক্তি এবং সংস্কৃতি নিয়ে আলোচনা করে।',
                'issue_number' => '1',
                'publisher_id' => Publisher::first()?->id,
                'publication_date' => now(),
                'is_published' => true,
                'published_at' => now(),
            ]
        );

        // Create Research Papers
        ResearchPaper::firstOrCreate(
            ['slug' => 'ai-in-bengali-nlp'],
            [
                'title' => 'কৃত্রিম বুদ্ধিমত্তা এবং বাংলা ভাষা প্রক্রিয়াকরণ',
                'abstract' => 'এই গবেষণা পত্রে আমরা আলোচনা করি কীভাবে এআই বাংলা ভাষা বোঝে এবং প্রক্রিয়া করে।',
                'content' => 'আর্টিফিশিয়াল ইন্টেলিজেন্সের উন্নয়ন এবং বাংলা ভাষায় এর প্রয়োগ নিয়ে বিস্তারিত আলোচনা...',
                'category' => 'প্রযুক্তি',
                'author_id' => Author::first()?->id,
                'publication_date' => now(),
                'is_published' => true,
                'published_at' => now(),
            ]
        );
    }
}
