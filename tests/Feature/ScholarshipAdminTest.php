<?php

namespace Tests\Feature;

use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ScholarshipAdminTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private function admin(): User
    {
        return User::create([
            'name' => 'Sch Admin', 'email' => 'sch-admin2@example.test', 'phone' => '01710000012',
            'password' => Hash::make('test-password'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    private function campaign(): EventCampaign
    {
        return EventCampaign::updateOrCreate(['slug' => 'jshikkhabritti'], [
            'title' => 'Sch', 'type' => 'scholarship', 'is_active' => true, 'has_fee_or_donation' => false, 'fee_amount' => 0,
            'form_settings' => ['is_scholarship_form' => true],
        ]);
    }

    private function registration(): EventRegistration
    {
        return EventRegistration::create([
            'event_campaign_id' => $this->campaign()->id, 'registration_number' => '2027-1', 'name' => 'Rina Akter', 'phone' => '01711222333',
            'status' => 'confirmed', 'payment_status' => 'free',
            'form_data' => ['national_id' => '1234567890', 'father_name' => 'F', 'father_status' => 'Alive', 'pay_method' => 'bKash', 'pay_account_no' => '01711222333', 'college_name' => 'Rangpur College', 'scholarship_reason' => 'I need help.', 'ssc_roll' => '1', 'ssc_gpa' => '5'],
        ]);
    }

    public function test_the_admin_list_has_an_english_preview_for_scholarship_applications(): void
    {
        $reg = $this->registration();
        $html = $this->actingAs($this->admin())->get(route('admin.event-campaigns.show', $this->campaign()->id))->assertOk()->getContent();

        $this->assertTrue(str_contains($html, 'id="viewDetailsModal' . $reg->id . '"'));
        $this->assertTrue(str_contains($html, 'schPreviewTitle' . $reg->id), 'the scholarship preview replaces the generic Bengali one');
        $missing = array_values(array_filter(['>Applicant</h6>', '>Family</h6>', '>Academic</h6>', '>Education</h6>', '>Address</h6>', '>Payment</h6>', '>Statement</h6>', '1234567890', 'Rangpur College'], fn ($n) => !str_contains($html, $n)));
        $this->assertSame([], $missing, 'missing in the preview: ' . implode(' | ', $missing));
        $this->assertFalse(str_contains($html, 'পূর্ণ বিবরণ প্রিভিউ'), 'no Bengali tooltip on a scholarship row');
        $this->assertTrue(str_contains($html, 'title="PDF"'));
    }

    public function test_the_admin_can_set_the_scholarship_header_and_number_prefix(): void
    {
        $c = $this->campaign();
        $admin = $this->actingAs($this->admin());

        $this->get(route('admin.event-campaigns.edit', $c->id))->assertOk()->assertSee('Header')->assertSee('name="number_prefix"', false);

        $admin->put(route('admin.event-campaigns.update', $c->id), [
            'title' => 'Sch', 'slug' => 'jshikkhabritti', 'type' => 'scholarship', 'form_settings_json' => json_encode(['is_scholarship_form' => true]),
            'sch_title' => 'Merit Award', 'sch_heading' => 'Grant', 'sch_session' => 'Session 2028', 'sch_logo_side' => 'hidden', 'number_prefix' => 'SCH28',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $fs = $c->fresh()->form_settings;
        $this->assertSame(['Merit Award', 'Grant', 'Session 2028', 'hidden', 'SCH28'], [$fs['sch_title'], $fs['sch_heading'], $fs['sch_session'], $fs['sch_logo_side'], $fs['number_prefix']]);
        $this->assertTrue($fs['is_scholarship_form']);

        // a bad prefix is refused; an empty field goes back to the default
        $admin->put(route('admin.event-campaigns.update', $c->id), ['title' => 'Sch', 'slug' => 'jshikkhabritti', 'type' => 'scholarship', 'number_prefix' => 'no spaces!'])->assertSessionHasErrors('number_prefix');
        $admin->put(route('admin.event-campaigns.update', $c->id), ['title' => 'Sch', 'slug' => 'jshikkhabritti', 'type' => 'scholarship', 'form_settings_json' => '{}', 'sch_title' => ''])->assertSessionHasNoErrors();
        $this->assertSame('Joyee Shikkhabritti', $c->fresh()->scholarshipHeader()['title']);
    }

    public function test_the_admin_print_and_pdf_work_without_a_signed_link(): void
    {
        $reg = $this->registration();
        $admin = $this->actingAs($this->admin());
        $admin->get(route('admin.event-campaigns.registrations.print', $reg->id))->assertOk()->assertSee('1. Applicant');
        $admin->get(route('admin.event-campaigns.registrations.pdf', $reg->id))->assertOk();
        // staff also open the public address directly
        $admin->get(route('event.registration.print', $reg->registration_number))->assertOk();
    }
    public function test_the_scholarship_rows_show_only_the_english_modals_and_no_payment_controls(): void
    {
        $reg = $this->registration();
        $campaign = $this->campaign();
        $campaign->update(['has_fee_or_donation' => true, 'fee_amount' => 100]);
        $html = $this->actingAs($this->admin())->get(route('admin.event-campaigns.show', $campaign->id))->assertOk()->getContent();

        // exactly one edit modal and one preview modal per application, and they are the English ones
        $this->assertSame(1, substr_count($html, 'id="editRegModal' . $reg->id . '"'));
        $this->assertSame(1, substr_count($html, 'id="viewDetailsModal' . $reg->id . '"'));
        $this->assertTrue(str_contains($html, 'id="schEditName' . $reg->id . '"'));

        // nothing of the generic Bengali edit / preview modals leaks into a scholarship row
        foreach (['অংশগ্রহণকারী সম্পাদনা', 'আপডেট সংরক্ষণ', 'ব্যক্তিগত ও যোগাযোগ'] as $bn) {
            $this->assertFalse(str_contains($html, $bn), "Bengali generic modal text leaked: $bn");
        }

        // no payment fields, columns or filters for a scholarship
        $edit = substr($html, strpos($html, 'id="editRegModal' . $reg->id . '"'));
        $edit = substr($edit, 0, strpos($edit, '</form>'));
        foreach (['name="payment_status"', 'name="transaction_id"', 'name="payment_method"', 'name="amount_paid"'] as $field) {
            $this->assertFalse(str_contains($edit, $field), "payment field in the scholarship edit modal: $field");
        }
        foreach (['name="amount_paid"', 'name="transaction_id"'] as $field) {
            $this->assertFalse(str_contains($html, $field), "payment field anywhere on a scholarship page (also the add-participant form): $field");
        }
        preg_match_all('/class="(col-(?:fee|payment)\b[^"]*)"/', $html, $cols);
        $this->assertNotEmpty($cols[1]);
        foreach ($cols[1] as $classes) {
            $this->assertTrue(str_contains($classes, 'd-none'), "Fee / Payment column is visible: $classes");     // hidden for a scholarship
        }
        $this->assertFalse(str_contains($html, 'aec-btn-card-design aec-btn-hover-lift'), 'card design belongs to the other events');
        $this->assertFalse(str_contains($html, 'Verified Paid'));
        $this->assertFalse(str_contains($html, 'name="payment_status" class="form-select bg-light"'), 'no payment filter');
    }

    public function test_another_event_keeps_its_payment_controls(): void
    {
        $c = EventCampaign::create(['title' => 'Fest', 'slug' => 'fest-test', 'type' => 'event', 'is_active' => true, 'has_fee_or_donation' => true, 'fee_amount' => 100, 'form_settings' => []]);
        $reg = EventRegistration::create(['event_campaign_id' => $c->id, 'registration_number' => 'FEST-1', 'name' => 'Guest One', 'phone' => '01711000111', 'status' => 'confirmed', 'payment_status' => 'pending', 'form_data' => []]);
        $html = $this->actingAs($this->admin())->get(route('admin.event-campaigns.show', $c->id))->assertOk()->getContent();

        preg_match_all('/class="(col-(?:fee|payment)\b[^"]*)"/', $html, $cols);
        $this->assertTrue(collect($cols[1])->contains(fn ($c) => !str_contains($c, 'd-none')), 'other events show the Fee / Payment columns');
        $this->assertTrue(str_contains($html, 'name="payment_status" class="form-select bg-light"'));
        $this->assertTrue(str_contains($html, 'name="transaction_id"'));
        $this->assertTrue(str_contains($html, 'aec-btn-card-design aec-btn-hover-lift'), 'other events keep the card design button');
        $this->assertFalse(str_contains($html, 'id="schEditName' . $reg->id . '"'), 'the English scholarship modal is only for scholarships');
    }
}