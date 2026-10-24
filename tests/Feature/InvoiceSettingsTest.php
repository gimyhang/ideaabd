<?php

namespace Tests\Feature;

use App\Models\AdminDashboardSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InvoiceSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Inv Admin', 'email' => 'inv-admin@example.test', 'phone' => '01710000022',
            'password' => Hash::make('test-password'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    public function test_saving_the_sender_tab_keeps_the_logo_bank_details_and_receipt_styling(): void
    {
        AdminDashboardSetting::updateOrCreate(['key' => 'invoice_settings'], ['value' => [
            'business_name' => 'Old Name', 'logo' => 'images/settings/invoice_logo_1.png', 'bank_name' => 'Sonali Bank', 'bank_account_no' => '123456',
            'receipt_primary_color' => '#123456', 'quotation_default_terms' => 'Pay in 7 days', 'mfs_qr_image' => 'images/settings/mfs.png',
        ]]);

        $this->actingAs($this->admin())->post(route('admin.system-settings.update'), [
            'notice_type' => 'info',
            'invoice_sender_name' => 'New Publisher', 'invoice_sender_address' => 'Rangpur', 'invoice_sender_phone' => '01700000000',
            'invoice_sender_email' => 'new@example.test', 'invoice_sender_website' => 'www.example.test',
            'invoice_title' => 'Cash Memo', 'invoice_terms' => 'Check on delivery', 'invoice_footer' => 'Thanks',
        ])->assertSessionHasNoErrors();

        $saved = AdminDashboardSetting::where('key', 'invoice_settings')->firstOrFail()->value;

        // untouched settings survive (this save used to replace the whole record with eight keys)
        $this->assertSame('images/settings/invoice_logo_1.png', $saved['logo']);
        $this->assertSame('Sonali Bank', $saved['bank_name']);
        $this->assertSame('#123456', $saved['receipt_primary_color']);
        $this->assertSame('Pay in 7 days', $saved['quotation_default_terms']);
        $this->assertSame('images/settings/mfs.png', $saved['mfs_qr_image']);

        // and the sender is the same business on every document
        $this->assertSame('New Publisher', $saved['sender_name']);
        $this->assertSame('New Publisher', $saved['business_name']);
        $this->assertSame('Rangpur', $saved['address']);
        $this->assertSame('01700000000', $saved['phone']);
        $this->assertSame('new@example.test', $saved['email']);
        $this->assertSame('Cash Memo', $saved['invoice_title']);
    }

    public function test_a_save_that_leaves_a_field_out_keeps_the_stored_value(): void
    {
        AdminDashboardSetting::updateOrCreate(['key' => 'invoice_settings'], ['value' => ['sender_footer_x' => 1, 'invoice_footer' => 'Stored footer', 'sender_name' => 'Stored']]);

        $this->actingAs($this->admin())->post(route('admin.system-settings.update'), ['notice_type' => 'info', 'invoice_sender_name' => 'Changed'])->assertSessionHasNoErrors();

        $saved = AdminDashboardSetting::where('key', 'invoice_settings')->firstOrFail()->value;
        $this->assertSame('Changed', $saved['sender_name']);
        $this->assertSame('Stored footer', $saved['invoice_footer']);
    }
}
