<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendSmsCampaign;
use App\Models\AdminDashboardSetting;
use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Models\SmsBalanceSnapshot;
use App\Models\SmsLog;
use App\Models\SmsNumberRestriction;
use App\Models\User;
use App\Services\AdminAccessService;
use App\Services\CommunicationService;
use App\Services\EmailService;
use App\Services\SmsGuard;
use App\Services\SmsService;
use App\Support\CampaignLog;
use App\Support\PhoneNormalizer;
use App\Support\Secret;
use App\Support\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminSmsController extends Controller
{
    private const GROUPS = 'all,customers,authors,publishers,sellers,libraries,custom';

    public function __construct(
        protected AdminAccessService $accessService
    ) {}

    /**
     * SMS & Email hub: gateway, balance, transactions, number protection, templates and campaign history.
     */
    public function index(Request $request): View
    {
        $credentials = SmsService::getCredentials();
        $balanceInfo = SmsService::checkBalance();
        $smtp        = EmailService::getSmtpSettings();

        $filters = $request->validate([
            'q'       => 'nullable|string|max:30',
            'status'  => 'nullable|in:pending,sent,failed,blocked,rejected',
            'purpose' => 'nullable|in:otp,order,welcome,notification,campaign,test',
            'source'  => 'nullable|in:system,admin',
            'from'    => 'nullable|date',
            'to'      => 'nullable|date',
        ]);

        $tablesReady = $this->smsTablesExist();

        $transactions = $tablesReady
            ? $this->transactionQuery($filters)->paginate(25, ['*'], 'tx')->withQueryString()
            : null;

        return view('admin.sms.index', [
            // the key and the SMTP password never leave the server in a page
            'credentials'    => $credentials + ['api_key_masked' => SmsService::maskSecret($credentials['api_key'] ?? '')],
            'balanceInfo'    => $balanceInfo,
            'smtpSettings'   => ['password' => ''] + $smtp,
            'smtpHasPassword'=> $smtp['password'] !== '',
            'counts'         => $this->contactCounts('phone'),
            'emailCounts'    => $this->contactCounts('email'),
            'codeSamples'    => SmsService::getCodeSamples(null, $credentials['sender_id'] ?? null),
            'templates'      => $this->getTemplatesList(),
            'campaignLogs'   => $this->getCampaignLogsList(),
            'usingDefaultKey'=> ($credentials['api_key'] ?? '') === SmsService::DEFAULT_API_KEY,
            'tablesReady'    => $tablesReady,
            'stats'          => $tablesReady ? $this->smsStats() : null,
            'transactions'   => $transactions,
            'filters'        => $filters,
            'restrictions'   => $tablesReady ? SmsNumberRestriction::where(fn ($q) => $q->whereNotNull('blocked_at')->orWhere('restricted_until', '>', now()))->latest('updated_at')->limit(100)->get() : collect(),
            'balanceHistory' => $tablesReady ? SmsBalanceSnapshot::latest('id')->limit(15)->get() : collect(),
            'guard'          => [
                'limit'   => (int) config('sms.guard.daily_limit', 2),
                'hours'   => (int) config('sms.guard.restrict_hours', 24),
                'days'    => (int) config('sms.guard.block_after_days', 3),
                'cost'    => (float) config('sms.cost_per_sms', 0.35),
                'scope'   => config('sms.guard.limit_scope', 'otp'),
                'notify'  => (int) config('sms.guard.notification_limit', 5),
            ],
        ]);
    }

    /**
     * Get live SMS Balance via AJAX.
     */
    public function getBalance(Request $request): JsonResponse
    {
        return response()->json(SmsService::checkBalance($request->boolean('fresh')));
    }

    /**
     * Send a single/test SMS to diagnose delivery and view the gateway response.
     */
    public function sendTest(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'phone'   => 'required|string|max:20',
            'message' => 'required|string|max:500',
        ]);

        $res = SmsService::send($request->input('phone'), $request->input('message'), ['source' => 'admin', 'purpose' => 'test', 'campaign' => 'Quick test']);

        $this->accessService->log('sms_test_sent', 'টেস্ট এসএমএস: ' . $this->maskPhone($request->input('phone')) . ' | স্ট্যাটাস: ' . (!empty($res['success']) ? 'সফল' : 'ব্যর্থ'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res, !empty($res['success']) ? 200 : 422);
        }

        if (!empty($res['success'])) {
            return back()->with('success', 'টেস্ট এসএমএস সফলভাবে পাঠানো হয়েছে! গেটওয়ে রেসপন্স: ' . ($res['message'] ?? 'OK'));
        }

        return back()->with('error', 'এসএমএস পাঠাতে সমস্যা হয়েছে: ' . ($res['message'] ?? ($res['error'] ?? 'Unknown Error')));
    }

    /**
     * Broadcast bulk SMS to a target group or a pasted list of numbers.
     */
    public function broadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_group'   => 'required|string|in:' . self::GROUPS,
            'custom_numbers' => 'nullable|string|max:200000',
            'message'        => 'required|string|max:1000',
        ]);

        if ($validated['target_group'] === 'custom') {
            $recipients = preg_split('/[\s,;]+/', (string) ($validated['custom_numbers'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $recipients = $this->audience($validated['target_group'], 'phone')->pluck('contact')->all();
        }

        $recipients = $this->uniquePhones($recipients);

        if (empty($recipients)) {
            return back()->with('error', 'কোনো প্রাপকের মোবাইল নম্বর পাওয়া যায়নি!');
        }

        $title = 'Bulk SMS Broadcast (' . ucfirst($validated['target_group']) . ')';
        SendSmsCampaign::dispatchAfterResponse('broadcast', $recipients, $validated['message'], $validated['target_group'], $title, auth()->id(), auth()->user()?->name ?? 'Admin');

        $this->accessService->log('sms_bulk_broadcast', "বাল্ক এসএমএস শুরু হয়েছে ({$validated['target_group']}) — প্রাপক " . count($recipients));

        return $this->campaignStarted(count($recipients));
    }

    /**
     * Personalized Many-to-Many SMS: each person gets their own text with {name}, {role}, {phone}.
     */
    public function broadcastMany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_group'     => 'required|string|in:' . self::GROUPS,
            'custom_data'      => 'nullable|string|max:200000',
            'message_template' => 'required|string|max:1000',
        ]);

        $template = $validated['message_template'];
        $people   = [];

        if ($validated['target_group'] === 'custom') {
            foreach (preg_split('/[\r\n]+/', (string) ($validated['custom_data'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $line) {
                $parts = array_map('trim', explode('|', $line));
                if (($parts[0] ?? '') !== '') {
                    $people[] = ['phone' => $parts[0], 'name' => $parts[1] ?? '', 'role' => $parts[2] ?? ''];
                }
            }
        } else {
            foreach ($this->audience($validated['target_group'], 'phone') as $row) {
                $people[] = ['phone' => $row->contact, 'name' => $row->name, 'role' => $row->role];
            }
        }

        $messages = [];
        foreach ($people as $p) {
            $messages[] = [
                'to'      => $p['phone'],
                'message' => str_replace(
                    ['{name}', '{role}', '{phone}'],
                    [$p['name'] ?: 'সম্মানিত গ্রাহক', $p['role'] ? ucfirst($p['role']) : 'ইউজার', $p['phone']],
                    $template
                ),
            ];
        }

        if (empty($messages)) {
            return back()->with('error', 'কোনো প্রাপকের তথ্য পাওয়া যায়নি!');
        }

        $title = 'Personalized SMS (' . ucfirst($validated['target_group']) . ')';
        SendSmsCampaign::dispatchAfterResponse('many', $messages, $template, $validated['target_group'], $title, auth()->id(), auth()->user()?->name ?? 'Admin');

        $this->accessService->log('sms_many_broadcast', "মেনি-টু-মেনি এসএমএস শুরু হয়েছে ({$validated['target_group']}) — প্রাপক " . count($messages));

        return $this->campaignStarted(count($messages));
    }

    /**
     * Update SMS gateway settings. A blank API key keeps the saved one.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider'  => 'required|string|in:alaapcloud,bulksmsbd,greenweb,alphasms,sms4bd,generic',
            'url'       => 'required|string|url:https|max:255',
            'api_key'   => 'nullable|string|max:255',
            'sender_id' => 'required|string|max:100',
        ]);

        $saved = SiteSetting::get('sms_gateway_settings', []);
        $saved = is_array($saved) ? $saved : [];

        // blank = keep the saved key; no saved key = the server keeps using the .env one
        if (!empty($validated['api_key'])) {
            $validated['api_key'] = Secret::seal($validated['api_key']);
        } elseif (!empty($saved['api_key'])) {
            $validated['api_key'] = $saved['api_key'];
        } else {
            unset($validated['api_key']);
        }

        AdminDashboardSetting::updateOrCreate(
            ['key' => 'sms_gateway_settings'],
            ['value' => $validated, 'updated_by' => auth()->id()]
        );

        SiteSetting::clearCache();
        Cache::forget(SmsService::BALANCE_CACHE_KEY);

        $this->accessService->log('sms_settings_updated', "এসএমএস গেটওয়ে কনফিগারেশন আপডেট করা হয়েছে ({$validated['provider']})");

        return back()->with('success', 'এসএমএস গেটওয়ে কনফিগারেশন সংরক্ষিত হয়েছে।');
    }

    /**
     * Send a single/test Email.
     */
    public function sendEmailTest(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'recipient_email' => 'required|email',
            'subject'         => 'required|string|max:255',
            'body_content'    => 'required|string|max:10000',
            'action_text'     => 'nullable|string|max:100',
            'action_url'      => 'nullable|url:http,https|max:255',
        ]);

        $res = EmailService::sendSingle(
            toEmail: $validated['recipient_email'],
            subject: $validated['subject'],
            body: $validated['body_content'],
            actionText: $validated['action_text'] ?? null,
            actionUrl: $validated['action_url'] ?? null,
            recipientName: 'অ্যাডমিন/টেস্টার'
        );

        $this->accessService->log('email_test_sent', "টেস্ট ইমেইল পাঠানো হয়েছে: {$validated['recipient_email']} | স্ট্যাটাস: " . ($res['success'] ? 'সফল' : 'ব্যর্থ'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        return $res['success']
            ? back()->with('success', 'টেস্ট ইমেইল সফলভাবে পাঠানো হয়েছে: ' . $validated['recipient_email'])
            : back()->with('error', 'ইমেইল পাঠানো যায়নি: ' . $res['message']);
    }

    /**
     * Broadcast bulk email to target user group or custom email list.
     */
    public function broadcastEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_group'  => 'required|string|in:' . self::GROUPS,
            'custom_emails' => 'nullable|string|max:200000',
            'subject'       => 'required|string|max:255',
            'body_content'  => 'required|string|max:10000',
            'action_text'   => 'nullable|string|max:100',
            'action_url'    => 'nullable|url:http,https|max:255',
        ]);

        if ($validated['target_group'] === 'custom') {
            $recipients = preg_split('/[\s,;]+/', (string) ($validated['custom_emails'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $recipients = $this->audience($validated['target_group'], 'email')->pluck('contact')->all();
        }

        $recipients = array_values(array_unique(array_map('strtolower', array_filter(array_map('trim', $recipients)))));

        if (empty($recipients)) {
            return back()->with('error', 'কোনো প্রাপকের ইমেইল ঠিকানা পাওয়া যায়নি!');
        }

        $this->allowLongRun();
        $result = EmailService::sendBroadcast(
            recipients: $recipients,
            subject: $validated['subject'],
            body: $validated['body_content'],
            actionText: $validated['action_text'] ?? null,
            actionUrl: $validated['action_url'] ?? null
        );

        $this->accessService->log('email_bulk_broadcast', "বাল্ক ইমেইল ({$validated['target_group']}) — সফল: {$result['sent']}/{$result['total']}");

        $this->logCampaign('email', $validated['target_group'], $result['total'], $result['sent'], $result['failed'], $validated['subject'], $validated['body_content']);

        $msg = "সফলভাবে {$result['sent']} জন প্রাপকের কাছে ইমেইল পাঠানো হয়েছে।";
        if ($result['failed'] > 0) {
            $msg .= " ({$result['failed']} টি ব্যর্থ)";
        }

        return back()->with('success', $msg);
    }

    /**
     * Update SMTP server settings. A blank password keeps the saved one.
     */
    public function updateSmtpSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mailer'       => 'required|string|in:smtp,sendmail,log',
            'host'         => 'required|string|max:255',
            'port'         => 'required|integer|between:1,65535',
            'encryption'   => 'required|string|in:tls,ssl,none',
            'username'     => 'nullable|string|max:255',
            'password'     => 'nullable|string|max:255',
            'from_address' => 'required|email',
            'from_name'    => 'required|string|max:100',
        ]);

        $saved = SiteSetting::get('smtp_settings', []);
        $saved = is_array($saved) ? $saved : [];

        $validated['password'] = !empty($validated['password'])
            ? Secret::seal($validated['password'])
            : ($saved['password'] ?? '');

        AdminDashboardSetting::updateOrCreate(
            ['key' => 'smtp_settings'],
            ['value' => $validated, 'updated_by' => auth()->id()]
        );

        SiteSetting::clearCache();

        $this->accessService->log('smtp_settings_updated', "এসএমটিপি কনফিগারেশন আপডেট করা হয়েছে ({$validated['host']})");

        return back()->with('success', 'এসএমটিপি সেটিংস সংরক্ষিত হয়েছে।');
    }

    /**
     * Test sending an OTP to a phone and/or an email.
     */
    public function sendOtpTest(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'otp_type' => 'required|string|in:registration,password_reset,login_2fa,order_confirmation',
            'phone'    => 'nullable|string|max:20',
            'email'    => 'nullable|email',
            'otp_code' => 'nullable|digits_between:4,8',
            'channel'  => 'required|string|in:both,sms,email',
        ]);

        $otpCode   = !empty($validated['otp_code']) ? $validated['otp_code'] : (string) random_int(100000, 999999);
        $recipient = [
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'name'  => 'অ্যাডমিন/টেস্টার',
        ];

        $res = match ($validated['otp_type']) {
            'registration'       => CommunicationService::sendRegistrationOtp($recipient, $otpCode, $validated['channel']),
            'password_reset'     => CommunicationService::sendPasswordResetOtp($recipient, $otpCode, url('/reset-password'), $validated['channel']),
            'login_2fa'          => CommunicationService::sendLoginOtp($recipient, $otpCode, $validated['channel']),
            'order_confirmation' => CommunicationService::sendOrderOtp($recipient, $otpCode, 'TEST-' . random_int(1000, 9999), $validated['channel']),
        };

        // the code itself is not written to the activity log
        $this->accessService->log('otp_test_sent', "টেস্ট ওটিপি পাঠানো হয়েছে ({$validated['otp_type']}) | চ্যানেল: {$validated['channel']}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $res['success'],
                'message' => $res['sms']['message'] ?? ($res['email']['message'] ?? null),
                'results' => $res,
                'otp'     => $otpCode,
                'type'    => $validated['otp_type'],
            ]);
        }

        return $res['success']
            ? back()->with('success', 'টেস্ট ওটিপি সফলভাবে পাঠানো হয়েছে!')
            : back()->with('error', 'ওটিপি পাঠাতে সমস্যা হয়েছে। ফোন বা ইমেইল গেটওয়ে সেটিংস চেক করুন।');
    }

    /**
     * Dual-channel campaign: SMS and Email at the same time.
     */
    public function broadcastDual(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_group' => 'required|string|in:all,customers,authors,publishers,sellers,libraries',
            'subject'      => 'required|string|max:255',
            'message'      => 'required|string|max:1000',
            'action_text'  => 'nullable|string|max:100',
            'action_url'   => 'nullable|url:http,https|max:255',
            'channel'      => 'required|string|in:both,sms,email',
        ]);

        $recipients = [];
        foreach ($this->audience($validated['target_group'], 'both') as $row) {
            $recipients[] = ['phone' => $row->phone, 'email' => $row->email, 'name' => $row->name];
        }

        if (empty($recipients)) {
            return back()->with('error', 'কোনো প্রাপক পাওয়া যায়নি!');
        }

        $this->allowLongRun();
        $summary = CommunicationService::sendMarketingCampaign(
            recipients: $recipients,
            subject: $validated['subject'],
            message: $validated['message'],
            actionText: $validated['action_text'] ?? null,
            actionUrl: $validated['action_url'] ?? null,
            channel: $validated['channel']
        );

        $this->accessService->log('dual_broadcast_sent', "যৌথ ক্যাম্পেইন ({$validated['target_group']}) — SMS: {$summary['sms_sent']}, Email: {$summary['email_sent']}");

        $total  = ($validated['channel'] !== 'email' ? $summary['sms_total'] : 0) + ($validated['channel'] !== 'sms' ? $summary['email_total'] : 0);
        $sent   = $summary['sms_sent'] + $summary['email_sent'];

        $this->logCampaign($validated['channel'], $validated['target_group'], $total, $sent, max(0, $total - $sent), $validated['subject'], $validated['message'], 'dual_marketing');

        return back()->with('success', "ক্যাম্পেইন সম্পন্ন: SMS সফল {$summary['sms_sent']}, Email সফল {$summary['email_sent']}");
    }

    /**
     * Store or update a saved message template.
     */
    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id'          => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'title'       => 'required|string|max:150',
            'channel'     => 'required|string|in:sms,email,both',
            'tag'         => 'nullable|string|max:50',
            'subject'     => 'nullable|string|max:255',
            'body'        => 'required|string|max:2000',
            'action_text' => 'nullable|string|max:100',
            'action_url'  => 'nullable|url:http,https|max:255',
        ]);

        $templates  = $this->getTemplatesList();
        $templateId = $validated['id'] ?: ('tpl_' . uniqid());

        $new = [
            'id'          => $templateId,
            'title'       => $validated['title'],
            'channel'     => $validated['channel'],
            'tag'         => $validated['tag'] ?: 'General',
            'subject'     => $validated['subject'] ?? '',
            'body'        => $validated['body'],
            'action_text' => $validated['action_text'] ?? '',
            'action_url'  => $validated['action_url'] ?? '',
            'updated_at'  => now()->toDateTimeString(),
        ];

        $found = false;
        foreach ($templates as $i => $t) {
            if (($t['id'] ?? '') === $templateId) {
                $templates[$i] = $new;
                $found = true;
                break;
            }
        }
        if (!$found) {
            array_unshift($templates, $new);
        }

        AdminDashboardSetting::updateOrCreate(
            ['key' => 'messaging_templates'],
            ['value' => $templates, 'updated_by' => auth()->id()]
        );

        $this->accessService->log('messaging_template_saved', "মেসেজিং টেমপ্লেট সংরক্ষিত: {$validated['title']}");

        return back()->with('success', "টেমপ্লেট '{$validated['title']}' সংরক্ষিত হয়েছে।");
    }

    public function deleteTemplate(string $id): RedirectResponse
    {
        $filtered = array_values(array_filter($this->getTemplatesList(), fn ($t) => ($t['id'] ?? '') !== $id));

        AdminDashboardSetting::updateOrCreate(
            ['key' => 'messaging_templates'],
            ['value' => $filtered, 'updated_by' => auth()->id()]
        );

        $this->accessService->log('messaging_template_deleted', "মেসেজিং টেমপ্লেট ডিলিট (ID: {$id})");

        return back()->with('success', 'টেমপ্লেট মুছে ফেলা হয়েছে।');
    }

    public function clearLogs(): RedirectResponse
    {
        AdminDashboardSetting::updateOrCreate(
            ['key' => 'messaging_campaign_logs'],
            ['value' => [], 'updated_by' => auth()->id()]
        );

        $this->accessService->log('messaging_logs_cleared', 'ক্যাম্পেইন হিস্ট্রি ক্লিয়ার করা হয়েছে');

        return back()->with('success', 'ক্যাম্পেইন হিস্ট্রি মুছে ফেলা হয়েছে।');
    }

    /**
     * Export campaign history as CSV.
     */
    public function exportLogs(): StreamedResponse
    {
        $logs = $this->getCampaignLogsList();

        return $this->csv('messaging_campaign_logs', ['ID', 'Date & Time', 'Channel', 'Campaign Title', 'Target Group', 'Total', 'Sent', 'Failed', 'Skipped (fake/limited)', 'Admin', 'Message Preview'], function ($out) use ($logs) {
            foreach ($logs as $l) {
                $this->csvRow($out, [
                    $l['id'] ?? '', $l['created_at'] ?? '', strtoupper($l['channel'] ?? 'SMS'), $l['title'] ?? '',
                    $l['target_group'] ?? '', $l['total'] ?? 0, $l['sent'] ?? 0, $l['failed'] ?? 0, $l['refused'] ?? 0,
                    $l['admin_name'] ?? 'Admin', $l['preview'] ?? '',
                ]);
            }
        });
    }

    /**
     * Export the SMS transaction history (same filters as the page) as CSV.
     */
    public function exportTransactions(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'q'       => 'nullable|string|max:30',
            'status'  => 'nullable|in:pending,sent,failed,blocked,rejected',
            'purpose' => 'nullable|in:otp,order,welcome,notification,campaign,test',
            'source'  => 'nullable|in:system,admin',
            'from'    => 'nullable|date',
            'to'      => 'nullable|date',
        ]);

        $query = $this->transactionQuery($filters);

        return $this->csv('sms_transactions', ['Date & Time', 'Number', 'Purpose', 'Source', 'Status', 'Reason', 'SMS Parts', 'Cost (BDT)', 'Gateway Response', 'Campaign', 'Message'], function ($out) use ($query) {
            $query->chunkById(1000, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    $this->csvRow($out, [
                        $r->created_at?->format('Y-m-d H:i:s'), $r->phone, $r->purpose, $r->source, $r->status, $r->reason,
                        $r->segments, $r->cost, $r->gateway_response, $r->campaign, $r->message,
                    ]);
                }
            });
        });
    }

    /**
     * Lift the restriction or block of a number.
     */
    public function releaseNumber(Request $request): RedirectResponse
    {
        $phone = PhoneNormalizer::toE164($request->validate(['phone' => 'required|string|max:20'])['phone']);

        SmsGuard::release($phone);
        $this->accessService->log('sms_number_released', 'এসএমএস সীমাবদ্ধ/ব্লক নম্বর মুক্ত করা হয়েছে: ' . $this->maskPhone($phone));

        return back()->with('success', 'নম্বরটি মুক্ত করা হয়েছে। এখন থেকে আবার ওটিপি/মেসেজ পাঠানো যাবে।');
    }

    /**
     * Block a number for OTP by hand.
     */
    public function blockNumber(Request $request): RedirectResponse
    {
        $data  = $request->validate(['phone' => 'required|string|max:20', 'reason' => 'nullable|string|max:150']);
        $info  = PhoneNormalizer::normalize($data['phone']);

        if (SmsGuard::inspectNumber($info) === 'invalid_number') {
            return back()->with('error', 'সঠিক মোবাইল নম্বর দিন।');
        }

        SmsGuard::blockNumber($info['e164'], $data['reason'] ?? null, auth()->id());
        $this->accessService->log('sms_number_blocked', 'এসএমএস নম্বর ব্লক করা হয়েছে: ' . $this->maskPhone($info['e164']));

        return back()->with('success', 'নম্বরটিতে ওটিপি পাঠানো ব্লক করা হয়েছে।');
    }

    // ---------------------------------------------------------------------
    // helpers
    // ---------------------------------------------------------------------

    private function smsTablesExist(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable('sms_logs');
        } catch (\Throwable) {
            return false;
        }
    }

    private function transactionQuery(array $f)
    {
        $q = SmsLog::query()->orderByDesc('id');

        if (!empty($f['q'])) {
            $digits = preg_replace('/\D/', '', $f['q']);
            if (strlen($digits) >= 10) {
                $q->where('phone', PhoneNormalizer::toE164($digits));
            } elseif ($digits !== '') {
                $q->where('phone', 'like', '%' . $digits . '%');
            }
        }
        foreach (['status', 'purpose', 'source'] as $col) {
            if (!empty($f[$col])) {
                $q->where($col, $f[$col]);
            }
        }
        if (!empty($f['from'])) {
            $q->where('created_at', '>=', \Illuminate\Support\Carbon::parse($f['from'])->startOfDay());
        }
        if (!empty($f['to'])) {
            $q->where('created_at', '<=', \Illuminate\Support\Carbon::parse($f['to'])->endOfDay());
        }

        return $q;
    }

    /** Sent / failed / refused counts and the estimated money spent. */
    private function smsStats(): array
    {
        try {
            $today = now()->startOfDay()->toDateTimeString();
            $month = now()->startOfMonth()->toDateTimeString();

            $row = SmsLog::query()->selectRaw(
                "COALESCE(SUM(CASE WHEN status = 'sent' AND created_at >= ? THEN segments ELSE 0 END), 0) AS sent_today,
                 COALESCE(SUM(CASE WHEN status = 'failed' AND created_at >= ? THEN 1 ELSE 0 END), 0) AS failed_today,
                 COALESCE(SUM(CASE WHEN status IN ('blocked','rejected') AND created_at >= ? THEN 1 ELSE 0 END), 0) AS refused_today,
                 COALESCE(SUM(CASE WHEN status = 'sent' AND created_at >= ? THEN segments ELSE 0 END), 0) AS sent_month,
                 COALESCE(SUM(CASE WHEN status = 'sent' AND created_at >= ? THEN cost ELSE 0 END), 0) AS cost_month,
                 COALESCE(SUM(CASE WHEN status = 'sent' THEN segments ELSE 0 END), 0) AS sent_total",
                [$today, $today, $today, $month, $month]
            )->first();

            return [
                'sent_today'    => (int) $row->sent_today,
                'failed_today'  => (int) $row->failed_today,
                'refused_today' => (int) $row->refused_today,
                'sent_month'    => (int) $row->sent_month,
                'cost_month'    => (float) $row->cost_month,
                'sent_total'    => (int) $row->sent_total,
            ];
        } catch (\Throwable $e) {
            Log::warning('SMS stats failed: ' . $e->getMessage());

            return ['sent_today' => 0, 'failed_today' => 0, 'refused_today' => 0, 'sent_month' => 0, 'cost_month' => 0.0, 'sent_total' => 0];
        }
    }

    /** @return int[] */
    private function libraryCampaignIds(): array
    {
        return EventCampaign::where('slug', 'pathagar')->orWhere('type', 'library')->pluck('id')->all();
    }

    /** Number of users per audience that have a phone number / an email address. */
    private function contactCounts(string $field): array
    {
        $byRole = User::whereNotNull($field)->where($field, '!=', '')
            ->selectRaw('role, COUNT(*) AS c')->groupBy('role')->pluck('c', 'role');

        $sum = fn (array $roles) => (int) collect($roles)->sum(fn ($r) => $byRole[$r] ?? 0);

        return [
            'total_users' => (int) $byRole->sum(),
            'customers'   => $sum(['customer', 'buyer', 'user']),
            'authors'     => $sum(['author']),
            'publishers'  => $sum(['publisher']),
            'sellers'     => $sum(['seller', 'sub_admin']),
            'libraries'   => EventRegistration::whereIn('event_campaign_id', $this->libraryCampaignIds())->whereNotNull($field)->where($field, '!=', '')->count(),
        ];
    }

    /**
     * People of a target group. $field = phone | email | both; "contact" is the phone or email asked for.
     *
     * @return \Illuminate\Support\Collection<int, object{name: ?string, role: ?string, phone: ?string, email: ?string, contact: ?string}>
     */
    private function audience(string $group, string $field)
    {
        $has = fn ($q) => $field === 'both'
            ? $q->where(fn ($w) => $w->where(fn ($p) => $p->whereNotNull('phone')->where('phone', '!=', ''))->orWhere(fn ($e) => $e->whereNotNull('email')->where('email', '!=', '')))
            : $q->whereNotNull($field)->where($field, '!=', '');

        if ($group === 'libraries') {
            $rows = $has(EventRegistration::whereIn('event_campaign_id', $this->libraryCampaignIds()))->get(['name', 'phone', 'email']);
            $role = 'পাঠাগার';
        } else {
            $query = $has(User::query());
            match ($group) {
                'customers'  => $query->whereIn('role', ['customer', 'buyer', 'user']),
                'authors'    => $query->where('role', 'author'),
                'publishers' => $query->where('role', 'publisher'),
                'sellers'    => $query->whereIn('role', ['seller', 'sub_admin']),
                default      => null,
            };
            $rows = $query->get(['name', 'phone', 'email', 'role']);
            $role = null;
        }

        return $rows->map(fn ($r) => (object) [
            'name'    => $r->name,
            'role'    => $role ?? $r->role,
            'phone'   => $r->phone,
            'email'   => $r->email,
            'contact' => $field === 'email' ? $r->email : $r->phone,
        ]);
    }

    /** One entry per real phone: 017…, +88017… and 88017… are the same person. */
    private function uniquePhones(array $numbers): array
    {
        $unique = [];
        foreach ($numbers as $n) {
            $n = trim((string) $n);
            if ($n !== '') {
                $unique[PhoneNormalizer::toE164($n)] = $n;
            }
        }

        return array_values($unique);
    }

    private function campaignStarted(int $count): RedirectResponse
    {
        return back()->with('success', 'ক্যাম্পেইন শুরু হয়েছে — ' . number_format($count) . " জন প্রাপক। শেষ হলে ফলাফল (সফল, ব্যর্থ, ভুয়া বাদ) 'হিস্ট্রি' ও 'ব্যালান্স' ট্যাবে দেখা যাবে।");
    }

    /** A big campaign makes many gateway calls: do not let PHP stop it half way. */
    private function allowLongRun(): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        ignore_user_abort(true);
    }

    private function maskPhone(string $phone): string
    {
        $d = preg_replace('/\D/', '', $phone);

        return strlen($d) > 7 ? substr($d, 0, 5) . '****' . substr($d, -2) : $d;
    }

    private function csv(string $name, array $header, callable $body): StreamedResponse
    {
        $filename = $name . '_' . date('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () use ($header, $body) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Bangla
            fputcsv($out, $header);
            $body($out);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** A cell that starts with = + - @ is run as a formula by Excel: prefix it with a quote. */
    private function csvRow($out, array $cells): void
    {
        fputcsv($out, array_map(function ($c) {
            $c = (string) $c;

            return $c !== '' && in_array($c[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $c : $c;
        }, $cells));
    }

    private function getTemplatesList(): array
    {
        $setting = AdminDashboardSetting::where('key', 'messaging_templates')->first();
        if ($setting && is_array($setting->value) && !empty($setting->value)) {
            return $setting->value;
        }

        return [
            [
                'id'          => 'tpl_eid_discount',
                'title'       => 'বাংলা নববর্ষ ও বিশেষ বই উৎসব ছাড়',
                'channel'     => 'both',
                'tag'         => 'Marketing',
                'subject'     => 'আইডিয়া প্রকাশনে বিশেষ বই উৎসব — আকর্ষণীয় ছাড়ে বই সংগ্রহ করুন!',
                'body'        => "সম্মানিত {name},\nআইডিয়া প্রকাশনের বিশেষ বই মেলায় আপনার পছন্দের বই সংগ্রহ করতে ভিজিট করুন: https://ideaabd.com/books । প্রোমোকোড ব্যবহারে পান আকর্ষণীয় ছাড়!",
                'action_text' => 'বইয়ের তালিকা দেখুন',
                'action_url'  => 'https://ideaabd.com/books',
            ],
            [
                'id'          => 'tpl_library_grant',
                'title'       => 'পাঠাগার বই অনুদান আপডেট নোটিশ',
                'channel'     => 'both',
                'tag'         => 'Grant',
                'subject'     => 'পাঠাগার উন্নয়ন ও বই অনুদান ২০২৬ সংক্রান্ত আপডেট — আইডিয়া প্রকাশন',
                'body'        => "সুপ্রিয় {name},\nআইডিয়া পাঠাগার উন্নয়ন বই অনুদান কার্যক্রমে আপনার আবেদনের অগ্রগতি সংক্রান্ত তথ্যের জন্য ওয়েবসাইট ভিজিট করুন। ধন্যবাদ, আইডিয়া প্রকাশন।",
                'action_text' => 'পাঠাগার ড্যাশবোর্ড',
                'action_url'  => 'https://ideaabd.com/pathagar',
            ],
            [
                'id'          => 'tpl_order_confirmed',
                'title'       => 'অর্ডার কনফার্মেশন ও ডেলিভারি আপডেট',
                'channel'     => 'sms',
                'tag'         => 'Transactional',
                'subject'     => 'আপনার অর্ডার #{order_id} নিশ্চিত করা হয়েছে',
                'body'        => "প্রিয় {name}, আইডিয়া প্রকাশনে আপনার অর্ডার #{order_id} সফলভাবে কনফার্ম হয়েছে। খুব দ্রুত আপনার ঠিকানায় পাঠানো হবে। হেল্পলাইন: 01726976982",
                'action_text' => 'অর্ডার ট্র্যাক করুন',
                'action_url'  => 'https://ideaabd.com/orders',
            ],
            [
                'id'          => 'tpl_payment_reminder',
                'title'       => 'বকেয়া বিল পরিশোধের রিমাইন্ডার',
                'channel'     => 'both',
                'tag'         => 'Billing',
                'subject'     => 'বকেয়া বিল পরিশোধ সংক্রান্ত নোটিশ — আইডিয়া প্রকাশন',
                'body'        => "সম্মানিত {name}, আপনার ইনভয়েসের বকেয়া ৳{due_amount} টাকা পরিশোধের জন্য লিংকটি ভিজিট করুন: https://ideaabd.com/pay । ধন্যবাদ।",
                'action_text' => 'পেমেন্ট সম্পন্ন করুন',
                'action_url'  => 'https://ideaabd.com/pay',
            ],
        ];
    }

    private function getCampaignLogsList(): array
    {
        $setting = AdminDashboardSetting::where('key', 'messaging_campaign_logs')->first();

        return ($setting && is_array($setting->value)) ? $setting->value : [];
    }

    private function logCampaign(string $channel, string $targetGroup, int $total, int $sent, int $failed, string $title, string $preview, string $type = 'broadcast', int $refused = 0): void
    {
        CampaignLog::record($channel, $targetGroup, $total, $sent, $failed, $title, $preview, $type, $refused);
    }
}
