<?php

namespace App\Http\Controllers;

use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Models\User;
use App\Support\PhoneNormalizer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PublicEventRegistrationController extends Controller
{
    /**
     * Show the public registration form for a given slug (e.g. /jsb, /rsu, /pathagar).
     * The campaign's own kind (scholarship / writer / library / event) picks the form - never the address typed.
     */
    /** Statuses in which a card, slip or check-in is allowed. */
    public const APPROVED_STATUSES = ['confirmed', 'approved', 'selected', 'attended'];

    public function show(string $slug)
    {
        $campaign = EventCampaign::resolve($slug, create: true);
        if (!$campaign) {
            abort(404);
        }

        $closed = match (true) {
            !$campaign->is_active    => 'This scholarship/registration program is currently closed.',
            $campaign->isExpired()   => 'The application deadline has passed.',
            $campaign->isFull()      => 'Sorry, the maximum application limit has been reached.',
            default                  => null,
        };
        if ($closed) {
            return view('frontend.events.inactive', ['campaign' => $campaign, 'message' => $closed]);
        }

        $user = auth()->user();

        if ($campaign->isScholarship()) {
            $previewRegNumber = EventRegistration::generateRegNumber($campaign);
            return view('frontend.events.scholarship_register', compact('campaign', 'user', 'previewRegNumber'));
        }

        if ($campaign->isLibrary()) {
            return view('frontend.events.library_register', compact('campaign', 'user'));
        }

        // Writer and plain event forms show the visitor's own earlier registration (matched by their phone only)
        $existingRegistration = null;
        if ($user && $user->phone) {
            $existingRegistration = EventRegistration::where('event_campaign_id', $campaign->id)
                ->whereIn('phone', PhoneNormalizer::normalize($user->phone)['query_phones'])
                ->first();
        }
        $previewRegNumber = $existingRegistration?->registration_number ?? EventRegistration::generateRegNumber($campaign);
        $view = $campaign->isWriter() ? 'frontend.events.writer_register' : 'frontend.events.register';

        return view($view, compact('campaign', 'user', 'existingRegistration', 'previewRegNumber'));
    }
    /**
     * Admin-defined custom fields -> Laravel rules (type, required, dropdown whitelist, length).
     *
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    /**
     * Fields the built-in scholarship form already renders. Admin-defined custom fields with the
     * same name would be duplicate inputs (and break the form), so they are never rendered or validated.
     */
    public const SCHOLARSHIP_BUILTIN_FIELDS = [
        'group', 'admission_roll', 'merit_position', 'college_name', 'college_code', 'assigned_subject',
        'previous_subject', 'subject_choice', 'scholarship_degree', 'admission_fee', 'monthly_fee', 'hostel_cost',
        'father_name', 'mother_name', 'father_income', 'mother_income', 'guardian_income', 'father_phone', 'mother_phone',
        'guardian_name', 'guardian_phone', 'gender', 'religion', 'nationality', 'birth_date', 'marital_status',
        'annual_income', 'guardian_income', 'tuition_fee', 'other_scholarship', 'other_scholarship_details', 'prev_scholarships',
        'prev_joyee', 'books_read_count', 'reading_habit', 'books_read_names',
        'national_id', 'father_occupation', 'mother_occupation', 'father_status', 'mother_status', 'family_count', 'earning_count', 'orphan_status', 'disability', 'disability_type', 'disability_details', 'guardian_relation', 'current_year',
        'pay_method', 'pay_account_owner', 'pay_account_name', 'pay_account_no', 'pay_bank_name', 'pay_bank_branch',
        'ssc_roll', 'ssc_board', 'ssc_institute', 'ssc_year', 'ssc_gpa',
        'hsc_roll', 'hsc_board', 'hsc_institute', 'hsc_year', 'hsc_gpa',
        'grad_roll', 'grad_board', 'grad_institute', 'grad_year', 'grad_cgpa',
        'other_roll', 'other_board', 'other_institute', 'other_year', 'other_cgpa',
        'perm_division', 'permanent_district', 'perm_city_corporation', 'perm_municipality', 'perm_upazila', 'perm_post_office', 'perm_postcode', 'perm_village', 'permanent_address',
        'pres_division', 'present_district', 'pres_city_corporation', 'pres_municipality', 'pres_upazila', 'pres_post_office', 'pres_postcode', 'pres_village', 'present_address',
        'scholarship_reason',
    ];

    /** Every extra answer the built-in forms may send (scholarship, writer, library); only these reach form_data. */
    public const EXTENDED_INPUT_KEYS = [
            'group', 'admission_roll', 'merit_position', 'college_name', 'college_code',
            'assigned_subject', 'previous_subject', 'subject_choice', 'father_name', 'mother_name',
            'guardian_name', 'guardian_phone', 'gender', 'religion', 'nationality', 'birth_date',
            'marital_status', 'annual_income', 
            'ssc_roll', 'ssc_board', 'ssc_institute', 'ssc_year', 'ssc_gpa',
            'hsc_roll', 'hsc_board', 'hsc_institute', 'hsc_year', 'hsc_gpa', 
            'grad_roll', 'grad_board', 'grad_institute', 'grad_year', 'grad_cgpa',
            'other_roll', 'other_board', 'other_institute', 'other_year', 'other_cgpa',
            'perm_division', 'permanent_district', 'perm_city_corporation', 'perm_municipality', 'perm_upazila', 'perm_post_office', 'perm_postcode', 'perm_village', 'permanent_address',
            'pres_division', 'present_district', 'pres_city_corporation', 'pres_municipality', 'pres_upazila', 'pres_post_office', 'pres_postcode', 'pres_village', 'present_address',
            'scholarship_reason', 'scholarship_degree', 'father_income', 'mother_income', 'father_phone', 'mother_phone', 'admission_fee', 'hostel_cost', 'monthly_fee', 'other_scholarship', 'other_scholarship_details', 'prev_scholarships',
            'prev_joyee', 'books_read_count', 'reading_habit', 'books_read_names',
            'national_id', 'nid', 'father_occupation', 'mother_occupation', 'father_status', 'mother_status', 'family_count', 'earning_count', 'orphan_status', 'disability', 'disability_type', 'disability_details', 'guardian_relation', 'current_year',
        'pay_method', 'pay_account_owner', 'pay_account_name', 'pay_account_no', 'pay_bank_name', 'pay_bank_branch',
            // International / Abroad Address Fields
            'resident_type', 'country_name', 'custom_country', 'state_or_city', 'zip_code', 'foreign_address',
            // Writer & Cultural Artist Specific Fields
            'author_category', 'author_categories', 'art_medium', 'organization_name', 'published_books_count', 'notable_books', 'magazine_name', 'magazine_issue_count',
            // Library & Book Grant Specific Fields
            'library_name', 'library_type', 'established_year', 'reg_no', 
            'president_name', 'president_phone', 'secretary_name', 'secretary_phone',
            'reader_count', 'current_book_count', 'preferred_genres', 'delivery_method', 'division', 'remarks', 'library_address',
            'has_previous_books', 'previous_books_year', 'previous_books_count'
        ];

    /** Answers that arrive as a list (checkbox groups). */
    public const LIST_INPUT_KEYS = ['author_categories', 'preferred_genres'];

    /** Free-text answers that may be long. */
    public const LONG_TEXT_KEYS = ['notable_books', 'remarks', 'foreign_address', 'library_address', 'permanent_address', 'present_address', 'books_read_names', 'scholarship_reason'];

    protected function customFieldRules(EventCampaign $campaign): array
    {
        $rules = [];
        $attributes = [];
        $isScholarship = $campaign->isScholarship();

        foreach ((array) ($campaign->custom_fields ?? []) as $i => $field) {
            $name = $field['name'] ?? null;
            if (!$name || !preg_match('/^[A-Za-z0-9_]{1,60}$/', (string) $name)) {
                continue;
            }
            if ($isScholarship && in_array($name, self::SCHOLARSHIP_BUILTIN_FIELDS, true)) {
                continue;
            }
            // Never let a custom key shadow a built-in column of the form.
            if (in_array($name, ['name', 'phone', 'email', 'password', 'address', 'district', 'thana', 'amount_paid', 'transaction_id', 'payment_method', 'student_photo', 'optimized_photo_data'], true)) {
                continue;
            }

            $type = $field['type'] ?? 'text';
            $base = !empty($field['required']) ? ['required'] : ['nullable'];

            switch ($type) {
                case 'number':
                    $base[] = 'numeric';
                    break;
                case 'email':
                    $base[] = 'email';
                    $base[] = 'max:255';
                    break;
                case 'select':
                    $options = array_values(array_filter(array_map('trim', explode(',', (string) ($field['options'] ?? ''))), fn ($o) => $o !== ''));
                    $base[] = $options ? \Illuminate\Validation\Rule::in($options) : 'string';
                    break;
                case 'textarea':
                    $base[] = 'string';
                    $base[] = 'max:2000';
                    break;
                default:
                    $base[] = 'string';
                    $base[] = 'max:500';
            }

            $rules[$name] = $base;
            $attributes[$name] = $field['label'] ?? $name;
        }

        return [$rules, $attributes];
    }

    /**
     * Convert Bengali digits to English digits
     */
    protected function normalizeBnToEn(?string $str): string
    {
        if ($str === null || $str === '') return '';
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        return str_replace($bn, $en, $str);
    }

    /**
     * Handle form submission with auto-optimized image processing.
     */
    public function submit(Request $request, string $slug)
    {
        $campaign = EventCampaign::resolve($slug);
        if (!$campaign) {
            abort(404);
        }

        if (!$campaign->canAcceptRegistrations()) {
            return back()->with('error', 'This application form is currently closed.');
        }

        $isScholarship = $campaign->isScholarship();
        $isLibrary = $campaign->isLibrary();
        $isWriter = $campaign->isWriter();
        $requiresApproval = $campaign->requiresApproval();

        // Bengali digits must not fail numeric/date validation.
        $digitKeys = ['father_income', 'mother_income', 'guardian_income', 'annual_income', 'admission_fee', 'monthly_fee', 'hostel_cost', 'tuition_fee', 'amount_paid', 'birth_date', 'national_id', 'pay_account_no', 'family_count', 'earning_count', 'books_read_count'];
        $request->merge(collect($digitKeys)
            ->filter(fn ($k) => is_string($request->input($k)))
            ->mapWithKeys(fn ($k) => [$k => $this->normalizeBnToEn(trim($request->input($k)))])
            ->all());
        foreach (['national_id', 'pay_account_no'] as $k) {
            if (is_string($request->input($k))) {
                $request->merge([$k => preg_replace('/\D+/', '', $request->input($k))]);
            }
        }

        $rules = [
            'name'                 => 'required|string|max:255',
            'country_code'         => 'nullable|string|max:10',
            'phone'                => 'required|string|max:30',
            'email'                => 'nullable|email|max:255',
            'address'              => 'nullable|string|max:500',
            'district'             => 'nullable|string|max:100',
            'thana'                => 'nullable|string|max:100',
            'institution_or_org'   => 'nullable|string|max:255',
            'designation_or_class' => 'nullable|string|max:255',
            'amount_paid'          => 'nullable|numeric|min:0',
            'payment_method'       => 'nullable|string|max:50',
            'transaction_id'       => 'nullable|string|max:100',
            'password'             => ['nullable', 'string', 'min:8', 'max:64', new \App\Rules\StrongPassword()],
            'student_photo'        => 'nullable|file|mimes:jpeg,jpg,png,webp,gif|max:25600',
            'optimized_photo_data' => ['nullable', 'string', 'max:14000000', 'regex:/^data:image\/(png|jpe?g|webp);base64,/'],
            'scholarship_reason'   => 'nullable|string|max:2000',
            'prev_scholarships'    => 'nullable|array|max:5',
            'prev_scholarships.*.year'   => 'nullable|string|max:20',
            'prev_scholarships.*.times'  => 'nullable|string|max:20',
            'prev_scholarships.*.amount' => 'nullable|string|max:30',
            'admission_fee'        => 'nullable|numeric|min:0',
            'hostel_cost'          => 'nullable|numeric|min:0',
            'father_income'        => 'nullable|numeric|min:0',
            'mother_income'        => 'nullable|numeric|min:0',
            'guardian_income'      => 'nullable|numeric|min:0',
            'father_phone'         => 'nullable|string|max:20',
            'mother_phone'         => 'nullable|string|max:20',
            'scholarship_degree'   => 'nullable|string|max:20',
            'birth_date'           => 'nullable|date|after_or_equal:1970-01-01|before_or_equal:' . now()->subYears(10)->toDateString(),
            'monthly_fee'          => 'nullable|numeric|min:0',
            'other_scholarship'    => 'nullable|string|max:10',
            'other_scholarship_details' => 'nullable|string|max:255',
            'prev_joyee'           => 'nullable|in:Yes,No',
            'books_read_count'     => 'nullable|integer|min:0|max:9999',
            'reading_habit'        => 'nullable|string|max:30',
            'books_read_names'     => 'nullable|string|max:1000',
            'national_id'          => 'nullable|string|max:20',
            'nid'                  => 'nullable|string|max:30',
            'father_occupation'    => 'nullable|string|max:100',
            'mother_occupation'    => 'nullable|string|max:100',
            'family_count'         => 'nullable|integer|min:1|max:40',
            'earning_count'        => 'nullable|integer|min:0|max:40',
            'orphan_status'        => 'nullable|string|max:30',
            'disability'           => 'nullable|in:Yes,No',
            'disability_type'      => 'nullable|in:Visual,Hearing,Speech,Physical,Intellectual,Autism,Multiple,Other',
            'father_status'        => 'nullable|in:Alive,Deceased',
            'mother_status'        => 'nullable|in:Alive,Deceased',
            'disability_details'   => 'nullable|string|max:255',
            'guardian_relation'    => 'nullable|string|max:50',
            'current_year'         => 'nullable|string|max:50',
            'pay_method'           => 'nullable|string|max:20',
            'pay_account_owner'    => 'nullable|string|max:20',
            'pay_account_name'     => 'nullable|string|max:120',
            'pay_account_no'       => 'nullable|string|max:30',
            'pay_bank_name'        => 'nullable|string|max:120',
            'pay_bank_branch'      => 'nullable|string|max:120',
        ];

        if ($isScholarship) {
            // Mandatory parts of the scholarship form (the page enforces the same rules in the browser)
            $degree = strtoupper((string) $request->input('scholarship_degree'));
            $levelsNeeded = ['HSC' => ['ssc'], 'HONOURS' => ['ssc', 'hsc'], 'MASTERS' => ['ssc', 'hsc', 'grad']][$degree] ?? ['ssc'];
            foreach ($levelsNeeded as $lv) {
                $rules["{$lv}_roll"]      = 'required|string|max:50';
                $rules["{$lv}_board"]     = 'required|string|max:100';
                $rules["{$lv}_institute"] = 'required|string|max:255';
                $rules["{$lv}_year"]      = 'required|string|max:10';
                $rules[$lv === 'ssc' ? 'ssc_gpa' : "{$lv}_gpa"] = 'required|string|max:10';
            }
            if (in_array('grad', $levelsNeeded, true)) {
                unset($rules['grad_gpa']);
                $rules['grad_cgpa'] = 'required|string|max:10';
            }
            $rules['guardian_phone']   = 'required|string|min:10|max:20';
            $rules['prev_joyee']       = 'required|in:Yes,No';
            $rules['other_scholarship'] = 'required|in:Yes,No';
            $rules['other_scholarship_details'] = 'nullable|required_if:other_scholarship,Yes|string|max:255';
            $rules['books_read_count'] = 'required|integer|min:0|max:9999';
            $rules['reading_habit']    = 'required|in:I have read,I do not read';
            $rules['books_read_names'] = ($request->integer('books_read_count') > 0 || $request->input('reading_habit') === 'I have read')
                ? 'required|string|max:1000' : 'nullable|string|max:1000';
            // Identity, family and payment details needed to decide on and pay a scholarship
            $rules['national_id']        = ['required', 'regex:/^\d{10}$|^\d{13}$|^\d{17}$/'];   // NID (10/13/17 digits) or birth registration (17 digits)
            $rules['father_status']      = 'required|in:Alive,Deceased';
            $rules['mother_status']      = 'required|in:Alive,Deceased';
            $rules['father_occupation']  = $request->input('father_status') === 'Deceased' ? 'nullable|string|max:100' : 'required|string|max:100';
            $rules['mother_occupation']  = $request->input('mother_status') === 'Deceased' ? 'nullable|string|max:100' : 'required|string|max:100';
            $rules['family_count']       = 'required|integer|min:1|max:40';
            $rules['earning_count']      = 'required|integer|min:0|max:40|lte:family_count';
            $rules['disability']         = 'required|in:Yes,No';
            $rules['disability_type']    = 'nullable|required_if:disability,Yes|in:Visual,Hearing,Speech,Physical,Intellectual,Autism,Multiple,Other';
            $rules['disability_details'] = $request->input('disability_type') === 'Other' ? 'required|string|max:255' : 'nullable|string|max:255';
            $rules['guardian_relation']  = $request->filled('guardian_name') ? 'required|string|max:50' : 'nullable|string|max:50';
            $rules['current_year']       = 'required|string|max:50';
            $rules['pay_method']         = 'required|in:bKash,Nagad,Rocket,Bank';
            $rules['pay_account_owner']  = 'required|in:Self,Father,Mother,Guardian';
            $rules['pay_account_name']   = 'required|string|max:120';
            $rules['pay_account_no']     = $request->input('pay_method') === 'Bank'
                ? ['required', 'regex:/^\d{8,20}$/']
                : ['required', 'regex:/^01\d{9}$/'];
            $rules['pay_bank_name']      = 'nullable|required_if:pay_method,Bank|string|max:120';
            $rules['pay_bank_branch']    = 'nullable|required_if:pay_method,Bank|string|max:120';
            if ($request->input('prev_joyee') === 'Yes') {
                $rules['prev_scholarships.0.year']   = 'required|string|max:20';
                $rules['prev_scholarships.0.amount'] = 'required|string|max:30';
            }
        }

        if ($isLibrary) {
            $rules['institution_or_org']   = 'required|string|max:255';
            $rules['president_name']       = 'required|string|max:255';
            $rules['president_phone']      = 'required|string|max:30';
            $rules['secretary_name']       = 'required|string|max:255';
            $rules['secretary_phone']      = 'required|string|max:30';
            $rules['delivery_method']      = 'required|string|max:255';
            $rules['has_previous_books']   = 'nullable|string|max:30';
            $rules['previous_books_year']  = 'nullable|string|max:30';
            $rules['previous_books_count'] = 'nullable|string|max:30';
        }

        // Every other answer a form can send gets a size limit, so nothing unbounded is stored.
        foreach (self::EXTENDED_INPUT_KEYS as $k) {
            if (isset($rules[$k]) || in_array($k, self::LIST_INPUT_KEYS, true)) {
                continue;
            }
            $rules[$k] = 'nullable|string|max:' . (in_array($k, self::LONG_TEXT_KEYS, true) ? 1000 : 255);
        }
        foreach (self::LIST_INPUT_KEYS as $k) {
            $rules[$k] = 'nullable|array|max:20';
            $rules["{$k}.*"] = 'nullable|string|max:100';
        }

        [$customRules, $customAttributes] = $this->customFieldRules($campaign);
        $validated = $request->validate($rules + $customRules, [], $customAttributes);

        // Photo: mandatory for writers and scholarship applicants; whatever is sent must be a real, reasonably sized image.
        $hasPhotoData = $request->filled('optimized_photo_data') || $request->hasFile('student_photo');
        if ($hasPhotoData && !$this->photoIsReadable($request)) {
            return back()->withInput()->with('error', 'The photo could not be read. Please choose a JPG, PNG or WebP picture.');
        }
        if (($isWriter || $isScholarship) && !$hasPhotoData) {
            $hasEarlierEntry = EventRegistration::where('event_campaign_id', $campaign->id)
                ->whereIn('phone', PhoneNormalizer::normalize($validated['phone'], $request->input('country_code'))['query_phones'])
                ->exists();
            if (!$hasEarlierEntry) {
                return back()->withInput()->with('error', 'Please upload a passport-size photo to complete the registration (photo is mandatory).');
            }
        }

        // Max 50 words check
        if ($isScholarship && $request->filled('scholarship_reason')) {
            $words = preg_split('/\s+/', trim(strip_tags($request->input('scholarship_reason'))), -1, PREG_SPLIT_NO_EMPTY);
            if (count($words) > 55) {
                return back()->withInput()->with('error', 'Your reason exceeds the 50-word limit (' . count($words) . ' words). Please shorten your statement.');
            }
        }

        // Normalize Phone & Country Code using universal PhoneNormalizer
        $pInfo = \App\Support\PhoneNormalizer::normalize($validated['phone'], $request->input('country_code'));
        $localPhone = $pInfo['local_phone'];
        $formattedPhone = $pInfo['formatted_phone'];
        $cleanDigits = $pInfo['core'];
        $queryPhones = $pInfo['query_phones'];
        $countryCode = $pInfo['country_code'];

        $email = !empty($validated['email']) ? strtolower(trim($validated['email'])) : null;

        // Customer account sync.
        // A registration form must never sign a visitor in as an existing account: anyone can type
        // another person's phone or email. So an existing account is neither modified nor logged in;
        // only a brand-new account (created right here, password chosen by this visitor) is signed in.
        $user = null;
        if (auth()->check()) {
            $user = auth()->user();
        } else {
            try {
                $phoneOwner = User::withTrashed()->where(function ($q) use ($queryPhones, $pInfo) {
                    $q->whereIn('phone', $queryPhones);
                    if ($pInfo['is_bd'] && strlen($pInfo['core']) === 10) {
                        $q->orWhere('phone', 'LIKE', '%' . $pInfo['core']);
                    }
                })->first();

                if (!$phoneOwner) {
                    $userEmail = $email ?: ($cleanDigits . '@customer.ideaabd.com');
                    if (User::withTrashed()->where('email', $userEmail)->exists()) {
                        // The typed email belongs to someone else: keep it out of the new account.
                        $userEmail = $cleanDigits . '_' . time() . '@customer.ideaabd.com';
                    }
                    $userPassword = !empty($validated['password']) ? $validated['password'] : Str::random(12);

                    $user = User::create([
                        'name'              => $validated['name'],
                        'phone'             => $localPhone,
                        'email'             => $userEmail,
                        'password'          => Hash::make($userPassword),
                        'role'              => User::ROLE_BUYER,
                        'reg_type'          => 'customer',
                        'reg_status'        => User::STATUS_APPROVED,
                        'is_active'         => true,
                        'phone_verified_at' => null,
                        'email_verified_at' => null,
                        'reg_data'          => [
                            'source'              => 'event_application',
                            'country_code'        => $countryCode,
                            'campaign_slug'       => $campaign->slug,
                            'district'            => $validated['district'] ?? ($request->input('permanent_district') ?: $request->input('present_district')),
                            'institution'         => $validated['institution_or_org'] ?? $request->input('college_name'),
                            'has_custom_password' => !empty($validated['password']),
                        ],
                    ]);

                    \Illuminate\Support\Facades\Auth::login($user);
                }
            } catch (\Throwable $e) {
                Log::warning("User auto-sync notice in event registration: " . $e->getMessage());
            }
        }

        // One registration per phone number per campaign. The signed-in account is NOT part of the check:
        // a shared computer or a parent/teacher registering someone else must not be blocked by the previous person.
        $existingRegistration = EventRegistration::where('event_campaign_id', $campaign->id)
            ->whereIn('phone', $queryPhones)
            ->first();

        if ($existingRegistration) {
            $this->rememberRegistration($campaign, $existingRegistration);

            return redirect()->route('event.success', $campaign->slug)
                ->with('info', "মোবাইল নম্বরে ইতোমধ্যে নিবন্ধন সম্পন্ন হয়েছে! রেজিস্ট্রেশন রোল: #{$existingRegistration->registration_number}");
        }
        // Custom field answers & auto-optimized photo processing
        $customFieldAnswers = [];

        // 1. Process optimized photo (client canvas or raw upload)
        if ($request->filled('optimized_photo_data')) {
            $savedPhoto = $this->optimizeAndSavePhoto($request->input('optimized_photo_data'));
            if ($savedPhoto) {
                $customFieldAnswers['student_photo'] = $savedPhoto;
            }
        } elseif ($request->hasFile('student_photo')) {
            $savedPhoto = $this->optimizeAndSavePhoto($request->file('student_photo'));
            if ($savedPhoto) {
                $customFieldAnswers['student_photo'] = $savedPhoto;
            }
        }

        if (($isWriter || $isScholarship) && $hasPhotoData && empty($customFieldAnswers['student_photo'])) {
            return back()->withInput()->with('error', 'The photo could not be saved. Please choose a JPG, PNG or WebP picture.');
        }

        // 2. Extract scholarship, writer & library fields
        $extendedInputKeys = self::EXTENDED_INPUT_KEYS;

        foreach ($extendedInputKeys as $key) {
            if ($request->has($key)) {
                $val = $request->input($key);
                if (is_string($val)) {
                    if (str_contains($key, 'phone')) {
                        // president_phone -> president_country_code, etc.; fields without a selector default to +880
                        $ccKey = str_replace('_phone', '_country_code', $key);
                        $pNorm = \App\Support\PhoneNormalizer::normalize($val, $request->input($ccKey) ?: '+880');
                        $val = $pNorm['local_phone'];
                    } elseif (preg_match('/roll|year|count|fee|income|cost|gpa|postcode|national_id|^nid$|account_no/', $key)) {
                        $val = $this->normalizeBnToEn($val);
                    }
                }
                if ($key === 'prev_scholarships' && is_array($val)) {
                    $val = collect($val)->map(fn ($r) => array_map(fn ($x) => $this->normalizeBnToEn(is_string($x) ? trim($x) : ''), (array) $r))->values()->all();
                }
                $customFieldAnswers[$key] = $val;
            }
        }

        if ($isScholarship) {
            // A deceased parent has no occupation, income or mobile; orphan status is derived, never trusted from the client
            $dead = [];
            foreach (['father', 'mother'] as $pk) {
                $dead[$pk] = ($customFieldAnswers[$pk . '_status'] ?? '') === 'Deceased';
                if ($dead[$pk]) {
                    $customFieldAnswers[$pk . '_occupation'] = 'Nil';
                    unset($customFieldAnswers[$pk . '_income'], $customFieldAnswers[$pk . '_phone']);
                }
            }
            $customFieldAnswers['orphan_status'] = $dead['father'] && $dead['mother'] ? 'Both deceased' : ($dead['father'] ? 'Father deceased' : ($dead['mother'] ? 'Mother deceased' : 'None'));
            if (($customFieldAnswers['disability'] ?? 'No') !== 'Yes') {
                unset($customFieldAnswers['disability_type'], $customFieldAnswers['disability_details']);
            }
        }

        if ($request->has('author_category') && is_string($request->input('author_category'))) {
            $catSingle = trim($request->input('author_category'));
            if ($catSingle !== '') {
                $customFieldAnswers['author_category'] = $catSingle;
                $customFieldAnswers['author_categories'] = [$catSingle];
            }
        } elseif ($request->has('author_categories') && is_array($request->input('author_categories'))) {
            $catList = array_values(array_filter($request->input('author_categories')));
            $customFieldAnswers['author_categories'] = $catList;
            $customFieldAnswers['author_category'] = implode(', ', $catList);
        }

        // Sanitize Resident Type & Country
        $resType = $request->input('resident_type', 'domestic');
        if ($resType === 'international' || $resType === 'foreign') {
            $customFieldAnswers['resident_type'] = 'international';
            $cName = $request->input('country_name');
            if ($cName === 'OTHER' && $request->filled('custom_country')) {
                $cName = trim($request->input('custom_country'));
            }
            $customFieldAnswers['country_name'] = $cName ?: 'Foreign/International';
        } else {
            $customFieldAnswers['resident_type'] = 'domestic';
            $customFieldAnswers['country_name'] = 'বাংলাদেশ (Bangladesh)';
            unset(
                $customFieldAnswers['custom_country'],
                $customFieldAnswers['state_or_city'],
                $customFieldAnswers['zip_code'],
                $customFieldAnswers['foreign_address']
            );
        }

        if (!empty($campaign->custom_fields) && is_array($campaign->custom_fields)) {
            foreach ($campaign->custom_fields as $field) {
                $fName = $field['name'] ?? null;
                if ($fName && $request->has($fName) && !isset($customFieldAnswers[$fName])) {
                    $customFieldAnswers[$fName] = $request->input($fName);
                }
            }
        }

        $authorCatVal = is_array($request->input('author_categories')) ? implode(', ', $request->input('author_categories')) : $request->input('author_category');
        $institution = $validated['institution_or_org'] ?? ($request->input('organization_name') ?: ($request->input('library_name') ?: ($request->input('college_name') ?: ($request->input('magazine_name') ?: null))));
        $designation = $validated['designation_or_class'] ?? ($authorCatVal ?: ($request->input('art_medium') ?: ($request->input('library_type') ?: ($request->input('assigned_subject') ?: ($request->input('group') ?: null)))));
        $address = $validated['address'] ?? ($request->input('foreign_address') ?: ($request->input('library_address') ?: ($request->input('present_address') ?: ($request->input('permanent_address') ?: null))));
        $district = $validated['district'] ?? ($request->input('state_or_city') ?: ($request->input('district') ?: ($request->input('present_district') ?: ($request->input('permanent_district') ?: null))));

        $amountPaid = floatval($validated['amount_paid'] ?? 0);
        if ($isScholarship) {
            $amountPaid = 0;                       // a scholarship takes no application payment
        }
        $paymentStatus = 'free';
        if ($campaign->has_fee_or_donation && !$isScholarship) {
            $paymentStatus = !empty($validated['transaction_id']) ? 'pending' : 'unpaid';
        }

        $regNumber = null; // assigned inside the locked section below

        $initialStatus = $requiresApproval ? 'pending' : 'confirmed';

        $hasPrevBooks = $request->input('has_previous_books') ?: ($customFieldAnswers['has_previous_books'] ?? null);
        $prevYear = !empty($customFieldAnswers['previous_books_year']) ? $customFieldAnswers['previous_books_year'] : null;
        $prevCount = !empty($customFieldAnswers['previous_books_count']) ? intval($customFieldAnswers['previous_books_count']) : null;

        // Linked to the signed-in account only when it is that person's own number (a library keeps its manager's account).
        $ownerId = ($user && ($isLibrary || array_intersect(PhoneNormalizer::normalize((string) $user->phone)['query_phones'], $queryPhones))) ? $user->id : null;

        $registration = \Illuminate\Support\Facades\DB::transaction(function () use ($campaign, $queryPhones, $ownerId, $request, $validated, $localPhone, $email, $address, $district, $institution, $designation, $hasPrevBooks, $prevYear, $prevCount, $amountPaid, $paymentStatus, $customFieldAnswers, $initialStatus, &$regNumber) {
            // Serialise submissions per campaign so a double click or a retry can neither
            // create two registrations for one phone nor hand out the same number twice.
            EventCampaign::whereKey($campaign->id)->lockForUpdate()->first();

            $already = EventRegistration::where('event_campaign_id', $campaign->id)->whereIn('phone', $queryPhones)->first();
            if ($already) {
                return $already;
            }

            // the limit is checked again under the lock, so the last seats cannot be oversold
            if ($campaign->max_participants && $campaign->registrations()->count() >= $campaign->max_participants) {
                return null;
            }

            $regNumber = EventRegistration::generateRegNumber($campaign);
            return EventRegistration::create([
            'event_campaign_id'    => $campaign->id,
            'user_id'              => $ownerId,
            'registration_number'  => $regNumber,
            'name'                 => $validated['name'],
            'phone'                => $localPhone,
            'email'                => $email,
            'address'              => $address,
            'district'             => $district,
            'thana'                => $validated['thana'] ?? null,
            'institution_or_org'   => $institution,
            'designation_or_class' => $designation,
            'has_previous_books'   => $hasPrevBooks,
            'previous_books_year'  => $prevYear,
            'previous_books_count' => $prevCount,
            'amount_paid'          => $amountPaid,
            'payment_method'       => $validated['payment_method'] ?? ($campaign->has_fee_or_donation ? 'online' : 'free'),
            'transaction_id'       => $validated['transaction_id'] ?? null,
            'payment_status'       => $paymentStatus,
            'form_data'            => $customFieldAnswers,
            'status'               => $initialStatus,
            'ip_address'           => $request->ip(),
            ]);
        });

        if (!$registration) {
            return back()->withInput()->with('error', 'Sorry, the maximum application limit has been reached.');
        }

        // Lost the race to an identical submission: show that registration instead of a second one.
        if ($registration->wasRecentlyCreated === false) {
            $this->rememberRegistration($campaign, $registration);

            return redirect()->route('event.success', $campaign->slug)
                ->with('info', "মোবাইল নম্বরে ইতোমধ্যে নিবন্ধন সম্পন্ন হয়েছে! রেজিস্ট্রেশন রোল: #{$registration->registration_number}");
        }

        // Confirmation SMS with Direct Card Link (Admin Approval required if applicable)
        try {
            $downloadUrl = route('event.registration.print', $regNumber);
            if ($isLibrary) {
                if ($requiresApproval) {
                    $smsText = "আইডিয়া প্রকাশন — আপনার পাঠাগারের নিবন্ধন সফলভাবে জমা হয়েছে! Reg No: #{$regNumber}। এডমিন অনুমোদন দিলে আপনাকে মেসেজে চূড়ান্ত স্লিপের লিংক পাঠানো হবে। www.ideaabd.com";
                } else {
                    $smsText = "আইডিয়া প্রকাশন — আপনার পাঠাগারের নিবন্ধন সফল হয়েছে! Reg No: #{$regNumber}। স্লিপ ডাউনলোড: {$downloadUrl} । www.ideaabd.com";
                }
            } elseif ($requiresApproval) {
                $smsText = "আইডিয়া প্রকাশন — '{$campaign->title}'-এ আপনার তথ্য সফলভাবে জমা হয়েছে (Reg: #{$regNumber})। এডমিন অনুমোদন দিলে আপনাকে মেসেজে কার্ডের লিংক পাঠানো হবে। www.ideaabd.com";
            } else {
                $smsText = "আইডিয়া প্রকাশন — '{$campaign->title}'-এ আপনার আবেদন সফল হয়েছে! Reg No: #{$regNumber}। কার্ড ডাউনলোড লিংক: {$downloadUrl} । www.ideaabd.com";
            }
            // the SMS gateway can take many seconds: send after the visitor already has their page
            defer(function () use ($localPhone, $smsText) {
                try {
                    \App\Services\SmsService::send($localPhone, $smsText);
                } catch (\Throwable $e) {
                    Log::warning("Event SMS error: " . $e->getMessage());
                }
            });
        } catch (\Throwable $e) {
            Log::warning("Event SMS error: " . $e->getMessage());
        }

        $this->rememberRegistration($campaign, $registration);

        if ($isLibrary) {
            $successNotice = 'ধন্যবাদ! আপনার পাঠাগার নিবন্ধন সম্পন্ন হয়েছে।';
            if (auth()->check()) {
                return redirect()->route('my-account', ['tab' => 'libraryGrant'])
                    ->with('success', $successNotice);
            }
        } elseif ($isWriter) {
            $successNotice = 'ধন্যবাদ! রংপুর সাহিত্য উৎসব ও লিটিলম্যাগমেলায় আপনার তথ্য জমা হয়েছে। এডমিন অনুমোদন দিলে কার্ড ডাউনলোড করতে পারবেন।';
        } elseif ($isScholarship) {
            $successNotice = 'Thank you! Your application has been submitted.';
        } else {
            $successNotice = 'ধন্যবাদ! আপনার আবেদন সফলভাবে জমা হয়েছে।';
        }

        return redirect()->route('event.success', $campaign->slug)
            ->with('success', $successNotice);
    }

    /** What the "thank you" page shows. Tied to its campaign, so another campaign's page never displays it. */
    protected function rememberRegistration(EventCampaign $campaign, EventRegistration $registration): void
    {
        session(['recent_event_registration' => [
            'campaign_title'      => $campaign->title,
            'campaign_slug'       => $campaign->slug,
            'registration_number' => $registration->registration_number,
            'name'                => $registration->name,
            'phone'               => $registration->phone,
            'category'            => $registration->designation_or_class,
            'created_at'          => ($registration->created_at ?? now())->format('d M, Y - h:i A'),
            'amount_paid'         => $registration->amount_paid,
            'payment_status'      => $registration->payment_status,
            'is_scholarship'      => $campaign->isScholarship(),
            'is_writer'           => $campaign->isWriter(),
            'is_library'          => $campaign->isLibrary(),
            'status'              => $registration->status,
        ]]);
    }

    /** The photo that came with the form (canvas data or file) is a decodable image of sane size. */
    protected function photoIsReadable(Request $request): bool
    {
        if ($request->filled('optimized_photo_data')) {
            $raw = base64_decode(substr((string) strstr($request->input('optimized_photo_data'), ','), 1), true);
            $info = $raw ? @getimagesizefromstring($raw) : false;
        } else {
            $info = @getimagesize($request->file('student_photo')->getRealPath());
        }

        return $info !== false && $info[0] > 0 && $info[1] > 0 && $info[0] <= 10000 && $info[1] <= 10000 && $info[0] * $info[1] <= 60_000_000;
    }
    /**
     * Auto-crop, resize and compress student/author photo to clean 300x360 passport JPEG.
     */
    protected function optimizeAndSavePhoto($file): ?string
    {
        try {
            $imgData = null;
            $extension = 'jpg';

            if (is_string($file) && str_starts_with($file, 'data:image')) {
                $parts = explode(',', $file);
                $imgData = base64_decode($parts[1] ?? '');
            } elseif (is_string($file) && strlen($file) > 100) {
                $imgData = base64_decode($file);
            } elseif ($file instanceof \Illuminate\Http\UploadedFile) {
                $extension = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
                $imgData = file_get_contents($file->getRealPath());
            }

            if (!$imgData || strlen($imgData) < 10) {
                return null;
            }

            $filename = 'scholarships/photos/' . Str::random(24) . '.jpg';
            $fullPath = storage_path('app/public/' . $filename);
            $pubPath  = public_path('storage/' . $filename);

            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $pubDir = dirname($pubPath);
            if (!is_dir($pubDir)) {
                @mkdir($pubDir, 0755, true);
            }

            $src = @imagecreatefromstring($imgData);
            if (!$src) {
                return null;   // not an image: nothing is stored
            }

            // Correct EXIF orientation if uploaded file
            if (function_exists('exif_read_data') && ($file instanceof \Illuminate\Http\UploadedFile)) {
                try {
                    $exif = @exif_read_data($file->getRealPath());
                    if (!empty($exif['Orientation'])) {
                        switch ($exif['Orientation']) {
                            case 3:
                                $src = imagerotate($src, 180, 0);
                                break;
                            case 6:
                                $src = imagerotate($src, -90, 0);
                                break;
                            case 8:
                                $src = imagerotate($src, 90, 0);
                                break;
                        }
                    }
                } catch (\Throwable $e) {}
            }

            $w = imagesx($src);
            $h = imagesy($src);

            $targetW = 300;
            $targetH = 360;

            $srcRatio = $w / $h;
            $targetRatio = $targetW / $targetH;

            if ($srcRatio > $targetRatio) {
                $cropW = (int) ($h * $targetRatio);
                $cropH = $h;
                $cropX = (int) (($w - $cropW) / 2);
                $cropY = 0;
            } else {
                $cropW = $w;
                $cropH = (int) ($w / $targetRatio);
                $cropX = 0;
                $cropY = (int) (($h - $cropH) / 2);
            }

            $dst = imagecreatetruecolor($targetW, $targetH);
            imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);

            imagejpeg($dst, $fullPath, 88);
            @copy($fullPath, $pubPath);

            imagedestroy($src);
            imagedestroy($dst);

            return $filename;
        } catch (\Throwable $e) {
            Log::warning("Photo optimization failed: " . $e->getMessage());
            return null;
        }
    }

    /** Staff, the signed-in owner, or a valid signed link may open a scholarship form. */
    protected function mayOpenScholarshipForm(EventRegistration $registration): bool
    {
        if (request()->hasValidSignature()) {
            return true;
        }
        $user = auth()->user();

        return $user && ($user->isSubAdmin() || (int) $registration->user_id === (int) $user->id);
    }

    protected function scholarshipLinkExpired(EventCampaign $campaign)
    {
        return redirect('/' . $campaign->slug . '#get-form')
            ->with('error', 'This link has expired or is not valid. Enter your name and mobile number under "Get my form" to open your application again.');
    }

    /**
     * "Already applied?" box on the scholarship page: name + mobile -> signed links to the applicant's own form.
     */
    public function lookup(Request $request, string $slug)
    {
        $campaign = EventCampaign::resolve($slug);
        abort_unless($campaign && $campaign->isScholarship(), 404);

        $data = $request->validate([
            'name'  => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:25'],
        ]);

        $norm = fn (string $v) => preg_replace('/\s+/u', ' ', mb_strtolower(trim($v)));
        $phones = \App\Support\PhoneNormalizer::normalize($data['phone'], $request->input('country_code'))['query_phones'];

        $match = EventRegistration::where('event_campaign_id', $campaign->id)
            ->whereIn('phone', $phones)->orderBy('id')->get()
            ->first(function ($reg) use ($norm, $data) {
                similar_text($norm($reg->name), $norm($data['name']), $percent);

                return $norm($reg->name) === $norm($data['name']) || $percent >= 88;      // a typo or a missing middle letter is fine
            });

        // same answer whether the number or the name was wrong: nobody can probe who has applied
        if (!$match) {
            return response()->json(['found' => false, 'message' => 'No application found for this name and mobile number. Please register below.']);
        }

        $match->setRelation('campaign', $campaign);

        return response()->json([
            'found'          => true,
            'application_no' => $match->registration_number,
            'name'           => $match->name,
            'status'         => $match->status,
            'pdf_url'        => $match->scholarshipUrl('pdf', 60 * 6),
            'print_url'      => $match->scholarshipUrl('print', 60 * 6),
        ]);
    }

    /**
     * Show registration success confirmation screen.
     */
    public function success(string $slug)
    {
        $campaign = EventCampaign::resolve($slug);
        if (!$campaign) {
            abort(404);
        }

        // the summary belongs to the campaign it was made for; another campaign's page never shows it
        $summary = session('recent_event_registration');
        if (($summary['campaign_slug'] ?? null) !== $campaign->slug) {
            $summary = null;
        }
        // scholarship forms open through signed links only (they carry ID and payment details)
        $formLinks = null;
        if (!empty($summary['registration_number'])) {
            $reg = EventRegistration::with('campaign')->where('registration_number', $summary['registration_number'])->first();
            if ($reg && $reg->isScholarship()) {
                $formLinks = ['print' => $reg->scholarshipUrl('print', 60 * 24), 'pdf' => $reg->scholarshipUrl('pdf', 60 * 24)];
            }
        }

        return view('frontend.events.success', compact('campaign', 'summary', 'formLinks'));
    }

    /**
     * Printable view of an application/registration form or event entry pass.
     */
    public function print(string $registrationNumber)
    {
        $registration = EventRegistration::with('campaign', 'user')
            ->where('registration_number', $registrationNumber)
            ->firstOrFail();

        $campaign = $registration->campaign;
        abort_unless($campaign, 404);
        $isScholarship = $campaign->isScholarship();
        if ($isScholarship && !$this->mayOpenScholarshipForm($registration)) {
            return $this->scholarshipLinkExpired($campaign);
        }
        $isLibrary = $registration->isLibrary();

        // Check Admin Approval for Event Cards & Library passes
        $isApproved = in_array($registration->status, self::APPROVED_STATUSES, true);
        if (!$isApproved && !$isScholarship) {
            return view('frontend.events.pending_approval', compact('registration', 'campaign'));
        }

        if ($isLibrary) {
            $viewName = 'frontend.events.library_form_print';
        } elseif ($isScholarship) {
            $viewName = 'frontend.events.scholarship_form_print';
        } else {
            $viewName = 'frontend.events.ticket_print';
        }

        return view($viewName, [
            'registration' => $registration,
            'campaign'     => $campaign,
            'isPdf'        => false,
        ]);
    }

    /**
     * Download registration/application form or pass as PDF.
     */
    public function downloadPdf(string $registrationNumber)
    {
        $registration = EventRegistration::with('campaign', 'user')
            ->where('registration_number', $registrationNumber)
            ->firstOrFail();

        $campaign = $registration->campaign;
        abort_unless($campaign, 404);
        $isScholarship = $campaign->isScholarship();
        if ($isScholarship && !$this->mayOpenScholarshipForm($registration)) {
            return $this->scholarshipLinkExpired($campaign);
        }
        $isLibrary = $registration->isLibrary();

        // Check Admin Approval for Event Cards
        $isApproved = in_array($registration->status, self::APPROVED_STATUSES, true);
        if (!$isApproved && !$isScholarship) {
            return redirect()->route('event.registration.print', $registration->registration_number)
                ->with('error', 'এডমিন এপ্রুভাল ছাড়া ইভেন্ট কার্ড ডাউনলোড বা দেখা যাবে না। এডমিন অনুমোদন দিলে আপনাকে মেসেজে কার্ডের লিংক পাঠানো হবে।');
        }
        
        if (!$isScholarship && !$isLibrary) {
            return redirect()->route('event.registration.print', [
                'registrationNumber' => $registration->registration_number,
                'download'           => 1,
            ]);
        }

        $viewName = $isLibrary ? 'frontend.events.library_form_print' : 'frontend.events.scholarship_form_print';
        $pdf = Pdf::loadView($viewName, [
            'registration' => $registration,
            'campaign'     => $campaign,
            'isPdf'        => true,
        ]);

        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'sans-serif',
        ]);

        $libOrAppName = $isLibrary ? ($registration->institution_or_org ?: 'Library') : $registration->name;
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $libOrAppName);
        $prefix = $isLibrary ? 'Library_Grant_Form' : 'Scholarship_Form';
        $filename = "{$prefix}_{$registration->registration_number}_{$safeName}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Show Public/User Library Book Grant Receipt Acknowledgment Form (বই প্রাপ্তিস্বীকার ফরম).
     */
    public function showAcknowledgment(string $registrationNumber)
    {
        $registration = EventRegistration::with('campaign', 'user')
            ->where('registration_number', $registrationNumber)
            ->firstOrFail();

        return view('frontend.events.library_acknowledgment', compact('registration'));
    }

    /**
     * Submit Receipt Acknowledgment by the Library Representative.
     */
    public function submitAcknowledgment(Request $request, string $registrationNumber)
    {
        $registration = EventRegistration::with('campaign', 'user')
            ->where('registration_number', $registrationNumber)
            ->firstOrFail();

        if (!in_array($registration->status, self::APPROVED_STATUSES, true)) {
            return back()->with('error', 'এই নিবন্ধনটি এখনও অনুমোদিত হয়নি।');
        }
        if ($registration->isAcknowledged()) {
            return back()->with('error', 'প্রাপ্তিস্বীকার ইতিমধ্যে জমা দেওয়া হয়েছে।');
        }

        $validated = $request->validate([
            'received_books_count' => 'required|integer|min:1',
            'received_date'        => 'required|date',
            'acknowledgment_notes' => 'nullable|string|max:2000',
            'receipt_photo'        => 'nullable|file|mimes:jpeg,png,jpg,webp|max:8192',
            'optimized_photo_data' => 'nullable|string',
        ]);

        $formData = $registration->form_data ?? [];
        $formData['received_books_count']  = intval($validated['received_books_count']);
        $formData['received_date']         = $validated['received_date'];
        $formData['acknowledgment_notes']  = $validated['acknowledgment_notes'] ?? null;
        $formData['acknowledgment_status'] = 'acknowledged';
        $formData['is_acknowledged']       = true;
        $formData['acknowledged_at']       = now()->toDateTimeString();
        $formData['acknowledged_by']       = 'library_representative';

        // Process photo if uploaded
        if ($request->filled('optimized_photo_data')) {
            $savedPhoto = $this->optimizeAndSavePhoto($request->input('optimized_photo_data'));
            if ($savedPhoto) {
                $formData['receipt_photo'] = $savedPhoto;
            }
        } elseif ($request->hasFile('receipt_photo')) {
            $savedPhoto = $this->optimizeAndSavePhoto($request->file('receipt_photo'));
            if ($savedPhoto) {
                $formData['receipt_photo'] = $savedPhoto;
            }
        }

        $registration->update([
            'form_data' => $formData,
        ]);

        return back()->with('success', 'ধন্যবাদ! আপনার পাঠাগারের বই অনুদান প্রাপ্তিস্বীকার সফলভাবে সম্পন্ন হয়েছে।');
    }

    /**
     * Public Printable Library Slip.
     */
    public function printLibrarySlip(string $registrationNumber)
    {
        $registration = EventRegistration::with('campaign', 'user')
            ->where('registration_number', $registrationNumber)
            ->firstOrFail();

        $isApproved = in_array($registration->status, self::APPROVED_STATUSES, true);
        if (!$isApproved) {
            return view('frontend.events.pending_approval', [
                'registration' => $registration,
                'campaign'     => $registration->campaign,
            ]);
        }

        return view('frontend.events.library_token_print', compact('registration'));
    }

    /**
     * Scan QR or Click URL to Verify & Confirm Event Attendance (উপস্থিতি নিশ্চিতকরণ).
     */
    public function verifyAttendance(Request $request, string $registrationNumber)
    {
        $cleanRegNum = ltrim(trim($registrationNumber), '#');
        // exact number only: a partial match could check in somebody else
        $registration = EventRegistration::with('campaign', 'user')
            ->whereIn('registration_number', [$cleanRegNum, '#' . $cleanRegNum])
            ->first();

        if (!$registration) {
            abort(404, 'নিবন্ধন নম্বরটি সিস্টেমে পাওয়া যায়নি।');
        }

        // Only an approved entry can be checked in; a pending or rejected one never becomes "attended".
        if (!in_array($registration->status, self::APPROVED_STATUSES, true)) {
            abort(403, 'এই নিবন্ধনটি অনুমোদিত নয়, তাই উপস্থিতি নিশ্চিত করা যাবে না।');
        }
        $campaign = $registration->campaign;
        $alreadyAttended = ($registration->status === 'attended');
        $formData = $registration->form_data ?? [];
        $attendanceTime = null;

        $sigToken = null;
        if (!$alreadyAttended) {
            $attendanceTime = now()->toDateTimeString();
            $sigToken = 'SIG-' . strtoupper(substr(md5($cleanRegNum . $attendanceTime), 0, 10));
            $formData['attended_at'] = $attendanceTime;
            $formData['attendance_ip'] = $request->ip();
            $formData['attendance_user_agent'] = substr($request->userAgent() ?? '', 0, 150);
            $formData['attendance_verified_via'] = 'qr_scan_or_click';
            $formData['attendance_signature_token'] = $sigToken;
            $formData['is_attendance_signed'] = true;

            $registration->status = 'attended';
            $registration->form_data = $formData;
            $registration->save();
        } else {
            $attendanceTime = $formData['attended_at'] ?? $registration->updated_at->toDateTimeString();
            $sigToken = $formData['attendance_signature_token'] ?? ('SIG-' . strtoupper(substr(md5($cleanRegNum . $attendanceTime), 0, 10)));
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'          => true,
                'already_attended' => $alreadyAttended,
                'status'           => 'attended',
                'attended_at'      => $attendanceTime,
                'signature_token'  => $sigToken,
                'participant'      => [
                    'name'         => $registration->name,
                    'reg_no'       => $registration->registration_number,
                    'designation'  => $registration->designation_or_class,
                    'institution'  => $registration->institution_or_org,
                    'phone'        => $registration->phone,
                ],
                'campaign_title'   => $campaign ? $campaign->title : '',
                'message'          => $alreadyAttended 
                    ? 'উপস্থিতি ও স্বাক্ষর ইতিপূর্বেই নিশ্চিত করা হয়েছে।' 
                    : 'অনুষ্ঠানে উপস্থিতি ও ডিজিটাল স্বাক্ষর সফলভাবে নিশ্চিত হয়েছে!',
            ]);
        }

        return view('frontend.events.attendance_confirmed', [
            'registration'    => $registration,
            'campaign'        => $campaign,
            'alreadyAttended' => $alreadyAttended,
            'attendanceTime'  => $attendanceTime,
            'signatureToken'  => $sigToken,
        ]);
    }
}
