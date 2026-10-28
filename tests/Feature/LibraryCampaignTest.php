<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\EventCampaignAdminController;
use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LibraryCampaignTest extends TestCase
{
    use RefreshDatabase;

    private function campaign(): EventCampaign
    {
        return EventCampaign::create([
            'title' => 'Library', 'slug' => 'pathagar', 'type' => 'library', 'is_active' => true,
            'has_fee_or_donation' => false, 'fee_amount' => 0, 'custom_fields' => [],
            'form_settings' => ['is_library_form' => true, 'requires_approval' => true],
        ]);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'institution_or_org' => 'Test Pathagar', 'library_type' => 'গণপাঠাগার / পাবলিক লাইব্রেরি',
            'reg_no' => 'R-1', 'established_year' => '২০১৮',
            'name' => 'Rep One', 'designation_or_class' => 'সভাপতি',
            'country_code' => '+880', 'phone' => '01711222333',
            'guardian_country_code' => '+880', 'guardian_phone' => '01811222333',
            'email' => 'lib@example.test', 'nid' => '১২৩৪৫৬৭৮৯০',
            'president_name' => 'Pres', 'president_country_code' => '+880', 'president_phone' => '01911222333',
            'secretary_name' => 'Sec', 'secretary_country_code' => '+91', 'secretary_phone' => '9876543210',
            'division' => 'Rangpur', 'district' => 'Rangpur', 'thana' => 'Sadar',
            'perm_village' => 'Village', 'perm_post_office' => 'PO 5400', 'library_address' => 'Full address',
            'current_book_count' => '৫০০', 'reader_count' => '১২০',
            'has_previous_books' => 'হ্যাঁ', 'previous_books_year' => '২০২৪', 'previous_books_count' => '৫০',
            'delivery_method' => 'এসএ পরিবহন', 'preferred_genres' => ['কবিতা ও ছড়া'],
            'scholarship_reason' => 'We need books.', 'declaration_agreed' => 'on',
        ], $over);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Lib Admin', 'email' => 'lib-admin@example.test', 'phone' => '01710000077',
            'password' => Hash::make('test-password'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    public function test_library_application_is_saved_as_pending_with_nid_and_country_aware_phones(): void
    {
        Http::fake();
        $this->campaign();

        $this->get('/pathagar')->assertOk();
        $this->post('/pathagar', $this->payload())->assertSessionHasNoErrors();

        $reg = EventRegistration::firstOrFail();
        $this->assertSame('pending', $reg->status);
        $this->assertSame('1234567890', $reg->form_data['nid'], 'the NID typed on the form is kept, in English digits');
        $this->assertSame('01911222333', $reg->form_data['president_phone']);
        $this->assertSame('+919876543210', $reg->form_data['secretary_phone'], 'a +91 number keeps its country code instead of being treated as a Bangladesh number');
        $this->get('/event-registration/' . $reg->registration_number . '/print')->assertOk();
    }

    public function test_same_mobile_cannot_register_twice(): void
    {
        Http::fake();
        $this->campaign();

        $this->post('/pathagar', $this->payload());
        $this->post('/pathagar', $this->payload(['institution_or_org' => 'Second']))->assertRedirect();

        $this->assertSame(1, EventRegistration::count());
    }

    public function test_required_committee_fields_are_enforced(): void
    {
        Http::fake();
        $this->campaign();

        $this->post('/pathagar', $this->payload(['president_name' => '']))->assertSessionHasErrors('president_name');
        $this->assertSame(0, EventRegistration::count());
    }

    public function test_admin_pages_and_print_work_for_a_library_application(): void
    {
        Http::fake();
        $c = $this->campaign();
        $this->post('/pathagar', $this->payload());
        Auth::logout();
        $reg = EventRegistration::firstOrFail();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.event-campaigns.show', $c->id))->assertOk()->assertSee('Test Pathagar');
        $this->actingAs($admin)->get(route('admin.event-campaigns.edit', $c->id))->assertOk();
        $this->actingAs($admin)->get(route('admin.event-campaigns.export', $c->id))->assertOk();
        $this->actingAs($admin)->get(route('admin.event-campaigns.registrations.print', $reg->id))
            ->assertOk()
            ->assertSee('Test Pathagar');
    }

    public function test_approval_and_rejection_sms_are_worded_for_the_library_not_the_festival(): void
    {
        Http::fake();
        $c = $this->campaign();
        $this->post('/pathagar', $this->payload());
        $reg = EventRegistration::with('campaign')->firstOrFail();

        $controller = app(EventCampaignAdminController::class);
        $approve = (new \ReflectionMethod($controller, 'approvalSmsText'))->invoke($controller, $reg);
        $reject = (new \ReflectionMethod($controller, 'rejectionSmsText'))->invoke($controller, $reg);

        $this->assertStringContainsString('পাঠাগারের নিবন্ধন', $approve);
        $this->assertStringContainsString($reg->registration_number, $approve);
        $this->assertStringContainsString('/card/' . $reg->registration_number, $approve);
        $this->assertStringNotContainsString('সাহিত্য উৎসব', $approve);
        $this->assertStringContainsString('পাঠাগারের নিবন্ধন', $reject);
        $this->assertStringNotContainsString('সাহিত্য উৎসব', $reject);

        // the festival keeps its own wording
        $writer = EventCampaign::create([
            'title' => 'RSU', 'slug' => 'rsutshab', 'type' => 'writer', 'is_active' => true,
            'has_fee_or_donation' => false, 'fee_amount' => 0, 'form_settings' => ['is_writer_form' => true],
        ]);
        $w = EventRegistration::create([
            'event_campaign_id' => $writer->id, 'registration_number' => 'RSU-1', 'name' => 'W', 'phone' => '01712000111', 'status' => 'pending',
        ]);
        $w->load('campaign');
        $this->assertStringContainsString('সাহিত্য উৎসব', (new \ReflectionMethod($controller, 'approvalSmsText'))->invoke($controller, $w));
    }
}
