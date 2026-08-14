<?php

declare(strict_types=1);

namespace App\Support;

use Modules\Author\Models\Author;
use Modules\Blog\Models\BlogPost;
use Modules\Book\Models\Book;
use Modules\Ebook\Models\Ebook;
use Modules\Publisher\Models\Publisher;
use Modules\Webzine\Models\Webzine;

/**
 * Registry describing every content type the admin can create, edit, moderate
 * and delete on someone else's behalf.
 *
 * Keeping the shape of each type here — rather than in six near-identical
 * controllers — means one generic controller and one generic form view serve
 * books, ebooks, authors, publishers, webzines and blog posts alike.
 *
 * Field spec keys:
 *   label   Bangla label shown above the input
 *   type    text | textarea | editor | number | date | select | checkbox | file
 *   rules   validation rules, minus any FK existence check (added at runtime)
 *   col     Bootstrap column width out of 12
 *   lookup  table name for a select whose options come from the database
 *   options static option map for a select
 *   accept  accept attribute for a file input
 *   disk    sub-folder under storage/app/public for an uploaded file
 */
final class ContentTypes
{
    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        return [
            'books' => [
                'label'     => 'বই',
                'model'     => Book::class,
                'table'     => 'books',
                'listRoute' => 'admin.books',
                'display'   => 'title',
                'slugFrom'  => 'title',
                'icon'      => 'book',
                'fields'    => [
                    'title'          => ['label' => 'বইয়ের নাম',        'type' => 'text',             'rules' => 'required|string|max:255',                                    'col' => 8],
                    'isbn'           => ['label' => 'ISBN',              'type' => 'text',             'rules' => 'nullable|string|max:30',                                     'col' => 4],
                    'subtitle'       => ['label' => 'সাব-টাইটেল',        'type' => 'text',             'rules' => 'nullable|string|max:500',                                    'col' => 12],
                    'author_role_group' => ['label' => 'লেখক / অনুবাদক / সম্পাদক', 'type' => 'author_role_group', 'rules' => 'nullable|string|max:30', 'col' => 12],
                    'description'    => ['label' => 'বিবরণ',             'type' => 'editor',           'rules' => 'nullable|string|max:20000',                                  'col' => 12],
                    'category_id'    => ['label' => 'ক্যাটাগরি',         'type' => 'select',           'lookup' => 'categories',  'rules' => 'nullable|integer',              'col' => 4],
                    'publisher_id'   => ['label' => 'প্রকাশক',           'type' => 'select',           'lookup' => 'publishers',  'rules' => 'nullable|integer',              'col' => 4],
                    'format'         => ['label' => 'ধরন',               'type' => 'select',           'default' => 'printed',    'rules' => 'nullable|in:printed,ebook,both', 'col' => 4,
                                          'options' => ['printed' => 'ছাপা বই', 'ebook' => 'ই-বুক', 'both' => 'উভয়']],
                    'price'          => ['label' => 'দাম (৳)',           'type' => 'number',           'default' => 0, 'step' => '0.01', 'rules' => 'nullable|numeric|min:0|max:9999999',    'col' => 3],
                    'discount_price' => ['label' => 'ছাড়ের দাম (৳)',    'type' => 'number',           'step' => '0.01',          'rules' => 'nullable|numeric|min:0|max:9999999',    'col' => 3],
                    'stock_quantity' => ['label' => 'স্টক',               'type' => 'number',           'default' => 0,            'rules' => 'nullable|integer|min:0|max:1000000',    'col' => 3],
                    'preview_pages'  => ['label' => 'প্রিভিউ পৃষ্ঠা',    'type' => 'number',           'default' => 0,            'rules' => 'nullable|integer|min:0|max:10000',      'col' => 3],
                    'cover_image'    => ['label' => 'কভার ছবি',          'type' => 'file',             'accept' => 'image/*',     'disk' => 'books',                          'rules' => 'nullable|image|max:4096',                  'col' => 6],
                    'sample_pdf_path'=> ['label' => 'নমুনা PDF',         'type' => 'file',             'accept' => 'application/pdf', 'disk' => 'books/samples',              'rules' => 'nullable|file|mimetypes:application/pdf|max:20480', 'col' => 6],
                    'is_active'      => ['label' => 'সাইটে সক্রিয়',     'type' => 'checkbox',         'rules' => 'nullable|boolean',                                          'col' => 12],
                ],
            ],

            'ebooks' => [
                'label'     => 'ই-বুক',
                'model'     => Ebook::class,
                'table'     => 'ebooks',
                'listRoute' => 'admin.ebooks',
                'display'   => 'title',
                'slugFrom'  => 'title',
                'icon'      => 'tablet-screen-button',
                'fields'    => [
                    'title'          => ['label' => 'ই-বুকের নাম',       'type' => 'text',   'rules' => 'required|string|max:255',    'col' => 8],
                    'isbn'           => ['label' => 'ISBN',              'type' => 'text',   'rules' => 'nullable|string|max:30',     'col' => 4],
                    'subtitle'       => ['label' => 'সাব-টাইটেল',        'type' => 'text',   'rules' => 'nullable|string|max:500',    'col' => 12],
                    'author_role_group' => ['label' => 'লেখক / অনুবাদক / সম্পাদক', 'type' => 'author_role_group', 'rules' => 'nullable|string|max:30', 'col' => 12],
                    'description'    => ['label' => 'বিবরণ',             'type' => 'editor', 'rules' => 'nullable|string|max:20000', 'col' => 12],
                    'category_id'    => ['label' => 'ক্যাটাগরি',         'type' => 'select', 'lookup' => 'categories', 'rules' => 'nullable|integer', 'col' => 6],
                    'author_id'      => ['label' => 'লেখক (তালিকা)',      'type' => 'select', 'lookup' => 'authors',    'rules' => 'nullable|integer', 'col' => 6],
                    'price'          => ['label' => 'দাম (৳)',           'type' => 'number', 'default' => 0, 'step' => '0.01', 'rules' => 'nullable|numeric|min:0|max:9999999', 'col' => 4],
                    'discount_price' => ['label' => 'ছাড়ের দাম (৳)',    'type' => 'number', 'step' => '0.01',          'rules' => 'nullable|numeric|min:0|max:9999999', 'col' => 4],
                    'pages'          => ['label' => 'পৃষ্ঠা সংখ্যা',      'type' => 'number', 'default' => 0,            'rules' => 'nullable|integer|min:0|max:100000',  'col' => 4],
                    'cover_image'    => ['label' => 'কভার ছবি',          'type' => 'file',   'accept' => 'image/*',     'disk' => 'ebooks',       'rules' => 'nullable|image|max:4096',                                                    'col' => 6],
                    'file_path'      => ['label' => 'ই-বুক ফাইল (PDF/EPUB)', 'type' => 'file', 'accept' => '.pdf,.epub', 'disk' => 'ebooks/files', 'rules' => 'nullable|file|mimetypes:application/pdf,application/epub+zip|max:51200', 'col' => 6],
                    'is_active'      => ['label' => 'সাইটে সক্রিয়',     'type' => 'checkbox', 'rules' => 'nullable|boolean',                                                  'col' => 12],
                ],
            ],

            'authors' => [
                'label'     => 'লেখক',
                'model'     => Author::class,
                'table'     => 'authors',
                'listRoute' => 'admin.authors',
                'display'   => 'name',
                'slugFrom'  => 'name',
                'icon'      => 'pen-fancy',
                'fields'    => [
                    'name'        => ['label' => 'লেখকের নাম', 'type' => 'text', 'rules' => 'required|string|max:255', 'col' => 8],
                    'email'       => ['label' => 'ইমেইল', 'type' => 'text', 'rules' => 'nullable|email|max:255', 'col' => 4],
                    'phone'       => ['label' => 'ফোন', 'type' => 'text', 'rules' => 'nullable|string|max:30', 'col' => 4],
                    'website'     => ['label' => 'ওয়েবসাইট', 'type' => 'text', 'rules' => 'nullable|url|max:255', 'col' => 8],
                    'bio'         => ['label' => 'পরিচিতি', 'type' => 'editor', 'rules' => 'nullable|string|max:20000', 'col' => 12],
                    'avatar'      => ['label' => 'ছবি', 'type' => 'file', 'accept' => 'image/*', 'disk' => 'authors', 'rules' => 'nullable|image|max:4096', 'col' => 6],
                    'is_verified' => ['label' => 'যাচাইকৃত', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 6],
                    'is_active'   => ['label' => 'সাইটে সক্রিয়', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 12],
                ],
            ],

            'publishers' => [
                'label'     => 'প্রকাশক',
                'model'     => Publisher::class,
                'table'     => 'publishers',
                'listRoute' => 'admin.publishers',
                'display'   => 'name',
                'slugFrom'  => 'name',
                'icon'      => 'building',
                'fields'    => [
                    'name'        => ['label' => 'প্রকাশনীর নাম', 'type' => 'text', 'rules' => 'required|string|max:255', 'col' => 8],
                    'email'       => ['label' => 'ইমেইল', 'type' => 'text', 'rules' => 'nullable|email|max:255', 'col' => 4],
                    'phone'       => ['label' => 'ফোন', 'type' => 'text', 'rules' => 'nullable|string|max:30', 'col' => 4],
                    'website'     => ['label' => 'ওয়েবসাইট', 'type' => 'text', 'rules' => 'nullable|url|max:255', 'col' => 4],
                    'country'     => ['label' => 'দেশ', 'type' => 'text', 'rules' => 'nullable|string|max:100', 'col' => 4],
                    'address'     => ['label' => 'ঠিকানা', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'col' => 12],
                    'description' => ['label' => 'বিবরণ', 'type' => 'editor', 'rules' => 'nullable|string|max:20000', 'col' => 12],
                    'logo'        => ['label' => 'লোগো', 'type' => 'file', 'accept' => 'image/*', 'disk' => 'publishers', 'rules' => 'nullable|image|max:4096', 'col' => 6],
                    'is_verified' => ['label' => 'যাচাইকৃত', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 6],
                    'is_active'   => ['label' => 'সাইটে সক্রিয়', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 12],
                ],
            ],

            'webzines' => [
                'label'     => 'ওয়েবজিন',
                'model'     => Webzine::class,
                'table'     => 'webzines',
                'listRoute' => 'admin.webzines',
                'display'   => 'title',
                'slugFrom'  => 'title',
                'icon'      => 'newspaper',
                'fields'    => [
                    'title'            => ['label' => 'শিরোনাম', 'type' => 'text', 'rules' => 'required|string|max:255', 'unique' => true, 'col' => 8],
                    'issue_number'     => ['label' => 'সংখ্যা', 'type' => 'text', 'rules' => 'required|string|max:50', 'col' => 4],
                    'publisher_id'     => ['label' => 'প্রকাশক', 'type' => 'select', 'lookup' => 'publishers', 'rules' => 'nullable|integer', 'col' => 4],
                    'category'         => ['label' => 'বিভাগ', 'type' => 'text', 'rules' => 'nullable|string|max:100', 'col' => 4],
                    'publication_date' => ['label' => 'প্রকাশের তারিখ', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 4],
                    'description'      => ['label' => 'বিবরণ', 'type' => 'editor', 'rules' => 'nullable|string|max:20000', 'col' => 12],
                    'cover_image'      => ['label' => 'কভার ছবি', 'type' => 'file', 'accept' => 'image/*', 'disk' => 'webzines', 'rules' => 'nullable|image|max:4096', 'col' => 6],
                    'epub_file_path'   => ['label' => 'EPUB/PDF ফাইল', 'type' => 'file', 'accept' => '.epub,.pdf', 'disk' => 'webzines/files', 'rules' => 'nullable|file|mimetypes:application/epub+zip,application/pdf|max:51200', 'col' => 6],
                    'is_published'     => ['label' => 'প্রকাশিত', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 12],
                ],
            ],

            'blog' => [
                'label'     => 'ব্লগ পোস্ট',
                'model'     => BlogPost::class,
                'table'     => 'blog_posts',
                'listRoute' => 'admin.blog',
                'display'   => 'title',
                'slugFrom'  => 'title',
                'icon'      => 'blog',
                'fields'    => [
                    'title'          => ['label' => 'শিরোনাম', 'type' => 'text', 'rules' => 'required|string|max:255', 'unique' => true, 'col' => 8],
                    'category_id'    => ['label' => 'ক্যাটাগরি', 'type' => 'select', 'lookup' => 'blog_categories', 'rules' => 'nullable|integer', 'col' => 4],
                    'excerpt'        => ['label' => 'সংক্ষিপ্তসার', 'type' => 'textarea', 'rules' => 'nullable|string|max:1000', 'col' => 12],
                    'content'        => ['label' => 'মূল লেখা', 'type' => 'editor', 'rules' => 'required|string|max:100000', 'col' => 12],
                    'status'         => ['label' => 'অবস্থা', 'type' => 'select', 'rules' => 'nullable|in:draft,pending,published,archived', 'col' => 4,
                                          'options' => ['draft' => 'খসড়া', 'pending' => 'অপেক্ষমাণ', 'published' => 'প্রকাশিত', 'archived' => 'সংরক্ষিত']],
                    'featured_image' => ['label' => 'ফিচার্ড ছবি', 'type' => 'file', 'accept' => 'image/*', 'disk' => 'blog', 'rules' => 'nullable|image|max:4096', 'col' => 8],
                    'is_featured'    => ['label' => 'ফিচার্ড পোস্ট', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 12],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function get(string $key): array
    {
        $type = self::all()[$key] ?? null;

        abort_if($type === null, 404);

        return $type + ['key' => $key];
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, self::all());
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /** Listing route name => content type key, used to add buttons to list pages. */
    public static function keyForListRoute(string $routeName): ?string
    {
        foreach (self::all() as $key => $type) {
            if ($type['listRoute'] === $routeName) {
                return $key;
            }
        }

        return null;
    }
}
