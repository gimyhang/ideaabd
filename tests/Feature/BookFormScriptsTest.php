<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Book\Models\Book;
use Tests\TestCase;

class BookFormScriptsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Form Admin', 'email' => 'form-admin@example.test', 'phone' => '01710000088',
            'password' => Hash::make('test-password'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    public function test_the_generic_form_functions_never_overwrite_the_book_forms_own_versions(): void
    {
        $html = $this->actingAs($this->admin())->get(route('admin.content.create', ['type' => 'books']))->assertOk()->getContent();

        // every function that books_form.blade.php also defines exists in form.blade.php only as a *Generic fallback
        foreach (['updateLiveMockupCard', 'syncBookSizeCombined', 'previewAdminCoverInput', 'updateHardcoverCalculations', 'updatePaperbackCalculations', 'addAuthorField'] as $fn) {
            $this->assertStringContainsString("window.$fn = function", $html, "$fn missing from the book form");
            $this->assertStringContainsString("if (typeof window.$fn !== 'function') { window.$fn = {$fn}Generic; }", $html);
            $this->assertStringNotContainsString("\nfunction $fn(", $html, "$fn is declared globally again and would win");
        }
    }

    public function test_only_one_cost_price_panel_is_submitted_and_a_hardcover_books_cost_is_prefilled(): void
    {
        $book = Book::forceCreate(['title' => 'HC', 'slug' => 'hc-' . uniqid(), 'price' => 300, 'cost_price' => 55, 'cover_type' => 'hardcover', 'is_active' => true]);

        $html = $this->actingAs($this->admin())->get(route('admin.content.edit', ['type' => 'books', 'id' => $book->id]))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="f-hardcover_cost_price"[^>]*\s+value="55(\.00)?"/s', $html);
        $this->assertStringContainsString('Both pricing panels post a field called cost_price', $html);
    }
}
