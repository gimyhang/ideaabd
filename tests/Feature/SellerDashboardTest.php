<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use App\Support\Bn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SellerDashboardTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function user(string $role, string $name): User
    {
        $this->seq++;

        return User::create([
            'name' => $name, 'email' => "u{$this->seq}@example.test", 'phone' => '0170000' . str_pad((string) (100 + $this->seq), 4, '0', STR_PAD_LEFT),
            'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => $role, 'reg_type' => $role,
            'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'reg_data' => $role === 'seller' ? ['shop_name' => "{$name} বুকশপ", 'address' => 'রংপুর'] : null,
        ]);
    }

    private function bill(User $seller, float $total, float $paid, string $status, array $extra = []): Bill
    {
        $when = $extra['created_at'] ?? null;
        unset($extra['created_at']);

        $bill = Bill::create($extra + [
            'seller_id' => $seller->id, 'customer_name' => 'গ্রাহক', 'items' => [['title' => 'বই', 'quantity' => 2, 'price' => $total / 2]],
            'subtotal' => $total, 'total' => $total, 'paid_amount' => $paid, 'due_amount' => $total - $paid, 'payment_status' => $status,
        ]);
        if ($when) {
            DB::table('bills')->where('id', $bill->id)->update(['created_at' => $when]); // created_at is not mass-assignable
        }

        return $bill;
    }

    private function page(User $as, string $query = ''): string
    {
        return $this->actingAs($as)->get('/seller/dashboard' . $query)->assertOk()->getContent();
    }

    /** The big number and the small line under a tile, e.g. tile($html, 'আদায়') → ['৳১,২০০', '১টি পরিশোধিত বিল']. */
    private function tile(string $html, string $label): array
    {
        $found = preg_match('#<span class="sp-label">' . preg_quote($label, '#') . '</span>\s*<span class="sp-value[^"]*">(.*?)</span>\s*<span class="sp-sub">(.*?)</span>#su', $html, $m);
        if (!$found) {
            $this->fail("tile \"{$label}\" is not on the page"); // short message: PHPUnit would otherwise print the whole page
        }

        return [trim($m[1]), trim($m[2])];
    }

    private function taka(float $n): string
    {
        return '৳' . Bn::num($n);
    }

    public function test_the_numbers_use_what_was_actually_paid_not_the_bill_total(): void
    {
        $seller = $this->user('seller', 'রহিম');
        $this->bill($seller, 1000, 1000, 'paid');
        $this->bill($seller, 600, 200, 'partial');   // 400 still owed
        $this->bill($seller, 300, 0, 'unpaid');

        $html = $this->page($seller);

        $this->assertSame([$this->taka(1900), Bn::num(3) . 'টি বিল'], $this->tile($html, 'মোট বিক্রি'));
        $this->assertSame([$this->taka(1200), Bn::num(1) . 'টি পরিশোধিত বিল'], $this->tile($html, 'আদায়'));   // money received, not the total of "paid" bills
        $this->assertSame([$this->taka(700), Bn::num(2) . 'টি অপরিশোধিত বিল'], $this->tile($html, 'বকেয়া'));
    }

    public function test_it_adds_up_the_same_way_as_the_billing_pages(): void
    {
        $seller = $this->user('seller', 'রহিম');
        $this->bill($seller, 1000, 400, 'partial');
        $this->bill($seller, 500, 500, 'paid');

        $html = $this->page($seller);

        // the same sums the bills page and the accounts page print
        $this->assertSame($this->taka((float) Bill::sum('paid_amount')), $this->tile($html, 'আদায়')[0]);
        $this->assertSame($this->taka((float) Bill::sum('due_amount')), $this->tile($html, 'বকেয়া')[0]);
        $this->assertSame($this->taka((float) Bill::sum('total')), $this->tile($html, 'মোট বিক্রি')[0]);
    }

    public function test_a_seller_only_sees_their_own_bills_and_cannot_pick_another_seller(): void
    {
        $mine  = $this->user('seller', 'আমি');
        $other = $this->user('seller', 'অন্যজন');
        $this->bill($mine, 100, 100, 'paid');
        $this->bill($other, 9000, 9000, 'paid');

        $html = $this->page($mine, '?seller_id=' . $other->id);

        $this->assertSame($this->taka(100), $this->tile($html, 'মোট বিক্রি')[0]);
        $this->assertFalse(str_contains($html, 'অন্যজন'), "unexpected: " . 'অন্যজন');
        $this->assertFalse(str_contains($html, 'id="sellerFilter"'), "unexpected: " . 'id="sellerFilter"');
    }

    public function test_today_and_month_are_counted_by_date(): void
    {
        Carbon::setTestNow('2026-10-15 12:00:00');
        $seller = $this->user('seller', 'রহিম');
        $this->bill($seller, 100, 100, 'paid');                                              // today
        $this->bill($seller, 200, 200, 'paid', ['created_at' => '2026-10-02 09:00:00']);     // this month
        $this->bill($seller, 400, 400, 'paid', ['created_at' => '2026-09-20 09:00:00']);     // last month

        $html = $this->page($seller);

        $this->assertSame([$this->taka(100), Bn::num(1) . 'টি বিল'], $this->tile($html, 'আজ'));
        $this->assertSame($this->taka(300), $this->tile($html, 'এ মাস')[0]);
        $this->assertSame($this->taka(700), $this->tile($html, 'মোট বিক্রি')[0]);
    }

    public function test_copies_sold_adds_up_the_items(): void
    {
        $seller = $this->user('seller', 'রহিম');
        $this->bill($seller, 100, 100, 'paid');                                                                // 2 copies
        $this->bill($seller, 50, 50, 'paid', ['items' => [['title' => 'a', 'qty' => 3], ['title' => 'b']]]);  // 3 + 1 (a line with no quantity is one copy)
        $this->bill($seller, 10, 10, 'paid', ['items' => []]);

        $this->assertSame(Bn::num(6), $this->tile($this->page($seller), 'বিক্রিত কপি')[0]);
    }

    public function test_the_admin_sees_everyone_or_one_seller_and_a_bad_id_is_ignored(): void
    {
        $admin = $this->user('admin', 'অ্যাডমিন');
        $a = $this->user('seller', 'ক');
        $b = $this->user('seller', 'খ');
        $this->bill($a, 100, 100, 'paid');
        $this->bill($b, 250, 0, 'unpaid');

        $all = $this->page($admin);
        $this->assertSame($this->taka(350), $this->tile($all, 'মোট বিক্রি')[0]);
        $this->assertTrue(str_contains($all, 'সকল বিক্রেতা'), "missing: " . 'সকল বিক্রেতা');
        $this->assertTrue(str_contains($all, 'id="sellerFilter"'), "missing: " . 'id="sellerFilter"');

        $one = $this->page($admin, '?seller_id=' . $b->id);
        $this->assertSame($this->taka(250), $this->tile($one, 'মোট বিক্রি')[0]);
        $this->assertTrue(str_contains($one, 'খ বুকশপ'), "missing: " . 'খ বুকশপ');
        preg_match('#<header class="sp-head">.*?</header>#su', $one, $banner);
        $this->assertNotEmpty($banner);
        $this->assertFalse(str_contains($banner[0], $admin->email) || str_contains($banner[0], $admin->phone), "the banner shows the picked seller, not the admin's own contact");

        $customer = $this->user('customer', 'পাঠক');
        foreach ([99999, $customer->id] as $bad) {
            $this->assertSame($this->taka(350), $this->tile($this->page($admin, '?seller_id=' . $bad), 'মোট বিক্রি')[0], "seller_id {$bad} is ignored");
        }
        $this->actingAs($admin)->get('/seller/dashboard?seller_id=abc')->assertSessionHasErrors('seller_id');
    }

    public function test_the_number_of_queries_does_not_grow_with_the_number_of_bills(): void
    {
        $seller = $this->user('seller', 'রহিম');
        $queries = function () use ($seller): int {
            DB::enableQueryLog();
            DB::flushQueryLog();
            $this->actingAs($seller)->get('/seller/dashboard')->assertOk();
            $n = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $n;
        };

        for ($i = 0; $i < 2; $i++) {
            $this->bill($seller, 100, 100, 'paid');
        }
        $few = $queries();

        for ($i = 0; $i < 600; $i++) {   // more than one chunk of 500
            $this->bill($seller, 100, 100, 'paid');
        }
        $many = $queries();

        $this->assertLessThanOrEqual($few + 1, $many, "2 bills = {$few} queries, 602 bills = {$many}"); // +1: the second chunk of the copies counter
    }

    public function test_an_empty_dashboard_and_the_other_billing_pages_render_with_the_shared_header(): void
    {
        $seller = $this->user('seller', 'রহিম');
        $bill   = $this->bill($seller, 100, 100, 'paid');

        $this->actingAs($seller)->get('/seller/dashboard')->assertOk()->assertSee('রহিম বুকশপ');
        foreach (['/seller/bills', '/seller/bills/create', '/seller/accounts', "/seller/bills/{$bill->id}"] as $url) {
            $this->actingAs($seller)->get($url)->assertOk()->assertSee('সেলার মেনু', false);
        }

        $empty = $this->user('seller', 'নতুন');
        $this->actingAs($empty)->get('/seller/dashboard')->assertOk()->assertSee('এখনো কোনো বিল নেই');
    }

    public function test_quick_pay_from_the_dashboard_marks_the_bill_paid_and_only_for_the_owner(): void
    {
        $owner = $this->user('seller', 'মালিক');
        $other = $this->user('seller', 'অন্যজন');
        $bill  = $this->bill($owner, 500, 100, 'partial');

        $this->actingAs($other)->post("/seller/bills/{$bill->id}/quick-pay")->assertForbidden();
        $this->actingAs($owner)->post("/seller/bills/{$bill->id}/quick-pay")->assertRedirect();

        $bill->refresh();
        $this->assertSame('paid', $bill->payment_status);
        $this->assertEquals(500, $bill->paid_amount);
        $this->assertEquals(0, $bill->due_amount);
    }

    public function test_customers_and_guests_cannot_open_it(): void
    {
        $this->get('/seller/dashboard')->assertRedirect(route('login'));

        $customer = $this->user('customer', 'পাঠক');
        $this->actingAs($customer)->get('/seller/dashboard')->assertStatus(403);
    }
}

