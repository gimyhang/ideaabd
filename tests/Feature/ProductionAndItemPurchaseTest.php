<?php

namespace Tests\Feature;

use App\Models\BookProductionEvent;
use App\Models\Product;
use App\Models\PublisherPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Book\Models\Book;
use Modules\Inventory\Models\Inventory;
use Tests\TestCase;

class ProductionAndItemPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private ?User $adminUser = null;

    private function admin(): User
    {
        return $this->adminUser ??= User::create([
            'name' => 'Root', 'email' => 'prod@example.test', 'phone' => '01700000066',
            'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function book(string $title = 'Press Book'): Book
    {
        $b = new Book();
        $b->title = $title;
        $b->slug = \Illuminate\Support\Str::slug($title) . '-' . uniqid();
        $b->price = 100;
        $b->stock_quantity = 0;
        $b->is_active = true;
        $b->save();

        return $b;
    }

    private function stage(Book $book, string $stage, int $qty, string $date): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('admin.production.store'), [
            'book_id' => $book->id, 'stage' => $stage, 'quantity' => $qty, 'event_date' => $date, 'batch_no' => 'B1',
        ]);
    }

    public function test_same_title_can_be_bound_and_received_on_different_dates(): void
    {
        $this->actingAs($this->admin());
        $book = $this->book();

        $this->stage($book, 'printed', 1000, now()->subDays(10)->toDateString())->assertSessionHasNoErrors();
        $this->stage($book, 'printed', 500, now()->subDays(3)->toDateString())->assertSessionHasNoErrors();
        $this->stage($book, 'bound', 400, now()->subDays(8)->toDateString())->assertSessionHasNoErrors();
        $this->stage($book, 'bound', 700, now()->subDays(2)->toDateString())->assertSessionHasNoErrors();
        $this->stage($book, 'received', 300, now()->subDays(6)->toDateString())->assertSessionHasNoErrors();
        $this->stage($book, 'received', 600, now()->subDay()->toDateString())->assertSessionHasNoErrors();

        $row = app(\App\Services\BookProductionService::class)->summary()->first();
        $this->assertSame(1500, $row->printed);
        $this->assertSame(1100, $row->bound);
        $this->assertSame(900, $row->received);
        $this->assertSame(400, $row->at_press);   // 1500 - 1100
        $this->assertSame(200, $row->at_binder);  // 1100 - 900

        // Only the office-received copies are stock.
        $this->assertSame(900, (int) $book->fresh()->stock_quantity);
        $this->assertSame(900, (int) Inventory::where('book_id', $book->id)->sum('quantity'));
    }

    public function test_date_window_counts_only_activity_in_range_but_balances_stay_lifetime(): void
    {
        $this->actingAs($this->admin());
        $book = $this->book();
        $this->stage($book, 'printed', 1000, now()->subDays(20)->toDateString());
        $this->stage($book, 'printed', 200, now()->subDays(2)->toDateString());
        $this->stage($book, 'bound', 600, now()->subDays(15)->toDateString());

        $row = app(\App\Services\BookProductionService::class)
            ->summary(now()->subDays(5)->toDateString(), now()->toDateString())->first();

        $this->assertSame(200, $row->printed);
        $this->assertSame(0, $row->bound);
        $this->assertSame(600, $row->at_press);
    }

    public function test_cannot_bind_or_receive_more_than_is_waiting(): void
    {
        $this->actingAs($this->admin());
        $book = $this->book();
        $this->stage($book, 'printed', 100, now()->toDateString());

        $this->stage($book, 'bound', 101, now()->toDateString())->assertSessionHasErrors('quantity');
        $this->stage($book, 'bound', 100, now()->toDateString())->assertSessionHasNoErrors();
        $this->stage($book, 'received', 101, now()->toDateString())->assertSessionHasErrors('quantity');
        $this->assertSame(0, (int) $book->fresh()->stock_quantity);
        $this->assertSame(2, BookProductionEvent::count());
    }

    public function test_removing_received_entry_takes_stock_back_and_chain_is_protected(): void
    {
        $this->actingAs($this->admin());
        $book = $this->book();
        $this->stage($book, 'printed', 100, now()->toDateString());
        $this->stage($book, 'bound', 100, now()->toDateString());
        $this->stage($book, 'received', 60, now()->toDateString());

        $printed = BookProductionEvent::where('stage', 'printed')->first();
        $this->delete(route('admin.production.destroy', $printed->id))->assertSessionHasErrors('event');
        $this->assertSame(3, BookProductionEvent::count());

        $received = BookProductionEvent::where('stage', 'received')->first();
        $this->delete(route('admin.production.destroy', $received->id))->assertSessionHasNoErrors();
        $this->assertSame(0, (int) $book->fresh()->stock_quantity);
        $this->assertSame(0, (int) Inventory::where('book_id', $book->id)->sum('quantity'));
    }

    public function test_future_dates_are_rejected_and_page_renders(): void
    {
        $this->actingAs($this->admin());
        $book = $this->book();
        $this->stage($book, 'printed', 10, now()->addDays(3)->toDateString())->assertSessionHasErrors('event_date');
        $this->stage($book, 'printed', 10, now()->toDateString());

        $this->get(route('admin.production.index'))->assertOk()->assertSee('Press Book')->assertSee('Printed');
        $this->getJson(route('admin.production.books', ['q' => 'Press']))->assertOk()->assertJsonFragment(['at_press' => 10]);
    }

    public function test_live_endpoint_returns_totals_and_store_stock(): void
    {
        $this->actingAs($this->admin());
        $book = $this->book();
        $this->stage($book, 'printed', 10, now()->toDateString());

        $this->getJson(route('admin.production.live'))->assertOk()
            ->assertJsonPath('totals.printed', 10)
            ->assertJsonPath('totals.at_press', 10)
            ->assertJsonStructure(['store' => ['stock', 'warehouse', 'showroom'], 'rows' => [['id', 'warehouse', 'showroom']], 'updated_at']);
    }

    public function test_item_stock_is_grouped_by_type_and_category(): void
    {
        $this->actingAs($this->admin());
        $pen = \App\Models\ProductCategory::create(['type' => 'stationery', 'name' => 'Pens', 'slug' => 'pens-t']);
        $cam = \App\Models\ProductCategory::create(['type' => 'electronics', 'name' => 'Cameras', 'slug' => 'cameras-t']);
        \App\Models\Product::create(['type' => 'stationery', 'category_id' => $pen->id, 'title' => 'Blue Pen', 'slug' => 'blue-pen-t', 'price' => 10, 'stock' => 4, 'is_active' => true]);
        \App\Models\Product::create(['type' => 'electronics', 'category_id' => $cam->id, 'title' => 'Webcam', 'slug' => 'webcam-t', 'price' => 900, 'stock' => 9, 'is_active' => true]);

        $this->get(route('admin.purchases.items.stock'))->assertOk()
            ->assertSeeInOrder(['Electronics', 'Cameras', 'Webcam', 'Stationery', 'Pens', 'Blue Pen']);
        $this->get(route('admin.purchases.items.stock', ['category_id' => $pen->id]))->assertOk()
            ->assertSee('Blue Pen')->assertDontSee('Webcam');
    }

    public function test_typed_name_of_existing_item_reuses_the_product(): void
    {
        $this->actingAs($this->admin());
        $p = \App\Models\Product::create(['type' => 'electronics', 'title' => 'Webcam Pro', 'slug' => 'webcam-pro-d', 'price' => 900, 'stock' => 2, 'is_active' => true]);

        $this->post(route('admin.purchases.items.store'), [
            'type' => 'electronics', 'purchase_no' => 'ELC-DUP-1', 'purchase_date' => now()->toDateString(), 'vendor_name' => 'V',
            'items' => [['title' => '  webcam pro ', 'quantity' => 3, 'cost_price' => 500]],
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, \App\Models\Product::where('type', 'electronics')->count());
        $this->assertSame(5, (int) $p->fresh()->stock);
    }

    public function test_electronics_form_embeds_live_stock_list(): void
    {
        $this->actingAs($this->admin());
        $cam = \App\Models\ProductCategory::create(['type' => 'electronics', 'name' => 'Cameras', 'slug' => 'cameras-e']);
        \App\Models\Product::create(['type' => 'electronics', 'category_id' => $cam->id, 'title' => 'Webcam Pro', 'slug' => 'webcam-pro-e', 'price' => 900, 'stock' => 9, 'is_active' => true]);
        \App\Models\Product::create(['type' => 'stationery', 'title' => 'Red Pen', 'slug' => 'red-pen-e', 'price' => 10, 'stock' => 3, 'is_active' => true]);

        $this->get(route('admin.purchases.electronics'))->assertOk()->assertSee('piStockBody', false)->assertSee('Webcam Pro')->assertDontSee('Red Pen');
        $this->get(route('admin.purchases.items.stock-list', ['type' => 'electronics', 'q' => 'nomatch']))->assertOk()->assertDontSee('Webcam Pro');
        $this->get(route('admin.purchases.items.stock-list', ['type' => 'electronics', 'category_id' => $cam->id, 'buy' => 1]))->assertOk()->assertSee('Webcam Pro')->assertSee('pi-buy', false)->assertSee('pi-tab', false);
        $this->get(route('admin.purchases.items.stock-list', ['type' => 'electronics', 'category_id' => $cam->id]))->assertOk()->assertDontSee('pi-buy', false);
        $this->get(route('admin.purchases.items.stock-list', ['category_id' => 'none']))->assertOk()->assertSee('Red Pen')->assertDontSee('Webcam Pro');
    }

    public function test_duplicates_are_detected_and_merged(): void
    {
        $this->actingAs($this->admin());
        $a = \App\Models\Product::create(['type' => 'stationery', 'title' => 'Blue  Pen', 'slug' => 'blue-pen-m1', 'price' => 10, 'stock' => 4, 'is_active' => true]);
        $b = \App\Models\Product::create(['type' => 'stationery', 'title' => 'blue-pen', 'slug' => 'blue-pen-m2', 'price' => 10, 'stock' => 6, 'is_active' => true]);
        $other = \App\Models\Product::create(['type' => 'electronics', 'title' => 'Blue Pen', 'slug' => 'blue-pen-m3', 'price' => 10, 'stock' => 1, 'is_active' => true]);

        $this->get(route('admin.purchases.items.stock-list'))->assertOk()->assertSee('1 duplicate group', false)->assertSee('pi-merge', false);

        $this->postJson(route('admin.purchases.items.merge'), ['keep_id' => $a->id, 'merge_ids' => [$other->id]])->assertStatus(422);
        $this->postJson(route('admin.purchases.items.merge'), ['keep_id' => $a->id, 'merge_ids' => [$b->id]])->assertOk();

        $this->assertNull(\App\Models\Product::find($b->id));
        $this->assertSame(10, (int) $a->fresh()->stock);
        $this->get(route('admin.purchases.items.stock-list'))->assertOk()->assertDontSee('duplicate group', false);
    }

    public function test_guests_are_blocked_from_production(): void
    {
        $this->get(route('admin.production.index'))->assertRedirect(route('login'));
        $this->post(route('admin.production.store'), [])->assertRedirect(route('login'));
    }

    public function test_stationery_purchase_adds_stock_creates_items_and_reverses_on_delete(): void
    {
        $this->actingAs($this->admin());
        $this->get(route('admin.purchases.stationery'))->assertOk()->assertSee('Stationery');
        $this->get(route('admin.purchases.electronics'))->assertOk();

        $existing = Product::create(['type' => 'stationery', 'title' => 'A4 Paper', 'slug' => 'a4-paper', 'price' => 10, 'stock' => 5, 'is_active' => true]);

        $this->post(route('admin.purchases.items.store'), [
            'type' => 'stationery', 'purchase_no' => 'STN-T-1', 'purchase_date' => now()->toDateString(),
            'vendor_name' => 'Paper Mart', 'paid_amount' => 100, 'payment_method' => 'cash',
            'items' => [
                ['product_id' => $existing->id, 'quantity' => 20, 'cost_price' => 8, 'sale_price' => 12],
                ['title' => 'Blue Pen', 'quantity' => 50, 'cost_price' => 3],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(25, (int) $existing->fresh()->stock);
        $this->assertEquals(12, (float) $existing->fresh()->price);
        $pen = Product::where('title', 'Blue Pen')->firstOrFail();
        $this->assertSame('stationery', $pen->type);
        $this->assertSame(50, (int) $pen->stock);

        $purchase = PublisherPurchase::where('purchase_no', 'STN-T-1')->firstOrFail();
        $this->assertSame('stationery', $purchase->purchase_category);
        $this->assertEquals(310, (float) $purchase->grand_total); // 20*8 + 50*3
        $this->assertEquals(100, (float) $purchase->paid_amount);
        $this->assertSame('partial', $purchase->payment_status);

        $this->get(route('admin.purchases.items.stock'))->assertOk()->assertSee('Blue Pen')->assertSee('A4 Paper');
        $this->get(route('admin.purchases.index', ['category' => 'stationery']))->assertOk();
        $this->get(route('admin.purchases.edit', $purchase->id))->assertRedirect(route('admin.purchases.show', $purchase->id));

        $this->delete(route('admin.purchases.destroy', $purchase->id));
        $this->assertSame(5, (int) $existing->fresh()->stock);
        $this->assertSame(0, (int) $pen->fresh()->stock);
    }

    public function test_item_purchase_validation(): void
    {
        $this->actingAs($this->admin());
        $base = ['type' => 'electronics', 'purchase_no' => 'ELC-T-1', 'purchase_date' => now()->toDateString(), 'vendor_name' => 'Volt Ltd'];

        $this->post(route('admin.purchases.items.store'), $base + ['items' => [['quantity' => 1, 'cost_price' => 5]]])
            ->assertSessionHasErrors('items.0.title');
        $this->post(route('admin.purchases.items.store'), $base + ['paid_amount' => 999, 'items' => [['title' => 'Cable', 'quantity' => 1, 'cost_price' => 5]]])
            ->assertSessionHasErrors('paid_amount');
        $this->post(route('admin.purchases.items.store'), $base + ['discount' => 99, 'items' => [['title' => 'Cable', 'quantity' => 1, 'cost_price' => 5]]])
            ->assertSessionHasErrors('discount');
        $this->assertSame(0, Product::count());
        $this->assertSame(0, PublisherPurchase::count());

        $this->getJson(route('admin.purchases.items.products', ['type' => 'electronics', 'q' => 'zzz']))->assertOk()->assertJson([]);
    }

    public function test_monthly_report_and_sidebar_include_new_categories(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('admin.purchases.items.store'), [
            'type' => 'electronics', 'purchase_no' => 'ELC-M-1', 'purchase_date' => now()->toDateString(), 'vendor_name' => 'Volt Ltd',
            'items' => [['title' => 'Charger', 'quantity' => 2, 'cost_price' => 100]],
        ])->assertSessionHasNoErrors();

        $this->get(route('admin.purchases.monthly-report'))->assertOk()->assertSee('Electronics')->assertSee('ELC-M-1');
        $this->get(route('admin.purchases.index'))->assertOk()
            ->assertSee(route('admin.purchases.stationery'), false)
            ->assertSee(route('admin.production.index'), false);
    }
}
