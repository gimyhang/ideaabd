<?php

namespace App\Services;

use App\Models\SmsCampaign;
use App\Support\CampaignLog;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Sends a saved SMS campaign a slice at a time.
 *
 * Why: the whole campaign used to run in one PHP process after the page was returned. Shared hosting stops such a
 * process after a while (LiteSpeed / request time limit), so a big campaign quietly ended after the first few hundred
 * numbers and never reached the history. Now every slice of 100 numbers is recorded before it is sent, and whoever
 * runs next (the request that started it, the admin page while it is open, or the scheduler) carries on from there.
 *
 * At most once: the position moves past a slice *before* the gateway is called. If the process dies in the middle,
 * that slice is counted as "unknown" and is not sent again — a duplicate SMS costs money and annoys people, a missing
 * one can be re-sent by hand.
 */
class SmsCampaignRunner
{
    public const SLICE = 100;

    /** A worker holds the campaign this long; a dead worker's lock simply runs out. */
    private const LOCK_SECONDS = 90;

    /** Gateway answers that stop the campaign: sending the rest would fail the same way. */
    private const STOP_CODES = [1002, 1003, 1007, 1009, 1010, 1011, 1012, 1032];

    /** Gateway answer "too fast": nothing was sent, so the slice is tried again on the next run. */
    private const RETRY_CODES = [1013];

    public static function ready(): bool
    {
        static $ok = null;

        return $ok ??= Schema::hasTable('sms_campaigns');
    }

    /**
     * Save a new campaign. $payload is a list of numbers (broadcast) or [['to' => …, 'message' => …], …] (many).
     * Returns [campaign, duplicateOf]: duplicateOf is the running campaign with the same text and audience, if any.
     *
     * @return array{0: ?SmsCampaign, 1: ?SmsCampaign}
     */
    public static function create(string $mode, array $payload, string $message, string $group, string $title, ?int $adminId, string $adminName): array
    {
        // One entry per real phone: 017…, +88017… and 88017… are the same person.
        $unique = [];
        foreach ($payload as $item) {
            $to = trim((string) ($mode === 'many' ? ($item['to'] ?? '') : $item));
            if ($to === '') {
                continue;
            }
            $key = PhoneNormalizer::toE164($to) ?: $to;
            if (!isset($unique[$key])) {
                $unique[$key] = $mode === 'many' ? ['to' => $to, 'message' => (string) ($item['message'] ?? $message)] : $to;
            }
        }
        $payload = array_values($unique);

        $fingerprint = hash('sha256', $mode . '|' . $group . '|' . trim($message) . '|' . count($payload));
        $duplicate = SmsCampaign::where('fingerprint', $fingerprint)
            ->whereIn('status', [SmsCampaign::RUNNING, SmsCampaign::PAUSED])
            ->where('created_at', '>=', now()->subHours(6))
            ->first();
        if ($duplicate) {
            return [null, $duplicate];
        }

        $segments = $mode === 'many'
            ? max(array_map(fn ($i) => SmsService::segments($i['message']), $payload ?: [['message' => $message]]))
            : SmsService::segments($message);

        $campaign = SmsCampaign::create([
            'mode'         => $mode,
            'title'        => mb_substr($title, 0, 150),
            'target_group' => $group,
            'message'      => $message,
            'payload'      => $payload,
            'status'       => SmsCampaign::RUNNING,
            'total'        => count($payload),
            'segments'     => $segments,
            'admin_id'     => $adminId,
            'admin_name'   => mb_substr($adminName, 0, 100),
            'fingerprint'  => $fingerprint,
            'last_beat_at' => now(),
        ]);

        return [$campaign, null];
    }

    /** Work on every running campaign for up to $seconds in total (scheduler, admin page polling). */
    public static function runDue(int $seconds = 20): void
    {
        if (!self::ready()) {
            return;
        }
        $until = microtime(true) + $seconds;
        $ids = SmsCampaign::where('status', SmsCampaign::RUNNING)
            ->where(fn ($q) => $q->whereNull('locked_until')->orWhere('locked_until', '<', now()))
            ->orderBy('id')->limit(5)->pluck('id');
        foreach ($ids as $id) {
            $left = (int) floor($until - microtime(true));
            if ($left < 3) {
                break;
            }
            self::work($id, $left);
        }
    }

    /**
     * Send slices of one campaign for about $seconds. Returns false when another worker holds it.
     */
    public static function work(int $id, int $seconds = 20): bool
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(max(60, $seconds + 2 * SmsService::BATCH_TIMEOUT + 10));
        }
        ignore_user_abort(true);

        if (!self::claim($id)) {
            return false;
        }
        $until = microtime(true) + $seconds;

        try {
            /** @var SmsCampaign $c */
            $c = SmsCampaign::find($id);

            // A previous worker died while a slice was out: count it, do not resend it.
            if ($c->in_flight > 0) {
                $c->unknown  += $c->in_flight;
                $c->in_flight = 0;
                $c->last_error = 'আগের ধাপ মাঝপথে থেমে গিয়েছিল — সেই ' . $c->unknown . ' টি নম্বর আবার পাঠানো হয়নি (ডুপ্লিকেট এড়াতে)।';
                $c->save();
            }

            while (true) {
                $status = SmsCampaign::whereKey($id)->value('status');
                if ($status !== SmsCampaign::RUNNING) {
                    break; // paused or cancelled from the page
                }
                if ($c->position >= $c->total) {
                    self::finish($c);
                    break;
                }

                $slice = array_slice($c->payload ?? [], $c->position, self::SLICE);
                $n = count($slice);

                // Move past the slice first (at most once), then send it.
                $c->position    += $n;
                $c->in_flight    = $n;
                $c->locked_until = now()->addSeconds(self::LOCK_SECONDS);
                $c->last_beat_at = now();
                $c->save();

                $r = self::sendSlice($c, $slice);

                if (in_array($r['code'], self::RETRY_CODES, true)) {
                    // Rejected as too fast: nothing went out, so step back and try again on the next run.
                    $c->position  -= $n;
                    $c->in_flight  = 0;
                    $c->last_error = SmsService::getResponseCodeMessage($r['code']);
                    $c->save();
                    break;
                }

                if ($r['sent'] === 0 && in_array($r['code'], self::STOP_CODES, true)) {
                    // The slice was refused for an account reason (no balance, sender ID, IP whitelist …): nothing
                    // went out, so put the slice back and pause. "আবার চালু" continues from here once it is fixed.
                    $c->position  -= $n;
                    $c->in_flight  = 0;
                    $c->status     = SmsCampaign::PAUSED;
                    $c->last_error = 'থেমে আছে: ' . SmsService::getResponseCodeMessage($r['code'], $r['message']);
                    $c->save();
                    break;
                }

                $c->sent      += $r['sent'];
                $c->refused   += $r['refused'];
                $c->unknown   += $r['uncertain'];
                $c->failed    += max(0, $n - $r['sent'] - $r['refused'] - $r['uncertain']);
                $c->in_flight  = 0;
                $c->last_beat_at = now();

                if ($r['sent'] === 0 && $r['message'] !== '') {
                    $c->last_error = mb_substr($r['message'], 0, 250);
                }
                $c->save();

                if (microtime(true) >= $until) {
                    break;
                }
            }
        } catch (\Throwable $e) {
            Log::error("SMS campaign #{$id} slice failed: " . $e->getMessage());
            SmsCampaign::whereKey($id)->update(['last_error' => mb_substr($e->getMessage(), 0, 250)]);
        } finally {
            SmsCampaign::whereKey($id)->update(['locked_until' => null]);
        }

        return true;
    }

    public static function pause(SmsCampaign $c): void
    {
        if ($c->status === SmsCampaign::RUNNING) {
            $c->update(['status' => SmsCampaign::PAUSED]);
        }
    }

    public static function resume(SmsCampaign $c): void
    {
        if ($c->status === SmsCampaign::PAUSED) {
            $c->update(['status' => SmsCampaign::RUNNING, 'last_error' => null, 'last_beat_at' => now()]);
        }
    }

    public static function cancel(SmsCampaign $c): void
    {
        if ($c->isOpen()) {
            $c->update(['status' => SmsCampaign::CANCELLED, 'finished_at' => now()]);
            self::record($c->fresh());
        }
    }

    /** Campaigns for the progress card: open ones and those finished in the last day. */
    public static function recent(): array
    {
        if (!self::ready()) {
            return [];
        }

        return SmsCampaign::where(fn ($q) => $q->whereIn('status', [SmsCampaign::RUNNING, SmsCampaign::PAUSED])
                ->orWhere('finished_at', '>=', now()->subDay()))
            ->orderByDesc('id')->limit(6)
            ->get()->map->summary()->all();
    }

    // ── internals ───────────────────────────────────────────

    /** Atomically take the campaign for LOCK_SECONDS (works on MySQL and SQLite alike). */
    private static function claim(int $id): bool
    {
        return SmsCampaign::whereKey($id)
            ->where('status', SmsCampaign::RUNNING)
            ->where(fn ($q) => $q->whereNull('locked_until')->orWhere('locked_until', '<', now()))
            ->update(['locked_until' => now()->addSeconds(self::LOCK_SECONDS)]) === 1;
    }

    /** @return array{sent:int, refused:int, uncertain:int, code:?int, message:string} */
    private static function sendSlice(SmsCampaign $c, array $slice): array
    {
        $opts = ['source' => 'admin', 'purpose' => 'campaign', 'campaign' => $c->title];

        if ($c->mode === 'many') {
            $res = SmsService::sendManyToMany($slice, $opts);
            $sent = (int) ($res['total_sent'] ?? 0);
        } else {
            $res = SmsService::send($slice, $c->message, $opts);
            $sent = (int) ($res['sent'] ?? 0);
        }

        $code = $res['response_code'] ?? null;

        return [
            'sent'      => $sent,
            'refused'   => count($res['denied'] ?? []),
            'uncertain' => (int) ($res['uncertain'] ?? 0),
            'code'      => is_numeric($code) ? (int) $code : null,
            'message'   => (string) ($res['error'] ?? ($sent === 0 ? ($res['message'] ?? '') : '')),
        ];
    }

    private static function finish(SmsCampaign $c): void
    {
        $c->status      = SmsCampaign::DONE;
        $c->finished_at = now();
        $c->save();
        self::record($c);
    }

    /** One line in the "হিস্ট্রি" tab, written when the campaign ends. */
    private static function record(SmsCampaign $c): void
    {
        try {
            CampaignLog::record(
                'sms',
                $c->target_group,
                $c->total,
                $c->sent,
                $c->failed + $c->refused + $c->unknown + max(0, $c->total - $c->position),
                $c->title . ($c->status === SmsCampaign::CANCELLED ? ' — বাতিল' : ''),
                $c->message,
                $c->mode === 'many' ? 'many_to_many' : 'broadcast',
                $c->refused,
                $c->admin_id,
                $c->admin_name
            );
        } catch (\Throwable $e) {
            Log::warning('Campaign history not written: ' . $e->getMessage());
        }
    }
}
