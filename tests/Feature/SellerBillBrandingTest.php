<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use App\Support\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** The seller bill page shows the logo and company details saved in the bill settings, with short English labels. */
class SellerBillBrandingTest extends TestCase
{
    use RefreshDatabase;

    private const LOGO = 'images/settings/test_bill_logo.png';

    protected function tearDown(): void
    {
        @unlink(public_path(self::LOGO));
        parent::tearDown();
    }

    private function seller(): User
    {
        return User::create([
            'name' => 'Seller One', 'email' => 's1@example.test', 'phone' => '01700000101',
            'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => 'seller', 'reg_type' => 'seller',
            'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function bill(User $seller, string $type = 'invoice'): Bill
    {
        return Bill::create([
            'seller_id' => $seller->id, 'type' => $type, 'customer_name' => 'গ্রাহক',
            'items' => [['title' => 'বই', 'quantity' => 2, 'price' => 100]],
            'subtotal' => 200, 'total' => 200, 'paid_amount' => 50, 'due_amount' => 150, 'payment_status' => 'partial',
        ]);
    }

    private function saveSettings(array $settings): void
    {
        DB::table('admin_dashboard_settings')->updateOrInsert(
            ['key' => 'invoice_settings'],
            ['value' => json_encode($settings, JSON_UNESCAPED_UNICODE), 'created_at' => now(), 'updated_at' => now()]
        );
        SiteSetting::clearCache();
    }

    private function putLogoFile(): void
    {
        @mkdir(dirname(public_path(self::LOGO)), 0777, true);
        copy(public_path('images/logo.png'), public_path(self::LOGO));
    }

    public function test_the_logo_saved_in_the_bill_settings_is_the_one_on_the_bill(): void
    {
        $seller = $this->seller();
        $bill = $this->bill($seller);
        $this->putLogoFile();
        $this->saveSettings(['logo' => self::LOGO]);

        $html = $this->actingAs($seller)->get('/seller/bills/' . $bill->id)->assertOk()->getContent();

        $this->assertStringContainsString('src="' . asset(self::LOGO) . '?v=', $html);
    }

    public function test_a_replaced_logo_shows_at_once_even_when_the_file_name_is_reused(): void
    {
        $seller = $this->seller();
        $bill = $this->bill($seller);
        $this->putLogoFile();
        $this->saveSettings(['logo' => self::LOGO]);

        $first = $this->actingAs($seller)->get('/seller/bills/' . $bill->id)->getContent();
        touch(public_path(self::LOGO), time() + 120);           // the file was uploaded again under the same name
        clearstatcache();
        $second = $this->actingAs($seller)->get('/seller/bills/' . $bill->id)->getContent();

        preg_match('#' . preg_quote(asset(self::LOGO), '#') . '\?v=(\d+)#', $first, $a);
        preg_match('#' . preg_quote(asset(self::LOGO), '#') . '\?v=(\d+)#', $second, $b);
        $this->assertNotSame($a[1] ?? 'none', $b[1] ?? 'none', 'the browser must be told the logo changed');
    }

    public function test_a_missing_logo_file_falls_back_to_the_site_logo_instead_of_a_broken_image(): void
    {
        $seller = $this->seller();
        $bill = $this->bill($seller);
        $this->saveSettings(['logo' => 'images/settings/this_file_is_gone.png']);
        DB::table('admin_dashboard_settings')->insert(['key' => 'site_logo', 'value' => json_encode('images/logo.webp'), 'created_at' => now(), 'updated_at' => now()]);
        SiteSetting::clearCache();

        $html = $this->actingAs($seller)->get('/seller/bills/' . $bill->id)->assertOk()->getContent();

        $this->assertStringNotContainsString('this_file_is_gone', $html);
        $this->assertStringContainsString(asset('images/logo.webp') . '?v=', $html);
    }

    public function test_company_details_edited_in_the_bill_settings_reach_the_seller_bill(): void
    {
        $seller = $this->seller();
        $bill = $this->bill($seller);
        $this->saveSettings(['business_name' => 'Nova Books Ltd', 'tagline' => 'Read more', 'address' => 'Mirpur 10, Dhaka', 'phone' => '01999888777', 'email' => 'hello@nova.test']);

        $html = $this->actingAs($seller)->get('/seller/bills/' . $bill->id)->assertOk()->getContent();

        foreach (['Nova Books Ltd', 'Read more', 'Mirpur 10, Dhaka', '01999888777', 'hello@nova.test'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }

    public function test_an_old_copy_of_a_header_field_in_the_database_never_beats_the_edited_value(): void
    {
        $seller = $this->seller();
        $bill = $this->bill($seller);
        $this->saveSettings(['business_name' => 'New Name', 'company_name' => 'Old Name']);

        $html = $this->actingAs($seller)->get('/seller/bills/' . $bill->id)->getContent();

        $this->assertStringContainsString('New Name', $html);
        $this->assertStringNotContainsString('Old Name', $html);
    }

    public function test_bill_pages_use_short_english_labels(): void
    {
        $seller = $this->seller();
        $bill = $this->bill($seller);

        $show = $this->actingAs($seller)->get('/seller/bills/' . $bill->id)->assertOk()->getContent();
        foreach (['>Item</th>', '>Qty</th>', '>Rate</th>', '>Total</th>', 'Subtotal', 'Paid', 'Due', 'Invoice'] as $label) {
            $this->assertStringContainsString($label, $show);
        }
        foreach (['>পরিমাণ</th>', '>একক মূল্য</th>', 'উপমোট', 'সর্বমোট প্রদেয়'] as $old) {
            $this->assertStringNotContainsString($old, $show);
        }

        $accounts = $this->actingAs($seller)->get('/seller/accounts')->assertOk()->getContent();
        foreach (['>Bill No</th>', '>Customer</th>', '>Status</th>', '>Total</th>'] as $label) {
            $this->assertStringContainsString($label, $accounts);
        }

        $this->actingAs($seller)->get('/seller/bills/' . $bill->id . '/receipt')->assertOk()
            ->assertSee('>Item</th>', false)->assertSee('Subtotal:', false);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'a2@example.test', 'phone' => '01700000198', 'password' => Hash::make('Zx9#Tq!mW4$vLr7'),
            'role' => 'admin', 'reg_type' => 'admin', 'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    public function test_a_logo_uploaded_by_the_admin_appears_on_the_seller_bill(): void
    {
        $seller = $this->seller();
        $bill = $this->bill($seller);
        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

        $res = $this->actingAs($this->admin())->postJson(route('admin.accounting.settings.update'), [
            'business_name' => 'Nova Books Ltd', 'logo_base64' => $png,
        ])->assertOk()->assertJson(['success' => true]);

        $saved = $res->json('settings.logo');
        $this->assertMatchesRegularExpression('#^images/settings/invoice_logo_\d+\.png$#', $saved);
        $this->assertFileExists(public_path($saved));
        $this->assertArrayNotHasKey('company_name', $res->json('settings'), 'the header aliases are never stored');

        $html = $this->actingAs($seller)->get('/seller/bills/' . $bill->id)->assertOk()->getContent();
        $this->assertStringContainsString('src="' . asset($saved) . '?v=', $html);
        $this->assertStringContainsString('Nova Books Ltd', $html);

        @unlink(public_path($saved));
        @unlink(storage_path('app/public/settings/' . basename($saved)));
    }

    public function test_document_types_are_named_in_one_short_english_word(): void
    {
        $seller = $this->seller();

        $this->assertSame('Invoice', $this->bill($seller, 'invoice')->type_label);
        $this->assertSame('Challan', $this->bill($seller, 'challan')->type_label);
        $this->assertSame('Quotation', $this->bill($seller, 'quotation')->type_label);
    }

    public function test_the_seller_menu_and_the_admin_sidebar_use_english_names_for_bills(): void
    {
        $seller = $this->seller();

        $menu = $this->actingAs($seller)->get('/seller/dashboard')->assertOk()->getContent();
        foreach (['New Bill', 'Bills', 'Accounts'] as $name) {
            $this->assertStringContainsString($name, $menu);
        }
        $this->assertStringNotContainsString('বিল তালিকা', $menu);

        $admin = User::create([
            'name' => 'Admin', 'email' => 'a1@example.test', 'phone' => '01700000199', 'password' => Hash::make('Zx9#Tq!mW4$vLr7'),
            'role' => 'admin', 'reg_type' => 'admin', 'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
        $side = $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->getContent();
        foreach (['Invoices', 'Quotations'] as $name) {
            $this->assertStringContainsString($name, $side);
        }
    }
}
