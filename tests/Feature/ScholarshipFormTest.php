<?php

namespace Tests\Feature;

use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipFormTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private function campaign(): EventCampaign
    {
        return EventCampaign::updateOrCreate(['slug' => 'jshikkhabritti'], [
            'title' => 'Sch', 'type' => 'scholarship', 'is_active' => true,
            'has_fee_or_donation' => false, 'fee_amount' => 0, 'form_settings' => ['is_scholarship_form' => true],
        ]);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'name' => 'Test Student', 'phone' => '01711222333', 'email' => 's1@example.test', 'district' => 'Rangpur',
            'optimized_photo_data' => self::PNG,
            'gender' => 'Female', 'religion' => 'Islam', 'father_name' => 'F', 'mother_name' => 'M', 'birth_date' => '2002-01-29',
            'guardian_phone' => '01711000111', 'scholarship_degree' => 'HSC',
            'college_name' => 'Rangpur College', 'group' => 'SCIENCE', 'admission_roll' => '1', 'assigned_subject' => 'Physics',
            'ssc_roll' => '1', 'ssc_board' => 'DINAJPUR', 'ssc_institute' => 'School', 'ssc_year' => '2019', 'ssc_gpa' => '5.00',
            'prev_joyee' => 'No', 'other_scholarship' => 'No', 'books_read_count' => '0', 'reading_habit' => 'I do not read',
            'scholarship_reason' => 'I need help.',
            'national_id' => '1234567890', 'father_occupation' => 'Farmer', 'mother_occupation' => 'Housewife',
            'family_count' => '5', 'earning_count' => '1', 'father_status' => 'Alive', 'mother_status' => 'Alive', 'disability' => 'No', 'current_year' => '1st Year',
            'pay_method' => 'bKash', 'pay_account_owner' => 'Self', 'pay_account_name' => 'Test Student', 'pay_account_no' => '01711222333',
        ], $over);
    }

    public function test_the_form_is_english_and_has_the_new_sections(): void
    {
        $this->campaign();
        $html = $this->get('/jshikkhabritti')->assertOk()->getContent();

        foreach (['Educational Qualification', 'name="prev_joyee"', 'name="books_read_count"', 'name="books_read_names"', 'name="hsc_roll"', 'name="grad_roll"', 'name="other_roll"'] as $needle) {
            $this->assertStringContainsString($needle, $html);
        }
        $this->assertMatchesRegularExpression('/name="guardian_phone"[^>]*\srequired/', $html);
        $this->assertStringNotContainsString('Qualifications<', $html);
        $this->assertDoesNotMatchRegularExpression('/ব্যক্তিগত তথ্য|একাডেমিক তথ্য|আবেদনকারীর নাম/u', $html);
    }

    public function test_guardian_contact_scholarship_and_reading_answers_are_mandatory(): void
    {
        $this->campaign();

        foreach (['guardian_phone', 'prev_joyee', 'other_scholarship', 'books_read_count', 'reading_habit'] as $field) {
            $data = $this->payload();
            unset($data[$field]);
            $this->post('/jshikkhabritti', $data)->assertSessionHasErrors($field);
        }
        $this->assertSame(0, EventRegistration::count());
    }

    public function test_follow_up_answers_are_required_only_when_they_apply(): void
    {
        $this->campaign();

        $this->post('/jshikkhabritti', $this->payload(['prev_joyee' => 'Yes']))->assertSessionHasErrors(['prev_scholarships.0.year', 'prev_scholarships.0.amount']);
        $this->post('/jshikkhabritti', $this->payload(['other_scholarship' => 'Yes']))->assertSessionHasErrors('other_scholarship_details');
        $this->post('/jshikkhabritti', $this->payload(['books_read_count' => '3', 'reading_habit' => 'I have read']))->assertSessionHasErrors('books_read_names');
    }

    public function test_earlier_levels_are_required_below_the_chosen_degree(): void
    {
        $this->campaign();

        // Honours applicant must also give HSC; Masters applicant must give HSC and Honours
        $this->post('/jshikkhabritti', $this->payload(['scholarship_degree' => 'HONOURS']))->assertSessionHasErrors('hsc_roll');
        $this->post('/jshikkhabritti', $this->payload(['scholarship_degree' => 'MASTERS', 'hsc_roll' => '1', 'hsc_board' => 'B', 'hsc_institute' => 'C', 'hsc_year' => '2021', 'hsc_gpa' => '5']))->assertSessionHasErrors('grad_roll');
    }

    public function test_a_complete_application_is_saved_and_the_print_page_is_english(): void
    {
        $this->campaign();
        $this->post('/jshikkhabritti', $this->payload([
            'prev_joyee' => 'Yes', 'prev_scholarships' => [['year' => '2025', 'times' => '1', 'amount' => '5000']],
            'other_scholarship' => 'Yes', 'other_scholarship_details' => 'Some Trust, 2000',
            'books_read_count' => '2', 'reading_habit' => 'I have read', 'books_read_names' => "Book A\nBook B",
            'perm_city_corporation' => 'রংপুর সিটি কর্পোরেশন', 'permanent_address' => 'রংপুর, Village',
        ]))->assertSessionHasNoErrors();

        $reg = EventRegistration::firstOrFail();
        $this->assertSame('Yes', $reg->form_data['prev_joyee']);
        $this->assertSame(2, (int) $reg->form_data['books_read_count']);

        $html = $this->get(route('event.registration.print', $reg->registration_number))->assertOk()->getContent();
        foreach (['1. Applicant', '3. Academic', 'Received Joyee Scholarship before?', 'Original books read', 'Book A', '8. Statement (max 50 words)', 'Permanent'] as $needle) {
            if ($needle !== '') $this->assertStringContainsString($needle, $html);
        }
        $this->assertStringContainsString('Rangpur City Corporation', $html);   // city corporation is kept in the address, in English
        $this->assertStringContainsString('Session 2027', $html);
        $this->assertStringContainsString('Application No: <strong>2027-1</strong>', $html);
        $this->assertDoesNotMatchRegularExpression('/ব্যক্তিগত তথ্য|একাডেমিক তথ্য|আবেদনকারীর নাম|ঠিকানা</u', $html);
    }
    public function test_application_numbers_run_2027_1_2027_2_and_the_form_shows_the_next_one(): void
    {
        $this->campaign();
        $this->assertStringContainsString('Application No: <b>2027-1</b>', $this->get('/jshikkhabritti')->getContent());

        $this->post('/jshikkhabritti', $this->payload())->assertSessionHasNoErrors();
        auth()->logout();
        $this->flushSession();
        $this->post('/jshikkhabritti', $this->payload(['phone' => '01711222444', 'email' => 's2@example.test']))->assertSessionHasNoErrors();

        $this->assertSame(['2027-1', '2027-2'], EventRegistration::orderBy('id')->pluck('registration_number')->all());
        auth()->logout();
        $this->flushSession();
        $this->assertStringContainsString('Application No: <b>2027-3</b>', $this->get('/jshikkhabritti')->getContent());
    }

    public function test_session_and_degree_labels_and_english_place_names(): void
    {
        $this->campaign();
        $html = $this->get('/jshikkhabritti')->assertOk()->getContent();
        $this->assertStringContainsString('Session 2027 | HSC / Honours / Masters / Others', $html);
        $this->assertStringNotContainsString('Intermediate', $html);
        $this->assertStringNotContainsString('2026-2027', $html);
        $this->assertStringContainsString('<option value="HONOURS"', $html);
        $this->assertSame(1, substr_count($html, 'js/bd-geo-en.js'));

        $en = file_get_contents(public_path('js/bd-geo-en.js'));
        foreach (['"রংপুর":"Rangpur"', '"বদরগঞ্জ":"Badarganj"', '"রংপুর সিটি কর্পোরেশন":"Rangpur City Corporation"', '"রংপুর প্রধান ডাকঘর (৫৪০০)":"Rangpur Head Post Office (5400)"'] as $pair) {
            $this->assertStringContainsString($pair, $en);
        }
    }
    public function test_identity_family_and_payment_details_are_mandatory_and_checked(): void
    {
        $this->campaign();

        foreach (['national_id', 'father_occupation', 'mother_occupation', 'family_count', 'earning_count', 'father_status', 'mother_status', 'disability', 'current_year', 'pay_method', 'pay_account_owner', 'pay_account_name', 'pay_account_no'] as $field) {
            $data = $this->payload();
            unset($data[$field]);
            $this->post('/jshikkhabritti', $data)->assertSessionHasErrors($field);
        }

        $this->post('/jshikkhabritti', $this->payload(['national_id' => '12345']))->assertSessionHasErrors('national_id');
        $this->post('/jshikkhabritti', $this->payload(['earning_count' => '9']))->assertSessionHasErrors('earning_count');           // more earners than family members
        $this->post('/jshikkhabritti', $this->payload(['disability' => 'Yes']))->assertSessionHasErrors('disability_type');
        $this->post('/jshikkhabritti', $this->payload(['disability' => 'Yes', 'disability_type' => 'Other']))->assertSessionHasErrors('disability_details');
        $this->post('/jshikkhabritti', $this->payload(['father_status' => 'Alive', 'father_occupation' => '']))->assertSessionHasErrors('father_occupation');
        $this->post('/jshikkhabritti', $this->payload(['guardian_name' => 'Uncle']))->assertSessionHasErrors('guardian_relation');
        $this->post('/jshikkhabritti', $this->payload(['pay_account_no' => '12345']))->assertSessionHasErrors('pay_account_no');      // wallet needs 01XXXXXXXXX
        $this->post('/jshikkhabritti', $this->payload(['pay_method' => 'Bank', 'pay_account_no' => '1234567890']))->assertSessionHasErrors(['pay_bank_name', 'pay_bank_branch']);
        $this->assertSame(0, EventRegistration::count());

        // Bengali digits and separators are normalised, a bank account passes with bank + branch
        $this->post('/jshikkhabritti', $this->payload(['national_id' => '১২৩৪৫৬৭৮৯০', 'pay_method' => 'Bank', 'pay_account_no' => '1234-5678 90', 'pay_bank_name' => 'Sonali Bank', 'pay_bank_branch' => 'Rangpur']))->assertSessionHasNoErrors();
        $reg = EventRegistration::firstOrFail();
        $this->assertSame('1234567890', $reg->form_data['national_id']);
        $this->assertSame('1234567890', $reg->form_data['pay_account_no']);
    }

    public function test_the_print_page_and_the_admin_csv_carry_the_new_details(): void
    {
        $campaign = $this->campaign();
        $this->post('/jshikkhabritti', $this->payload([
            'guardian_name' => 'Uncle', 'guardian_relation' => 'Uncle', 'disability' => 'Yes', 'disability_type' => 'Visual', 'disability_details' => 'Low vision',
            'father_status' => 'Deceased', 'father_occupation' => 'Farmer', 'father_income' => '5000', 'father_phone' => '01711000999',
            'mother_occupation' => '=SUM(1)', 'mother_income' => '3000', 'mother_phone' => '01711000888',
        ]))->assertSessionHasNoErrors();
        $reg = EventRegistration::firstOrFail();

        // a deceased parent: occupation is forced to Nil, income and mobile are dropped, orphan status is derived on the server
        $this->assertSame('Nil', $reg->form_data['father_occupation']);
        $this->assertArrayNotHasKey('father_income', $reg->form_data);
        $this->assertArrayNotHasKey('father_phone', $reg->form_data);
        $this->assertSame('Father deceased', $reg->form_data['orphan_status']);
        $this->assertSame('3000', $reg->form_data['mother_income']);

        $print = $this->get(route('event.registration.print', $reg->registration_number))->assertOk()->getContent();
        foreach (['NID / Birth Reg.', '1234567890', 'Family / Earning Members', 'Father deceased', 'Visual impairment', 'Low vision', 'Mother Mobile', '01711000888', 'Nil', 'Payment Account', 'Wallet No.', '01711222333', 'Current Year', 'Guardian Contact', '(Uncle)'] as $needle) {
            $this->assertStringContainsString($needle, $print);
        }

        $admin = User::create(['name' => 'Sch Admin', 'email' => 'sch-admin@example.test', 'phone' => '01710000055', 'password' => Hash::make('test-password'), 'role' => 'admin', 'reg_type' => 'admin', 'reg_status' => 'approved', 'is_active' => true]);
        $csv = $this->actingAs($admin)->get(route('admin.event-campaigns.export', $campaign->id))->assertOk()->streamedContent();

        $this->assertStringContainsString('Account / Wallet No', $csv);
        $this->assertStringContainsString('NID / Birth Reg', $csv);
        $this->assertStringContainsString("\t01711222333", $csv);           // kept as text so Excel does not drop the leading zero
        $this->assertStringContainsString('Father Status', $csv);
        $this->assertStringContainsString('Disability Category', $csv);
        $this->assertStringContainsString("'=SUM(1)", $csv);                 // formula injection is neutralised
        $this->assertStringNotContainsString(',=SUM(1)', $csv);
        $this->assertStringContainsString('2027-1', $csv);
    }
    public function test_section_titles_run_in_the_same_serial_on_the_form_the_preview_and_the_print(): void
    {
        $this->campaign();
        $titles = ['1. Applicant', '2. Parents', '3. Academic', '4. Educational Qualification', '5. Address', '6. Scholarships', '7. Reading', '8. Statement', '9. Payment Account'];

        $form = $this->get('/jshikkhabritti')->assertOk()->getContent();
        $form = substr($form, strpos($form, 'id="dynamicScholarshipForm"'));
        $this->assertTitlesInOrder($form, $titles, 'form');

        $preview = substr($form, strpos($form, "pv-sec\">1. Applicant"));
        $this->assertTitlesInOrder($preview, $titles, 'A4 preview');

        $this->post('/jshikkhabritti', $this->payload())->assertSessionHasNoErrors();
        $reg = EventRegistration::firstOrFail();
        $print = $this->get(route('event.registration.print', $reg->registration_number))->assertOk()->getContent();
        $this->assertTitlesInOrder($print, $titles, 'print');
    }

    private function assertTitlesInOrder(string $html, array $titles, string $where): void
    {
        $last = -1;
        foreach ($titles as $t) {
            $pos = strpos($html, $t);
            $this->assertNotFalse($pos, "$where is missing the title '$t'");
            $this->assertGreaterThan($last, $pos, "$where has '$t' out of order");
            $last = $pos;
        }
    }

    public function test_the_form_has_a_phone_and_tablet_layout(): void
    {
        $this->campaign();
        // pages are minified, so compare without whitespace
        $html = preg_replace('/\s+/', '', $this->get('/jshikkhabritti')->assertOk()->getContent());

        foreach (['@media(max-width:767.98px)', '.grid-table:not(.text-center)td.label-col', 'font-size:16px!important', '.grid-table[hidden]{display:none!important', 'width=device-width'] as $needle) {
            $this->assertStringContainsString($needle, $html);
        }
    }
    public function test_a_photo_is_mandatory_and_the_pdf_has_two_remark_boxes(): void
    {
        $this->campaign();
        $html = $this->get('/jshikkhabritti')->assertOk()->getContent();
        $this->assertStringContainsString('Student Photo <span class="text-danger">*</span>', $html);
        $this->assertStringContainsString('id="photoError"', $html);

        $noPhoto = $this->payload();
        unset($noPhoto['optimized_photo_data']);
        $this->post('/jshikkhabritti', $noPhoto)->assertSessionHas('error');
        $this->assertSame(0, EventRegistration::count());

        $this->post('/jshikkhabritti', $this->payload())->assertSessionHasNoErrors();
        $reg = EventRegistration::firstOrFail();
        foreach ([route('event.registration.print', $reg->registration_number)] as $url) {
            $print = $this->get($url)->assertOk()->getContent();
            $left = strpos($print, 'Remarks (max 50 words) for Head of institute');
            $right = strpos($print, 'Remarks (max 50 words) for Public representative');
            $this->assertNotFalse($left);
            $this->assertGreaterThan($left, $right);                         // head of institute left, public representative right
            $this->assertStringContainsString('remark-cols', $print);
        }
        $this->get(route('event.registration.pdf', $reg->registration_number))->assertOk();
    }
    public function test_the_page_after_submit_is_english_with_instructions_and_time_to_download(): void
    {
        $this->campaign();
        $this->post('/jshikkhabritti', $this->payload())->assertRedirect(route('event.success', 'jshikkhabritti'));
        $html = $this->get(route('event.success', 'jshikkhabritti'))->assertOk()->getContent();
        $flat = preg_replace('/\s+/', ' ', $html);

        foreach (['Thank you!', 'Download your application form.', 'Get it signed by the head of your institute and your local councillor, and submit it on the day of the meeting.', 'Download Application Form', 'Download PDF', 'Application No:', '2027-1'] as $needle) {
            $this->assertTrue(str_contains($flat, $needle), "success page is missing: $needle");
        }
        $this->assertStringContainsString('<span id="countdownSecs">120</span>', $html);        // two minutes before the homepage redirect
        $this->assertStringContainsString('toggleCountdown()', $html);                         // pause button + auto-pause when a download starts
        $this->assertFalse(str_contains($html, 'আমন্ত্রণ কার্ড'), 'the Bengali invitation-card wording must not show for a scholarship');
        $this->assertFalse(str_contains($html, 'রিডাইরেক্ট থামান</button>'));
        $this->assertTrue(str_contains($flat, 'Thank you! Your application has been submitted.'));      // flash banner, English too
        $this->assertFalse(str_contains($html, 'আপনার আবেদন সফলভাবে'));
    }
    public function test_the_printed_form_is_two_pages_with_sections_1_to_6_on_the_first(): void
    {
        $this->campaign();
        $this->post('/jshikkhabritti', $this->payload())->assertSessionHasNoErrors();
        $reg = EventRegistration::firstOrFail();

        $print = $this->get(route('event.registration.print', $reg->registration_number))->assertOk()->getContent();
        $this->assertFalse(str_contains($print, '@@'), 'a template marker leaked into the print');

        $break = strpos($print, 'class="pb"');
        $this->assertNotFalse($break);
        foreach (['1. Applicant', '2. Parents', '3. Academic', '4. Educational Qualification', '5. Address', '6. Scholarships'] as $t) {
            $this->assertLessThan($break, strpos($print, $t), "$t must be on page 1");
        }
        foreach (['7. Reading', '8. Statement', '9. Payment Account', 'Remarks (max 50 words) for Head of institute'] as $t) {
            $this->assertGreaterThan($break, strpos($print, $t), "$t must be on page 2");
        }

        // the PDF engine must end up with exactly two sheets (nothing spills onto a third)
        $pdf = $this->get(route('event.registration.pdf', $reg->registration_number))->assertOk()->getContent();
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page[^s]/', $pdf), 'the PDF must be exactly two pages');
    }
    public function test_a_deleted_application_never_makes_its_number_be_handed_out_again(): void
    {
        $this->campaign();
        $this->post('/jshikkhabritti', $this->payload())->assertSessionHasNoErrors();
        EventRegistration::firstOrFail()->delete();                       // soft delete: row stays, unique index still holds 2027-1

        auth()->logout();
        $this->flushSession();
        $this->post('/jshikkhabritti', $this->payload(['phone' => '01711222555', 'email' => 's3@example.test']))->assertSessionHasNoErrors();

        $this->assertSame(['2027-1', '2027-2'], EventRegistration::withTrashed()->orderBy('id')->pluck('registration_number')->all());
    }
}