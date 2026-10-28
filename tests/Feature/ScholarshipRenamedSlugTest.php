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

/** The admin renamed the scholarship slug (jshikkhabritti -> jsb): old links, lookup and admin actions must keep working. */
class ScholarshipRenamedSlugTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private function campaign(): EventCampaign
    {
        $c = EventCampaign::where('type', 'scholarship')->first() ?? new EventCampaign();
        $c->fill([
            'title' => 'Joyee Shikkha Britti', 'slug' => 'jsb', 'type' => 'scholarship', 'is_active' => true,
            'has_fee_or_donation' => false, 'fee_amount' => 0,
            'form_settings' => ['is_scholarship_form' => true, 'number_prefix' => '2027'],
        ])->save();

        return $c;
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'name' => 'Test Student', 'phone' => '০১৭১১২২২৩৩৩', 'email' => 's1@example.test', 'district' => 'Rangpur',
            'optimized_photo_data' => self::PNG,
            'gender' => 'Female', 'religion' => 'Islam', 'father_name' => 'F', 'mother_name' => 'M', 'birth_date' => '২০০২-০১-২৯',
            'guardian_phone' => '01711000111', 'scholarship_degree' => 'HONOURS',
            'college_name' => 'Rangpur College', 'group' => 'SCIENCE', 'admission_roll' => '1', 'assigned_subject' => 'Physics',
            'ssc_roll' => '1', 'ssc_board' => 'DINAJPUR', 'ssc_institute' => 'School', 'ssc_year' => '2019', 'ssc_gpa' => '5.00',
            'hsc_roll' => '1', 'hsc_board' => 'DINAJPUR', 'hsc_institute' => 'College', 'hsc_year' => '2021', 'hsc_gpa' => '5.00',
            'prev_joyee' => 'No', 'other_scholarship' => 'No', 'books_read_count' => '0', 'reading_habit' => 'I do not read',
            'scholarship_reason' => 'I need help.',
            'national_id' => '১২৩৪৫৬৭৮৯০', 'father_occupation' => 'Farmer', 'mother_occupation' => 'Housewife',
            'family_count' => '5', 'earning_count' => '1', 'father_status' => 'Alive', 'mother_status' => 'Alive', 'disability' => 'No', 'current_year' => '1st Year',
            'pay_method' => 'bKash', 'pay_account_owner' => 'Self', 'pay_account_name' => 'Test Student', 'pay_account_no' => '01711222333',
        ], $over);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Sch Admin', 'email' => 'sch-admin3@example.test', 'phone' => '01710000098',
            'password' => Hash::make('test-password'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    public function test_old_alias_links_still_reach_the_renamed_campaign(): void
    {
        Http::fake();
        $this->campaign();

        foreach (['/jsb', '/jshikkhabritti', '/scholarship', '/shikkhabritti'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->post('/jshikkhabritti', $this->payload())->assertSessionHasNoErrors()->assertRedirect('/event-confirmed/jsb');
        $this->assertSame(1, EventRegistration::count());
        $this->assertSame(1, EventCampaign::where('type', 'scholarship')->count(), 'no second campaign is created under the old slug');
        $this->get('/event-confirmed/jsb')->assertOk();
        $this->get('/event-confirmed/jshikkhabritti')->assertOk();

        $reg = EventRegistration::firstOrFail();
        $this->assertSame('1234567890', $reg->form_data['national_id']);
        $this->assertSame('2002-01-29', $reg->form_data['birth_date']);
    }

    public function test_applicant_lookup_works_on_the_real_and_the_old_slug(): void
    {
        Http::fake();
        $this->campaign();
        $this->post('/jsb', $this->payload())->assertSessionHasNoErrors();

        foreach (['/event-lookup/jsb', '/event-lookup/jshikkhabritti'] as $url) {
            $json = $this->postJson($url, ['name' => 'Test Student', 'phone' => '01711222333'])->assertOk()->json();
            $this->assertTrue($json['found'], $url);
            $this->get($json['print_url'])->assertOk();
        }
    }

    public function test_admin_pages_and_actions_work_for_scholarship_applications(): void
    {
        Http::fake();
        $c = $this->campaign();
        $this->post('/jsb', $this->payload())->assertSessionHasNoErrors();
        Auth::logout();
        $this->flushSession();
        $this->post('/jsb', $this->payload(['phone' => '01711000201', 'name' => 'Second', 'scholarship_degree' => 'HSC']))->assertSessionHasNoErrors();
        Auth::logout();

        [$r1, $r2] = EventRegistration::orderBy('id')->get()->all();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.event-campaigns.show', $c->id))->assertOk();
        $this->actingAs($admin)->get(route('admin.event-campaigns.edit', $c->id))->assertOk();
        $this->actingAs($admin)->get(route('admin.event-campaigns.export', $c->id))->assertOk();
        $this->actingAs($admin)->get(route('admin.event-campaigns.print-all', $c->id))->assertOk();
        $this->actingAs($admin)->get(route('admin.event-campaigns.registrations.print', $r1->id))->assertOk();
        $this->actingAs($admin)->get(route('admin.event-campaigns.registrations.pdf', $r1->id))->assertOk();

        $this->actingAs($admin)->postJson(route('admin.event-campaigns.registrations.toggle-scholarship', $r1->id))->assertOk()->assertJson(['is_awarded' => true]);
        $this->actingAs($admin)->postJson(route('admin.event-campaigns.registrations.toggle-approval', $r2->id), ['status' => 'rejected'])->assertOk();
        foreach (['approve', 'reject', 'select_scholarship', 'mark_pending'] as $action) {
            $this->actingAs($admin)->post(route('admin.event-campaigns.bulk-action', $c->id), ['action' => $action, 'selected_ids' => [$r1->id, $r2->id]])->assertRedirect();
        }
        $this->actingAs($admin)->get(route('admin.event-campaigns.show', $c->id))->assertOk();
    }

    public function test_approval_sms_for_a_scholarship_carries_a_signed_form_link_not_the_festival_text(): void
    {
        Http::fake();
        $this->campaign();
        $this->post('/jsb', $this->payload())->assertSessionHasNoErrors();
        $reg = EventRegistration::with('campaign')->firstOrFail();

        $controller = app(EventCampaignAdminController::class);
        $text = (new \ReflectionMethod($controller, 'approvalSmsText'))->invoke($controller, $reg);

        $this->assertStringContainsString($reg->registration_number, $text);
        $this->assertStringContainsString('signature=', $text, 'the scholarship form only opens through a signed link');
        $this->assertStringNotContainsString('সাহিত্য উৎসব', $text);
    }
}
