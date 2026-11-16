<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Book\Models\Book;
use Tests\TestCase;

class BookCodesTest extends TestCase
{
    use RefreshDatabase;

    private ?User $adminUser = null;

    private function admin(): User
    {
        return $this->adminUser ??= User::create([
            'name' => 'Codes Admin', 'email' => 'codes-admin@example.test', 'phone' => '01710000077',
            'password' => Hash::make('test-password'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    public function test_the_create_form_shows_real_server_rendered_codes_and_download_buttons(): void
    {
        $html = $this->actingAs($this->admin())->get(route('admin.content.create', ['type' => 'books']))->assertOk()->getContent();

        $this->assertStringContainsString('id="sec-barcode"', $html);
        $this->assertStringContainsString(route('admin.books.code-preview'), $html);
        foreach (['Download PNG', 'Download SVG', 'Copy value', 'Copy link', 'Open link'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
        $this->assertStringContainsString('window.downloadBookCode = async function', $html);
        // The old look-alike generators must be gone: they drew codes that did not scan.
        $this->assertStringNotContainsString('generateQuickQrMatrix', $html);
        $this->assertStringNotContainsString('downloadSvgElementAsHighResPng', $html);
    }

    public function test_an_existing_book_gets_its_own_link_and_a_code128_barcode_from_its_sku(): void
    {
        $book = Book::forceCreate([
            'title' => 'Codes', 'slug' => 'codes-' . uniqid(), 'price' => 100, 'is_active' => true,
            'sku' => 'BK-00042', 'idea_serial_no' => 'IP042', 'isbn' => '978-0-306-40615-7',
        ]);

        $html = $this->actingAs($this->admin())->get(route('admin.content.edit', ['type' => 'books', 'id' => $book->id]))->assertOk()->getContent();

        $this->assertStringContainsString('data-format="code128"', $html);
        $this->assertStringContainsString('data-value="BK-00042"', $html);
        $this->assertStringContainsString('/books/' . $book->slug . '?serial=IP042&amp;isbn=978-0-306-40615-7', $html);
        $this->assertStringContainsString('shape-rendering="crispEdges"', $html);
    }

    public function test_preview_endpoint_switches_to_ean13_for_a_valid_isbn(): void
    {
        $json = $this->actingAs($this->admin())
            ->getJson(route('admin.books.code-preview', ['sku' => 'BK-1', 'isbn' => '0-306-40615-2', 'source' => 'isbn', 'slug' => 'a b']))
            ->assertOk()->json();

        $this->assertSame('ean13', $json['barcode']['format']);
        $this->assertSame('9780306406157', $json['barcode']['value']);
        $this->assertStringContainsString('EAN-13 barcode 9780306406157', $json['barcode']['svg']);
        $this->assertStringContainsString('/books/a%20b?serial=BK-1&isbn=0-306-40615-2', $json['qr']['payload']);
        $this->assertTrue($json['isbn']['valid']);
    }

    public function test_preview_endpoint_never_invents_a_value_and_explains_problems(): void
    {
        $empty = $this->actingAs($this->admin())->getJson(route('admin.books.code-preview'))->assertOk()->json();
        $this->assertNull($empty['barcode']['svg']);
        $this->assertSame('', $empty['barcode']['value']);
        $this->assertNotEmpty($empty['barcode']['warnings']);
        $this->assertNotNull($empty['qr']['svg'], 'the QR still points at the catalogue');
        $this->assertNotEmpty($empty['qr']['warnings'], 'unsaved book without slug is explained');

        $bad = $this->actingAs($this->admin())
            ->getJson(route('admin.books.code-preview', ['isbn' => '9780306406158', 'source' => 'isbn']))->assertOk()->json();
        $this->assertSame('code128', $bad['barcode']['format']);
        $this->assertFalse($bad['isbn']['valid']);
        $this->assertStringContainsString('not valid', $bad['barcode']['warnings'][0]);

        $bangla = $this->actingAs($this->admin())
            ->getJson(route('admin.books.code-preview', ['sku' => 'IP-০০১', 'source' => 'sku']))->assertOk()->json();
        $this->assertSame('IP-', $bangla['barcode']['value']);
        $this->assertStringContainsString('left out', $bangla['barcode']['warnings'][0]);
    }

    public function test_preview_endpoint_validates_input_and_is_admin_only(): void
    {
        $this->actingAs($this->admin())
            ->getJson(route('admin.books.code-preview', ['ecc' => 'Z']))->assertStatus(422);
        $this->actingAs($this->admin())
            ->getJson(route('admin.books.code-preview', ['source' => 'nope']))->assertStatus(422);

        auth()->logout();
        $this->getJson(route('admin.books.code-preview'))->assertStatus(401);
    }

    public function test_higher_error_correction_changes_the_qr(): void
    {
        $base = ['slug' => 'some-book', 'serial' => 'IP001', 'isbn' => '9780306406157'];
        $l = $this->actingAs($this->admin())->getJson(route('admin.books.code-preview', $base + ['ecc' => 'L']))->json();
        $h = $this->actingAs($this->admin())->getJson(route('admin.books.code-preview', $base + ['ecc' => 'H']))->json();

        $this->assertLessThan($h['qr']['modules'], $l['qr']['modules']);
    }
}
