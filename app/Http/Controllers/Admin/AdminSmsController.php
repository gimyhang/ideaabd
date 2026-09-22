<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminAccessService;
use App\Services\EmailService;
use App\Services\SmsService;
use App\Support\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminSmsController extends Controller
{
    public function __construct(
        protected AdminAccessService $accessService
    ) {}

    /**
     * Display SMS & Email Gateway Management, Live Balance & Broadcast Hub.
     */
    public function index(): View
    {
        $credentials = SmsService::getCredentials();
        $balanceInfo = SmsService::checkBalance();
        $smtpSettings = EmailService::getSmtpSettings();

        // Phone recipient counts
        $phoneCounts = [
            'total_users' => User::whereNotNull('phone')->where('phone', '!=', '')->count(),
            'customers'   => User::whereIn('role', ['customer', 'buyer', 'user'])->whereNotNull('phone')->where('phone', '!=', '')->count(),
            'authors'     => User::where('role', 'author')->whereNotNull('phone')->where('phone', '!=', '')->count(),
            'publishers'  => User::where('role', 'publisher')->whereNotNull('phone')->where('phone', '!=', '')->count(),
            'sellers'     => User::whereIn('role', ['seller', 'sub_admin'])->whereNotNull('phone')->where('phone', '!=', '')->count(),
        ];

        // Email recipient counts
        $emailCounts = [
            'total_users' => User::whereNotNull('email')->where('email', '!=', '')->count(),
            'customers'   => User::whereIn('role', ['customer', 'buyer', 'user'])->whereNotNull('email')->where('email', '!=', '')->count(),
            'authors'     => User::where('role', 'author')->whereNotNull('email')->where('email', '!=', '')->count(),
            'publishers'  => User::where('role', 'publisher')->whereNotNull('email')->where('email', '!=', '')->count(),
            'sellers'     => User::whereIn('role', ['seller', 'sub_admin'])->whereNotNull('email')->where('email', '!=', '')->count(),
        ];

        $codeSamples = SmsService::getCodeSamples($credentials['api_key'] ?? null, $credentials['sender_id'] ?? null);

        return view('admin.sms.index', [
            'credentials'  => $credentials,
            'balanceInfo'  => $balanceInfo,
            'smtpSettings' => $smtpSettings,
            'counts'       => $phoneCounts,
            'emailCounts'  => $emailCounts,
            'codeSamples'  => $codeSamples,
        ]);
    }

    /**
     * Get live SMS Balance via AJAX.
     */
    public function getBalance(): JsonResponse
    {
        $res = SmsService::checkBalance();
        return response()->json($res);
    }

    /**
     * Send a single/test SMS to diagnose delivery and view raw response.
     */
    public function sendTest(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'phone'   => 'required|string',
            'message' => 'required|string|max:500',
        ]);

        $res = SmsService::send($request->input('phone'), $request->input('message'));

        $this->accessService->log('sms_test_sent', "টেস্ট এসএমএস পাঠানো হয়েছে: {$request->input('phone')} | স্ট্যাটাস: " . (!empty($res['success']) ? 'সফল' : 'ব্যর্থ'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        if (!empty($res['success'])) {
            return back()->with('success', 'টেস্ট এসএমএস সফলভাবে পাঠানো হয়েছে! গেটওয়ে রেসপন্স: ' . ($res['message'] ?? 'OK'));
        }

        return back()->with('error', 'এসএমএস পাঠাতে সমস্যা হয়েছে: ' . ($res['message'] ?? ($res['error'] ?? 'Unknown Error')) . (!empty($res['raw_response']) ? ' | Raw: ' . substr($res['raw_response'], 0, 150) : ''));
    }

    /**
     * Broadcast bulk SMS to selected target user group or custom phone numbers.
     */
    public function broadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_group'   => 'required|string|in:all,customers,authors,publishers,sellers,custom',
            'custom_numbers' => 'nullable|string',
            'message'        => 'required|string|max:1000',
        ]);

        $recipients = [];

        if ($validated['target_group'] === 'custom') {
            $raw = $validated['custom_numbers'] ?? '';
            $recipients = preg_split('/[\r\n,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $query = User::whereNotNull('phone')->where('phone', '!=', '');

            if ($validated['target_group'] === 'customers') {
                $query->whereIn('role', ['customer', 'buyer', 'user']);
            } elseif ($validated['target_group'] === 'authors') {
                $query->where('role', 'author');
            } elseif ($validated['target_group'] === 'publishers') {
                $query->where('role', 'publisher');
            } elseif ($validated['target_group'] === 'sellers') {
                $query->whereIn('role', ['seller', 'sub_admin']);
            }

            $recipients = $query->pluck('phone')->toArray();
        }

        $recipients = array_unique(array_filter(array_map('trim', $recipients)));

        if (empty($recipients)) {
            return back()->with('error', 'কোনো বৈধ প্রাপকের মোবাইল নম্বর পাওয়া যায়নি!');
        }

        // Send in batches of 50 to avoid gateway payload overflow
        $totalSent = 0;
        $chunks = array_chunk($recipients, 50);

        foreach ($chunks as $chunk) {
            $res = SmsService::send($chunk, $validated['message']);
            if (!empty($res['success'])) {
                $totalSent += count($chunk);
            }
        }

        $this->accessService->log('sms_bulk_broadcast', "বাল্ক এসএমএস পাঠানো হয়েছে ({$validated['target_group']}) — মোট প্রাপক: {$totalSent}/" . count($recipients));

        return back()->with('success', "সফলভাবে {$totalSent} জন প্রাপকের কাছে বাল্ক এসএমএস পাঠানো সম্পন্ন হয়েছে!");
    }

    /**
     * Broadcast personalized Many-to-Many SMS where each user receives custom placeholders ({name}, {role}, {phone}).
     */
    public function broadcastMany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_group'     => 'required|string|in:all,customers,authors,publishers,sellers,custom',
            'custom_data'      => 'nullable|string',
            'message_template' => 'required|string|max:1000',
        ]);

        $messages = [];
        $template = $validated['message_template'];

        if ($validated['target_group'] === 'custom') {
            // Lines in format: 01726976982 | Rahim | Author
            $lines = preg_split('/[\r\n]+/', $validated['custom_data'] ?? '', -1, PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $line) {
                $parts = array_map('trim', explode('|', $line));
                $phone = $parts[0] ?? '';
                $name  = $parts[1] ?? 'সম্মানিত গ্রাহক';
                $role  = $parts[2] ?? 'ইউজার';

                if (!empty($phone)) {
                    $personalizedMsg = str_replace(
                        ['{name}', '{role}', '{phone}'],
                        [$name, $role, $phone],
                        $template
                    );
                    $messages[] = [
                        'to'      => $phone,
                        'message' => $personalizedMsg,
                    ];
                }
            }
        } else {
            $query = User::whereNotNull('phone')->where('phone', '!=', '');

            if ($validated['target_group'] === 'customers') {
                $query->whereIn('role', ['customer', 'buyer', 'user']);
            } elseif ($validated['target_group'] === 'authors') {
                $query->where('role', 'author');
            } elseif ($validated['target_group'] === 'publishers') {
                $query->where('role', 'publisher');
            } elseif ($validated['target_group'] === 'sellers') {
                $query->whereIn('role', ['seller', 'sub_admin']);
            }

            $users = $query->get(['name', 'phone', 'role']);

            foreach ($users as $u) {
                $personalizedMsg = str_replace(
                    ['{name}', '{role}', '{phone}'],
                    [$u->name ?: 'সম্মানিত গ্রাহক', ucfirst($u->role ?: 'পাঠক'), $u->phone],
                    $template
                );

                $messages[] = [
                    'to'      => $u->phone,
                    'message' => $personalizedMsg,
                ];
            }
        }

        if (empty($messages)) {
            return back()->with('error', 'কোনো বৈধ প্রাপকের তথ্য পাওয়া যায়নি!');
        }

        $res = SmsService::sendManyToMany($messages);

        $this->accessService->log('sms_many_broadcast', "মেনি-টু-মেনি বাল্ক এসএমএস পাঠানো হয়েছে ({$validated['target_group']}) — মোট প্রেরিত: " . ($res['total_sent'] ?? 0) . "/" . count($messages));

        if (!empty($res['success'])) {
            return back()->with('success', $res['message'] ?? 'মেনি-টু-মেনি পার্সোনালাইজড ক্যাম্পেইন সফলভাবে সম্পন্ন হয়েছে!');
        }

        return back()->with('error', 'মেনি-টু-মেনি ক্যাম্পেইনে সমস্যা হয়েছে: ' . ($res['message'] ?? 'Unknown Error'));
    }

    /**
     * Update SMS Gateway Settings in Database (SiteSetting).
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider'  => 'required|string|in:alaapcloud,bulksmsbd,greenweb,alphasms,sms4bd,generic',
            'url'       => 'required|string|url',
            'api_key'   => 'required|string|max:255',
            'sender_id' => 'required|string|max:100',
        ]);

        \App\Models\AdminDashboardSetting::updateOrCreate(
            ['key' => 'sms_gateway_settings'],
            [
                'value'      => $validated,
                'updated_by' => auth()->id(),
            ]
        );

        SiteSetting::clearCache();

        $this->accessService->log('sms_settings_updated', "এসএমএস গেটওয়ে কনফিগারেশন আপডেট করা হয়েছে ({$validated['provider']})");

        return back()->with('success', 'এসএমএস গেটওয়ে কনফিগারেশন সফলভাবে সংরক্ষিত ও আপডেট করা হয়েছে!');
    }

    /**
     * Send a single/test Email.
     */
    public function sendEmailTest(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'recipient_email' => 'required|email',
            'subject'         => 'required|string|max:255',
            'body_content'    => 'required|string',
            'action_text'     => 'nullable|string|max:100',
            'action_url'      => 'nullable|url|max:255',
        ]);

        $res = EmailService::sendSingle(
            toEmail: $validated['recipient_email'],
            subject: $validated['subject'],
            body: $validated['body_content'],
            actionText: $validated['action_text'] ?? null,
            actionUrl: $validated['action_url'] ?? null,
            recipientName: 'অ্যাডমিন/টেস্টার'
        );

        $this->accessService->log('email_test_sent', "টেস্ট ইমেইল পাঠানো হয়েছে: {$validated['recipient_email']} | স্ট্যাটাস: " . ($res['success'] ? 'সফল' : 'ব্যর্থ'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        if ($res['success']) {
            return back()->with('success', 'টেস্ট ইমেইল সফলভাবে পাঠানো হয়েছে: ' . $validated['recipient_email']);
        }

        return back()->with('error', 'ইমেইল পাঠানো যায়নি: ' . $res['message']);
    }

    /**
     * Broadcast bulk email to target user group or custom email list.
     */
    public function broadcastEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_group'   => 'required|string|in:all,customers,authors,publishers,sellers,custom',
            'custom_emails'  => 'nullable|string',
            'subject'        => 'required|string|max:255',
            'body_content'   => 'required|string',
            'action_text'    => 'nullable|string|max:100',
            'action_url'     => 'nullable|url|max:255',
        ]);

        $recipients = [];

        if ($validated['target_group'] === 'custom') {
            $raw = $validated['custom_emails'] ?? '';
            $recipients = preg_split('/[\r\n,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $query = User::whereNotNull('email')->where('email', '!=', '');

            if ($validated['target_group'] === 'customers') {
                $query->whereIn('role', ['customer', 'buyer', 'user']);
            } elseif ($validated['target_group'] === 'authors') {
                $query->where('role', 'author');
            } elseif ($validated['target_group'] === 'publishers') {
                $query->where('role', 'publisher');
            } elseif ($validated['target_group'] === 'sellers') {
                $query->whereIn('role', ['seller', 'sub_admin']);
            }

            $recipients = $query->pluck('email')->toArray();
        }

        $recipients = array_unique(array_filter(array_map('trim', $recipients)));

        if (empty($recipients)) {
            return back()->with('error', 'কোনো বৈধ প্রাপকের ইমেইল ঠিকানা পাওয়া যায়নি!');
        }

        $broadcastResult = EmailService::sendBroadcast(
            recipients: $recipients,
            subject: $validated['subject'],
            body: $validated['body_content'],
            actionText: $validated['action_text'] ?? null,
            actionUrl: $validated['action_url'] ?? null
        );

        $this->accessService->log('email_bulk_broadcast', "বাল্ক ইমেইল ব্রডকাস্ট পাঠানো হয়েছে ({$validated['target_group']}) — সফল: {$broadcastResult['sent']}/{$broadcastResult['total']}");

        $msg = "সফলভাবে {$broadcastResult['sent']} জন প্রাপকের কাছে ইমেইল পাঠানো সম্পন্ন হয়েছে!";
        if ($broadcastResult['failed'] > 0) {
            $msg .= " ({$broadcastResult['failed']} টি ইমেইল পাঠানো ব্যর্থ হয়েছে)";
        }

        return back()->with('success', $msg);
    }

    /**
     * Update SMTP Server Settings in Database (SiteSetting).
     */
    public function updateSmtpSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mailer'       => 'required|string|in:smtp,sendmail,log',
            'host'         => 'required|string',
            'port'         => 'required|numeric',
            'encryption'   => 'required|string|in:tls,ssl,none',
            'username'     => 'nullable|string',
            'password'     => 'nullable|string',
            'from_address' => 'required|email',
            'from_name'    => 'required|string|max:100',
        ]);

        \App\Models\AdminDashboardSetting::updateOrCreate(
            ['key' => 'smtp_settings'],
            [
                'value'      => $validated,
                'updated_by' => auth()->id(),
            ]
        );

        SiteSetting::clearCache();

        $this->accessService->log('smtp_settings_updated', "এসএমটিপি ইমেইল সার্ভার কনফিগারেশন আপডেট করা হয়েছে ({$validated['host']})");

        return back()->with('success', 'এসএমটিপি ইমেইল সার্ভার সেটিংস সফলভাবে সংরক্ষিত ও আপডেট করা হয়েছে!');
    }

    /**
     * Test sending unified OTP to both Phone and Email simultaneously.
     */
    public function sendOtpTest(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'otp_type' => 'required|string|in:registration,password_reset,login_2fa,order_confirmation',
            'phone'    => 'nullable|string',
            'email'    => 'nullable|email',
            'otp_code' => 'nullable|string',
            'channel'  => 'required|string|in:both,sms,email',
        ]);

        $otpCode = !empty($validated['otp_code']) ? trim($validated['otp_code']) : (string) random_int(100000, 999999);
        $recipient = [
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'name'  => 'অ্যাডমিন/টেস্টার',
        ];

        $res = match ($validated['otp_type']) {
            'registration'       => \App\Services\CommunicationService::sendRegistrationOtp($recipient, $otpCode, $validated['channel']),
            'password_reset'     => \App\Services\CommunicationService::sendPasswordResetOtp($recipient, $otpCode, url('/reset-password?token=' . md5($otpCode)), $validated['channel']),
            'login_2fa'          => \App\Services\CommunicationService::sendLoginOtp($recipient, $otpCode, $validated['channel']),
            'order_confirmation' => \App\Services\CommunicationService::sendOrderOtp($recipient, $otpCode, 'TEST-' . rand(1000, 9999), $validated['channel']),
        };

        $this->accessService->log('otp_test_sent', "টেস্ট ওটিপি পাঠানো হয়েছে ({$validated['otp_type']}) — ওটিপি: {$otpCode} | চ্যানেল: {$validated['channel']}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $res['success'],
                'results' => $res,
                'otp'     => $otpCode,
                'type'    => $validated['otp_type'],
            ]);
        }

        if ($res['success']) {
            return back()->with('success', "টেস্ট ওটিপি ({$otpCode}) সফলভাবে পাঠানো হয়েছে!");
        }

        return back()->with('error', 'ওটিপি পাঠাতে সমস্যা হয়েছে। ফোন বা ইমেইল গেটওয়ে সেটিংস চেক করুন।');
    }

    /**
     * Send Dual-Channel Marketing Campaign (SMS + Email simultaneously).
     */
    public function broadcastDual(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_group'     => 'required|string|in:all,customers,authors,publishers,sellers,custom',
            'custom_list'      => 'nullable|string',
            'subject'          => 'required|string|max:255',
            'message'          => 'required|string|max:1000',
            'action_text'      => 'nullable|string|max:100',
            'action_url'       => 'nullable|url|max:255',
            'channel'          => 'required|string|in:both,sms,email',
        ]);

        $recipients = [];

        if ($validated['target_group'] === 'custom') {
            $raw = $validated['custom_list'] ?? '';
            $items = preg_split('/[\r\n,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
            $recipients = array_map('trim', $items);
        } else {
            $query = User::query();

            if ($validated['target_group'] === 'customers') {
                $query->whereIn('role', ['customer', 'buyer', 'user']);
            } elseif ($validated['target_group'] === 'authors') {
                $query->where('role', 'author');
            } elseif ($validated['target_group'] === 'publishers') {
                $query->where('role', 'publisher');
            } elseif ($validated['target_group'] === 'sellers') {
                $query->whereIn('role', ['seller', 'sub_admin']);
            }

            $users = $query->get(['phone', 'email', 'name']);
            foreach ($users as $u) {
                $recipients[] = [
                    'phone' => $u->phone,
                    'email' => $u->email,
                    'name'  => $u->name,
                ];
            }
        }

        if (empty($recipients)) {
            return back()->with('error', 'কোনো প্রাপক পাওয়া যায়নি!');
        }

        $summary = \App\Services\CommunicationService::sendMarketingCampaign(
            recipients: $recipients,
            subject: $validated['subject'],
            message: $validated['message'],
            actionText: $validated['action_text'] ?? null,
            actionUrl: $validated['action_url'] ?? null,
            channel: $validated['channel']
        );

        $this->accessService->log('dual_broadcast_sent', "যৌথ মার্কেটিং ক্যাম্পেইন পাঠানো হয়েছে ({$validated['target_group']}) — SMS: {$summary['sms_sent']}, Email: {$summary['email_sent']}");

        return back()->with('success', "মার্কেটিং ক্যাম্পেইন সফলভাবে পাঠানো হয়েছে! (SMS সফল: {$summary['sms_sent']}, Email সফল: {$summary['email_sent']})");
    }
}
