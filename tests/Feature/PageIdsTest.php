<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Book\Models\Book;
use Modules\Publisher\Models\Publisher;
use Tests\TestCase;

/**
 * A repeated id="" makes document.getElementById() and label[for] pick the wrong element, and the page scripts then act on
 * the wrong thing. The pages rebuilt in this change must not have any.
 */
class PageIdsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $email, string $phone): User
    {
        return User::create([
            'name' => 'Test ' . $role, 'email' => $email, 'phone' => $phone, 'password' => Hash::make('Zx9#Tq!mW4$vLr7'),
            'role' => $role, 'reg_type' => $role, 'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function duplicates(string $html): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>' . $html);
        $seen = [];
        foreach ((new \DOMXPath($dom))->query('//*[@id]') as $node) {
            $seen[$node->getAttribute('id')] = ($seen[$node->getAttribute('id')] ?? 0) + 1;
        }

        return array_keys(array_filter($seen, fn ($n) => $n > 1));
    }

    public function test_the_rebuilt_pages_have_no_repeated_ids(): void
    {
        $admin  = $this->user('admin', 'a@example.test', '01700000001');
        $seller = $this->user('seller', 's@example.test', '01700000002');
        $pubU   = $this->user('publisher', 'p@example.test', '01700000003');
        $pub    = Publisher::create(['name' => 'প্রকাশনী', 'slug' => 'p1', 'email' => 'p@example.test', 'phone' => '01700000003', 'is_active' => true, 'is_verified' => true]);
        (new Book())->forceFill(['title' => 'বই', 'slug' => 'b1', 'publisher_id' => $pub->id, 'price' => 100, 'stock_quantity' => 2, 'is_active' => true])->save();
        Bill::create(['seller_id' => $seller->id, 'customer_name' => 'গ্রাহক', 'items' => [], 'subtotal' => 10, 'total' => 10, 'paid_amount' => 0, 'due_amount' => 10, 'payment_status' => 'unpaid']);

        $pages = [
            [$admin,  '/admin/sms'],
            [$admin,  '/admin/backup'],
            [$seller, '/seller/dashboard'],
            [$pubU,   '/publisher/dashboard'],
            [$pubU,   '/publisher/dashboard?tab=add-book'],
        ];

        $problems = [];
        foreach ($pages as [$who, $url]) {
            $dups = $this->duplicates($this->actingAs($who)->get($url)->assertOk()->getContent());
            if ($dups) {
                $problems[] = $url . ' → ' . implode(', ', $dups);
            }
        }

        $this->assertSame([], $problems, 'repeated ids: ' . implode(' | ', $problems));
    }
}
