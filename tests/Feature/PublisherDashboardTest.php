<?php

namespace Tests\Feature;

use App\Models\PublisherPurchase;
use App\Models\User;
use App\Support\Bn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Book\Models\Book;
use Modules\Publisher\Models\Publisher;
use Tests\TestCase;

class PublisherDashboardTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** A publisher account with its Publisher record (found by e-mail). */
    private function publisher(string $name): array
    {
        $this->seq++;
        $email = "pub{$this->seq}@example.test";

        $user = User::create([
            'name' => $name . ' প্রতিনিধি', 'email' => $email, 'phone' => '0171000' . str_pad((string) (200 + $this->seq), 4, '0', STR_PAD_LEFT),
            'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => 'publisher', 'reg_type' => 'publisher',
            'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
        $pub = Publisher::create(['name' => $name, 'slug' => 'pub-' . $this->seq, 'email' => $email, 'phone' => $user->phone, 'is_active' => true, 'is_verified' => true]);

        return [$user, $pub];
    }

    private function admin(): User
    {
        $this->seq++;

        return User::create([
            'name' => 'Root', 'email' => "root{$this->seq}@example.test", 'phone' => '0170000' . str_pad((string) (300 + $this->seq), 4, '0', STR_PAD_LEFT),
            'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => User::STATUS_APPROVED, 'is_active' => true, 'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function book(Publisher $pub, string $title, int $stock, bool $active = true, array $extra = []): Book
    {
        $b = new Book();
        $b->forceFill($extra + [
            'title' => $title, 'slug' => 'b-' . ++$this->seq . '-' . uniqid(), 'publisher_id' => $pub->id,
            'price' => 200, 'stock_quantity' => $stock, 'is_active' => $active,
        ])->save();

        return $b;
    }

    private function purchase(Publisher $pub, float $total, float $paid, ?float $grand = null, ?string $date = null, int $qty = 2): PublisherPurchase
    {
        $p = new PublisherPurchase();
        $p->forceFill([
            'purchase_no' => 'PUR-' . ++$this->seq . '-' . uniqid(), 'publisher_id' => $pub->id, 'purchase_date' => $date ?? now()->toDateString(),
            'total_amount' => $total, 'grand_total' => $grand ?? $total, 'paid_amount' => $paid, 'due_amount' => max(0, ($grand ?? $total) - $paid),
            'payment_status' => $paid >= ($grand ?? $total) ? 'paid' : ($paid > 0 ? 'partial' : 'due'),
        ])->save();
        DB::table('publisher_purchase_items')->insert(['purchase_id' => $p->id, 'book_title' => 'বই', 'quantity' => $qty, 'subtotal' => $total, 'created_at' => now(), 'updated_at' => now()]);

        return $p;
    }

    private function page(User $as, string $query = ''): string
    {
        return $this->actingAs($as)->get('/publisher/dashboard' . $query)->assertOk()->getContent();
    }

    private function tile(string $html, string $label): array
    {
        $found = preg_match('#<span class="sp-label">' . preg_quote($label, '#') . '</span><span class="sp-value[^"]*">(.*?)</span><span class="sp-sub">(.*?)</span>#su', $html, $m);
        if (!$found) {
            $this->fail("tile \"{$label}\" is not on the page");
        }

        return [trim($m[1]), trim($m[2])];
    }

    private function has(string $html, string $needle): bool
    {
        return str_contains($html, $needle);
    }

    public function test_the_numbers_count_the_catalogue_and_use_grand_totals_and_per_order_dues(): void
    {
        [$user, $pub] = $this->publisher('আলো প্রকাশনী');
        $this->book($pub, 'ক', 50);
        $this->book($pub, 'খ', 3);               // low
        $this->book($pub, 'গ', 0);               // out
        $this->book($pub, 'ঘ', 10, false);       // draft

        $this->purchase($pub, 1000, 1000);                 // paid
        $this->purchase($pub, 2000, 500, 1800);            // 10% discount: owed = 1800 - 500
        $this->purchase($pub, 400, 600);                   // overpaid: owes nothing, must not hide the 1300 above

        $html = $this->page($user);

        $this->assertSame([Bn::num(4), 'প্রকাশিত ' . Bn::num(3) . 'টি'], $this->tile($html, 'মোট বই'));
        $this->assertSame([Bn::num(63)], [$this->tile($html, 'স্টকে কপি')[0]]);
        $this->assertSame(Bn::num(2), $this->tile($html, 'কম বা শেষ স্টক')[0]);
        $this->assertSame('৳' . Bn::num(3200), $this->tile($html, 'মোট ক্রয়')[0]);          // 1000 + 1800 + 400
        $this->assertSame('৳' . Bn::num(2100), $this->tile($html, 'পরিশোধিত')[0]);         // 1000 + 500 + 600
        $this->assertSame('৳' . Bn::num(1300), $this->tile($html, 'বকেয়া')[0]);
    }

    public function test_a_publisher_never_sees_another_publishers_books_or_orders(): void
    {
        [$mine, $myPub] = $this->publisher('আমার প্রকাশনী');
        [, $other]      = $this->publisher('অন্য প্রকাশনী');
        $this->book($myPub, 'আমার বই', 5);
        $this->book($other, 'অন্যের গোপন বই', 5);
        $this->purchase($other, 99999, 0);

        $html = $this->page($mine, '?tab=books');

        $this->assertTrue($this->has($html, 'আমার বই'));
        $this->assertFalse($this->has($html, 'অন্যের গোপন বই'));
        $this->assertSame(Bn::num(1), $this->tile($html, 'মোট বই')[0]);
        $this->assertSame('৳' . Bn::num(0), $this->tile($html, 'বকেয়া')[0]);

        // an admin's ?publisher_id= is not available to a publisher
        $html = $this->page($mine, '?publisher_id=' . $other->id);
        $this->assertFalse($this->has($html, 'অন্যের গোপন বই'));
        $this->assertFalse($this->has($html, 'name="publisher_id"'));
    }

    public function test_the_tab_comes_from_a_fixed_list_and_only_own_books_can_be_edited(): void
    {
        [$user, $pub] = $this->publisher('আলো প্রকাশনী');
        [, $other]    = $this->publisher('অন্য প্রকাশনী');
        $mine   = $this->book($pub, 'আমার বই', 5);
        $theirs = $this->book($other, 'অন্যের বই', 5);

        $active = fn (string $html, string $tab) => (bool) preg_match('#id="tab-' . $tab . '-btn"\s+class="active"#', $html);

        $this->assertTrue($active($this->page($user), 'overview'));
        $this->assertTrue($active($this->page($user, '?tab=books'), 'books'));
        $this->assertTrue($active($this->page($user, '?tab=%22%3E%3Cscript%3Ealert(1)%3C/script%3E'), 'overview'));
        $this->assertFalse($this->has($this->page($user, '?tab=%22%3E%3Cscript%3Ealert(1)%3C/script%3E'), '<script>alert(1)'));

        $edit = $this->page($user, '?edit_id=' . $mine->id);
        $this->assertTrue($active($edit, 'add-book'));
        $this->assertTrue($this->has($edit, 'আমার বই'));

        $stolen = $this->page($user, '?edit_id=' . $theirs->id);
        $this->assertFalse($this->has($stolen, 'অন্যের বই'));
        $this->assertTrue($active($stolen, 'overview'), 'a book that is not yours opens nothing');
    }

    public function test_all_tabs_render_including_the_book_form(): void
    {
        [$user, $pub] = $this->publisher('আলো প্রকাশনী');
        $this->book($pub, 'ক', 5);
        $this->purchase($pub, 500, 100);

        $html = $this->page($user, '?tab=add-book');

        foreach (['id="tab-overview"', 'id="tab-today-purchases"', 'id="tab-books"', 'id="tab-add-book"', 'id="tab-orders"', 'id="tab-settings"', 'id="pubBookForm"', 'id="pubQuickCategoryModal"', 'function switchPublisherTab'] as $needle) {
            $this->assertTrue($this->has($html, $needle), "missing {$needle}");
        }
    }

    public function test_every_tab_is_a_direct_child_of_the_tab_container(): void
    {
        // the book form once left a <div> open, which pushed the bills and profile tabs inside the form's tab
        [$user, $pub] = $this->publisher('আলো প্রকাশনী');
        $this->book($pub, 'ক', 5);

        foreach (['', '?tab=add-book', '?tab=orders'] as $q) {
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8"?>' . $this->page($user, $q));
            $children = [];
            foreach ((new \DOMXPath($dom))->query('//*[@id="publisherTabsContent"]/*') as $node) {
                $children[] = $node->getAttribute('id');
            }

            $this->assertSame(['tab-overview', 'tab-today-purchases', 'tab-books', 'tab-add-book', 'tab-orders', 'tab-settings'], $children, "page {$q}");
        }
    }

    public function test_orders_can_be_filtered_by_day_and_are_paginated(): void
    {
        Carbon::setTestNow('2026-10-15 12:00:00');
        [$user, $pub] = $this->publisher('আলো প্রকাশনী');
        $this->purchase($pub, 111, 111, null, '2026-10-15');
        $this->purchase($pub, 222, 222, null, '2026-10-14');
        $this->purchase($pub, 333, 333, null, '2026-09-01');

        $count = function (string $q) use ($user): int {
            $html = $this->actingAs($user)->get('/publisher/dashboard?tab=today-purchases' . $q)->assertOk()->getContent();
            $start = strpos($html, 'id="tab-today-purchases"');
            $pane  = substr($html, $start, strpos($html, 'id="tab-books"') - $start);   // the orders tab only

            return (int) preg_match_all('#<span class="fw-semibold font-monospace">#', $pane);
        };

        $this->assertSame(3, $count(''));
        $this->assertSame(1, $count('&date_filter=today'));
        $this->assertSame(1, $count('&date_filter=yesterday'));
        $this->assertSame(1, $count('&date_filter=custom&date=2026-09-01'));
        $this->assertSame(3, $count('&date_filter=nonsense'), 'an unknown filter means all');

        for ($i = 0; $i < 25; $i++) {
            $this->purchase($pub, 10, 10);
        }
        $this->assertSame(20, $count(''), 'the orders list shows 20 per page, not every order ever made');
    }

    public function test_search_sort_and_stock_filters_work_and_a_wildcard_is_just_text(): void
    {
        [$user, $pub] = $this->publisher('আলো প্রকাশনী');
        $this->book($pub, 'রাতের গান', 0);
        $this->book($pub, 'দিনের আলো', 40, true, ['price' => 100]);
        $this->book($pub, '100% সত্য', 7);

        $titles = fn (string $q) => preg_match_all('#class="fw-semibold text-decoration-none text-truncate d-block">(.*?)</a>#su', $this->page($user, '?tab=books' . $q), $m) ? array_map('trim', $m[1]) : [];

        $this->assertCount(3, $titles(''));
        $this->assertSame(['রাতের গান'], $titles('&stock=out'));
        $this->assertSame(['দিনের আলো'], $titles('&search=' . urlencode('আলো')));
        $this->assertSame(['100% সত্য'], $titles('&search=' . urlencode('100%')));
        $this->assertSame(['100% সত্য'], $titles('&search=' . urlencode('%')), 'a lone % matches only a title that really contains %');
        $this->assertSame('দিনের আলো', $titles('&sort=price_low')[0]);
    }

    public function test_the_admin_can_pick_which_publisher_to_look_at_and_the_choice_sticks(): void
    {
        $admin = $this->admin();
        [, $a] = $this->publisher('ক প্রকাশনী');
        [, $b] = $this->publisher('খ প্রকাশনী');
        $this->book($a, 'ক-এর বই', 1);
        $this->book($b, 'খ-এর বই', 1);

        $html = $this->page($admin, '?tab=books&publisher_id=' . $b->id);
        $this->assertTrue($this->has($html, 'খ-এর বই'));
        $this->assertFalse($this->has($html, 'ক-এর বই'));
        $this->assertTrue($this->has($html, 'name="publisher_id"'));

        // the next page load (no query) is still that publisher, so saving a book lands on the same one
        $this->assertTrue($this->has($this->page($admin, '?tab=books'), 'খ-এর বই'));
    }

    public function test_the_number_of_queries_does_not_grow_with_books_and_orders(): void
    {
        [$user, $pub] = $this->publisher('আলো প্রকাশনী');
        $queries = function () use ($user): int {
            DB::enableQueryLog();
            DB::flushQueryLog();
            $this->actingAs($user)->get('/publisher/dashboard?tab=books')->assertOk();
            $n = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $n;
        };

        $this->book($pub, 'ক', 3);
        $this->purchase($pub, 100, 50);
        $few = $queries();

        for ($i = 0; $i < 14; $i++) {
            $this->book($pub, 'বই ' . $i, 3);
            $this->purchase($pub, 100, 50);
        }
        $many = $queries();

        $this->assertLessThanOrEqual($few + 1, $many, "1 book = {$few} queries, 15 books = {$many}");
    }

    public function test_buyers_and_guests_are_kept_out(): void
    {
        $this->get('/publisher/dashboard')->assertRedirect(route('login'));

        $this->seq++;
        $buyer = User::create(['name' => 'ক্রেতা', 'email' => "b{$this->seq}@example.test", 'phone' => '01700099' . $this->seq . '1', 'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => 'buyer', 'is_active' => true, 'email_verified_at' => now()]);
        $this->actingAs($buyer)->get('/publisher/dashboard')->assertForbidden();
    }
}
