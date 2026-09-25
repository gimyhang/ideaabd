<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\EventCampaign;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        EventCampaign::updateOrCreate(
            ['slug' => 'jshikkhabritti'],
            [
                'title'               => 'Joyee Shikkha Britti Application Form',
                'type'                => 'scholarship',
                'badge_text'          => 'Education Scholarship 2026',
                'short_description'   => 'Higher Secondary & Bachelor\'s Education Scholarship Application Form',
                'description'         => "Higher Secondary (HSC) and Bachelor's (Hons) students can apply for the Joyee Shikkha Britti. Please provide all academic and personal details accurately. Attach a passport size photograph and maximum 50 words statement on why you require this scholarship.",
                'theme_color'         => '#0f3a68',
                'has_fee_or_donation' => false,
                'fee_amount'          => 0.00,
                'is_active'           => true,
                'success_message'     => 'Your scholarship application has been successfully submitted! Please print or download your application form below.',
                'custom_fields'       => [
                    ['name' => 'group', 'label' => 'Group / Stream', 'type' => 'text', 'required' => true],
                    ['name' => 'admission_roll', 'label' => 'Admission / Application Roll', 'type' => 'text', 'required' => true],
                    ['name' => 'merit_position', 'label' => 'Merit Position', 'type' => 'text', 'required' => false],
                    ['name' => 'college_name', 'label' => 'College Name', 'type' => 'text', 'required' => true],
                    ['name' => 'college_code', 'label' => 'College Code', 'type' => 'text', 'required' => false],
                    ['name' => 'assigned_subject', 'label' => 'Assigned Subject / Dept', 'type' => 'text', 'required' => true],
                    ['name' => 'previous_subject', 'label' => 'Previous Subject / Level', 'type' => 'text', 'required' => false],
                    ['name' => 'subject_choice', 'label' => 'Subject Choice', 'type' => 'text', 'required' => false],
                    ['name' => 'father_name', 'label' => 'Father\'s Name', 'type' => 'text', 'required' => true],
                    ['name' => 'mother_name', 'label' => 'Mother\'s Name', 'type' => 'text', 'required' => true],
                    ['name' => 'guardian_name', 'label' => 'Guardian\'s Name', 'type' => 'text', 'required' => false],
                    ['name' => 'guardian_phone', 'label' => 'Guardian\'s Mobile No.', 'type' => 'text', 'required' => false],
                    ['name' => 'gender', 'label' => 'Gender', 'type' => 'text', 'required' => true],
                    ['name' => 'religion', 'label' => 'Religion', 'type' => 'text', 'required' => true],
                    ['name' => 'nationality', 'label' => 'Nationality', 'type' => 'text', 'required' => true],
                    ['name' => 'birth_date', 'label' => 'Birth Date', 'type' => 'text', 'required' => true],
                    ['name' => 'marital_status', 'label' => 'Marital Status', 'type' => 'text', 'required' => true],
                    ['name' => 'annual_income', 'label' => 'Annual Income (Tk)', 'type' => 'text', 'required' => true],
                    ['name' => 'ssc_roll', 'label' => 'SSC Roll', 'type' => 'text', 'required' => true],
                    ['name' => 'ssc_board', 'label' => 'SSC Board', 'type' => 'text', 'required' => true],
                    ['name' => 'ssc_year', 'label' => 'SSC Year', 'type' => 'text', 'required' => true],
                    ['name' => 'ssc_gpa', 'label' => 'SSC GPA', 'type' => 'text', 'required' => true],
                    ['name' => 'hsc_roll', 'label' => 'HSC Roll', 'type' => 'text', 'required' => false],
                    ['name' => 'hsc_board', 'label' => 'HSC Board', 'type' => 'text', 'required' => false],
                    ['name' => 'hsc_year', 'label' => 'HSC Year', 'type' => 'text', 'required' => false],
                    ['name' => 'hsc_gpa', 'label' => 'HSC GPA', 'type' => 'text', 'required' => false],
                    ['name' => 'permanent_address', 'label' => 'Permanent Address', 'type' => 'textarea', 'required' => true],
                    ['name' => 'permanent_district', 'label' => 'Permanent District', 'type' => 'text', 'required' => true],
                    ['name' => 'present_address', 'label' => 'Present Address', 'type' => 'textarea', 'required' => true],
                    ['name' => 'present_district', 'label' => 'Present District', 'type' => 'text', 'required' => true],
                    ['name' => 'scholarship_reason', 'label' => 'Reason for Scholarship (Max 50 Words)', 'type' => 'textarea', 'required' => true],
                ],
                'form_settings'       => [
                    'is_scholarship_form' => true,
                ],
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe keep
    }
};
