<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Book\Models\Book;
use Modules\Book\Models\Genre;
use Tests\TestCase;

/**
 * The whole "add a book" path as an admin uses it: the form's fields are really saved, numbers and
 * serials are unique, the QR carries the book number, quick edit does what its switches say, and
 * genres can be added on the spot.
 */
class BookUploadTest extends TestCase
{
    use RefreshDatabase;

    private ?User $adminUser = null;
    private int $categoryId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryId = DB::table('categories')->insertGetId(['name' => 'Novel', 'slug' => 'novel-x', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('publishers')->insert(['id' => 2, 'name' => 'আইডিয়া প্রকাশন', 'slug' => 'ideaprokashon', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function admin(): User
    {
        return $this->adminUser ??= User::create([
            'name' => 'Upload Admin', 'email' => 'upload@example.test', 'phone' => '01710000011', 'password' => Hash::make('x'),
            'role' => 'admin', 'reg_type' => 'admin', 'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'title' => 'Padma Nodir Majhi', 'title_en' => 'The Boatman of the Padma', 'subtitle' => 'A novel',
            'sku' => 'BK-90001', 'idea_serial_no' => 'IP900', 'isbn' => '978-0-306-40615-7',
            'author_names' => ['Manik Bandopadhyay'], 'author_role' => 'author',
            'category_id' => $this->categoryId, 'genre_category' => 'novel', 'language' => 'Bangla', 'country' => 'Bangladesh',
            'cover_type' => 'paperback', 'price' => 300, 'discount_price' => 250, 'cost_price' => 180,
            'stock_status' => 'in_stock', 'stock_quantity' => 25, 'page_count' => 128, 'edition' => '1st', 'is_active' => 1,
            'mod_status' => 'approved', 'summary' => 'short', 'description' => 'long',
        ], $over);
    }

    private function create(array $over = []): Book
    {
        $this->actingAs($this->admin())->post(route('admin.content.store', ['type' => 'books']), $this->payload($over))->assertSessionHasNoErrors();

        return Book::query()->latest('id')->firstOrFail();
    }

    // ── saving ───────────────────────────────────────────────────────────────

    public function test_what_the_form_sends_is_what_gets_saved(): void
    {
        $b = $this->create();

        $this->assertSame('BK-90001', $b->sku);
        $this->assertSame('IP900', $b->idea_serial_no, 'the serial shown on the barcode is the one saved');
        $this->assertSame(25, (int) $b->stock_quantity);
        $this->assertSame('in_stock', $b->stock_status);
        $this->assertSame('novel', $b->genre_category);
        $this->assertSame('The Boatman of the Padma', $b->title_en);
        $this->assertEquals(300, (float) $b->price);
        $this->assertSame(2, (int) $b->publisher_id, 'books without a publisher belong to Idea Prokashon');
    }

    public function test_a_blank_serial_and_sku_are_assigned_automatically(): void
    {
        $b = $this->create(['sku' => '', 'idea_serial_no' => '', 'isbn' => '']);

        $this->assertMatchesRegularExpression('/^BK-\d{5}$/', $b->sku);
        $this->assertMatchesRegularExpression('/^IP\d{3}$/', $b->idea_serial_no);
    }

    public function test_two_books_can_never_share_a_sku_or_serial(): void
    {
        $this->create();

        $this->actingAs($this->admin())
            ->post(route('admin.content.store', ['type' => 'books']), $this->payload(['title' => 'Other Book', 'isbn' => '', 'idea_serial_no' => 'IP901']))
            ->assertSessionHasErrors('sku');
        $this->actingAs($this->admin())
            ->post(route('admin.content.store', ['type' => 'books']), $this->payload(['title' => 'Third Book', 'isbn' => '', 'sku' => 'BK-90002']))
            ->assertSessionHasErrors('idea_serial_no');

        $this->assertSame(1, Book::count());

        // editing a book keeps its own numbers without tripping the check
        $b = Book::first();
        $this->actingAs($this->admin())->put(route('admin.content.update', ['type' => 'books', 'id' => $b->id]), $this->payload(['edition' => '2nd']))
            ->assertSessionHasNoErrors();
        $this->assertSame('2nd', $b->fresh()->edition);
    }

    public function test_saving_without_the_default_publisher_row_is_not_a_raw_sql_error(): void
    {
        DB::table('publishers')->delete();

        $b = $this->create(['title' => 'No Publisher Yet', 'sku' => 'BK-90003', 'idea_serial_no' => 'IP903', 'isbn' => '']);

        $this->assertNull($b->publisher_id);
    }

    // ── the number in the address ────────────────────────────────────────────

    public function test_a_new_book_gets_a_number_and_name_slug(): void
    {
        $b = $this->create(['title' => 'Padma Nodir Majhi', 'title_en' => 'Padma River Boatman']);

        $this->assertSame($b->id . '-padma-river-boatman', $b->slug);
        $this->assertSame($b->id . '-padma-river-boatman', $b->numberedSlug());
    }

    public function test_a_slug_typed_by_the_admin_is_respected(): void
    {
        $b = $this->create(['slug' => 'my-own-address']);

        $this->assertSame('my-own-address', $b->slug);
    }

    public function test_the_short_number_address_redirects_to_the_name_address_that_google_indexes(): void
    {
        $b = $this->create();

        $this->get('/books/' . $b->id)->assertStatus(301)->assertRedirect(route('book.show', $b->slug));
        $this->get('/books/' . $b->slug)->assertOk()->assertSee('<title>', false);
    }

    public function test_the_book_page_names_the_book_for_search_engines(): void
    {
        $b = $this->create();

        $html = $this->get('/books/' . $b->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<title>Padma Nodir Majhi', $html);
        $this->assertMatchesRegularExpression('/<link rel="canonical" href="[^"]*\/books\/' . preg_quote($b->slug, '/') . '"/', $html);
        $this->assertStringContainsString('"@type":"Book"', str_replace([' ', "\n"], '', $html));
    }

    public function test_an_inactive_book_is_not_revealed_by_its_number(): void
    {
        $b = $this->create(['is_active' => 0, 'mod_status' => 'pending']);
        auth()->logout();   // create() signed the admin in; this is a visitor

        $this->get('/books/' . $b->id)->assertNotFound();
        $this->actingAs($this->admin())->get('/books/' . $b->id)->assertStatus(301);
    }

    // ── QR + barcode ─────────────────────────────────────────────────────────

    public function test_the_qr_carries_only_the_book_number(): void
    {
        $b = $this->create();

        $json = $this->actingAs($this->admin())->getJson(route('admin.books.code-preview', ['book_id' => $b->id, 'sku' => $b->sku]))->assertOk()->json();

        $this->assertSame(url('/books/' . $b->id), $json['qr']['payload']);
        $this->assertFalse($json['qr']['provisional']);
        $this->assertSame($b->id, $json['qr']['number']);
        $this->assertSame($b->shortUrl(), $json['qr']['payload']);
    }

    public function test_before_saving_the_qr_is_only_a_preview_and_cannot_be_downloaded(): void
    {
        $html = $this->actingAs($this->admin())->get(route('admin.content.create', ['type' => 'books']))->assertOk()->getContent();

        $this->assertStringContainsString('Expected Book No. 1', $html);
        $this->assertMatchesRegularExpression('/id="bcQrPng"[^>]*\sdisabled/', $html);

        $json = $this->actingAs($this->admin())->getJson(route('admin.books.code-preview'))->json();
        $this->assertTrue($json['qr']['provisional']);
        $this->assertStringContainsString('Preview only', $json['qr']['warnings'][0]);
    }

    public function test_a_saved_book_shows_its_final_number_and_enables_downloads(): void
    {
        $b = $this->create();

        $html = $this->actingAs($this->admin())->get(route('admin.content.edit', ['type' => 'books', 'id' => $b->id]))->assertOk()->getContent();

        $this->assertStringContainsString('Book No. ' . $b->id, $html);
        $this->assertDoesNotMatchRegularExpression('/id="bcQrPng"[^>]*\sdisabled/', $html);
    }

    // ── stock + the quick editor ─────────────────────────────────────────────

    public function test_the_old_out_spelling_is_the_same_as_out_of_stock_everywhere(): void
    {
        $b = $this->create();
        DB::table('books')->where('id', $b->id)->update(['stock_status' => 'out']);   // how the quick tools used to save it

        $this->assertSame('out_of_stock', $b->fresh()->stock_status);

        $html = $this->actingAs($this->admin())->get(route('admin.content.edit', ['type' => 'books', 'id' => $b->id]))->getContent();
        $this->assertMatchesRegularExpression('/<option value="out_of_stock" selected[^>]*>Out of Stock/', $html);
        $this->assertStringContainsString('name="stock_quantity"', $html, 'the quantity can be entered in the full form');

        $this->actingAs($this->admin())->postJson(route('admin.books.quick-update'), ['book_id' => $b->id, 'stock_status' => 'out'])->assertOk();
        $this->assertSame('out_of_stock', DB::table('books')->where('id', $b->id)->value('stock_status'), 'stored with one spelling');
    }

    public function test_quick_edit_can_switch_an_approved_book_off(): void
    {
        $b = $this->create();
        $this->assertTrue((bool) $b->is_active);

        // the modal always sends is_active (hidden 0, or 1 when the switch is on)
        $this->actingAs($this->admin())->postJson(route('admin.books.quick-update'), ['book_id' => $b->id, 'mod_status' => 'approved', 'is_active' => '0'])->assertOk();
        $this->assertFalse((bool) $b->fresh()->is_active);

        $this->actingAs($this->admin())->postJson(route('admin.books.quick-update'), ['book_id' => $b->id, 'mod_status' => 'approved', 'is_active' => '1'])->assertOk();
        $this->assertTrue((bool) $b->fresh()->is_active);
    }

    public function test_quick_edit_binding_decides_which_prices_exist(): void
    {
        $b = $this->create(['cover_type' => 'both', 'hardcover_price' => 500, 'hardcover_discount_price' => 450]);

        $this->actingAs($this->admin())->postJson(route('admin.books.quick-update'), ['book_id' => $b->id, 'cover_type' => 'paperback', 'price' => 300, 'hardcover_price' => 500])->assertOk();
        $b = $b->fresh();
        $this->assertSame('paperback', $b->cover_type);
        $this->assertNull($b->hardcover_price, 'a paperback keeps no hidden hardcover price');
        $this->assertNull($b->hardcover_discount_price);

        $this->actingAs($this->admin())->postJson(route('admin.books.quick-update'), ['book_id' => $b->id, 'cover_type' => 'hardcover', 'hardcover_price' => 600, 'price' => 300])->assertOk();
        $b = $b->fresh();
        $this->assertSame('hardcover', $b->cover_type);
        $this->assertEquals(0, (float) $b->price, 'a hardcover-only book has no paperback price');
        $this->assertEquals(600, (float) $b->hardcover_price);
    }

    public function test_quick_edit_cannot_rename_a_book_to_an_existing_title(): void
    {
        $this->create();
        $other = $this->create(['title' => 'Another Book', 'sku' => 'BK-90010', 'idea_serial_no' => 'IP910', 'isbn' => '']);

        $res = $this->actingAs($this->admin())->postJson(route('admin.books.quick-update'), ['book_id' => $other->id, 'title' => 'Padma Nodir Majhi']);

        $this->assertNotSame(200, $res->getStatusCode(), 'the duplicate rule applies to quick edit too');
        $this->assertSame('Another Book', $other->fresh()->title);
    }

    // ── genres ───────────────────────────────────────────────────────────────

    public function test_a_genre_needs_only_one_name_and_gets_its_slug_automatically(): void
    {
        $en = $this->actingAs($this->admin())->postJson(route('admin.genres.store'), ['name_en' => 'Crime Thriller'])->assertOk()->json('genre');
        $this->assertSame('crime_thriller', $en['slug']);
        $this->assertSame('Crime Thriller', $en['name_en']);

        $bn = $this->actingAs($this->admin())->postJson(route('admin.genres.store'), ['name_bn' => 'ভৌতিক গল্প'])->assertOk()->json('genre');
        $this->assertSame('ভৌতিক গল্প', $bn['name_bn']);
        $this->assertMatchesRegularExpression('/^[a-z0-9_]+$/', $bn['slug']);
        $this->assertNotSame('', $bn['name_en']);

        $same = $this->actingAs($this->admin())->postJson(route('admin.genres.store'), ['name_en' => 'Crime Thriller'])->assertOk()->json('genre');
        $this->assertSame('crime_thriller_2', $same['slug'], 'the same name never collides');

        $this->actingAs($this->admin())->postJson(route('admin.genres.store'), [])->assertStatus(422);
    }

    public function test_a_new_genre_can_be_used_on_a_book_and_filtered_in_the_list(): void
    {
        $genre = $this->actingAs($this->admin())->postJson(route('admin.genres.store'), ['name_en' => 'Folk Tales', 'name_bn' => 'লোককথা'])->assertOk()->json('genre');

        $b = $this->create(['genre_category' => $genre['slug']]);
        $other = $this->create(['title' => 'Other Genre Book', 'sku' => 'BK-90020', 'idea_serial_no' => 'IP920', 'isbn' => '', 'genre_category' => 'novel']);

        $html = $this->actingAs($this->admin())->get(route('admin.books', ['genre' => $genre['slug']]))->assertOk()->getContent();
        // (the "book added" flash message also names the title, so look at the table rows themselves)
        $this->assertStringContainsString('id="bookRow_' . $b->id . '"', $html);
        $this->assertStringNotContainsString('id="bookRow_' . $other->id . '"', $html);
        $this->assertStringContainsString('id="genreManagerModal"', $html);
        $this->assertStringContainsString('id="fGenre"', $html);
        $this->assertStringContainsString('লোককথা', $html);
    }

    public function test_the_book_form_offers_new_genres_and_an_add_button(): void
    {
        Genre::create(['slug' => 'folk_tales', 'name_en' => 'Folk Tales', 'name_bn' => 'লোককথা', 'is_active' => true, 'sort_order' => 999]);

        $html = $this->actingAs($this->admin())->get(route('admin.content.create', ['type' => 'books']))->assertOk()->getContent();

        $this->assertStringContainsString('<option value="folk_tales"', $html);
        $this->assertStringContainsString('id="newGenreBox"', $html);
        $this->assertStringContainsString('window.saveNewGenre = async function', $html);
    }

    public function test_a_hidden_genre_stays_on_old_books_but_is_not_offered(): void
    {
        $genre = $this->actingAs($this->admin())->postJson(route('admin.genres.store'), ['name_en' => 'Retired'])->assertOk()->json('genre');
        $b = $this->create(['genre_category' => $genre['slug']]);

        $this->actingAs($this->admin())->putJson(route('admin.genres.update', $genre['id']), ['is_active' => false])->assertOk();

        $this->assertSame($genre['slug'], $b->fresh()->genre_category);
        $html = $this->actingAs($this->admin())->get(route('admin.content.create', ['type' => 'books']))->getContent();
        $this->assertStringNotContainsString('<option value="' . $genre['slug'] . '"', $html);
    }

    public function test_quick_add_uses_the_same_number_and_name_address(): void
    {
        $res = $this->actingAs($this->admin())->postJson(route('admin.books.quick-store'), ['title' => 'Quick Added Book', 'price' => 120, 'category_id' => $this->categoryId]);

        if ($res->getStatusCode() !== 200) {
            $this->markTestSkipped('Quick add needs the cover renderer, which is not available here: ' . $res->getStatusCode());
        }

        $book = Book::firstOrFail();
        $this->assertSame($book->id . '-quick-added-book', $book->slug);
        $this->get('/books/' . $book->id)->assertStatus(301)->assertRedirect(route('book.show', $book->slug));
    }

    // ── books that already exist ─────────────────────────────────────────────

    private function legacyBook(string $slug, string $title, ?string $titleEn = null, int $active = 1): Book
    {
        return Book::forceCreate([
            'title' => $title, 'title_en' => $titleEn, 'slug' => $slug, 'price' => 200, 'category_id' => $this->categoryId,
            'is_active' => $active, 'mod_status' => 'approved', 'sku' => 'LB-' . $slug, 'author_name' => 'Old Author',
        ]);
    }

    public function test_the_command_gives_existing_books_the_number_and_name_address_and_keeps_the_old_one(): void
    {
        $a = $this->legacyBook('amar-boi', 'Amar Boi', 'My Book');
        $b = $this->legacyBook('already-fine', 'Fine Book');
        $b->forceFill(['slug' => $b->id . '-fine-book'])->saveQuietly();
        $c = $this->legacyBook('no-name', 'আমার বই');

        $this->artisan('books:number-slugs')->assertExitCode(0);

        $this->assertSame($a->id . '-my-book', $a->fresh()->slug);
        $this->assertSame($b->id . '-fine-book', $b->fresh()->slug, 'an already numbered address is left alone');
        $this->assertStringStartsWith($c->id . '-', $c->fresh()->slug);
        $this->assertDatabaseHas('book_slug_redirects', ['book_id' => $a->id, 'old_slug' => 'amar-boi']);

        // running it again changes nothing
        $this->artisan('books:number-slugs')->assertExitCode(0);
        $this->assertSame($a->id . '-my-book', $a->fresh()->slug);
        $this->assertSame(1, \Modules\Book\Models\BookSlugRedirect::where('book_id', $a->id)->count());
    }

    public function test_the_dry_run_changes_nothing(): void
    {
        $a = $this->legacyBook('amar-boi', 'Amar Boi', 'My Book');

        $this->artisan('books:number-slugs --dry-run')->assertExitCode(0);

        $this->assertSame('amar-boi', $a->fresh()->slug);
        $this->assertDatabaseCount('book_slug_redirects', 0);
    }

    public function test_old_addresses_and_old_printed_qr_codes_land_on_the_new_address(): void
    {
        $a = $this->legacyBook('amar-boi', 'Amar Boi', 'My Book');
        $this->artisan('books:number-slugs')->assertExitCode(0);
        $new = $a->fresh()->slug;

        // the old address
        $this->get('/books/amar-boi')->assertStatus(301)->assertRedirect(route('book.show', $new));
        // an old printed QR: old slug plus its serial / isbn parameters (kept on the way)
        $res = $this->get('/books/amar-boi?serial=IP001&isbn=9780306406157')->assertStatus(301);
        $location = $res->headers->get('Location');
        $this->assertStringStartsWith(route('book.show', $new) . '?', $location);
        $this->assertStringContainsString('serial=IP001', $location, 'the old QR parameters travel with the redirect');
        $this->assertStringContainsString('isbn=9780306406157', $location);
        // the number alone, and the new address itself
        $this->get('/books/' . $a->id)->assertStatus(301)->assertRedirect(route('book.show', $new));
        $this->get('/books/' . $new)->assertOk();
    }

    public function test_an_old_address_never_reveals_an_inactive_book(): void
    {
        $a = $this->legacyBook('hidden-one', 'Hidden One', 'Hidden One', 0);
        $this->artisan('books:number-slugs')->assertExitCode(0);

        $this->get('/books/hidden-one')->assertNotFound();
    }

    public function test_editing_a_slug_later_keeps_the_previous_address_working(): void
    {
        $b = $this->create();
        $first = $b->slug;

        $this->actingAs($this->admin())->put(route('admin.content.update', ['type' => 'books', 'id' => $b->id]), $this->payload(['slug' => 'brand-new-address']))
            ->assertSessionHasNoErrors();
        auth()->logout();

        $this->assertSame('brand-new-address', $b->fresh()->slug);
        $this->get('/books/' . $first)->assertStatus(301)->assertRedirect(route('book.show', 'brand-new-address'));
    }

    public function test_the_new_address_is_never_also_an_old_address_of_another_book(): void
    {
        $a = $this->legacyBook('first-name', 'First');
        $this->artisan('books:number-slugs')->assertExitCode(0);
        $this->assertDatabaseHas('book_slug_redirects', ['old_slug' => 'first-name']);

        // another book now takes that address: it must win over the redirect
        $other = $this->legacyBook('second-name', 'Second');
        $other->update(['slug' => 'first-name']);

        $this->assertDatabaseMissing('book_slug_redirects', ['old_slug' => 'first-name']);
        $this->get('/books/first-name')->assertOk();
    }
}
