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
                'label'     => 'Book',
                'model'     => Book::class,
                'table'     => 'books',
                'listRoute' => 'admin.books',
                'display'   => 'title',
                'slugFrom'  => 'title',
                'icon'      => 'book',
                'fields'    => [
                    // --- 1. Basic Product Info & Contributors ---
                    'product_type'            => ['label' => 'Product Type',      'type' => 'select',           'default' => 'book',       'rules' => 'nullable|string|max:50',         'col' => 4,
                                                  'options' => ['book' => 'Book (বই)', 'stationery' => 'Stationery (স্টেশনারি)', 'islamic_gift' => 'Islamic Gift / Art (ইসলামিক গিফট)', 'other' => 'Other Item (অন্যান্য)']],
                    'title'                   => ['label' => 'Title / Book Name', 'type' => 'text',             'rules' => 'required|string|max:255',                                    'col' => 8],
                    'subtitle'                => ['label' => 'Subtitle / Tagline', 'type' => 'text',            'rules' => 'nullable|string|max:500',                                    'col' => 8],
                    'sku'                     => ['label' => 'Product SKU / Code','type' => 'text',             'rules' => 'nullable|string|max:50',                                     'col' => 4],
                    'isbn'                    => ['label' => 'ISBN / Barcode',    'type' => 'text',             'rules' => 'nullable|string|max:30',                                     'col' => 4],
                    'author_role_group'       => ['label' => 'Authors / Contributors', 'type' => 'author_role_group', 'lookup' => 'authors', 'rules' => 'nullable|string|max:30', 'col' => 12],
                    'translator_name'         => ['label' => 'Translator (if any)', 'type' => 'text',          'placeholder' => 'e.g. Translator Name', 'rules' => 'nullable|string|max:255', 'col' => 4],
                    'editor_name'             => ['label' => 'Editor / Compiler (if any)', 'type' => 'text',    'placeholder' => 'e.g. Editor Name', 'rules' => 'nullable|string|max:255', 'col' => 4],
                    'rewriter_name'           => ['label' => 'Rewriter / Adapter (if any)', 'type' => 'text',  'placeholder' => 'e.g. Rewriter Name', 'rules' => 'nullable|string|max:255', 'col' => 4],
                    'cover_artist'            => ['label' => 'Cover Artist',      'type' => 'text',             'placeholder' => 'e.g. Artist Name', 'rules' => 'nullable|string|max:255', 'col' => 4],
                    'category_id'             => ['label' => 'Category',          'type' => 'select',           'lookup' => 'categories',  'rules' => 'nullable|integer',              'col' => 4],
                    'publisher_id'            => ['label' => 'Publisher',         'type' => 'select',           'lookup' => 'publishers',  'rules' => 'nullable|integer',              'col' => 4],
                    'language'                => ['label' => 'Language',          'type' => 'select',           'default' => 'Bengali',    'rules' => 'nullable|string|max:50',         'col' => 4,
                                                  'options' => ['Bengali' => 'Bengali (বাংলা)', 'English' => 'English (ইংরেজি)', 'Arabic' => 'Arabic (আরবি)', 'Urdu' => 'Urdu (উর্দু)', 'Hindi' => 'Hindi (হিন্দি)', 'Persian' => 'Persian (ফারসি)', 'Other' => 'Other (অন্যান্য)']],
                    'country'                 => ['label' => 'Country',           'type' => 'select',           'default' => 'Bangladesh', 'rules' => 'nullable|string|max:100',        'col' => 4,
                                                  'options' => ['Bangladesh' => 'Bangladesh (বাংলাদেশ)', 'India' => 'India (ভারত)', 'Saudi Arabia' => 'Saudi Arabia (সৌদি আরব)', 'Egypt' => 'Egypt (মিশর)', 'United Kingdom' => 'United Kingdom (যুক্তরাজ্য)', 'United States' => 'United States (যুক্তরাষ্ট্র)', 'Other' => 'Other (অন্যান্য)']],

                    // --- 2. Binding, Pricing & Discounts ---
                    'cover_type'              => ['label' => 'Binding Format',    'type' => 'select',           'default' => 'paperback',  'rules' => 'nullable|in:paperback,hardcover,board_book,spiral,both', 'col' => 12,
                                                  'options' => ['paperback' => 'Paperback (পেপারব্যাক)', 'hardcover' => 'Hardcover (হার্ডকভার)', 'board_book' => 'Board Book (বোর্ড বুক)', 'spiral' => 'Spiral Bound (স্পাইরাল বাঁধাই)', 'both' => 'Both (Hardcover & Paperback)']],
                    'hardcover_price'         => ['label' => 'Hardcover Regular Price (৳)',   'type' => 'number', 'step' => '0.01',          'rules' => 'nullable|numeric|min:0|max:9999999', 'col' => 4],
                    'hardcover_discount_price'=> ['label' => 'Hardcover Sale Price (৳)', 'type' => 'number', 'step' => '0.01',       'rules' => 'nullable|numeric|min:0|max:9999999', 'col' => 4],
                    'price'                   => ['label' => 'Paperback Regular Price (৳)', 'type' => 'number', 'default' => 0, 'step' => '0.01', 'rules' => 'nullable|numeric|min:0|max:9999999', 'col' => 4],
                    'discount_price'          => ['label' => 'Paperback Sale Price (৳)', 'type' => 'number', 'step' => '0.01',       'rules' => 'nullable|numeric|min:0|max:9999999', 'col' => 4],
                    'cost_price'              => ['label' => 'Cost / Purchase Price (৳)', 'type' => 'number', 'step' => '0.01',          'rules' => 'nullable|numeric|min:0|max:9999999', 'col' => 4],

                    // --- 3. Publishing & Stock Status ---
                    'published_at'            => ['label' => 'Publication Date / Year', 'type' => 'date',            'rules' => 'nullable|date',                                              'col' => 4],
                    'edition'                 => ['label' => 'Edition / Print',    'type' => 'text',             'placeholder' => 'e.g. 1st Edition 2026 / Revised', 'rules' => 'nullable|string|max:100', 'col' => 4],
                    'stock_status'            => ['label' => 'Order & Stock Type','type' => 'select',           'default' => 'in_stock',   'rules' => 'nullable|in:in_stock,pre_order,out_of_stock,upcoming,backorder', 'col' => 4,
                                                  'options' => ['in_stock' => 'Buy Now / In Stock (সরাসরি ক্রয়)', 'pre_order' => 'Pre-Order (প্রি-অর্ডার)', 'out_of_stock' => 'Out of Stock (স্টক শেষ)', 'upcoming' => 'Upcoming (শীঘ্রই আসছে)', 'backorder' => 'Backorder']],
                    'pre_order_release_date'  => ['label' => 'Pre-Order Estimated Delivery Date', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 6],
                    'pre_order_note'          => ['label' => 'Pre-Order Special Note / Gift Offer', 'type' => 'textarea', 'placeholder' => 'e.g. Includes author signature and exclusive bookmark...', 'rules' => 'nullable|string|max:1000', 'col' => 6],
                    'stock_quantity'          => ['label' => 'Stock Quantity (Units)', 'type' => 'number',         'default' => 10,           'rules' => 'nullable|integer|min:0|max:1000000',    'col' => 4],

                    // --- 4. Physical Specifications ---
                    'book_size'               => ['label' => 'Book Size / Dimensions',   'type' => 'text',             'placeholder' => 'e.g. Demy 1/8, Royal, 5.5" × 8.5", A5', 'rules' => 'nullable|string|max:100', 'col' => 4],
                    'page_count'              => ['label' => 'Total Page Count',  'type' => 'number',           'default' => 0,            'rules' => 'nullable|integer|min:0|max:50000',      'col' => 4],
                    'paper_type'              => ['label' => 'Paper Type / Quality',  'type' => 'text',             'placeholder' => 'e.g. 80 GSM Cream Paper / Offset', 'rules' => 'nullable|string|max:100', 'col' => 4],
                    'weight'                  => ['label' => 'Weight (Grams)', 'type' => 'number',           'placeholder' => 'e.g. 350', 'rules' => 'nullable|integer|min:0|max:50000',     'col' => 4],
                    'preview_pages'           => ['label' => 'Online Preview Pages', 'type' => 'number',        'default' => 0,            'rules' => 'nullable|integer|min:0|max:10000',      'col' => 4],

                    // --- 5. Media & Sample Files ---
                    'cover_image'             => ['label' => 'Cover Image (2:3 Standard)', 'type' => 'file',   'accept' => 'image/*',     'disk' => 'books',                          'rules' => 'nullable|image|max:4096',                  'col' => 4],
                    'author_image'            => ['label' => 'Author Photo (1:1 Square)', 'type' => 'file',   'accept' => 'image/*',     'disk' => 'authors',                        'rules' => 'nullable|image|max:4096',                  'col' => 4],
                    'sample_pdf_path'         => ['label' => 'Sample PDF Preview', 'type' => 'file',       'accept' => 'application/pdf', 'disk' => 'books/samples',              'rules' => 'nullable|file|mimetypes:application/pdf|max:20480', 'col' => 4],

                    // --- 6. Summary & Status ---
                    'summary'                 => ['label' => 'Product Summary (বইয়ের সংক্ষেপ)', 'type' => 'textarea', 'rules' => 'nullable|string|max:8000', 'col' => 12],
                    'is_active'               => ['label' => 'Active & Visible in Shop', 'type' => 'checkbox',       'rules' => 'nullable|boolean',                                          'col' => 12],
                ],
            ],

            'ebooks' => [
                'label'     => 'E-Book',
                'model'     => Ebook::class,
                'table'     => 'ebooks',
                'listRoute' => 'admin.ebooks',
                'display'   => 'title',
                'slugFrom'  => 'title',
                'icon'      => 'tablet-screen-button',
                'fields'    => [
                    'title'          => ['label' => 'E-Book Title',       'type' => 'text',   'rules' => 'required|string|max:255',    'col' => 8],
                    'isbn'           => ['label' => 'ISBN',              'type' => 'text',   'rules' => 'nullable|string|max:30',     'col' => 4],
                    'subtitle'       => ['label' => 'Subtitle',          'type' => 'text',   'rules' => 'nullable|string|max:500',    'col' => 12],
                    'author_role_group' => ['label' => 'Authors & Contributors', 'type' => 'author_role_group', 'rules' => 'nullable|string|max:30', 'col' => 12],
                    'description'    => ['label' => 'Description',       'type' => 'editor', 'rules' => 'nullable|string|max:20000', 'col' => 12],
                    'category_id'    => ['label' => 'Category',          'type' => 'select', 'lookup' => 'categories', 'rules' => 'nullable|integer', 'col' => 4],
                    'author_id'      => ['label' => 'Primary Author',    'type' => 'select', 'lookup' => 'authors',    'rules' => 'nullable|integer', 'col' => 4],
                    'publisher_id'   => ['label' => 'Publisher',         'type' => 'select', 'lookup' => 'publishers', 'rules' => 'nullable|integer', 'col' => 4],
                    'price'          => ['label' => 'Price (৳) [0 for Free]', 'type' => 'number', 'default' => 0, 'step' => '0.01', 'rules' => 'nullable|numeric|min:0|max:9999999', 'col' => 4],
                    'discount_price' => ['label' => 'Discounted Price (৳)',    'type' => 'number', 'step' => '0.01',          'rules' => 'nullable|numeric|min:0|max:9999999', 'col' => 4],
                    'pages'          => ['label' => 'Page Count',        'type' => 'number', 'default' => 0,            'rules' => 'nullable|integer|min:0|max:100000',  'col' => 4],
                    'cover_image'    => ['label' => 'Cover Image',       'type' => 'file',   'accept' => 'image/*',     'disk' => 'ebooks',       'rules' => 'nullable|image|max:8192', 'col' => 6],
                    'file_path'      => ['label' => 'Main E-Book File (PDF/EPUB)', 'type' => 'file', 'accept' => '.pdf,.epub', 'disk' => 'ebooks/files', 'rules' => 'nullable|file|max:102400', 'col' => 6],
                    'epub_file_path' => ['label' => 'Dedicated EPUB File (Optional)', 'type' => 'file', 'accept' => '.epub', 'disk' => 'ebooks/files', 'rules' => 'nullable|file|max:102400', 'col' => 6],
                    'sample_file_path'=> ['label' => 'Free Sample Preview File', 'type' => 'file', 'accept' => '.pdf,.epub', 'disk' => 'ebooks/files', 'rules' => 'nullable|file|max:51200', 'col' => 6],
                    'is_active'      => ['label' => 'Active in Store',   'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 12],
                ],
            ],

            'authors' => [
                'label'     => 'Author',
                'model'     => Author::class,
                'table'     => 'authors',
                'listRoute' => 'admin.authors',
                'display'   => 'name',
                'slugFrom'  => 'name',
                'icon'      => 'pen-fancy',
                'fields'    => [
                    'name'        => ['label' => 'Author Name', 'type' => 'text', 'rules' => 'required|string|max:255', 'col' => 8],
                    'email'       => ['label' => 'Email', 'type' => 'text', 'rules' => 'nullable|email|max:255', 'col' => 4],
                    'phone'       => ['label' => 'Phone', 'type' => 'text', 'rules' => 'nullable|string|max:30', 'col' => 4],
                    'website'     => ['label' => 'Website', 'type' => 'text', 'rules' => 'nullable|url|max:255', 'col' => 8],
                    'bio'         => ['label' => 'Biography', 'type' => 'editor', 'rules' => 'nullable|string|max:20000', 'col' => 12],
                    'avatar'      => ['label' => 'Photo', 'type' => 'file', 'accept' => 'image/*', 'disk' => 'authors', 'rules' => 'nullable|image|max:4096', 'col' => 6],
                    'is_verified' => ['label' => 'Verified Badge', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 6],
                    'is_active'   => ['label' => 'Active Status', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 12],
                ],
            ],

            'publishers' => [
                'label'     => 'Publisher',
                'model'     => Publisher::class,
                'table'     => 'publishers',
                'listRoute' => 'admin.publishers',
                'display'   => 'name',
                'slugFrom'  => 'name',
                'icon'      => 'building',
                'fields'    => [
                    'name'        => ['label' => 'Publisher / Company Name', 'type' => 'text', 'rules' => 'required|string|max:255', 'col' => 8],
                    'email'       => ['label' => 'Email Address', 'type' => 'text', 'rules' => 'nullable|email|max:255', 'col' => 4],
                    'phone'       => ['label' => 'Phone Number', 'type' => 'text', 'rules' => 'nullable|string|max:30', 'col' => 4],
                    'website'     => ['label' => 'Website', 'type' => 'text', 'rules' => 'nullable|url|max:255', 'col' => 4],
                    'country'     => ['label' => 'Country', 'type' => 'text', 'rules' => 'nullable|string|max:100', 'col' => 4],
                    'address'     => ['label' => 'Address', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'col' => 12],
                    'description' => ['label' => 'Company Description', 'type' => 'editor', 'rules' => 'nullable|string|max:20000', 'col' => 12],
                    'logo'        => ['label' => 'Publisher Logo', 'type' => 'file', 'accept' => 'image/*', 'disk' => 'publishers', 'rules' => 'nullable|image|max:4096', 'col' => 6],
                    'is_verified' => ['label' => 'Verified Partner', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 6],
                    'is_active'   => ['label' => 'Active Status', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 12],
                ],
            ],

            'webzines' => [
                'label'     => 'Webzine',
                'model'     => Webzine::class,
                'table'     => 'webzines',
                'listRoute' => 'admin.webzines',
                'display'   => 'title',
                'slugFrom'  => 'title',
                'icon'      => 'newspaper',
                'fields'    => [
                    'title'            => ['label' => 'Webzine Title', 'type' => 'text', 'rules' => 'required|string|max:255', 'unique' => true, 'col' => 8],
                    'issue_number'     => ['label' => 'Issue Number', 'type' => 'text', 'rules' => 'required|string|max:50', 'col' => 4],
                    'publisher_id'     => ['label' => 'Publisher', 'type' => 'select', 'lookup' => 'publishers', 'rules' => 'nullable|integer', 'col' => 4],
                    'category'         => ['label' => 'Category', 'type' => 'text', 'rules' => 'nullable|string|max:100', 'col' => 4],
                    'publication_date' => ['label' => 'Publication Date', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 4],
                    'description'      => ['label' => 'Description', 'type' => 'editor', 'rules' => 'nullable|string|max:20000', 'col' => 12],
                    'cover_image'      => ['label' => 'Cover Image', 'type' => 'file', 'accept' => 'image/*', 'disk' => 'webzines', 'rules' => 'nullable|image|max:4096', 'col' => 6],
                    'epub_file_path'   => ['label' => 'EPUB/PDF File', 'type' => 'file', 'accept' => '.epub,.pdf', 'disk' => 'webzines/files', 'rules' => 'nullable|file|mimetypes:application/epub+zip,application/pdf|max:51200', 'col' => 6],
                    'is_published'     => ['label' => 'Published', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 12],
                ],
            ],

            'blog' => [
                'label'     => 'Blog Post',
                'model'     => BlogPost::class,
                'table'     => 'blog_posts',
                'listRoute' => 'admin.blog',
                'display'   => 'title',
                'slugFrom'  => 'title',
                'icon'      => 'blog',
                'fields'    => [
                    'title'          => ['label' => 'Main Headline', 'type' => 'text', 'rules' => 'required|string|max:255', 'col' => 12],
                    'author_id'      => ['label' => 'Author', 'type' => 'select', 'rules' => 'nullable|string|max:100', 'col' => 6],
                    'category_id'    => ['label' => 'Category', 'type' => 'select', 'lookup' => 'blog_categories', 'rules' => 'nullable|integer', 'col' => 6],
                    'subtitle'       => ['label' => 'Subtitle / Tagline', 'type' => 'text', 'rules' => 'nullable|string|max:500', 'col' => 12],
                    'excerpt'        => ['label' => 'Short Summary / Excerpt', 'type' => 'textarea', 'rules' => 'nullable|string|max:1000', 'col' => 12],
                    'content'        => ['label' => 'Post Content (Article / Story / Poetry)', 'type' => 'editor', 'rules' => 'required|string|max:100000', 'col' => 12],
                    'featured_image' => ['label' => 'Featured Cover Image', 'type' => 'file', 'accept' => 'image/*', 'disk' => 'blog', 'rules' => 'nullable|image|max:8192', 'col' => 6],
                    'published_at'   => ['label' => 'Publish Date', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 3],
                    'status'         => ['label' => 'Status', 'type' => 'select', 'rules' => 'nullable|in:draft,pending,published,archived', 'col' => 3,
                                          'options' => ['published' => 'Published', 'draft' => 'Draft', 'pending' => 'Pending Review', 'archived' => 'Archived']],
                    'is_featured'    => ['label' => 'Featured Post (Pin to Homepage)', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 12],
                ],
            ],

            'categories' => [
                'label'     => 'Book Category',
                'model'     => \Modules\Book\Models\Category::class,
                'table'     => 'categories',
                'listRoute' => 'admin.categories',
                'display'   => 'name',
                'slugFrom'  => 'name',
                'icon'      => 'folder-tree',
                'fields'    => [
                    'name'        => ['label' => 'Category Name', 'type' => 'text', 'rules' => 'required|string|max:255', 'unique' => true, 'col' => 6],
                    'parent_id'   => ['label' => 'Parent Category', 'type' => 'select', 'lookup' => 'parent_categories', 'rules' => 'nullable|integer', 'col' => 6],
                    'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000', 'col' => 12],
                    'sort_order'  => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => 'nullable|integer|min:0', 'col' => 6],
                    'is_active'   => ['label' => 'Active Status', 'type' => 'checkbox', 'rules' => 'nullable|boolean', 'col' => 6],
                ],
            ],

            'blog_categories' => [
                'label'     => 'Blog Category',
                'model'     => \Modules\Blog\Models\BlogCategory::class,
                'table'     => 'blog_categories',
                'listRoute' => 'admin.blog-categories',
                'display'   => 'name',
                'slugFrom'  => 'name',
                'icon'      => 'shapes',
                'fields'    => [
                    'name'        => ['label' => 'Category Name', 'type' => 'text', 'rules' => 'required|string|max:255', 'unique' => true, 'col' => 6],
                    'icon'        => ['label' => 'FontAwesome Icon Class', 'type' => 'text', 'rules' => 'nullable|string|max:100', 'col' => 6],
                    'image'       => ['label' => 'Category Thumbnail', 'type' => 'file', 'accept' => 'image/*', 'disk' => 'blog/categories', 'rules' => 'nullable|image|max:3072', 'col' => 12],
                    'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000', 'col' => 12],
                    'is_active'   => ['label' => 'Active Status', 'type' => 'checkbox', 'default' => true, 'rules' => 'nullable|boolean', 'col' => 12],
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
