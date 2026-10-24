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

        foreach (['Education', 'name="prev_joyee"', 'name="books_read_count"', 'name="books_read_names"', 'name="hsc_roll"', 'name="grad_roll"', 'name="other_roll"'] as $needle) {
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
        foreach (['1. Applicant', '3. Academic', 'Received Joyee before?', 'Original books read', 'Book A', '8. Statement', 'Permanent'] as $needle) {
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
        foreach (['NID / Birth Reg.', '1234567890', 'Family / Earning Members', 'Father deceased', 'Visual impairment', 'Low vision', 'Mother Mobile', '01711000888', 'Nil', 'Payment', 'Wallet No.', '01711222333', 'Current Year', 'Guardian Contact', '(Uncle)'] as $needle) {
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
        $titles = ['1. Applicant', '2. Parents', '3. Academic', '4. Education', '5. Address', '6. Scholarships', '7. Reading', '8. Statement', '9. Payment'];

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
            $left = strpos($print, 'Remarks: Head');
            $right = strpos($print, 'Remarks: Councillor');
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

        foreach (['Thank you!', 'Download your application form.', 'Get it signed by the head of your institute and your local councillor, and submit it on the day of the meeting.', 'Download', 'PDF', 'Number:', '2027-1'] as $needle) {
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
        foreach (['1. Applicant', '2. Parents', '3. Academic', '4. Education', '5. Address', '6. Scholarships'] as $t) {
            $this->assertLessThan($break, strpos($print, $t), "$t must be on page 1");
        }
        foreach (['7. Reading', '8. Statement', '9. Payment', 'Remarks: Head'] as $t) {
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
    public function test_campaign_form_settings_reach_the_scholarship_form(): void
    {
        EventCampaign::updateOrCreate(['slug' => 'jshikkhabritti'], [
            'title' => 'Sch', 'type' => 'scholarship', 'is_active' => true, 'has_fee_or_donation' => false, 'fee_amount' => 0,
            'badge_text' => 'OPEN', 'contact_phone' => '01711000000', 'contact_email' => 'help@example.test',
            'form_settings' => [
                'is_scholarship_form' => true, 'theme_color' => '#7c3aed', 'form_notice_active' => true, 'form_notice_text' => 'Apply before the 15th.', 'form_notice_type' => 'warning',
                'form_subhead' => 'Merit award', 'logo_image' => 'campaigns/logos/x.png', 'logo_size' => 70,
                'fields' => ['email' => ['label' => 'ইমেইল (Mail Address)', 'enabled' => true]],
            ],
        ]);
        $html = preg_replace('/\s+/', ' ', $this->get('/jshikkhabritti')->assertOk()->getContent());

        foreach (['--sch-theme: #7c3aed', 'Apply before the 15th.', 'alert-warning', 'Merit award', 'storage/campaigns/logos/x.png', '>OPEN<', 'help@example.test', 'Mail Address'] as $needle) {
            $this->assertTrue(str_contains($html, $needle), "missing from the form: $needle");
        }

        // the e-mail question can be switched off in the form studio
        $c = EventCampaign::where('slug', 'jshikkhabritti')->first();
        $c->update(['form_settings' => array_merge($c->form_settings, ['fields' => ['email' => ['enabled' => false]]])]);
        $page = $this->get('/jshikkhabritti')->getContent();
        $form = substr($page, strpos($page, 'id="dynamicScholarshipForm"'));
        $form = substr($form, 0, strpos($form, '</form>'));        // the footer newsletter box has its own email field
        $this->assertFalse(str_contains($form, 'name="email"'));
    }

    public function test_an_application_fee_is_asked_for_checked_and_priced_by_the_campaign(): void
    {
        EventCampaign::updateOrCreate(['slug' => 'jshikkhabritti'], [
            'title' => 'Sch', 'type' => 'scholarship', 'is_active' => true, 'has_fee_or_donation' => true, 'fee_amount' => 150,
            'redirect_url' => 'https://example.test/thanks', 'success_message' => 'We will call you within 3 days.',
            'form_settings' => ['is_scholarship_form' => true],
        ]);
        $page = $this->get('/jshikkhabritti')->assertOk()->getContent();
        $this->assertTrue(str_contains($page, '10. Fee'));
        $this->assertTrue(str_contains($page, 'name="transaction_id"'));

        $this->post('/jshikkhabritti', $this->payload())->assertSessionHasErrors(['payment_method', 'transaction_id']);

        $this->post('/jshikkhabritti', $this->payload(['payment_method' => 'bkash', 'transaction_id' => '9J3K8L2M', 'amount_paid' => '1']))->assertSessionHasNoErrors();
        $reg = EventRegistration::firstOrFail();
        $this->assertSame(150.0, (float) $reg->amount_paid);                       // not the 1 the browser sent
        $this->assertSame('9J3K8L2M', $reg->transaction_id);

        $print = $this->get(route('event.registration.print', $reg->registration_number))->assertOk()->getContent();
        $this->assertTrue(str_contains($print, '10. Fee'));
        $this->assertTrue(str_contains($print, '9J3K8L2M'));

        $success = $this->get(route('event.success', 'jshikkhabritti'))->assertOk()->getContent();
        $this->assertTrue(str_contains($success, 'We will call you within 3 days.'));
        $this->assertTrue(str_contains($success, 'https:\/\/example.test\/thanks'));    // redirect_url replaces the homepage
    }

    public function test_the_print_uses_the_campaign_logo_header_lines_copy_tag_and_signature_switch(): void
    {
        $dir = storage_path('app/public/campaigns/logos');
        @mkdir($dir, 0775, true);
        file_put_contents($dir . '/print-test.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='));
        try {
            $c = EventCampaign::updateOrCreate(['slug' => 'jshikkhabritti'], [
                'title' => 'Sch', 'type' => 'scholarship', 'is_active' => true, 'has_fee_or_donation' => false, 'fee_amount' => 0,
                'form_settings' => ['is_scholarship_form' => true, 'logo_image' => 'campaigns/logos/print-test.png', 'form_org' => 'Firiye Dekha', 'form_venue' => 'Rangpur', 'form_date' => '12 Dec 2026', 'form_copy_tag' => 'Applicant copy', 'enable_signature' => false],
            ]);
            $this->post('/jshikkhabritti', $this->payload())->assertSessionHasNoErrors();
            $reg = EventRegistration::firstOrFail();

            $print = $this->get(route('event.registration.print', $reg->registration_number))->assertOk()->getContent();
            foreach (['data:image/png;base64,', 'Firiye Dekha', 'Rangpur | 12 Dec 2026', 'Applicant copy'] as $needle) {
                $this->assertTrue(str_contains($print, $needle), "missing from the print: $needle");
            }
            $this->assertFalse(str_contains($print, 'Applicant signature'), 'enable_signature=false hides the applicant signature');
            $this->get(route('event.registration.pdf', $reg->registration_number))->assertOk();
        } finally {
            @unlink($dir . '/print-test.png');
        }
    }

    public function test_server_errors_on_the_newer_fields_send_the_visitor_to_the_right_step(): void
    {
        $this->campaign();
        $data = $this->payload();
        unset($data['national_id']);
        $html = $this->from('/jshikkhabritti')->followingRedirects()->post('/jshikkhabritti', $data)->getContent();
        $this->assertTrue(str_contains($html, 'data-initial-step="0"'), 'a missing NID must open the first step');

        $data = $this->payload();
        unset($data['pay_account_no']);
        $html = $this->from('/jshikkhabritti')->followingRedirects()->post('/jshikkhabritti', $data)->getContent();
        $this->assertTrue(str_contains($html, 'data-initial-step="3"'), 'a missing payment account must open the fourth step');
    }
    public function test_a_scholarship_form_cannot_be_opened_by_guessing_the_application_number(): void
    {
        $this->campaign();
        $this->post('/jshikkhabritti', $this->payload())->assertSessionHasNoErrors();
        $reg = EventRegistration::firstOrFail();

        // a stranger (not signed in, no signed link) is sent back to the scholarship page
        auth()->logout();
        $this->flushSession();
        $this->get(route('event.registration.print', $reg->registration_number))->assertRedirect('/jshikkhabritti#get-form');
        $this->get(route('event.registration.pdf', $reg->registration_number))->assertRedirect('/jshikkhabritti#get-form');
        $this->get('/card/' . $reg->registration_number)->assertRedirect('/jshikkhabritti#get-form');

        // the signed link works for anybody holding it, and stops working when tampered with or expired
        $this->get($reg->scholarshipUrl('print'))->assertOk()->assertSee('1. Applicant');
        $this->get($reg->scholarshipUrl('pdf'))->assertOk();
        $this->get($reg->scholarshipUrl('print') . 'x')->assertRedirect('/jshikkhabritti#get-form');
        $this->travel(2)->hours();
        $this->get($reg->scholarshipUrl('print', 60))->assertOk();
        $this->travel(3)->hours();
        $this->get(\Illuminate\Support\Facades\URL::temporarySignedRoute('event.registration.print', now()->subHour(), ['registrationNumber' => $reg->registration_number]))->assertRedirect('/jshikkhabritti#get-form');
    }

    public function test_an_applicant_gets_their_form_back_with_name_and_mobile_and_a_new_one_is_told_to_register(): void
    {
        $this->campaign();
        $this->post('/jshikkhabritti', $this->payload(['name' => 'Rina Akter', 'phone' => '01711222333']))->assertSessionHasNoErrors();
        auth()->logout();
        $this->flushSession();

        // right name + mobile (Bangla digits, different case, a typo) -> signed links to own form
        foreach ([['Rina Akter', '01711222333'], ['  rina   AKTER ', '০১৭১১২২২৩৩৩'], ['Rina Aktar', '+880 1711-222333']] as [$name, $phone]) {
            $json = $this->postJson(route('event.lookup', 'jshikkhabritti'), ['name' => $name, 'phone' => $phone])->assertOk()->assertJson(['found' => true, 'application_no' => '2027-1'])->json();
            $this->get($json['pdf_url'])->assertOk();
            $this->get($json['print_url'])->assertOk();
        }

        // wrong name, wrong phone, nobody: the same refusal, no links
        foreach ([['Someone Else', '01711222333'], ['Rina Akter', '01799999999']] as [$name, $phone]) {
            $this->postJson(route('event.lookup', 'jshikkhabritti'), ['name' => $name, 'phone' => $phone])
                ->assertOk()->assertJson(['found' => false])->assertJsonMissing(['pdf_url']);
        }
        $this->postJson(route('event.lookup', 'jshikkhabritti'), ['name' => '', 'phone' => ''])->assertUnprocessable();

        // the page offers both ways in
        $html = $this->get('/jshikkhabritti')->assertOk()->getContent();
        $this->assertTrue(str_contains($html, 'id="get-form"'));
        $this->assertTrue(str_contains($html, route('event.lookup', 'jshikkhabritti')));
    }

    public function test_header_and_logo_settings_drive_the_form_the_print_and_the_number_prefix(): void
    {
        EventCampaign::updateOrCreate(['slug' => 'jshikkhabritti'], [
            'title' => 'Sch', 'type' => 'scholarship', 'is_active' => true, 'has_fee_or_donation' => false, 'fee_amount' => 0,
            'form_settings' => ['is_scholarship_form' => true, 'sch_title' => 'Merit Award', 'sch_heading' => 'Grant', 'sch_session' => 'Session 2028', 'sch_logo_side' => 'center', 'number_prefix' => 'SCH28', 'logo_size' => 80],
        ]);
        $c = EventCampaign::where('slug', 'jshikkhabritti')->first();
        $h = $c->scholarshipHeader();
        $this->assertSame(['Merit Award', 'Grant', 'Session 2028', 'center', 80], [$h['title'], $h['heading'], $h['session'], $h['logo_side'], $h['logo_size']]);

        $form = preg_replace('/\s+/', ' ', $this->get('/jshikkhabritti')->getContent());
        $this->assertTrue(str_contains($form, 'Session 2028'));

        $this->post('/jshikkhabritti', $this->payload())->assertSessionHasNoErrors();
        $reg = EventRegistration::firstOrFail();
        $this->assertSame('SCH28-1', $reg->registration_number);

        $print = $this->get($reg->scholarshipUrl('print'))->assertOk()->getContent();
        foreach (['Merit Award', 'Grant', 'Session 2028'] as $needle) {
            $this->assertTrue(str_contains($print, $needle), "missing from the print: $needle");
        }

        // Bengali settings never leak into the English form: defaults are used instead
        $c->update(['form_settings' => array_merge($c->form_settings, ['sch_title' => 'বৃত্তি'])]);
        $this->assertSame('Joyee Shikkhabritti', $c->fresh()->scholarshipHeader()['title']);
    }

    public function test_the_pdf_embeds_the_photo_and_stays_on_two_pages_even_with_long_answers(): void
    {
        $this->campaign();
        $long = str_repeat('Very Long Institute Name ', 4);
        $this->post('/jshikkhabritti', $this->payload([
            'ssc_institute' => $long, 'hsc_roll' => '1', 'hsc_board' => 'B', 'hsc_institute' => $long, 'hsc_year' => '2021', 'hsc_gpa' => '5',
            'scholarship_degree' => 'HONOURS', 'grad_roll' => '9', 'grad_board' => 'NU', 'grad_institute' => $long, 'grad_year' => '2025', 'grad_cgpa' => '3.5',
            'college_name' => $long, 'permanent_address' => str_repeat('Village Road House Upazila District Division ', 4),
        ]))->assertSessionHasNoErrors();
        $reg = EventRegistration::firstOrFail();

        $html = $this->get($reg->scholarshipUrl('print'))->assertOk()->getContent();
        $this->assertTrue(str_contains($html, 'density-'), 'the print adapts its density to long answers');
        $this->assertTrue(str_contains($html, 'data:image/jpeg;base64,') || str_contains($html, 'data:image/png;base64,'), 'the photo is embedded');

        $pdf = $this->get($reg->scholarshipUrl('pdf'))->assertOk()->getContent();
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page[^s]/', $pdf), 'sections 1-6 on page one, the rest on page two');
    }
    public function test_a_very_full_application_still_prints_on_two_pages(): void
    {
        EventCampaign::updateOrCreate(['slug' => 'jshikkhabritti'], [
            'title' => 'Sch', 'type' => 'scholarship', 'is_active' => true, 'has_fee_or_donation' => true, 'fee_amount' => 150,
            'form_settings' => ['is_scholarship_form' => true, 'form_org' => 'A Long Organization Name For The Header Line', 'form_venue' => 'Rangpur Zilla School Field', 'form_date' => '12 December 2026'],
        ]);
        $long = str_repeat('Very Long Institute Name ', 4);
        $words = trim(str_repeat('word ', 50));
        $this->post('/jshikkhabritti', $this->payload([
            'ssc_institute' => $long, 'hsc_roll' => '1', 'hsc_board' => 'B', 'hsc_institute' => $long, 'hsc_year' => '2021', 'hsc_gpa' => '5',
            'scholarship_degree' => 'MASTERS', 'grad_roll' => '9', 'grad_board' => 'NU', 'grad_institute' => $long, 'grad_year' => '2025', 'grad_cgpa' => '3.5',
            'other_roll' => '7', 'other_board' => 'NU', 'other_institute' => $long, 'other_year' => '2027', 'other_cgpa' => '3.6',
            'college_name' => $long, 'permanent_address' => str_repeat('Village Road House Upazila District Division ', 5), 'present_address' => str_repeat('Other Present Address Lane Block ', 5),
            'guardian_name' => 'Guardian Name', 'guardian_relation' => 'Uncle', 'disability' => 'Yes', 'disability_type' => 'Other', 'disability_details' => str_repeat('Some detail ', 5),
            'prev_joyee' => 'Yes', 'prev_scholarships' => [['year' => '2024', 'times' => '1', 'amount' => '5000'], ['year' => '2025', 'times' => '1', 'amount' => '6000'], ['year' => '2026', 'times' => '1', 'amount' => '7000']],
            'other_scholarship' => 'Yes', 'other_scholarship_details' => str_repeat('Other Trust ', 4),
            'books_read_count' => '40', 'reading_habit' => 'I have read', 'books_read_names' => str_repeat("A Book Title By Some Author\n", 8),
            'scholarship_reason' => $words, 'payment_method' => 'bkash', 'transaction_id' => 'TRX123456',
            'pay_method' => 'Bank', 'pay_account_no' => '1234567890123', 'pay_bank_name' => 'A Very Long Bank Name Limited', 'pay_bank_branch' => 'Main Branch Rangpur',
        ]))->assertSessionHasNoErrors();
        $reg = EventRegistration::firstOrFail();

        $pdf = $this->get($reg->scholarshipUrl('pdf'))->assertOk()->getContent();
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page[^s]/', $pdf), 'even the longest answers fit on two pages');
    }
}