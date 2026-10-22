<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Book\Models\Book;
use Tests\TestCase;

class BookDuplicateGuardTest extends TestCase
{
    use RefreshDatabase;

    private function createBook(array $attributes): Book
    {
        return Book::forceCreate(array_merge([
            'title' => 'Example Book',
            'slug' => 'example-book-' . uniqid(),
            'is_active' => true,
        ], $attributes));
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Catalog Admin',
            'email' => 'catalog-admin@example.test',
            'phone' => '01710000089',
            'password' => Hash::make('test-password'),
            'role' => 'admin',
            'reg_type' => 'admin',
            'reg_status' => 'approved',
            'is_active' => true,
        ]);
    }

    public function test_isbn_duplicate_is_rejected_even_when_formatting_differs(): void
    {
        $this->createBook(['isbn' => '978-0-306-40615-7']);

        try {
            $this->createBook([
                'title' => 'A Different Title',
                'isbn' => '9780306406157',
            ]);
            $this->fail('A duplicate ISBN should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('isbn', $exception->errors());
        }
    }

    public function test_same_title_author_and_edition_are_rejected_but_another_edition_is_allowed(): void
    {
        $this->createBook([
            'title' => '  The Same Book ',
            'author_name' => 'Example Author',
            'edition' => '1st Edition',
        ]);

        try {
            $this->createBook([
                'title' => 'the same book',
                'author_name' => 'Example Author',
                'edition' => '1st Edition',
            ]);
            $this->fail('A duplicate title, author, and edition should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('title', $exception->errors());
        }

        $differentEdition = $this->createBook([
            'title' => 'The Same Book',
            'author_name' => 'Example Author',
            'edition' => '2nd Edition',
        ]);

        $this->assertDatabaseHas('books', ['id' => $differentEdition->id]);
    }

    public function test_admin_quick_store_returns_validation_error_for_duplicate_book(): void
    {
        $this->createBook([
            'title' => 'Already Catalogued',
            'author_name' => 'আইডিয়া প্রকাশন',
        ]);

        $this->actingAs($this->admin())
            ->postJson(route('admin.books.quick-store'), [
                'title' => 'Already Catalogued',
                'price' => 100,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');

        $this->assertDatabaseCount('books', 1);
    }
}
