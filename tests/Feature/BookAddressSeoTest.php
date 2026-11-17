<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Book\Models\Book;
use Modules\SEO\Models\SeoMeta;
use Modules\SEO\Models\SeoRedirect;
use Tests\TestCase;

/**
 * A book's address can change (one-time numbering, or an admin edit) without costing it its search
 * ranking: custom SEO text stays, everything that points at the old address follows the book, and the
 * sitemap lists the new address.
 */
class BookAddressSeoTest extends TestCase
{
    use RefreshDatabase;

    private int $categoryId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryId = DB::table('categories')->insertGetId(['name' => 'Novel', 'slug' => 'novel-x', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function book(string $slug, string $title, ?string $titleEn = null): Book
    {
        return Book::forceCreate([
            'title' => $title, 'title_en' => $titleEn, 'slug' => $slug, 'price' => 200, 'category_id' => $this->categoryId,
            'is_active' => true, 'mod_status' => 'approved', 'sku' => 'SK-' . $slug, 'author_name' => 'Some Author',
        ]);
    }

    public function test_custom_seo_text_stays_and_points_at_the_new_address(): void
    {
        $book = $this->book('amar-boi', 'Amar Boi', 'My Book');
        $meta = SeoMeta::updateOrCreate(
            ['seoable_type' => Book::class, 'seoable_id' => $book->id],
            [
                'meta_title' => 'My hand written title', 'meta_description' => 'Hand written description',
                'canonical_url' => url('/books/amar-boi'), 'og_image' => url('/books/amar-boi/share-card.jpg'),
                'schema_json' => ['@type' => 'Book', 'url' => url('/books/amar-boi'), 'name' => 'Amar Boi'],
            ]
        );

        $this->artisan('books:number-slugs')->assertExitCode(0);

        $new = $book->fresh()->slug;
        $meta = SeoMeta::where('seoable_type', Book::class)->where('seoable_id', $book->id)->firstOrFail();
        $this->assertSame(url('/books/' . $new), $meta->canonical_url);
        $this->assertSame(url('/books/' . $new . '/share-card.jpg'), $meta->og_image);
        $this->assertSame(url('/books/' . $new), $meta->schema_json['url']);
        $this->assertSame('Amar Boi', $meta->schema_json['name'], 'unrelated schema fields are untouched');
    }

    public function test_only_whole_addresses_are_rewritten(): void
    {
        $short = $this->book('a-short', 'A Short');
        $long = $this->book('a-short-and-long', 'A Short And Long');
        SeoMeta::updateOrCreate(['seoable_type' => Book::class, 'seoable_id' => $long->id], ['canonical_url' => url('/books/a-short-and-long')]);

        $short->update(['slug' => 'renamed-short']);

        $this->assertSame(url('/books/a-short-and-long'), SeoMeta::where('seoable_id', $long->id)->where('seoable_type', Book::class)->value('canonical_url'),
            'another book whose address merely starts the same is left alone');
    }

    public function test_records_and_rules_keyed_by_the_old_path_follow_the_book(): void
    {
        $book = $this->book('amar-boi', 'Amar Boi', 'My Book');
        $byPath = SeoMeta::create(['url_path' => '/books/amar-boi', 'meta_title' => 'Typed under /admin/seo', 'canonical_url' => url('/books/amar-boi')]);
        $toOld = SeoRedirect::create(['source_url' => '/old-campaign', 'target_url' => '/books/amar-boi', 'status_code' => 301, 'is_active' => true]);
        $absolute = SeoRedirect::create(['source_url' => '/another', 'target_url' => url('/books/amar-boi') . '?utm=x', 'status_code' => 301, 'is_active' => true]);

        $this->artisan('books:number-slugs')->assertExitCode(0);
        $new = $book->fresh()->slug;

        $this->assertSame('/books/' . $new, $byPath->fresh()->url_path);
        $this->assertSame('Typed under /admin/seo', $byPath->fresh()->meta_title);
        $this->assertSame('/books/' . $new, $toOld->fresh()->target_url, 'no two-step redirect');
        $this->assertSame(url('/books/' . $new) . '?utm=x', $absolute->fresh()->target_url);

        $this->get('/old-campaign')->assertStatus(301)->assertRedirect(url('/books/' . $new));
    }

    public function test_a_redirect_rule_cannot_hijack_a_books_new_address(): void
    {
        $book = $this->book('first-one', 'First One');
        $rule = SeoRedirect::create(['source_url' => '/books/chosen-address', 'target_url' => '/somewhere', 'status_code' => 301, 'is_active' => true]);

        $book->update(['slug' => 'chosen-address']);

        $this->assertFalse((bool) $rule->fresh()->is_active);
        $this->get('/books/chosen-address')->assertOk();
    }

    public function test_the_sitemap_lists_the_new_address_and_not_the_redirecting_one(): void
    {
        $book = $this->book('amar-boi', 'Amar Boi', 'My Book');
        Cache::put('master_sitemap_xml_v2', 'STALE', 1800);

        $this->artisan('books:number-slugs')->assertExitCode(0);
        $new = $book->fresh()->slug;

        $this->assertNull(Cache::get('master_sitemap_xml_v2'), 'the cached sitemap is dropped');
        $xml = $this->get('/sitemaps/books.xml')->assertOk()->getContent();
        $this->assertStringContainsString('/books/' . $new, $xml);
        $this->assertStringNotContainsString('/books/amar-boi<', $xml);
    }

    public function test_an_old_preview_image_link_reaches_the_current_one(): void
    {
        $book = $this->book('amar-boi', 'Amar Boi', 'My Book');
        $this->artisan('books:number-slugs')->assertExitCode(0);

        $this->get('/books/amar-boi/share-card.jpg')->assertStatus(301)->assertRedirect(route('book.share-card', $book->fresh()->slug));
    }

    public function test_the_book_page_itself_announces_only_the_new_address(): void
    {
        $book = $this->book('amar-boi', 'Amar Boi', 'My Book');
        $this->artisan('books:number-slugs')->assertExitCode(0);
        $new = $book->fresh()->slug;

        $html = $this->get('/books/' . $new)->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="', $html);
        $this->assertMatchesRegularExpression('#<link rel="canonical" href="[^"]*/books/' . preg_quote($new, '#') . '"#', $html);
        $this->assertMatchesRegularExpression('#<meta property="og:url" content="[^"]*/books/' . preg_quote($new, '#') . '"#', $html);
        $this->assertStringNotContainsString('/books/amar-boi"', $html, 'the old address is nowhere in the page');
    }
}
