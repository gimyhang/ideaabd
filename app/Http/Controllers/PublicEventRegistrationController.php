<?php

namespace App\Http\Controllers;

use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PublicEventRegistrationController extends Controller
{
    /**
     * Show the public registration form for a given slug (e.g. /jshikkhabritti, /rsutshab).
     */
    public function show(string $slug)
    {
        $campaign = EventCampaign::where('slug', $slug)->first();

        // Check known aliases to support /rangpursutsab, /rsutshab, /rsu, /pathagar, /library, /boi-bitoron
        if (!$campaign) {
            if (in_array($slug, ['rsu', 'rsutshab', 'rangpursutsab'])) {
                $campaign = EventCampaign::whereIn('slug', ['rangpursutsab', 'rsutshab', 'rsu'])
                    ->orWhere('type', 'writer')
                    ->first();
            } elseif (in_array($slug, ['jshikkhabritti', 'scholarship', 'shikkhabritti'])) {
                $campaign = EventCampaign::where('slug', 'jshikkhabritti')
                    ->orWhere('type', 'scholarship')
                    ->first();
            } elseif (in_array($slug, ['pathagar', 'library', 'boi-bitoron', 'library-grant', 'pathagar-nibondhon'])) {
                $campaign = EventCampaign::whereIn('slug', ['pathagar', 'library', 'boi-bitoron', 'library-grant', 'pathagar-nibondhon'])
                    ->orWhere('type', 'library')
                    ->first();
            }
        }

        // Auto-initialize jshikkhabritti if not present at all
        if (!$campaign && $slug === 'jshikkhabritti') {
            $campaign = EventCampaign::create([
                'title'               => 'Joyee Shikkha Britti Application Form',
                'slug'                => 'jshikkhabritti',
                'type'                => 'scholarship',
                'badge_text'          => 'Education Scholarship 2026',
                'short_description'   => 'Higher Secondary & Bachelor\'s Education Scholarship Application Form',
                'description'         => 'Higher Secondary and Bachelor\'s students can apply for the Joyee Shikkha Britti. Please provide accurate academic details. Max 50 words reason for scholarship.',
                'theme_color'         => '#0f3a68',
                'has_fee_or_donation' => false,
                'fee_amount'          => 0.00,
                'is_active'           => true,
                'success_message'     => 'Your scholarship application has been successfully submitted! Please print or download your application form below.',
                'custom_fields'       => [],
                'form_settings'       => ['is_scholarship_form' => true],
            ]);
        }

        // Auto-initialize rsu / writer campaign if not present at all
        if (!$campaign && in_array($slug, ['rsu', 'rsutshab', 'rangpursutsab'])) {
            $campaign = EventCampaign::create([
                'title'               => 'রংপুর সাহিত্য উৎসব ও লেখক সমাবেশ ২০২৬',
                'slug'                => $slug,
                'type'                => 'event',
                'badge_text'          => 'লেখক ও প্রতিনিধি নিবন্ধন',
                'short_description'   => 'রংপুর সাহিত্য উৎসব ও লিটিলম্যাগ মেলা ২০২৬ এ লেখক নিবন্ধন ও আমন্ত্রণ কার্ড সংগ্রহ ফরম।',
                'description'         => 'উত্তরবঙ্গের সর্ববৃহৎ সাহিত্য মিলনমেলায় অংশ নিতে লেখকবৃন্দকে এই ফরম পূরণ করার জন্য আমন্ত্রণ জানানো হচ্ছে।',
                'theme_color'         => '#991b1b',
                'has_fee_or_donation' => false,
                'fee_amount'          => 0.00,
                'is_active'           => true,
                'success_message'     => 'আপনার লেখক নিবন্ধন সফলভাবে জমা হয়েছে! ২৪ ঘণ্টা পর আপনার মোবাইল নম্বর দিয়ে লগইন করে আমন্ত্রণ কার্ড ডাউনলোড করতে পারবেন।',
                'custom_fields'       => [],
                'form_settings'       => ['is_writer_form' => true, 'requires_approval' => true],
            ]);
        }

        // Auto-initialize pathagar / Library campaign if not present at all
        if (!$campaign && in_array($slug, ['pathagar', 'library', 'boi-bitoron', 'library-grant', 'pathagar-nibondhon'])) {
            $campaign = EventCampaign::create([
                'title'               => 'বিনামূল্যে বই বিতরণ কর্মসূচি ও পাঠাগার নিবন্ধন ২০২৬',
                'slug'                => $slug === 'pathagar' ? 'pathagar' : $slug,
                'type'                => 'library',
                'badge_text'          => 'পাঠাগার বই অনুদান ২০২৬',
                'short_description'   => 'বিনামূল্যে বই বিতরণ কর্মসূচিতে অংশ নিয়ে পাঠাগার ও শিক্ষা প্রতিষ্ঠানের জন্য বই অনুদান প্রাপ্তির নিবন্ধন ফরম।',
                'description'         => 'আইডিয়া পাঠাগারের বিনামূল্যে বই বিতরণ কর্মসূচি, নিবন্ধন সম্পন্ন করুন।',
                'theme_color'         => '#047857',
                'has_fee_or_donation' => false,
                'fee_amount'          => 0.00,
                'is_active'           => true,
                'success_message'     => 'আপনার পাঠাগারের নিবন্ধন সফলভাবে সম্পন্ন হয়েছে! বই অনুদানের তথ্য জানিয়ে দেওয়া হবে।',
                'custom_fields'       => [],
                'form_settings'       => ['is_library_form' => true, 'requires_approval' => true],
            ]);
        }

        if (!$campaign) {
            abort(404);
        }

        if (!$campaign->is_active) {
            return view('frontend.events.inactive', [
                'campaign' => $campaign,
                'message'  => 'This scholarship/registration program is currently closed.',
            ]);
        }

        if ($campaign->isExpired()) {
            return view('frontend.events.inactive', [
                'campaign' => $campaign,
                'message'  => 'The application deadline has passed.',
            ]);
        }

        if ($campaign->isFull()) {
            return view('frontend.events.inactive', [
                'campaign' => $campaign,
                'message'  => 'Sorry, the maximum application limit has been reached.',
            ]);
        }

        $user = auth()->user();

        if ($campaign->type === 'scholarship' || $slug === 'jshikkhabritti' || !empty($campaign->form_settings['is_scholarship_form'])) {
            return view('frontend.events.scholarship_register', compact('campaign', 'user'));
        }

        if (in_array($slug, ['pathagar', 'library', 'boi-bitoron', 'library-grant', 'pathagar-nibondhon']) || $campaign->type === 'library' || !empty($campaign->form_settings['is_library_form'])) {
            return view('frontend.events.library_register', compact('campaign', 'user'));
        }

        if (in_array($slug, ['rsu', 'rsutshab', 'rangpursutsab']) || $campaign->type === 'writer' || !empty($campaign->form_settings['is_writer_form'])) {
            $existingRegistration = null;
            if ($user) {
                $existingRegistration = EventRegistration::where('event_campaign_id', $campaign->id)
                    ->where(function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->orWhere('phone', $user->phone);
                    })
                    ->first();
            }
            $previewRegNumber = $existingRegistration ? $existingRegistration->registration_number : EventRegistration::generateRegNumber($campaign->slug, $campaign->id);
            return view('frontend.events.writer_register', compact('campaign', 'user', 'existingRegistration', 'previewRegNumber'));
        }

        return view('frontend.events.register', compact('campaign', 'user'));
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
        $campaign = EventCampaign::where('slug', $slug)->first();
        if (!$campaign) {
            if (in_array($slug, ['rsu', 'rsutshab', 'rangpursutsab'])) {
                $campaign = EventCampaign::whereIn('slug', ['rangpursutsab', 'rsutshab', 'rsu'])
                    ->orWhere('type', 'writer')
                    ->first();
            } elseif (in_array($slug, ['jshikkhabritti', 'scholarship', 'shikkhabritti'])) {
                $campaign = EventCampaign::where('slug', 'jshikkhabritti')
                    ->orWhere('type', 'scholarship')
                    ->first();
            } elseif (in_array($slug, ['pathagar', 'library', 'boi-bitoron', 'library-grant', 'pathagar-nibondhon'])) {
                $campaign = EventCampaign::whereIn('slug', ['pathagar', 'library', 'boi-bitoron', 'library-grant', 'pathagar-nibondhon'])
                    ->orWhere('type', 'library')
                    ->first();
            }
        }

        if (!$campaign) {
            abort(404);
        }

        if (!$campaign->canAcceptRegistrations()) {
            return back()->with('error', 'This application form is currently closed.');
        }

        $isScholarship = ($campaign->type === 'scholarship' || $slug === 'jshikkhabritti' || !empty($campaign->form_settings['is_scholarship_form']));

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
            'password'             => 'nullable|string|min:6|max:64',
            'student_photo'        => 'nullable|max:25600',
            'optimized_photo_data' => 'nullable|string',
            'scholarship_reason'   => 'nullable|string|max:2000',
        ];

        $isLibrary = (in_array($slug, ['pathagar', 'library', 'boi-bitoron', 'library-grant', 'pathagar-nibondhon']) || $campaign->type === 'library' || !empty($campaign->form_settings['is_library_form']));

        if ($isLibrary) {
            $rules['institution_or_org'] = 'required|string|max:255';
            $rules['president_name'] = 'required|string|max:255';
            $rules['president_phone'] = 'required|string|max:30';
            $rules['secretary_name'] = 'required|string|max:255';
            $rules['secretary_phone'] = 'required|string|max:30';
            $rules['delivery_method'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        // Max 50 words check
        if ($isScholarship && $request->filled('scholarship_reason')) {
            $words = preg_split('/\s+/', trim(strip_tags($request->input('scholarship_reason'))), -1, PREG_SPLIT_NO_EMPTY);
            if (count($words) > 55) {
                return back()->withInput()->with('error', 'Your reason exceeds the 50-word limit (' . count($words) . ' words). Please shorten your statement.');
            }
        }

        // Normalize Phone & Country Code (Supports Bengali & English numerals, and international country codes)
        $countryCode = trim($request->input('country_code', '+880'));
        $rawPhone = $this->normalizeBnToEn(trim($validated['phone']));
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);

        if (str_starts_with($countryCode, '+880') || $countryCode === '880') {
            if (str_starts_with($cleanDigits, '880')) {
                $cleanDigits = substr($cleanDigits, 3);
            }
            if (str_starts_with($cleanDigits, '0')) {
                $cleanDigits = substr($cleanDigits, 1);
            }
            $formattedPhone = '+880' . $cleanDigits;
            $localPhone = '0' . $cleanDigits;
        } else {
            $prefix = str_starts_with($countryCode, '+') ? $countryCode : '+' . $countryCode;
            $formattedPhone = $prefix . ltrim($cleanDigits, '0');
            $localPhone = $formattedPhone;
        }

        $email = !empty($validated['email']) ? strtolower(trim($validated['email'])) : null;

        // Auto customer account sync
        $user = null;
        if (auth()->check()) {
            $user = auth()->user();
        } else {
            try {
                $user = User::where('phone', $formattedPhone)
                    ->orWhere('phone', $localPhone)
                    ->orWhere('phone', 'LIKE', '%' . substr($cleanDigits, -10))
                    ->when($email, fn($q) => $q->orWhere('email', $email))
                    ->first();

                if ($user) {
                    $updateFields = [];
                    if (empty($user->phone_verified_at)) {
                        $updateFields['phone_verified_at'] = now();
                    }
                    if (!empty($validated['password']) && (empty($user->password) || $user->password === '')) {
                        $updateFields['password'] = Hash::make($validated['password']);
                    }
                    if (!empty($updateFields)) {
                        $user->update($updateFields);
                    }
                } else {
                    $userEmail = $email ?: ($cleanDigits . '@customer.ideaabd.com');
                    if (User::where('email', $userEmail)->exists()) {
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
                        'phone_verified_at' => now(),
                        'email_verified_at' => $email ? now() : null,
                        'reg_data'          => [
                            'source'              => 'event_application',
                            'country_code'        => $countryCode,
                            'campaign_slug'       => $campaign->slug,
                            'district'            => $validated['district'] ?? ($request->input('permanent_district') ?: $request->input('present_district')),
                            'institution'         => $validated['institution_or_org'] ?? $request->input('college_name'),
                            'has_custom_password' => !empty($validated['password']),
                        ],
                    ]);
                }

                if (!auth()->check() && $user) {
                    \Illuminate\Support\Facades\Auth::login($user, true);
                }
            } catch (\Throwable $e) {
                Log::warning("User auto-sync notice in event registration: " . $e->getMessage());
            }
        }

        // Check duplicate
        $existingRegistration = EventRegistration::where('event_campaign_id', $campaign->id)
            ->where(function ($q) use ($formattedPhone, $localPhone, $user) {
                $q->where('phone', $localPhone)
                  ->orWhere('phone', $formattedPhone);
                if ($user) {
                    $q->orWhere('user_id', $user->id);
                }
            })
            ->first();

        if ($existingRegistration) {
            session(['recent_event_registration' => [
                'campaign_title'      => $campaign->title,
                'campaign_slug'       => $campaign->slug,
                'registration_number' => $existingRegistration->registration_number,
                'name'                => $existingRegistration->name,
                'phone'               => $existingRegistration->phone,
                'category'            => $existingRegistration->designation_or_class,
                'created_at'          => $existingRegistration->created_at ? $existingRegistration->created_at->format('d M, Y - h:i A') : now()->format('d M, Y - h:i A'),
                'amount_paid'         => $existingRegistration->amount_paid,
                'payment_status'      => $existingRegistration->payment_status,
                'is_scholarship'      => $isScholarship,
                'is_writer'           => in_array($slug, ['rsu', 'rsutshab', 'rangpursutsab']) || $campaign->type === 'writer' || !empty($campaign->form_settings['is_writer_form']),
                'status'              => $existingRegistration->status,
            ]]);

            return redirect()->route('event.success', $campaign->slug)
                ->with('info', "মোবাইল নম্বরে ইতোমধ্যে নিবন্ধন সম্পন্ন হয়েছে! রেজিস্ট্রেশন রোল: #{$existingRegistration->registration_number}");
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

        // 2. Extract scholarship, writer & library fields
        $extendedInputKeys = [
            'group', 'admission_roll', 'merit_position', 'college_name', 'college_code',
            'assigned_subject', 'previous_subject', 'subject_choice', 'father_name', 'mother_name',
            'guardian_name', 'guardian_phone', 'gender', 'religion', 'nationality', 'birth_date',
            'marital_status', 'annual_income', 
            'ssc_roll', 'ssc_board', 'ssc_institute', 'ssc_year', 'ssc_gpa',
            'hsc_roll', 'hsc_board', 'hsc_institute', 'hsc_year', 'hsc_gpa', 
            'grad_roll', 'grad_board', 'grad_institute', 'grad_year', 'grad_cgpa',
            'other_roll', 'other_board', 'other_institute', 'other_year', 'other_cgpa',
            'perm_division', 'permanent_district', 'perm_upazila', 'perm_post_office', 'perm_village', 'permanent_address',
            'pres_division', 'present_district', 'pres_upazila', 'pres_post_office', 'pres_village', 'present_address', 
            'scholarship_reason',
            // International / Abroad Address Fields
            'resident_type', 'country_name', 'custom_country', 'state_or_city', 'zip_code', 'foreign_address',
            // Writer & Cultural Artist Specific Fields
            'author_category', 'author_categories', 'art_medium', 'organization_name', 'published_books_count', 'notable_books', 'magazine_name', 'magazine_issue_count',
            // Library & Book Grant Specific Fields
            'library_name', 'library_type', 'established_year', 'reg_no', 
            'president_name', 'president_phone', 'secretary_name', 'secretary_phone',
            'reader_count', 'current_book_count', 'preferred_genres', 'delivery_method', 'division', 'remarks', 'library_address'
        ];

        foreach ($extendedInputKeys as $key) {
            if ($request->has($key)) {
                $val = $request->input($key);
                if (is_string($val) && (str_contains($key, 'phone') || str_contains($key, 'roll') || str_contains($key, 'year'))) {
                    $val = $this->normalizeBnToEn($val);
                }
                $customFieldAnswers[$key] = $val;
            }
        }

        if ($request->has('author_categories') && is_array($request->input('author_categories'))) {
            $catList = array_filter($request->input('author_categories'));
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

        $isLibrary = (in_array($slug, ['pathagar', 'library', 'boi-bitoron', 'library-grant', 'pathagar-nibondhon']) || $campaign->type === 'library' || !empty($campaign->form_settings['is_library_form']));

        $authorCatVal = is_array($request->input('author_categories')) ? implode(', ', $request->input('author_categories')) : $request->input('author_category');
        $institution = $validated['institution_or_org'] ?? ($request->input('organization_name') ?: ($request->input('library_name') ?: ($request->input('college_name') ?: ($request->input('magazine_name') ?: null))));
        $designation = $validated['designation_or_class'] ?? ($authorCatVal ?: ($request->input('art_medium') ?: ($request->input('library_type') ?: ($request->input('assigned_subject') ?: ($request->input('group') ?: null)))));
        $address = $validated['address'] ?? ($request->input('foreign_address') ?: ($request->input('library_address') ?: ($request->input('present_address') ?: ($request->input('permanent_address') ?: null))));
        $district = $validated['district'] ?? ($request->input('state_or_city') ?: ($request->input('district') ?: ($request->input('present_district') ?: ($request->input('permanent_district') ?: null))));

        $amountPaid = floatval($validated['amount_paid'] ?? 0);
        $paymentStatus = 'free';
        if ($campaign->has_fee_or_donation) {
            $paymentStatus = !empty($validated['transaction_id']) ? 'pending' : 'unpaid';
        }

        $regNumber = EventRegistration::generateRegNumber($campaign->slug);

        $requiresApproval = (in_array($slug, ['rsu', 'rsutshab', 'rangpursutsab']) || $campaign->type === 'writer' || !empty($campaign->form_settings['is_writer_form']) || !empty($campaign->form_settings['requires_approval']));
        $initialStatus = $requiresApproval ? 'pending' : 'confirmed';

        $registration = EventRegistration::create([
            'event_campaign_id'    => $campaign->id,
            'user_id'              => $user?->id,
            'registration_number'  => $regNumber,
            'name'                 => $validated['name'],
            'phone'                => $localPhone,
            'email'                => $email,
            'address'              => $address,
            'district'             => $district,
            'thana'                => $validated['thana'] ?? null,
            'institution_or_org'   => $institution,
            'designation_or_class' => $designation,
            'amount_paid'          => $amountPaid,
            'payment_method'       => $validated['payment_method'] ?? ($campaign->has_fee_or_donation ? 'online' : 'free'),
            'transaction_id'       => $validated['transaction_id'] ?? null,
            'payment_status'       => $paymentStatus,
            'form_data'            => $customFieldAnswers,
            'status'               => $initialStatus,
            'ip_address'           => $request->ip(),
        ]);

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
            \App\Services\SmsService::send($localPhone, $smsText);
        } catch (\Throwable $e) {
            Log::warning("Event SMS error: " . $e->getMessage());
        }

        $isWriter = in_array($slug, ['rsu', 'rsutshab', 'rangpursutsab']) || $campaign->type === 'writer' || !empty($campaign->form_settings['is_writer_form']);

        session(['recent_event_registration' => [
            'campaign_title'      => $campaign->title,
            'campaign_slug'       => $campaign->slug,
            'registration_number' => $regNumber,
            'name'                => $registration->name,
            'phone'               => $registration->phone,
            'category'            => $designation,
            'created_at'          => $registration->created_at->format('d M, Y - h:i A'),
            'amount_paid'         => $registration->amount_paid,
            'payment_status'      => $registration->payment_status,
            'is_scholarship'      => $isScholarship,
            'is_writer'           => $isWriter,
            'is_library'          => $isLibrary,
            'status'              => $registration->status,
        ]]);

        if ($isLibrary) {
            $successNotice = 'ধন্যবাদ! আপনার পাঠাগার নিবন্ধন সম্পন্ন হয়েছে।';
            if (auth()->check()) {
                return redirect()->route('my-account', ['tab' => 'libraryGrant'])
                    ->with('success', $successNotice);
            }
        } elseif ($isWriter) {
            $successNotice = 'ধন্যবাদ! রংপুর সাহিত্য উৎসব ও লিটিলম্যাগমেলায় আপনার তথ্য জমা হয়েছে। এডমিন অনুমোদন দিলে কার্ড ডাউনলোড করতে পারবেন।';
        } else {
            $successNotice = 'ধন্যবাদ! আপনার আবেদন সফলভাবে জমা হয়েছে।';
        }

        return redirect()->route('event.success', $campaign->slug)
            ->with('success', $successNotice);
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
                // Fallback: Save binary directly if GD cannot decode
                @file_put_contents($fullPath, $imgData);
                @file_put_contents($pubPath, $imgData);
                return $filename;
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
            Log::warning("Photo optimization fallback: " . $e->getMessage());
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                return $file->store('scholarships/photos', 'public');
            }
            return null;
        }
    }

    /**
     * Show registration success confirmation screen.
     */
    public function success(string $slug)
    {
        $campaign = EventCampaign::where('slug', $slug)->first();
        if (!$campaign) {
            if (in_array($slug, ['rsu', 'rsutshab', 'rangpursutsab'])) {
                $campaign = EventCampaign::whereIn('slug', ['rangpursutsab', 'rsutshab', 'rsu'])
                    ->orWhere('type', 'writer')
                    ->first();
            } elseif (in_array($slug, ['jshikkhabritti', 'scholarship', 'shikkhabritti'])) {
                $campaign = EventCampaign::where('slug', 'jshikkhabritti')
                    ->orWhere('type', 'scholarship')
                    ->first();
            }
        }

        if (!$campaign) {
            $campaign = EventCampaign::latest()->firstOrFail();
        }

        $summary = session('recent_event_registration');

        return view('frontend.events.success', compact('campaign', 'summary'));
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
        $isScholarship = ($campaign->type === 'scholarship' || $campaign->slug === 'jshikkhabritti' || !empty($campaign->form_settings['is_scholarship_form']));
        $isLibrary = ($registration->isLibrary() || $campaign->type === 'library' || $campaign->slug === 'pathagar' || !empty($campaign->form_settings['is_library_form']));

        // Check Admin Approval for Event Cards & Library passes
        $isApproved = in_array($registration->status, ['confirmed', 'approved', 'selected', 'attended'], true);
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
        $isScholarship = ($campaign->type === 'scholarship' || $campaign->slug === 'jshikkhabritti' || !empty($campaign->form_settings['is_scholarship_form']));
        $isLibrary = ($registration->isLibrary() || $campaign->type === 'library' || $campaign->slug === 'pathagar' || !empty($campaign->form_settings['is_library_form']));

        // Check Admin Approval for Event Cards
        $isApproved = in_array($registration->status, ['confirmed', 'approved', 'selected', 'attended'], true);
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

        $pdf->setPaper('a4', 'portrait');
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

        $isApproved = in_array($registration->status, ['confirmed', 'approved', 'selected', 'attended'], true);
        if (!$isApproved) {
            return view('frontend.events.pending_approval', [
                'registration' => $registration,
                'campaign'     => $registration->campaign,
            ]);
        }

        return view('frontend.events.library_token_print', compact('registration'));
    }
}
