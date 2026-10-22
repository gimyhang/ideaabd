<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Book\Models\Book;
use Modules\Book\Models\Genre;
use Modules\Book\Services\RecommendationEngine;
use Tests\TestCase;

class GenreAndCoverTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private function admin(): User
    {
        return User::create([
            'name' => 'Genre Admin', 'email' => 'genre-admin@example.test', 'phone' => '01710000077',
            'password' => Hash::make('test-password'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    /** Minimum the admin book form requires, plus whatever the test changes. */
    private function payload(array $over = []): array
    {
        $cat = \Modules\Book\Models\Category::firstOrCreate(['slug' => 'test-cat'], ['name' => 'Test Category']);

        $pub = \Illuminate\Support\Facades\DB::table('publishers')->where('slug', 'test-pub')->value('id')
            ?: \Illuminate\Support\Facades\DB::table('publishers')->insertGetId(['name' => 'Test Publisher', 'slug' => 'test-pub', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        return array_merge(['title' => 'Test Book', 'category_id' => $cat->id, 'publisher_id' => $pub, 'price' => 100], $over);
    }

    private function book(array $over = []): Book
    {
        return Book::forceCreate(array_merge(['title' => 'Test Book', 'slug' => 'test-book-' . uniqid(), 'price' => 100, 'is_active' => true], $over));
    }

    // ---------- one genre list ----------

    public function test_genres_are_seeded_once_and_every_form_reads_the_same_list(): void
    {
        $this->assertSame(10, Genre::count());

        $html = view('partials.genre-options', ['selected' => 'poetry', 'lang' => 'both'])->render();
        $this->assertStringContainsString('value="poetry" selected', $html);
        $this->assertStringContainsString('(Poetry)', $html);                      // Bangla (English) label for the publisher portal

        $en = view('partials.genre-options', ['selected' => '', 'lang' => 'en'])->render();
        $this->assertStringContainsString('>Novel<', $en);                        // English label for the admin form
    }

    public function test_a_new_genre_reaches_the_book_form_without_touching_any_template(): void
    {
        Genre::create(['slug' => 'crime_thriller', 'name_en' => 'Crime', 'name_bn' => 'অপরাধ', 'sort_order' => 999, 'is_active' => true]);

        $this->actingAs($this->admin())
            ->get(route('admin.content.create', ['type' => 'books']))
            ->assertOk()
            ->assertSee('value="crime_thriller"', false);
    }

    public function test_an_inactive_genre_disappears_from_forms_but_an_old_books_value_is_kept(): void
    {
        Genre::where('slug', 'islamic')->update(['is_active' => false]);
        Genre::forgetCache();

        $fresh = view('partials.genre-options', ['selected' => '', 'lang' => 'en'])->render();
        $this->assertStringNotContainsString('value="islamic"', $fresh);

        $old = view('partials.genre-options', ['selected' => 'islamic', 'lang' => 'en'])->render();
        $this->assertStringContainsString('value="islamic" selected', $old);
        $this->assertStringContainsString('(inactive)', $old);
    }

    // ---------- server-side validation ----------

    public function test_the_admin_form_rejects_a_genre_that_is_not_in_the_list(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.content.store', ['type' => 'books']), $this->payload(['title' => 'A', 'genre_category' => 'hacked']))
            ->assertSessionHasErrors('genre_category');

        $this->assertDatabaseMissing('books', ['title' => 'A']);
    }

    public function test_the_admin_form_accepts_a_listed_genre_and_can_clear_it_again(): void
    {
        $admin = $this->actingAs($this->admin());

        $admin->post(route('admin.content.store', ['type' => 'books']), $this->payload(['title' => 'Poem Book', 'genre_category' => 'poetry']))->assertSessionDoesntHaveErrors();
        $book = Book::where('title', 'Poem Book')->firstOrFail();
        $this->assertSame('poetry', $book->genre_category);

        // field present but empty = "remove the genre"
        $admin->put(route('admin.content.update', ['type' => 'books', 'id' => $book->id]), $this->payload(['title' => 'Poem Book', 'genre_category' => '']))->assertSessionDoesntHaveErrors();
        $this->assertNull($book->fresh()->genre_category);
    }

    public function test_updating_without_the_genre_field_keeps_it_and_a_switched_off_current_genre_stays_valid(): void
    {
        $book = $this->book(['genre_category' => 'islamic']);
        Genre::where('slug', 'islamic')->update(['is_active' => false]);
        Genre::forgetCache();

        $admin = $this->actingAs($this->admin());

        // untouched field (not sent) -> unchanged
        $admin->put(route('admin.content.update', ['type' => 'books', 'id' => $book->id]), $this->payload(['title' => 'Renamed']))->assertSessionDoesntHaveErrors();
        $this->assertSame('islamic', $book->fresh()->genre_category);

        // sending the book's own (now inactive) genre is still accepted…
        $admin->put(route('admin.content.update', ['type' => 'books', 'id' => $book->id]), $this->payload(['title' => 'Renamed', 'genre_category' => 'islamic']))->assertSessionDoesntHaveErrors();

        // …but picking another inactive genre is not
        Genre::where('slug', 'novel')->update(['is_active' => false]);
        Genre::forgetCache();
        $admin->put(route('admin.content.update', ['type' => 'books', 'id' => $book->id]), $this->payload(['title' => 'Renamed', 'genre_category' => 'novel']))->assertSessionHasErrors('genre_category');
    }

    // ---------- cover generator ----------

    public function test_an_auto_generated_cover_never_replaces_an_existing_cover_by_itself(): void
    {
        Storage::fake('public');
        $book = $this->book(['cover_image' => 'storage/books/covers/original.webp']);

        $this->actingAs($this->admin())
            ->put(route('admin.content.update', ['type' => 'books', 'id' => $book->id]), [
                ...$this->payload(['generated_cover_data' => self::PNG]),          // what the page script used to send on load / on title edits
            ])->assertSessionDoesntHaveErrors();

        $this->assertSame('storage/books/covers/original.webp', $book->fresh()->cover_image);
    }

    public function test_an_auto_generated_cover_is_used_when_the_admin_asks_for_a_new_one_or_when_there_is_none(): void
    {
        Storage::fake('public');
        $admin = $this->actingAs($this->admin());

        $withCover = $this->book(['cover_image' => 'storage/books/covers/original.webp']);
        $admin->put(route('admin.content.update', ['type' => 'books', 'id' => $withCover->id]), [
            ...$this->payload(['generated_cover_data' => self::PNG, 'regenerate_cover' => '1']),
        ])->assertSessionDoesntHaveErrors();
        $this->assertNotSame('storage/books/covers/original.webp', $withCover->fresh()->cover_image);

        $noCover = $this->book(['cover_image' => null]);
        $admin->put(route('admin.content.update', ['type' => 'books', 'id' => $noCover->id]), [
            ...$this->payload(['generated_cover_data' => self::PNG]),
        ])->assertSessionDoesntHaveErrors();
        $this->assertNotEmpty($noCover->fresh()->cover_image);
    }

    public function test_the_book_form_page_defines_one_cover_generator_and_the_generic_one_cannot_override_it(): void
    {
        $html = $this->actingAs($this->admin())->get(route('admin.content.create', ['type' => 'books']))->assertOk()->getContent();

        $this->assertStringContainsString('window.generateAutoBookCoverLive = function', $html);                 // books_form's guarded version
        $this->assertStringContainsString('typeof window.generateAutoBookCoverLive !== \'function\'', $html);   // generic one only as a fallback
        $this->assertStringNotContainsString("window.generateAutoBookCoverLive = generateAutoBookCoverLive;", $html);
        $this->assertStringContainsString('name="regenerate_cover"', $html);
    }

    // ---------- genre admin ----------

    public function test_genre_admin_can_add_rename_reorder_hide_and_delete_unused_genres(): void
    {
        $admin = $this->actingAs($this->admin());

        $admin->postJson(route('admin.genres.store'), ['slug' => 'cookery', 'name_en' => 'Cookery', 'name_bn' => 'রান্না'])->assertOk()->assertJsonPath('success', true);
        $admin->postJson(route('admin.genres.store'), ['slug' => 'cookery', 'name_en' => 'Dup'])->assertUnprocessable();           // slug is unique
        $admin->postJson(route('admin.genres.store'), ['slug' => 'Bad Slug!', 'name_en' => 'x'])->assertUnprocessable();            // slug format

        $g = Genre::where('slug', 'cookery')->first();
        $admin->putJson(route('admin.genres.update', $g), ['name_en' => 'Cooking', 'is_active' => false])->assertOk();
        $this->assertSame('Cooking', Genre::nameFor('cookery'));
        $this->assertNotContains('cookery', Genre::active()->pluck('slug')->all());

        $ids = Genre::orderBy('id', 'desc')->pluck('id')->all();                                                                   // reverse the order
        $admin->postJson(route('admin.genres.reorder'), ['ids' => $ids])->assertOk();
        $this->assertSame('cookery', Genre::everything()->first()['slug']);

        $admin->deleteJson(route('admin.genres.destroy', $g))->assertOk();
        $this->assertDatabaseMissing('genres', ['slug' => 'cookery']);
    }

    public function test_a_genre_used_by_books_cannot_be_deleted(): void
    {
        $this->book(['genre_category' => 'novel']);
        $novel = Genre::where('slug', 'novel')->first();

        $this->actingAs($this->admin())->deleteJson(route('admin.genres.destroy', $novel))->assertUnprocessable()->assertJsonPath('success', false);
        $this->assertDatabaseHas('genres', ['slug' => 'novel']);
    }

    public function test_the_genre_page_requires_an_admin(): void
    {
        $this->get(route('admin.genres.index'))->assertRedirect();
        $this->actingAs($this->admin())->get(route('admin.genres.index'))->assertOk()->assertSee('Genre list');
    }

    // ---------- genre is used by recommendations and SEO ----------

    public function test_related_books_include_books_of_the_same_genre_from_other_categories(): void
    {
        $base = $this->book(['title' => 'Base', 'category_id' => null, 'genre_category' => 'poetry']);
        $sameGenre = $this->book(['title' => 'Same genre', 'category_id' => null, 'genre_category' => 'poetry']);
        $other = $this->book(['title' => 'Other genre', 'category_id' => null, 'genre_category' => 'novel']);

        $titles = app(RecommendationEngine::class)->getRelatedBooks($base)->pluck('title')->all();

        $this->assertContains('Same genre', $titles);
        $this->assertNotContains('Other genre', $titles);
    }
}
