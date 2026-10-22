<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Book\Models\Book;
use Modules\Book\Models\BookShare;
use Modules\Book\Models\Wishlist;
use Tests\TestCase;

class BookActionsTest extends TestCase
{
    use RefreshDatabase;

    private function book(array $over = []): Book
    {
        return Book::forceCreate(array_merge(['title' => 'Essential Business Terms', 'slug' => 'essential-business-terms', 'price' => 400, 'discount_price' => 300, 'is_active' => true, 'stock_quantity' => 5], $over));
    }

    private function reader(): User
    {
        return User::create([
            'name' => 'Reader', 'email' => 'reader@example.test', 'phone' => '01710000044',
            'password' => Hash::make('test-password'), 'role' => 'buyer', 'reg_type' => 'customer',
            'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    public function test_the_book_page_uses_the_new_action_row_and_share_sheet(): void
    {
        $book = $this->book();
        $html = $this->get('/books/essential-business-terms')->assertOk()->getContent();

        $this->assertStringContainsString('class="ba-actions"', $html);
        $this->assertStringContainsString('id="baShareSheet"', $html);
        $this->assertStringContainsString('css/book-actions.css', $html);
        $this->assertStringContainsString('js/book-actions.js', $html);
        $this->assertStringContainsString('data-share-endpoint="' . route('book.share', $book->id) . '"', $html);
        foreach (['whatsapp', 'facebook', 'telegram', 'x', 'linkedin', 'email', 'sms', 'qr', 'native', 'copy'] as $ch) {
            $this->assertStringContainsString('data-channel="' . $ch . '"', $html);
        }
        $this->assertStringNotContainsString('fa-share-nodes', $html);                // no icon font in the action row
        $this->assertStringNotContainsString('onclick="shareBook(', $html);           // the old inline handler is gone
        $this->assertStringNotContainsString('id="wishlistIcon"', $html);

        foreach (['css/book-actions.css', 'js/book-actions.js'] as $asset) {
            $this->assertFileExists(public_path($asset));
        }
    }

    public function test_sharing_is_counted_once_per_visitor_channel_and_hour_and_only_for_known_channels(): void
    {
        $book = $this->book();

        $this->postJson(route('book.share', $book->id), ['channel' => 'whatsapp'])->assertOk()->assertJson(['success' => true, 'shares' => 1]);
        $this->postJson(route('book.share', $book->id), ['channel' => 'whatsapp'])->assertOk()->assertJson(['shares' => 1]);   // same visitor + channel: not inflated
        $this->postJson(route('book.share', $book->id), ['channel' => 'copy'])->assertOk()->assertJson(['shares' => 2]);
        $this->postJson(route('book.share', $book->id), ['channel' => 'myspace'])->assertUnprocessable();
        $this->postJson(route('book.share', 999999), ['channel' => 'copy'])->assertNotFound();

        $this->assertSame(2, BookShare::count());
        $this->assertStringNotContainsString('127.0.0.1', (string) BookShare::first()->visitor_hash);   // hashed, no raw IP

        // the count shows on the button
        $this->assertMatchesRegularExpression('/data-ba="share-count"\s*>2</', $this->get('/books/essential-business-terms')->getContent());
    }

    public function test_an_inactive_book_cannot_be_shared_or_saved(): void
    {
        $book = $this->book(['is_active' => false, 'slug' => 'hidden']);
        $this->postJson(route('book.share', $book->id), ['channel' => 'copy'])->assertNotFound();
        $this->actingAs($this->reader())->postJson(route('book.wishlist.toggle', $book->id))->assertNotFound();
    }

    public function test_the_wishlist_toggles_for_a_signed_in_reader_and_guests_are_sent_to_login(): void
    {
        $book = $this->book();

        $this->postJson(route('book.wishlist.toggle', $book->id))->assertUnauthorized()->assertJson(['guest' => true]);

        $reader = $this->reader();
        $this->actingAs($reader)->postJson(route('book.wishlist.toggle', $book->id))->assertOk()->assertJson(['saved' => true, 'count' => 1]);
        $this->assertTrue(Wishlist::isInWishlist($reader->id, $book->id));
        $this->assertStringContainsString('aria-pressed="true"', $this->get('/books/essential-business-terms')->getContent());

        $this->postJson(route('book.wishlist.toggle', $book->id))->assertOk()->assertJson(['saved' => false, 'count' => 0]);
        $this->assertFalse(Wishlist::isInWishlist($reader->id, $book->id));
    }

    public function test_a_guests_browser_wishlist_moves_into_the_account_without_duplicates_or_hidden_books(): void
    {
        $one = $this->book();
        $two = $this->book(['title' => 'Second', 'slug' => 'second']);
        $hidden = $this->book(['title' => 'Draft', 'slug' => 'draft', 'is_active' => false]);

        $this->postJson(route('book.wishlist.sync'), ['ids' => [$one->id]])->assertUnauthorized();

        $reader = $this->reader();
        $this->actingAs($reader)->postJson(route('book.wishlist.sync'), ['ids' => [$one->id, $two->id, $hidden->id, $one->id]])
            ->assertOk()->assertJsonCount(2, 'ids');
        $this->assertSame(2, Wishlist::where('user_id', $reader->id)->count());

        $this->postJson(route('book.wishlist.sync'), ['ids' => ['x']])->assertUnprocessable();
    }
    public function test_facebook_gets_a_landscape_share_card_and_book_meta_tags(): void
    {
        $rel = 'test-covers/portrait-' . uniqid() . '.jpg';
        $abs = storage_path('app/public/' . $rel);
        @mkdir(dirname($abs), 0775, true);
        $im = imagecreatetruecolor(400, 600);
        imagefilledrectangle($im, 0, 0, 400, 600, imagecolorallocate($im, 20, 120, 160));
        imagejpeg($im, $abs);

        try {
            $book = $this->book(['cover_image' => $rel, 'isbn' => '9789840000001']);
            config(['services.facebook.app_id' => '123456789']);

            $html = $this->get('/books/essential-business-terms')->assertOk()->getContent();
            $card = route('book.share-card', 'essential-business-terms');
            // the layout rewrites a local host to the canonical domain, so compare the path only
            $this->assertSame(1, preg_match('#<meta property="og:image" content="[^"]*/books/essential-business-terms/share-card\.jpg">#', $html), 'og:image must be the share card');
            $this->assertTrue(str_contains($html, '<meta property="book:author"'), 'missing in page: ' . '<meta property="book:author"');
            $this->assertTrue(str_contains($html, '<meta property="book:isbn" content="9789840000001">'), 'missing in page: ' . '<meta property="book:isbn" content="9789840000001">');
            $this->assertTrue(str_contains($html, '<meta property="product:price:amount" content="300.00">'), 'missing in page: ' . '<meta property="product:price:amount" content="300.00">');
            $this->assertTrue(str_contains($html, '<meta property="product:price:currency" content="BDT">'), 'missing in page: ' . '<meta property="product:price:currency" content="BDT">');
            $this->assertTrue(str_contains($html, '<meta property="fb:app_id" content="123456789">'), 'missing in page: ' . '<meta property="fb:app_id" content="123456789">');
            $this->assertTrue(str_contains($html, 'data-channel="messenger"'), 'missing in page: ' . 'data-channel="messenger"');

            $res = $this->get($card)->assertOk()->assertHeader('Content-Type', 'image/jpeg');
            $img = getimagesizefromstring(file_get_contents($res->baseResponse->getFile()->getPathname()));
            $this->assertSame([1200, 630], [$img[0], $img[1]]);                // landscape, whatever the cover shape

            $this->get(route('book.share-card', 'no-such-book'))->assertNotFound();
        } finally {
            @unlink($abs);
            foreach (glob(storage_path('app/public/share-cards/*-*.jpg')) ?: [] as $f) { @unlink($f); }
        }

        // a book without a stored cover keeps the plain image and has no card
        $this->book(['title' => 'No Cover', 'slug' => 'no-cover']);
        $this->get(route('book.share-card', 'no-cover'))->assertNotFound();
        $this->assertFalse(str_contains($this->get('/books/no-cover')->getContent(), 'share-card.jpg'));
    }

    public function test_the_facebook_button_opens_a_popup_and_keeps_the_message_ready(): void
    {
        $js = file_get_contents(public_path('js/book-actions.js'));
        $this->assertStringContainsString('facebook.com/sharer/sharer.php', $js);
        $this->assertStringContainsString("popup=yes", $js);                  // centred popup on desktop
        $this->assertStringContainsString('fb-messenger://share', $js);      // Messenger deep link on phones
        $this->assertStringContainsString('hashtag', $js);
    }
}