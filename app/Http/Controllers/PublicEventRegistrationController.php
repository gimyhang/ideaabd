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
                'short_description'   => 'রংপুর বিভাগীয় সাহিত্য উৎসব ও লেখক সমাবেশ ২০২৬ এ লেখকবৃন্দের তথ্য নিবন্ধন ও আমন্ত্রণ কার্ড সংগ্রহ ফরম।',
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

        // Auto-initialize pathagar / library grant campaign if not present at all
        if (!$campaign && in_array($slug, ['pathagar', 'library', 'boi-bitoron', 'library-grant', 'pathagar-nibondhon'])) {
            $campaign = EventCampaign::create([
                'title'               => 'বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচি ও পাঠাগার নিবন্ধন ২০২৬',
                'slug'                => $slug === 'pathagar' ? 'pathagar' : $slug,
                'type'                => 'library',
                'badge_text'          => 'পাঠাগার বই অনুদান ২০২৬',
                'short_description'   => 'বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচিতে অংশ নিয়ে পাঠাগার ও শিক্ষা প্রতিষ্ঠানের জন্য বই অনুদান প্রাপ্তির নিবন্ধন ফরম।',
                'description'         => 'আইডিয়া প্রকাশন ও বুকস অব আইডিয়া-এর বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচির আওতায় বাংলাদেশের বিভিন্ন প্রান্তের সাধারণ পাঠাগার, ক্লাব লাইব্রেরি ও শিক্ষা প্রতিষ্ঠানসমূহে বিনামূল্যে বই প্রদান করা হবে। ফরমটি যথাযথভাবে পূরণ করে নিবন্ধন সম্পন্ন করুন।',
                'theme_color'         => '#047857',
                'has_fee_or_donation' => false,
                'fee_amount'          => 0.00,
                'is_active'           => true,
                'success_message'     => 'আপনার পাঠাগারের নিবন্ধন সফলভাবে সম্পন্ন হয়েছে! আমাদের প্রতিনিধি আপনার সাথে দ্রুত যোগাযোগ করবে এবং যাচাই শেষে বই অনুদানের তথ্য জানিয়ে দেওয়া হবে।',
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
            return view('frontend.events.writer_register', compact('campaign', 'user'));
        }

        return view('frontend.events.register', compact('campaign', 'user'));
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
            'phone'                => 'required|string|max:20',
            'email'                => 'nullable|email|max:255',
            'address'              => 'nullable|string|max:500',
            'district'             => 'nullable|string|max:100',
            'thana'                => 'nullable|string|max:100',
            'institution_or_org'   => 'nullable|string|max:255',
            'designation_or_class' => 'nullable|string|max:255',
            'amount_paid'          => 'nullable|numeric|min:0',
            'payment_method'       => 'nullable|string|max:50',
            'transaction_id'       => 'nullable|string|max:100',
            'password'             => 'nullable|string|min:8|max:64',
            'student_photo'        => 'nullable|file|mimes:jpeg,png,jpg,webp|max:8192',
            'optimized_photo_data' => 'nullable|string',
            'scholarship_reason'   => 'nullable|string|max:2000',
        ];

        $isLibrary = (in_array($slug, ['pathagar', 'library', 'boi-bitoron', 'library-grant', 'pathagar-nibondhon']) || $campaign->type === 'library' || !empty($campaign->form_settings['is_library_form']));

        if ($isLibrary) {
            $rules['institution_or_org'] = 'required|string|max:255';
            $rules['president_name'] = 'required|string|max:255';
            $rules['president_phone'] = 'required|string|max:20';
            $rules['secretary_name'] = 'required|string|max:255';
            $rules['secretary_phone'] = 'required|string|max:20';
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

        // Normalize Phone
        $rawPhone = trim($validated['phone']);
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);
        if (str_starts_with($cleanDigits, '880')) {
            $cleanDigits = substr($cleanDigits, 3);
        }
        if (str_starts_with($cleanDigits, '0')) {
            $cleanDigits = substr($cleanDigits, 1);
        }
        $formattedPhone = '+880' . $cleanDigits;
        $localPhone = '0' . $cleanDigits;
        $email = !empty($validated['email']) ? strtolower(trim($validated['email'])) : null;

        // Auto customer account sync
        $user = null;
        if (auth()->check()) {
            $user = auth()->user();
        } else {
            try {
                $user = User::where('phone', $formattedPhone)
                    ->orWhere('phone', $localPhone)
                    ->when($email, fn($q) => $q->orWhere('email', $email))
                    ->first();

                if (!$user) {
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
                            'source'         => 'event_application',
                            'campaign_slug'  => $campaign->slug,
                            'district'       => $validated['district'] ?? ($request->input('permanent_district') ?: $request->input('present_district')),
                            'institution'    => $validated['institution_or_org'] ?? $request->input('college_name'),
                        ],
                    ]);
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
                ->with('info', "এই মোবাইল নম্বরে ইতিমধ্যে নিবন্ধন সম্পন্ন হয়েছে! রেজিস্ট্রেশন রোল: #{$existingRegistration->registration_number}");
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
            // Writer Specific Fields
            'author_category', 'published_books_count', 'notable_books', 'magazine_name', 'magazine_issue_count',
            // Library & Book Grant Specific Fields
            'library_name', 'library_type', 'established_year', 'reg_no', 
            'president_name', 'president_phone', 'secretary_name', 'secretary_phone',
            'reader_count', 'current_book_count', 'preferred_genres', 'delivery_method', 'division', 'remarks', 'library_address'
        ];

        foreach ($extendedInputKeys as $key) {
            if ($request->has($key)) {
                $customFieldAnswers[$key] = $request->input($key);
            }
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

        $institution = $validated['institution_or_org'] ?? ($request->input('library_name') ?: ($request->input('college_name') ?: ($request->input('magazine_name') ?: null)));
        $designation = $validated['designation_or_class'] ?? ($request->input('library_type') ?: ($request->input('author_category') ?: ($request->input('assigned_subject') ?: ($request->input('group') ?: null))));
        $address = $validated['address'] ?? ($request->input('library_address') ?: ($request->input('present_address') ?: ($request->input('permanent_address') ?: null)));
        $district = $validated['district'] ?? ($request->input('district') ?: ($request->input('present_district') ?: ($request->input('permanent_district') ?: null)));

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

        // Confirmation SMS with Direct Card Link
        try {
            $downloadUrl = route('event.registration.print', $regNumber);
            if ($isLibrary) {
                $smsText = "আইডিয়া প্রকাশন — বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচিতে আপনার পাঠাগারের নিবন্ধন সফল হয়েছে! Reg No: #{$regNumber}। স্লিপ ডাউনলোড: {$downloadUrl} । www.ideaabd.com";
            } elseif ($requiresApproval) {
                $smsText = "আইডিয়া প্রকাশন — '{$campaign->title}'-এ আপনার তথ্য জমা হয়েছে (Reg: #{$regNumber})। কার্ড দেখুন ও ডাউনলোড: {$downloadUrl} । ২৪ ঘণ্টা পর মোবাইল নম্বর দিয়ে লগইন করে চূড়ান্ত কার্ড ডাউনলোড করুন। www.ideaabd.com";
            } else {
                $smsText = "আইডিয়া প্রকাশন — '{$campaign->title}'-এ আপনার আবেদন সফল হয়েছে! Reg No: #{$regNumber}। ডাউনলোড লিংক: {$downloadUrl} । www.ideaabd.com";
            }
            \App\Services\SmsService::send($localPhone, $smsText);
        } catch (\Throwable $e) {
            Log::warning("Event SMS error: " . $e->getMessage());
        }

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
            'is_writer'           => in_array($slug, ['rsu', 'rsutshab', 'rangpursutsab']) || $campaign->type === 'writer' || !empty($campaign->form_settings['is_writer_form']),
            'is_library'          => $isLibrary,
            'status'              => $registration->status,
        ]]);

        if ($isLibrary) {
            $successNotice = 'ধন্যবাদ! বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচিতে আপনার পাঠাগারের নিবন্ধন সফলভাবে জমা হয়েছে। আমাদের টিম যাচাই শেষে বই অনুদানের দিনক্ষণ জানিয়ে দেবে।';
        } elseif ($requiresApproval) {
            $successNotice = 'ধন্যবাদ! আপনার লেখক নিবন্ধন ও তথ্য সফলভাবে জমা হয়েছে। ২৪ ঘণ্টা পর আপনার মোবাইল নম্বর দিয়ে লগইন করে আমন্ত্রণ কার্ড ডাউনলোড করতে পারবেন।';
        } else {
            $successNotice = 'ধন্যবাদ! আপনার আবেদন সফলভাবে সম্পন্ন হয়েছে।';
        }

        return redirect()->route('event.success', $campaign->slug)
            ->with('success', $successNotice);
    }

    /**
     * Auto-crop, resize and compress student photo to clean 300x360 passport JPEG.
     */
    protected function optimizeAndSavePhoto($file): ?string
    {
        try {
            $imgData = null;
            if (is_string($file) && str_starts_with($file, 'data:image')) {
                $parts = explode(',', $file);
                $imgData = base64_decode($parts[1] ?? '');
            } elseif ($file instanceof \Illuminate\Http\UploadedFile) {
                $imgData = file_get_contents($file->getRealPath());
            }

            if (!$imgData) {
                return null;
            }

            $src = @imagecreatefromstring($imgData);
            if (!$src) {
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    return $file->store('scholarships/photos', 'public');
                }
                return null;
            }

            // Correct EXIF orientation
            if (function_exists('exif_read_data') && ($file instanceof \Illuminate\Http\UploadedFile)) {
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

            $filename = 'scholarships/photos/' . Str::random(24) . '.jpg';
            $fullPath = storage_path('app/public/' . $filename);

            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            imagejpeg($dst, $fullPath, 85);
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

        return view('frontend.events.library_token_print', compact('registration'));
    }
}
