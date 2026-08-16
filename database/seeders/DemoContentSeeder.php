<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Demo catalog content (books, authors, categories, blog) without fake staff accounts.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Ensure main admin exists via AdminUserSeeder
        $this->call(AdminUserSeeder::class);

        // ── Category ────────────────────────────────────────────────────
        $categoryId = $this->upsert('categories', ['slug' => 'uponnash'], [
            'name'        => 'উপন্যাস',
            'description' => 'বাংলা সাহিত্যের কালজয়ী ও সমকালীন উপন্যাসের সংগ্রহ।',
            'sort_order'  => 1,
            'is_active'   => true,
            'is_featured' => true,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        // ── Publisher ───────────────────────────────────────────────────
        $publisherId = $this->upsert('publishers', ['slug' => 'idea-prokashon'], [
            'name'        => 'আইডিয়া প্রকাশন',
            'description' => 'মননশীল সাহিত্য ও গবেষণাগ্রন্থ প্রকাশে নিবেদিত প্রকাশনা প্রতিষ্ঠান।',
            'email'       => 'ideapbd@gmail.com',
            'phone'       => '+8801726976982',
            'country'     => 'বাংলাদেশ',
            'is_verified' => true,
            'is_active'   => true,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        // ── Author ──────────────────────────────────────────────────────
        $authorId = $this->upsert('authors', ['slug' => 'nabanita-rahman'], [
            'name'         => 'নবনীতা রহমান',
            'bio'          => 'সমকালীন বাংলা কথাসাহিত্যের লেখক। নদীভাঙন ও গ্রামীণ জীবন তাঁর লেখার প্রধান বিষয়।',
            'email'        => 'nabanita@example.com',
            'is_verified'  => true,
            'is_active'    => true,
            'is_publisher' => false,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        // ── Book ────────────────────────────────────────────────────────
        $bookId = $this->upsert('books', ['slug' => 'nadir-opar-akash'], [
            'category_id'    => $categoryId,
            'publisher_id'   => $publisherId,
            'title'          => 'নদীর ওপার আকাশ',
            'isbn'           => '9789849001234',
            'description'    => 'নদীভাঙনে ভিটেমাটি হারানো এক পরিবারের টিকে থাকার গল্প। বাস্তবচ্যুত মানুষের স্বপ্ন ও সংগ্রামের কথা উঠে এসেছে এই উপন্যাসে।',
            'price'          => 480.00,
            'discount_price' => 384.00,
            'preview_pages'  => 12,
            'stock_quantity' => 45,
            'sales_count'    => 18,
            'format'         => 'both',
            'is_active'      => true,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        if ($bookId && $authorId && Schema::hasTable('book_author')) {
            DB::table('book_author')->updateOrInsert(
                ['book_id' => $bookId, 'author_id' => $authorId],
                ['created_at' => $now, 'updated_at' => $now],
            );
        }

        // ── Ebook ───────────────────────────────────────────────────────
        $this->upsert('ebooks', ['slug' => 'digital-shikkha-bhabna'], [
            'category_id' => $categoryId,
            'author_id'   => $authorId,
            'title'       => 'ডিজিটাল শিক্ষাভাবনা',
            'description' => 'প্রযুক্তিনির্ভর শিক্ষাব্যবস্থায় শিক্ষক ও শিক্ষার্থীর বদলে যাওয়া ভূমিকা নিয়ে আলোচনা।',
            'price'       => 220.00,
            'file_type'   => 'pdf',
            'file_size'   => '৩.৪ MB',
            'pages'       => 168,
            'sales_count' => 7,
            'is_active'   => true,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        // ── Blog ────────────────────────────────────────────────────────
        $blogCategoryId = $this->upsert('blog_categories', ['slug' => 'porar-ghor'], [
            'name'        => 'পড়ার ঘর',
            'description' => 'বই পড়া, পাঠাভ্যাস ও পাঠপ্রতিক্রিয়া নিয়ে লেখা।',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $this->upsert('blog_posts', ['slug' => 'boi-porar-obhyash-gore-tola'], [
            'title'        => 'বই পড়ার অভ্যাস গড়ে তোলার সাতটি ধাপ',
            'excerpt'      => 'প্রতিদিন অল্প করে পড়েও বছরে অনেকগুলো বই শেষ করা সম্ভব — শুরুটা কীভাবে করবেন।',
            'content'      => "পড়ার অভ্যাস একদিনে তৈরি হয় না। ছোট লক্ষ্য দিয়ে শুরু করলে সেটি টিকে থাকে।\n\nপ্রথম ধাপ: দিনে মাত্র দশ মিনিট নির্দিষ্ট করুন। সময়টি প্রতিদিন একই রাখুন।\n\nদ্বিতীয় ধাপ: হাতের কাছে বই রাখুন। যে বই চোখে পড়ে, সেটিই পড়া হয়।\n\nতৃতীয় ধাপ: পছন্দের বিষয় দিয়ে শুরু করুন, কঠিন বই দিয়ে নয়।",
            'category_id'  => $blogCategoryId,
            'author_id'    => $this->anyUserId(),
            'status'       => 'published',
            'published_at' => $now->copy()->subDays(3),
            'view_count'   => 142,
            'is_featured'  => true,
            'created_at'   => $now->copy()->subDays(3),
            'updated_at'   => $now,
        ]);

        // ── Webzine ─────────────────────────────────────────────────────
        $this->upsert('webzines', ['slug' => 'boichitro-sonkha-01'], [
            'title'            => 'বৈচিত্র্য — প্রথম সংখ্যা',
            'description'      => 'সাহিত্য, বিজ্ঞান ও সমাজভাবনা নিয়ে আমাদের ওয়েবজিনের সূচনা সংখ্যা।',
            'issue_number'     => '০১',
            'category'         => 'সাহিত্য',
            'publication_date' => $now->copy()->subDays(10),
            'view_count'       => 96,
            'is_published'     => true,
            'published_at'     => $now->copy()->subDays(10),
            'created_at'       => $now->copy()->subDays(10),
            'updated_at'       => $now,
        ]);

        // ── Research paper ──────────────────────────────────────────────
        $this->upsert('research_papers', ['slug' => 'grameen-pathagar-o-shikkha'], [
            'title'            => 'গ্রামীণ পাঠাগার ও প্রাথমিক শিক্ষার সম্পর্ক',
            'abstract'         => 'গ্রামীণ পাঠাগারের উপস্থিতি প্রাথমিক পর্যায়ের শিক্ষার্থীদের পাঠদক্ষতায় কী প্রভাব ফেলে — তা নিয়ে একটি পর্যালোচনামূলক গবেষণা।',
            'content'          => "ভূমিকা\n\nগ্রামীণ জনপদে পাঠাগার কেবল বই রাখার জায়গা নয় — এটি শিশুদের প্রথম পাঠকেন্দ্র হিসেবেও কাজ করে।\n\nপদ্ধতি\n\nদেশের তিনটি উপজেলার বারোটি প্রাথমিক বিদ্যালয়ের শিক্ষার্থীদের পাঠদক্ষতা পর্যালোচনা করা হয়েছে।\n\nফলাফল\n\nযেসব এলাকায় সক্রিয় পাঠাগার রয়েছে, সেখানে শিক্ষার্থীদের পঠন-বোধগম্যতা তুলনামূলকভাবে ভালো পাওয়া গেছে।",
            'author_id'        => $authorId,
            'keywords'         => 'পাঠাগার, প্রাথমিক শিক্ষা, পাঠদক্ষতা',
            'category'         => 'শিক্ষা',
            'publication_date' => $now->copy()->subDays(20),
            'citations_count'  => 4,
            'view_count'       => 58,
            'download_count'   => 12,
            'is_published'     => true,
            'published_at'     => $now->copy()->subDays(20),
            'created_at'       => $now->copy()->subDays(20),
            'updated_at'       => $now,
        ]);

        // ── Tag ─────────────────────────────────────────────────────────
        $this->upsert('tags', ['slug' => 'bangla-uponnash'], [
            'name'        => 'বাংলা উপন্যাস',
            'description' => 'বাংলা ভাষায় রচিত উপন্যাস।',
            'category'    => 'বিষয়',
            'color'       => '#0066cc',
            'is_active'   => true,
            'usage_count' => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        if ($this->command) {
            $this->command->info('ডেমো কন্টেন্ট প্রস্তুত।');
        }
    }

    private function upsert(string $table, array $match, array $values): ?int
    {
        if (! Schema::hasTable($table)) {
            if ($this->command) {
                $this->command->warn("এড়িয়ে যাওয়া হলো: `{$table}` টেবিল নেই।");
            }
            return null;
        }

        $values = array_filter(
            $values,
            fn ($key) => Schema::hasColumn($table, $key),
            ARRAY_FILTER_USE_KEY
        );

        DB::table($table)->updateOrInsert($match, $values);

        return (int) DB::table($table)->where($match)->value('id');
    }

    private function anyUserId(): ?int
    {
        return User::where('role', User::ROLE_ADMIN)->value('id')
            ?? User::query()->value('id');
    }
}
