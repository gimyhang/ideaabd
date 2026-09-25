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

        // Auto-initialize jshikkhabritti if not present
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

        if ($slug === 'rangpursutsab' || !empty($campaign->form_settings['is_writer_form'])) {
            return view('frontend.events.writer_register', compact('campaign', 'user'));
        }

        return view('frontend.events.register', compact('campaign', 'user'));
    }

    /**
     * Handle form submission with auto-optimized image processing.
     */
    public function submit(Request $request, string $slug)
    {
        $campaign = EventCampaign::where('slug', $slug)->firstOrFail();

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
            $user = User::where('phone', $formattedPhone)
                ->orWhere('phone', $localPhone)
                ->when($email, fn($q) => $q->orWhere('email', $email))
                ->first();

            if (!$user) {
                $userEmail = $email ?: ($cleanDigits . '@customer.ideaabd.com');
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
                        'source'         => 'scholarship_application',
                        'campaign_slug'  => $campaign->slug,
                        'district'       => $validated['district'] ?? ($request->input('permanent_district') ?: $request->input('present_district')),
                        'institution'    => $validated['institution_or_org'] ?? $request->input('college_name'),
                    ],
                ]);
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
            return redirect()->route('event.registration.print', $existingRegistration->registration_number)
                ->with('info', "An application with this phone number already exists! Application Roll: #{$existingRegistration->registration_number}");
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

        // 2. Extract scholarship & writer fields
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
            'author_category', 'published_books_count', 'notable_books', 'magazine_name', 'magazine_issue_count'
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

        $institution = $validated['institution_or_org'] ?? ($request->input('college_name') ?: ($request->input('magazine_name') ?: null));
        $designation = $validated['designation_or_class'] ?? ($request->input('author_category') ?: ($request->input('assigned_subject') ?: ($request->input('group') ?: null)));
        $address = $validated['address'] ?? ($request->input('present_address') ?: ($request->input('permanent_address') ?: null));
        $district = $validated['district'] ?? ($request->input('present_district') ?: ($request->input('permanent_district') ?: null));

        $amountPaid = floatval($validated['amount_paid'] ?? 0);
        $paymentStatus = 'free';
        if ($campaign->has_fee_or_donation) {
            $paymentStatus = !empty($validated['transaction_id']) ? 'pending' : 'unpaid';
        }

        $regNumber = EventRegistration::generateRegNumber($campaign->slug);

        $requiresApproval = ($slug === 'rangpursutsab' || !empty($campaign->form_settings['is_writer_form']) || !empty($campaign->form_settings['requires_approval']));
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

        // Confirmation SMS
        try {
            if ($requiresApproval) {
                $smsText = "আইডিয়া প্রকাশন — '{$campaign->title}'-এ আপনার তথ্য জমা হয়েছে (Reg: #{$regNumber})। ২৪ ঘণ্টা পর মোবাইল নম্বর দিয়ে লগিন করে কার্ড ডাউনলোড করুন। www.ideaabd.com";
            } else {
                $smsText = "আইডিয়া প্রকাশন — '{$campaign->title}'-এ আপনার আবেদন সফল হয়েছে! Reg No: #{$regNumber}। প্রিন্ট কপি সংরক্ষণ করুন। www.ideaabd.com";
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
            'created_at'          => $registration->created_at->format('d M, Y - h:i A'),
            'amount_paid'         => $registration->amount_paid,
            'payment_status'      => $registration->payment_status,
            'is_scholarship'      => $isScholarship,
            'status'              => $registration->status,
        ]]);

        $successNotice = $requiresApproval
            ? 'আপনার তথ্য সফলভাবে জমা হয়েছে! ২৪ ঘণ্টা পর মোবাইল নম্বর দিয়ে লগিন করে কার্ড নম্বর ও আমন্ত্রণ কার্ড ডাউনলোড করুন।'
            : 'Your application has been successfully submitted! Please print or download your form below.';

        return redirect()->route('event.registration.print', $regNumber)
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
        $campaign = EventCampaign::where('slug', $slug)->firstOrFail();
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

        $isAdmin = auth()->check() && in_array(auth()->user()->role, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_SUB_ADMIN]);
        $requiresApproval = ($registration->campaign->slug === 'rangpursutsab' || !empty($registration->campaign->form_settings['is_writer_form']) || !empty($registration->campaign->form_settings['requires_approval']));

        // Guard against unauthorized public access to unapproved delegate cards
        if (!$isAdmin && $requiresApproval && $registration->status === 'pending') {
            return view('frontend.events.pending_approval', [
                'registration' => $registration,
                'campaign'     => $registration->campaign,
            ]);
        }

        $isScholarship = ($registration->campaign->type === 'scholarship' || $registration->campaign->slug === 'jshikkhabritti' || !empty($registration->campaign->form_settings['is_scholarship_form']));
        $viewName = $isScholarship ? 'frontend.events.scholarship_form_print' : 'frontend.events.ticket_print';

        return view($viewName, [
            'registration' => $registration,
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

        $isAdmin = auth()->check() && in_array(auth()->user()->role, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_SUB_ADMIN]);
        $requiresApproval = ($registration->campaign->slug === 'rangpursutsab' || !empty($registration->campaign->form_settings['is_writer_form']) || !empty($registration->campaign->form_settings['requires_approval']));

        if (!$isAdmin && $requiresApproval && $registration->status === 'pending') {
            return redirect()->route('event.registration.print', $registration->registration_number)
                ->with('info', 'আপনার আবেদনটি বর্তমানে এডমিন কর্তৃক যাচাইাধীন রয়েছে। তথ্য অনুমোদিত (Approved) হলে ডেলিগেট কার্ড ডাউনলোড করতে পারবেন।');
        }

        $isScholarship = ($registration->campaign->type === 'scholarship' || $registration->campaign->slug === 'jshikkhabritti' || !empty($registration->campaign->form_settings['is_scholarship_form']));
        $viewName = $isScholarship ? 'frontend.events.scholarship_form_print' : 'frontend.events.ticket_print';

        $pdf = Pdf::loadView($viewName, [
            'registration' => $registration,
            'isPdf'        => true,
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'sans-serif',
        ]);

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $registration->name);
        $prefix = $isScholarship ? 'Scholarship_Form' : 'Delegate_Pass';
        $filename = "{$prefix}_{$registration->registration_number}_{$safeName}.pdf";

        return $pdf->download($filename);
    }
}
