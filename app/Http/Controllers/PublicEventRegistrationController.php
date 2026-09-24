<?php

namespace App\Http\Controllers;

use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PublicEventRegistrationController extends Controller
{
    /**
     * Show the public dynamic registration form for a given slug (e.g., /rsutshab, /joyeeshikkhabritti)
     */
    public function show(string $slug)
    {
        $campaign = EventCampaign::where('slug', $slug)->firstOrFail();

        // If inactive or deleted
        if (!$campaign->is_active) {
            return view('frontend.events.inactive', [
                'campaign' => $campaign,
                'message'  => 'এই রেজিস্ট্রেশন কার্যক্রমটি বর্তমানে বন্ধ রয়েছে।',
            ]);
        }

        // If expired
        if ($campaign->isExpired()) {
            return view('frontend.events.inactive', [
                'campaign' => $campaign,
                'message'  => 'এই রেজিস্ট্রেশনের নির্ধারিত সময়সীমা অতিক্রম হয়েছে।',
            ]);
        }

        // If participant limit reached
        if ($campaign->isFull()) {
            return view('frontend.events.inactive', [
                'campaign' => $campaign,
                'message'  => 'দুঃখিত, এই ইভেন্টের সর্বোচ্চ আসন সংখ্যা পূর্ণ হয়ে গেছে।',
            ]);
        }

        $user = auth()->user();

        return view('frontend.events.register', compact('campaign', 'user'));
    }

    /**
     * Handle submission of the event/campaign registration form.
     * Crucial requirement: Automatically ensures user exists in users table as a registered customer (buyer)
     * so that even if the event/campaign is later deleted, they remain in customer registration records!
     */
    public function submit(Request $request, string $slug)
    {
        $campaign = EventCampaign::where('slug', $slug)->firstOrFail();

        if (!$campaign->canAcceptRegistrations()) {
            return back()->with('error', 'এই রেজিস্ট্রেশন ফর্মটি বর্তমানে আর নতুন আবেদন গ্রহণ করছে না।');
        }

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
        ];

        $validated = $request->validate($rules);

        // Normalize phone
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

        // 1. Find or Auto-Create User in `users` table as Customer (Buyer)
        $user = null;
        if (auth()->check()) {
            $user = auth()->user();
        } else {
            // Check by phone or email
            $user = User::where('phone', $formattedPhone)
                ->orWhere('phone', $localPhone)
                ->when($email, function ($q) use ($email) {
                    $q->orWhere('email', $email);
                })
                ->first();

            if (!$user) {
                // Generate safe fallback email if none provided
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
                    'phone_verified_at' => now(), // Auto-verified through campaign
                    'email_verified_at' => $email ? now() : null,
                    'reg_data'          => [
                        'source'         => 'event_campaign',
                        'campaign_slug'  => $campaign->slug,
                        'campaign_title' => $campaign->title,
                        'district'       => $validated['district'] ?? null,
                        'thana'          => $validated['thana'] ?? null,
                        'address'        => $validated['address'] ?? null,
                        'institution'    => $validated['institution_or_org'] ?? null,
                    ],
                ]);
            }
        }

        // 2. Prevent duplicate submission for same event if phone already registered
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
            return back()->withInput()->with('error', "এই মোবাইল নম্বরটি দিয়ে ইতিমধ্যে এই কার্যক্রমে রেজিস্ট্রেশন করা হয়েছে! রেজিস্ট্রেশন নম্বর: {$existingRegistration->registration_number}");
        }

        // 3. Extract custom fields if any
        $customFieldAnswers = [];
        if (!empty($campaign->custom_fields) && is_array($campaign->custom_fields)) {
            foreach ($campaign->custom_fields as $field) {
                $fName = $field['name'] ?? null;
                if ($fName && $request->has($fName)) {
                    $customFieldAnswers[$fName] = $request->input($fName);
                }
            }
        }

        // 4. Determine payment status
        $amountPaid = floatval($validated['amount_paid'] ?? 0);
        $paymentStatus = 'free';
        if ($campaign->has_fee_or_donation) {
            $paymentStatus = !empty($validated['transaction_id']) ? 'pending' : 'unpaid';
        }

        // 5. Create Event Registration record
        $regNumber = EventRegistration::generateRegNumber($campaign->slug);

        $registration = EventRegistration::create([
            'event_campaign_id'    => $campaign->id,
            'user_id'              => $user?->id,
            'registration_number'  => $regNumber,
            'name'                 => $validated['name'],
            'phone'                => $localPhone,
            'email'                => $email,
            'address'              => $validated['address'] ?? null,
            'district'             => $validated['district'] ?? null,
            'thana'                => $validated['thana'] ?? null,
            'institution_or_org'   => $validated['institution_or_org'] ?? null,
            'designation_or_class' => $validated['designation_or_class'] ?? null,
            'amount_paid'          => $amountPaid,
            'payment_method'       => $validated['payment_method'] ?? ($campaign->has_fee_or_donation ? 'online' : 'free'),
            'transaction_id'       => $validated['transaction_id'] ?? null,
            'payment_status'       => $paymentStatus,
            'form_data'            => $customFieldAnswers,
            'status'               => 'confirmed',
            'ip_address'           => $request->ip(),
        ]);

        // Send instant confirmation SMS
        try {
            $smsText = "আইডিয়া প্রকাশন — '{$campaign->title}'-এ আপনার রেজিস্ট্রেশন সফল হয়েছে! Reg No: #{$regNumber}। সাথে থাকার জন্য ধন্যবাদ। www.ideaabd.com";
            \App\Services\SmsService::send($localPhone, $smsText);
        } catch (\Throwable $e) {
            Log::warning("Event registration confirmation SMS error: " . $e->getMessage());
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
        ]]);

        return redirect()->route('event.success', $campaign->slug)
            ->with('success', $campaign->success_message ?: "আপনার রেজিস্ট্রেশন সফল হয়েছে! রেজিস্ট্রেশন নম্বর: {$regNumber}");
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
}
