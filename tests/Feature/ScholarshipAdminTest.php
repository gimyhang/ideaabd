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
}
