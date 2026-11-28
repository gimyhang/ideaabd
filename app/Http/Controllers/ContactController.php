<?php

namespace App\Http\Controllers;

use App\Models\ContactInquiry;
use App\Support\ContactInbox;
use App\Support\PhoneNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The website's contact form. AbuseGuard already limits it (bucket "form") and drops robot posts
 * (honeypot + too-fast stamp from <x-bot-trap/>). Here: strict input, no double rows, spam score,
 * and never the visitor's personal data in a log file.
 */
class ContactController extends Controller
{
    public function submit(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'min:2', 'max:100'],
            'phone'   => ['required', 'string', 'max:25', 'regex:/^[\s\d+()\-০-৯]{7,25}$/u'],
            'email'   => ['nullable', 'email:rfc', 'max:100'],
            'subject' => ['nullable', 'string', 'max:' . ContactInbox::SUBJECTS_MAX],
            'message' => ['required', 'string', 'min:5', 'max:3000'],
        ], [
            'phone.regex'  => 'সঠিক মোবাইল নম্বর দিন।',
            'message.min'  => 'বার্তাটি একটু বিস্তারিত লিখুন।',
        ]);

        $phone = PhoneNormalizer::toLocal($data['phone']);
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) < 7 || strlen($digits) > 15) {
            return $this->fail($request, ['phone' => 'সঠিক মোবাইল নম্বর দিন।']);
        }

        $row = [
            'name'    => ContactInbox::clean($data['name'], 100),
            'phone'   => mb_substr($phone, 0, 20),
            'email'   => !empty($data['email']) ? mb_strtolower(trim($data['email'])) : null,
            'subject' => ContactInbox::clean($data['subject'] ?? '', ContactInbox::SUBJECTS_MAX) ?: 'সাধারণ বার্তা',
            'message' => ContactInbox::clean($data['message'], 3000),
        ];
        if (mb_strlen($row['name']) < 2 || mb_strlen($row['message']) < 5) {
            return $this->fail($request, ['message' => 'বার্তাটি একটু বিস্তারিত লিখুন।']);
        }

        try {
            if (ContactInbox::ensureSchema()) {
                // the same message from the same number within 10 minutes (double click, refresh) is kept once
                $dup = ContactInquiry::where('phone', $row['phone'])->where('created_at', '>=', now()->subMinutes(10))
                    ->where('message', $row['message'])->exists();
                if (!$dup) {
                    $score = ContactInbox::spamScore($row['message'], $row['name'], (string) $row['email']);
                    ContactInquiry::create($row + [
                        'status'     => $score >= 60 ? 'spam' : 'new',
                        'spam_score' => $score,
                        'ip'         => $request->ip(),
                        'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                        'user_id'    => $request->user()?->id,
                    ]);
                    \Illuminate\Support\Facades\Cache::forget('admin_sidebar_pending_badges');
                }
            }
        } catch (\Throwable $e) {
            // no name / phone / message in the log
            Log::error('Contact form could not be saved: ' . get_class($e) . ' ' . $e->getMessage());

            return $this->fail($request, ['message' => 'এই মুহূর্তে বার্তা সংরক্ষণ করা যায়নি — একটু পরে আবার চেষ্টা করুন বা ফোন করুন।'], 500);
        }

        $ok = 'আপনার বার্তাটি সফলভাবে পাঠানো হয়েছে! আমাদের টিম দ্রুতই আপনার সাথে যোগাযোগ করবে।';

        return $request->ajax() || $request->wantsJson()
            ? response()->json(['success' => true, 'message' => $ok])
            : back()->with('success', $ok);
    }

    private function fail(Request $request, array $errors, int $status = 422): JsonResponse|RedirectResponse
    {
        return $request->ajax() || $request->wantsJson()
            ? response()->json(['success' => false, 'message' => reset($errors), 'errors' => array_map(fn ($m) => [$m], $errors)], $status)
            : back()->withInput()->withErrors($errors);
    }
}
