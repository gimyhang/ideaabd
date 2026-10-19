<?php

namespace Tests\Feature;

use App\Models\PublisherPayment;
use App\Models\PublisherPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Book\Models\Book;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\InventoryMovementLog;
use Modules\Inventory\Models\Location;
use Modules\Inventory\Services\InventoryService;
use Tests\TestCase;

class InventoryAdminTest extends TestCase
{
    use RefreshDatabase;

    private ?User $adminUser = null;

    private function admin(): User
    {
        return $this->adminUser ??= User::create([
            'name' => 'Root', 'email' => 'inv@example.test', 'phone' => '01700000055',
            'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function book(int $stock = 0, string $title = 'Sample Book'): Book
    {
        $b = new Book();
        $b->title = $title;
        $b->slug = \Illuminate\Support\Str::slug($title) . '-' . uniqid();
        $b->price = 100;
        $b->stock_quantity = $stock;
        $b->is_active = true;
        $b->save();

        return $b;
    }

    public function test_guests_cannot_reach_inventory_pages_or_api(): void
    {
        foreach (['/admin/inventory', '/admin/inventory/locations', '/admin/inventory/transfers', '/admin/inventory/wastages', '/admin/inventory/alerts', '/admin/inventory/logs', '/api/inventory/overview', '/api/inventory/locations'] as $path) {
            $this->get($path)->assertRedirect(route('login'));
        }
        $this->post('/api/inventory/production-in', [])->assertStatus(302);
        $this->post('/api/inventory/sales-out', [])->assertStatus(302);
    }

    public function test_all_inventory_pages_render_for_admin(): void
    {
        $this->actingAs($this->admin());
        $loc = Location::create(['name' => 'Main', 'code' => 'WH', 'type' => 'warehouse', 'is_central' => true, 'is_active' => true]);
        $book = $this->book(5);

        foreach (['index', 'locations', 'transfers', 'wastages', 'alerts', 'logs'] as $page) {
            $this->get(route("admin.inventory.$page"))->assertOk()->assertSee('iv-tabs', false);
        }
        $this->getJson(route('admin.inventory.books', ['q' => 'Sample']))->assertOk()->assertJsonFragment(['id' => $book->id]);
    }

    public function test_sidebar_lists_inventory_items(): void
    {
        $this->actingAs($this->admin())->get(route('admin.inventory.index'))
            ->assertSee(route('admin.inventory.locations'), false)
            ->assertSee(route('admin.inventory.transfers'), false);
    }

    public function test_stock_grid_filters_and_csv_export(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)
            ->get(route('admin.inventory.index', ['q' => 'zzz-none', 'low' => 1, 'empty' => 1, 'sort' => 'stock_quantity', 'dir' => 'desc']))
            ->assertOk()->assertSee('ivLive', false)->assertSee('No books');

        $this->actingAs($admin)->get(route('admin.inventory.export'))
            ->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_opening_balance_allocates_catalogue_stock_once(): void
    {
        $book = $this->book(12);
        $this->actingAs($this->admin())->post(route('admin.inventory.opening-balance'))->assertSessionHas('success');

        $central = Location::central()->first();
        $this->assertNotNull($central);
        $this->assertSame(12, Inventory::where('book_id', $book->id)->where('location_id', $central->id)->value('quantity'));
        $this->assertSame('opening_balance', InventoryMovementLog::first()->type);

        $this->post(route('admin.inventory.opening-balance'))->assertSessionHas('info');
        $this->assertSame(1, Inventory::count());
    }

    public function test_location_create_update_and_single_central(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('admin.inventory.locations.store'), ['name' => 'A', 'code' => 'A1', 'type' => 'warehouse', 'is_central' => 1])->assertSessionHasNoErrors();
        $this->post(route('admin.inventory.locations.store'), ['name' => 'B', 'code' => 'B1', 'type' => 'warehouse', 'is_central' => 1])->assertSessionHasNoErrors();
        $this->assertSame(1, Location::where('is_central', true)->count());
        $this->assertSame('B1', Location::central()->value('code'));

        $a = Location::where('code', 'A1')->first();
        $this->put(route('admin.inventory.locations.update', $a->id), ['name' => 'A2', 'code' => 'A1', 'type' => 'showroom'])->assertSessionHasNoErrors();
        $this->assertSame('showroom', $a->fresh()->type);
        $this->assertFalse($a->fresh()->is_central);

        $this->post(route('admin.inventory.locations.store'), ['name' => 'C', 'code' => 'A1', 'type' => 'showroom'])->assertSessionHasErrors('code');
    }

    public function test_stock_in_transfer_receive_and_wastage_flow(): void
    {
        $this->actingAs($this->admin());
        $wh = Location::create(['name' => 'WH', 'code' => 'WH', 'type' => 'warehouse', 'is_central' => true, 'is_active' => true]);
        $shop = Location::create(['name' => 'Shop', 'code' => 'SH', 'type' => 'showroom', 'is_active' => true]);
        $book = $this->book(0);

        $this->post(route('admin.inventory.stock-in'), ['book_id' => $book->id, 'quantity' => 50, 'location_id' => $wh->id])->assertSessionHas('success');
        $this->assertSame(50, Inventory::where('book_id', $book->id)->where('location_id', $wh->id)->value('quantity'));

        $this->post(route('admin.inventory.transfers.store'), [
            'from_location_id' => $wh->id, 'to_location_id' => $shop->id,
            'items' => [['book_id' => $book->id, 'quantity' => 20]],
        ])->assertSessionHasNoErrors();
        $transfer = \Modules\Inventory\Models\StockTransfer::firstOrFail();

        $this->get(route('admin.inventory.transfers.show', $transfer->id))->assertOk()->assertSee($transfer->challan_no);
        $this->post(route('admin.inventory.transfers.receive', $transfer->id))->assertSessionHas('success');
        $this->assertSame(30, Inventory::where('book_id', $book->id)->where('location_id', $wh->id)->value('quantity'));
        $this->assertSame(20, Inventory::where('book_id', $book->id)->where('location_id', $shop->id)->value('quantity'));

        $this->post(route('admin.inventory.transfers.store'), [
            'from_location_id' => $wh->id, 'to_location_id' => $shop->id,
            'items' => [['book_id' => $book->id, 'quantity' => 999]],
        ])->assertSessionHas('error');

        $this->post(route('admin.inventory.wastages.store'), ['book_id' => $book->id, 'location_id' => $shop->id, 'quantity' => 3, 'reason' => 'water_damage'])->assertSessionHas('success');
        $this->assertSame(17, Inventory::where('book_id', $book->id)->where('location_id', $shop->id)->value('quantity'));

        $this->put(route('admin.inventory.alerts.update', Inventory::where('location_id', $shop->id)->value('id')), ['min_alert_quantity' => 25])->assertSessionHasNoErrors();
        $this->get(route('admin.inventory.alerts'))->assertOk()->assertSee($book->title);
    }

    public function test_purchase_updates_location_stock_and_reverses_on_delete(): void
    {
        $this->actingAs($this->admin());
        $book = $this->book(0, 'Purchased Book');

        $this->post(route('admin.purchases.store'), [
            'purchase_category' => 'books', 'vendor_name' => 'Press One', 'purchase_no' => 'PUR-INV-1',
            'purchase_date' => now()->toDateString(), 'payment_type' => 'credit',
            'items' => [['title' => 'Purchased Book', 'book_id' => $book->id, 'quantity' => 40, 'cost_price' => 50]],
        ])->assertSessionHasNoErrors();

        $central = Location::central()->first();
        $this->assertNotNull($central);
        $this->assertSame(40, (int) Book::find($book->id)->stock_quantity);
        $this->assertSame(40, Inventory::where('book_id', $book->id)->where('location_id', $central->id)->value('quantity'));
        $this->assertSame('purchase_in', InventoryMovementLog::latest('id')->first()->type);

        $purchase = PublisherPurchase::firstOrFail();
        $this->delete(route('admin.purchases.destroy', $purchase));
        $this->assertSame(0, (int) Book::find($book->id)->stock_quantity);
        $this->assertSame(0, Inventory::where('book_id', $book->id)->where('location_id', $central->id)->value('quantity'));
    }

    public function test_purchase_overpayment_is_blocked_and_numbers_are_unique(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('admin.purchases.store'), [
            'purchase_category' => 'raw_materials', 'vendor_name' => 'Paper House', 'purchase_no' => 'PUR-OP-1',
            'purchase_date' => now()->toDateString(), 'payment_type' => 'credit',
            'items' => [['title' => 'Paper', 'quantity' => 10, 'cost_price' => 50]],
        ])->assertSessionHasNoErrors();
        $p = PublisherPurchase::firstOrFail();

        $this->post(route('admin.purchases.payments.store'), [
            'payment_target' => 'specific_invoice', 'purchase_id' => $p->id,
            'payment_date' => now()->toDateString(), 'amount' => 9999, 'payment_method' => 'cash',
        ])->assertSessionHas('error');
        $this->assertSame(0, PublisherPayment::count());

        $this->post(route('admin.purchases.payments.store'), [
            'payment_target' => 'specific_invoice', 'purchase_id' => $p->id,
            'payment_date' => now()->toDateString(), 'amount' => 500, 'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();
        $this->assertSame('paid', $p->fresh()->payment_status);

        $this->post(route('admin.purchases.store'), [
            'purchase_category' => 'raw_materials', 'vendor_name' => 'Paper House', 'purchase_no' => 'PUR-OP-2',
            'purchase_date' => now()->toDateString(), 'payment_type' => 'partial', 'paid_amount' => 9999,
            'items' => [['title' => 'Paper', 'quantity' => 1, 'cost_price' => 50]],
        ])->assertSessionHasErrors('paid_amount');
    }

    public function test_purchase_search_filters_are_not_bypassed(): void
    {
        $this->actingAs($this->admin());
        foreach ([['PUR-S-1', 'raw_materials'], ['PUR-S-2', 'other']] as [$no, $cat]) {
            $this->post(route('admin.purchases.store'), [
                'purchase_category' => $cat, 'vendor_name' => 'Same Vendor', 'purchase_no' => $no,
                'purchase_date' => now()->toDateString(), 'payment_type' => 'credit',
                'items' => [['title' => 'Item', 'quantity' => 1, 'cost_price' => 10]],
            ])->assertSessionHasNoErrors();
        }

        $res = $this->get(route('admin.purchases.index', ['category' => 'raw_materials', 'search' => 'Same Vendor']));
        $res->assertOk();
        $this->assertSame(['PUR-S-1'], $res->viewData('purchases')->pluck('purchase_no')->all());
    }

    public function test_service_mirror_clamps_at_zero_and_is_noop_for_unknown_book(): void
    {
        $svc = app(InventoryService::class);
        $book = $this->book(5);
        $svc->mirrorBookStock($book->id, 5, 'purchase_in');
        $svc->mirrorBookStock($book->id, -50, 'purchase_reversal');
        $this->assertSame(0, (int) Inventory::where('book_id', $book->id)->value('quantity'));
        $this->assertNull($svc->mirrorBookStock(999999, 5, 'purchase_in'));
    }

    public function test_monthly_report_renders_with_data(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('admin.purchases.store'), [
            'purchase_category' => 'raw_materials', 'vendor_name' => 'Paper House', 'purchase_no' => 'PUR-M-1',
            'purchase_date' => now()->toDateString(), 'payment_type' => 'credit',
            'items' => [['title' => 'Paper', 'quantity' => 2, 'cost_price' => 50]],
        ])->assertSessionHasNoErrors();

        $res = $this->get(route('admin.purchases.monthly-report'));
        $res->assertOk()->assertSee('purchases-report.css', false)->assertSee('PUR-M-1')->assertSee('Balance')->assertSee('Materials');
        $this->get(route('admin.purchases.monthly-report', ['month' => 1, 'year' => 2020]))->assertOk()->assertSee('January 2020');
    }
}
