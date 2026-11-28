<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Messages people send from the website's contact form, and the admin's answers to them.
 *
 *   contact_inquiries        one row per message: name, phone, email, subject, message, status, spam score, sender IP
 *   contact_inquiry_replies  what the admin did: SMS / e-mail sent from the inbox, call, WhatsApp, internal note
 *
 * status: new → read → replied; or spam / archived. Deleting moves to the trash (soft delete) for 30 days.
 * Tables are made from a web request (the production command line talks to another database). Older messages that
 * were kept in communication_logs (channel = inquiry) are copied in once.
 */
class ContactInbox
{
    public const STATUSES = ['new' => 'নতুন', 'read' => 'পড়া হয়েছে', 'replied' => 'উত্তর দেওয়া', 'spam' => 'স্প্যাম', 'archived' => 'আর্কাইভ'];
    public const SUBJECTS_MAX = 150;
    private const FLAG = 'contact-inbox:schema:v1';

    public static function ensureSchema(): bool
    {
        if (Cache::get(self::FLAG)) {
            return true;
        }
        try {
            if (!Schema::hasTable('contact_inquiries')) {
                Schema::create('contact_inquiries', function (Blueprint $t) {
                    $t->id();
                    $t->string('name', 100);
                    $t->string('phone', 20)->index();
                    $t->string('email', 100)->nullable();
                    $t->string('subject', self::SUBJECTS_MAX)->nullable();
                    $t->text('message');
                    $t->string('status', 12)->default('new')->index();
                    $t->boolean('starred')->default(false);
                    $t->unsignedTinyInteger('spam_score')->default(0);
                    $t->string('ip', 45)->nullable();
                    $t->string('user_agent', 255)->nullable();
                    $t->unsignedBigInteger('user_id')->nullable();
                    $t->text('note')->nullable();
                    $t->timestamp('read_at')->nullable();
                    $t->timestamp('replied_at')->nullable();
                    $t->unsignedBigInteger('replied_by')->nullable();
                    $t->unsignedBigInteger('legacy_id')->nullable()->unique(); // communication_logs.id it was copied from
                    $t->timestamps();
                    $t->softDeletes();
                    $t->index(['status', 'created_at']);
                });
            }
            if (!Schema::hasTable('contact_inquiry_replies')) {
                Schema::create('contact_inquiry_replies', function (Blueprint $t) {
                    $t->id();
                    $t->unsignedBigInteger('inquiry_id')->index();
                    $t->string('channel', 10); // sms | email | call | whatsapp | note
                    $t->text('body')->nullable();
                    $t->boolean('ok')->default(true);
                    $t->string('error', 255)->nullable();
                    $t->unsignedBigInteger('admin_id')->nullable();
                    $t->string('admin_name', 100)->nullable();
                    $t->timestamp('created_at')->nullable();
                });
            }
            self::backfill();
            Cache::forever(self::FLAG, true);

            return true;
        } catch (\Throwable $e) {
            Log::warning('ContactInbox::ensureSchema: ' . $e->getMessage());

            return false;
        }
    }

    public static function ready(): bool
    {
        return (bool) Cache::get(self::FLAG) || SchemaCache::has('contact_inquiries');
    }

    /** Copy the messages kept in communication_logs (old contact form) — once, safe to repeat. */
    public static function backfill(): int
    {
        if (!Schema::hasTable('communication_logs')) {
            return 0;
        }
        $n = 0;
        DB::table('communication_logs')->where('channel', 'inquiry')->orderBy('id')->chunkById(200, function ($rows) use (&$n) {
            $done = DB::table('contact_inquiries')->whereIn('legacy_id', $rows->pluck('id'))->pluck('legacy_id')->all();
            foreach ($rows as $r) {
                if (in_array($r->id, $done)) {
                    continue;
                }
                [$phone, $email] = array_pad(array_map('trim', explode(' / ', (string) $r->recipient, 2)), 2, null);
                $subject = (string) $r->subject;
                $name = '';
                if (($p = mb_strrpos($subject, ' — ')) !== false) {
                    $name = trim(mb_substr($subject, $p + 3));
                    $subject = trim(mb_substr($subject, 0, $p));
                }
                $status = ['delivered' => 'new', 'sent' => 'read', 'read' => 'read', 'replied' => 'replied'][$r->status] ?? 'read';
                DB::table('contact_inquiries')->insert([
                    'name' => mb_substr($name ?: 'নাম নেই', 0, 100), 'phone' => mb_substr((string) $phone, 0, 20),
                    'email' => $email && filter_var($email, FILTER_VALIDATE_EMAIL) ? mb_substr($email, 0, 100) : null,
                    'subject' => mb_substr($subject ?: 'সাধারণ বার্তা', 0, self::SUBJECTS_MAX), 'message' => (string) $r->error_message,
                    'status' => $status, 'spam_score' => self::spamScore((string) $r->error_message, $name, (string) $email),
                    'legacy_id' => $r->id, 'created_at' => $r->created_at, 'updated_at' => $r->updated_at ?? $r->created_at,
                ]);
                $n++;
            }
        });

        return $n;
    }

    /**
     * 0–100: links, link shorteners, crypto / casino / SEO words, Latin-only text full of caps, repeated characters.
     * 60 or more goes straight to "spam" (the admin can still open it and move it back).
     */
    public static function spamScore(string $message, string $name = '', string $email = ''): int
    {
        $t = mb_strtolower($message . ' ' . $name);
        $score = 0;
        $links = preg_match_all('~(https?://|www\.)~i', $message);
        $score += min(60, $links * 25);
        if (preg_match('~(bit\.ly|tinyurl|t\.me/|wa\.me/|\.ru/|\.xyz/|\.top/)~i', $message)) {
            $score += 25;
        }
        foreach (['casino', 'crypto', 'bitcoin', 'forex', 'viagra', 'loan offer', 'seo service', 'backlink', 'rank your website', 'guest post', 'betting', 'porn', 'escort', 'investment plan', 'earn money fast'] as $w) {
            if (str_contains($t, $w)) {
                $score += 30;
            }
        }
        if (!preg_match('/\p{Bengali}/u', $message) && preg_match('/[A-Z]{12,}/', $message)) {
            $score += 15;
        }
        if (preg_match('/(.)\1{9,}/u', $message)) {
            $score += 15;
        }
        if ($email !== '' && preg_match('/@(mailinator|guerrillamail|10minutemail|tempmail|yopmail)\./i', $email)) {
            $score += 20;
        }

        return min(100, $score);
    }

    /** Text that is safe to keep: no control characters, no HTML tags, trimmed, collapsed blank lines. */
    public static function clean(?string $text, int $max): string
    {
        $text = strip_tags((string) $text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', '', $text) ?? '';
        $text = preg_replace("/\n{3,}/", "\n\n", str_replace("\r\n", "\n", $text)) ?? '';

        return mb_substr(trim($text), 0, $max);
    }

    /** Values that start a formula in Excel (=, +, -, @) are made plain text in CSV exports. */
    public static function csvSafe(?string $v): string
    {
        $v = (string) $v;

        return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
    }

    /** Remove trashed messages older than 30 days (and their replies). */
    public static function purgeTrash(): int
    {
        if (!self::ready()) {
            return 0;
        }
        $ids = DB::table('contact_inquiries')->whereNotNull('deleted_at')->where('deleted_at', '<', now()->subDays(30))->pluck('id');
        if ($ids->isEmpty()) {
            return 0;
        }
        DB::table('contact_inquiry_replies')->whereIn('inquiry_id', $ids)->delete();

        return DB::table('contact_inquiries')->whereIn('id', $ids)->delete();
    }

    public static function newCount(): int
    {
        try {
            return self::ready() ? (int) DB::table('contact_inquiries')->whereNull('deleted_at')->where('status', 'new')->count() : 0;
        } catch (\Throwable) {
            return 0;
        }
    }
}
