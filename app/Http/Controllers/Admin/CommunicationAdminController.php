<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Models\ContactInquiryReply;
use App\Services\EmailService;
use App\Services\SmsService;
use App\Support\ContactInbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Inquiries inbox (admin/communication): messages from the website's contact form.
 * The page is a shell; the list, the message and every action are JSON calls (admin-inbox.js).
 *
 * Permissions (RoleMiddleware): read = support.view, act = support.reply; export and "delete for ever" = admin only.
 * Every change is written to the activity log.
 */
class CommunicationAdminController extends Controller
{
    private const FOLDERS = ['inbox', 'new', 'starred', 'replied', 'spam', 'archived', 'trash'];

    public function index(): View
    {
        ContactInbox::ensureSchema();
        ContactInbox::purgeTrash();

        return view('admin.communication.index', [
            'counts'  => $this->counts(),
            'isAdmin' => (bool) auth()->user()?->isAdmin(),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $v = $request->validate([
            'folder' => 'nullable|in:' . implode(',', self::FOLDERS),
            'q'      => 'nullable|string|max:100',
            'page'   => 'nullable|integer|min:1|max:10000',
        ]);
        $folder = $v['folder'] ?? 'inbox';
        $q = trim((string) ($v['q'] ?? ''));

        $query = ContactInquiry::query()->select(['id', 'name', 'phone', 'email', 'subject', 'message', 'status', 'starred', 'spam_score', 'created_at', 'replied_at']);
        match ($folder) {
            'trash'    => $query->onlyTrashed(),
            'new'      => $query->where('status', 'new'),
            'starred'  => $query->where('starred', true)->where('status', '!=', 'spam'),
            'replied'  => $query->where('status', 'replied'),
            'spam'     => $query->where('status', 'spam'),
            'archived' => $query->where('status', 'archived'),
            default    => $query->whereIn('status', ['new', 'read', 'replied']),
        };
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $digits = preg_replace('/\D/', '', $q);
            $query->where(function ($w) use ($like, $digits) {
                $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('subject', 'like', $like)->orWhere('message', 'like', $like);
                if (strlen($digits) >= 3) {
                    $w->orWhere('phone', 'like', '%' . substr($digits, -10) . '%');
                }
            });
        }
        $page = $query->orderByDesc($folder === 'trash' ? 'deleted_at' : 'created_at')->orderByDesc('id')->paginate(25, ['*'], 'page', $v['page'] ?? 1);

        return response()->json([
            'items'  => collect($page->items())->map(fn ($i) => [
                'id'      => $i->id,
                'name'    => $i->name,
                'phone'   => $i->localPhone(),
                'subject' => $i->subject,
                'snippet' => mb_substr(preg_replace('/\s+/u', ' ', $i->message), 0, 140),
                'status'  => $i->status,
                'starred' => $i->starred,
                'spam'    => $i->spam_score,
                'email'   => (bool) $i->email, // only whether one exists; the address is shown when opened
                'time'    => $i->created_at?->diffForHumans(null, true, true),
                'date'    => $i->created_at?->format('d M Y, h:i A'),
            ]),
            'page'   => $page->currentPage(),
            'pages'  => $page->lastPage(),
            'total'  => $page->total(),
            'counts' => $this->counts(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $i = ContactInquiry::withTrashed()->with('replies')->findOrFail($id);
        if ($i->status === 'new' && !$i->trashed()) {
            $i->forceFill(['status' => 'read', 'read_at' => now()])->save();
            Cache::forget('admin_sidebar_pending_badges');
        }
        $others = ContactInquiry::where('id', '!=', $i->id)->where('phone', $i->phone)->latest('id')->limit(10)
            ->get(['id', 'subject', 'status', 'created_at']);

        return response()->json([
            'id'       => $i->id,
            'name'     => $i->name,
            'phone'    => $i->localPhone(),
            'email'    => $i->email,
            'whatsapp' => $i->whatsappUrl(),
            'subject'  => $i->subject,
            'message'  => $i->message,
            'status'   => $i->status,
            'starred'  => $i->starred,
            'spam'     => $i->spam_score,
            'note'     => (string) $i->note,
            'trashed'  => $i->trashed(),
            'member'   => $i->user_id ? optional(\App\Models\User::find($i->user_id, ['id', 'name']))->name : null,
            'ip'       => auth()->user()?->isAdmin() ? $i->ip : null, // the sender's IP is for the admin only
            'date'     => $i->created_at?->format('d M Y, h:i A'),
            'ago'      => $i->created_at?->diffForHumans(),
            'replies'  => $i->replies->map(fn ($r) => [
                'channel' => $r->channel, 'body' => (string) $r->body, 'ok' => $r->ok, 'error' => $r->error,
                'by' => $r->admin_name, 'time' => optional($r->created_at)->format('d M, h:i A'),
            ]),
            'others'   => $others->map(fn ($o) => ['id' => $o->id, 'subject' => $o->subject, 'status' => $o->status, 'date' => $o->created_at?->format('d M Y')]),
            'counts'   => $this->counts(),
        ]);
    }

    public function status(Request $request, int $id): JsonResponse
    {
        $v = $request->validate(['status' => 'required|in:' . implode(',', array_keys(ContactInbox::STATUSES))]);
        $i = ContactInquiry::findOrFail($id);
        $i->forceFill(['status' => $v['status']] + ($v['status'] === 'replied' && !$i->replied_at ? ['replied_at' => now(), 'replied_by' => auth()->id()] : []))->save();
        $this->audit('inquiry_status', "বার্তা #{$i->id} → {$v['status']}");

        return $this->ok('“' . ContactInbox::STATUSES[$v['status']] . '” করা হয়েছে।');
    }

    public function star(int $id): JsonResponse
    {
        $i = ContactInquiry::findOrFail($id);
        $i->forceFill(['starred' => !$i->starred])->save();

        return $this->ok($i->starred ? 'তারকা দেওয়া হয়েছে।' : 'তারকা সরানো হয়েছে।', ['starred' => $i->starred]);
    }

    public function note(Request $request, int $id): JsonResponse
    {
        $v = $request->validate(['note' => 'nullable|string|max:2000']);
        $i = ContactInquiry::findOrFail($id);
        $i->forceFill(['note' => ContactInbox::clean($v['note'] ?? '', 2000) ?: null])->save();

        return $this->ok('নোট সংরক্ষিত।');
    }

    /** Answer by SMS or e-mail from the inbox; the answer is kept with the message. */
    public function reply(Request $request, int $id): JsonResponse
    {
        $v = $request->validate([
            'channel' => 'required|in:sms,email',
            'body'    => 'required|string|min:2|max:' . ($request->input('channel') === 'sms' ? 600 : 5000),
            'subject' => 'nullable|string|max:150',
        ]);
        $i = ContactInquiry::findOrFail($id);
        abort_if($i->status === 'spam', 422, 'স্প্যাম বার্তার উত্তর দেওয়া যায় না — আগে "ইনবক্সে ফেরত" দিন।');
        $body = ContactInbox::clean($v['body'], $v['channel'] === 'sms' ? 600 : 5000);

        if ($v['channel'] === 'sms') {
            $res = SmsService::send($i->phone, $body, ['source' => 'admin', 'purpose' => 'notification', 'campaign' => 'Inquiry #' . $i->id]);
            $ok = !empty($res['success']);
            $why = SmsService::diagnose($res['response_code'] ?? null, (string) ($res['message'] ?? $res['error'] ?? ''));
            $error = $ok ? null : ($why ? $why[1] : SmsService::scrub((string) ($res['message'] ?? $res['error'] ?? 'SMS যায়নি')));
        } else {
            if (!$i->email) {
                return $this->fail('এই বার্তায় ইমেইল ঠিকানা নেই — SMS-এ উত্তর দিন।');
            }
            $subject = ContactInbox::clean($v['subject'] ?? '', 150) ?: ('Re: ' . $i->subject);
            $res = EmailService::sendSingle(toEmail: $i->email, subject: $subject, body: $body, recipientName: $i->name);
            $ok = !empty($res['success']);
            $error = $ok ? null : mb_substr((string) ($res['message'] ?? 'ইমেইল যায়নি'), 0, 250);
        }

        ContactInquiryReply::create([
            'inquiry_id' => $i->id, 'channel' => $v['channel'], 'body' => $body, 'ok' => $ok, 'error' => $error,
            'admin_id' => auth()->id(), 'admin_name' => mb_substr((string) auth()->user()?->name, 0, 100),
        ]);
        if ($ok) {
            $i->forceFill(['status' => 'replied', 'replied_at' => now(), 'replied_by' => auth()->id()])->save();
        }
        $this->audit('inquiry_reply', "বার্তা #{$i->id}: {$v['channel']} উত্তর " . ($ok ? 'পাঠানো হয়েছে' : 'ব্যর্থ'));

        return $ok
            ? $this->ok(($v['channel'] === 'sms' ? 'SMS' : 'ইমেইল') . ' পাঠানো হয়েছে।')
            : $this->fail('পাঠানো যায়নি: ' . $error);
    }

    /** "I called" / "I answered on WhatsApp": keeps the history complete. */
    public function logContact(Request $request, int $id): JsonResponse
    {
        $v = $request->validate(['channel' => 'required|in:call,whatsapp', 'body' => 'nullable|string|max:500']);
        $i = ContactInquiry::findOrFail($id);
        ContactInquiryReply::create([
            'inquiry_id' => $i->id, 'channel' => $v['channel'], 'body' => ContactInbox::clean($v['body'] ?? '', 500) ?: null, 'ok' => true,
            'admin_id' => auth()->id(), 'admin_name' => mb_substr((string) auth()->user()?->name, 0, 100),
        ]);
        $i->forceFill(['status' => 'replied', 'replied_at' => $i->replied_at ?? now(), 'replied_by' => auth()->id()])->save();

        return $this->ok($v['channel'] === 'call' ? 'কল লিখে রাখা হয়েছে।' : 'WhatsApp উত্তর লিখে রাখা হয়েছে।');
    }

    public function destroy(int $id): JsonResponse
    {
        $i = ContactInquiry::findOrFail($id);
        $i->delete();
        $this->audit('inquiry_trash', "বার্তা #{$i->id} ট্র্যাশে");

        return $this->ok('ট্র্যাশে সরানো হয়েছে (৩০ দিন পর নিজে থেকে মুছবে)।');
    }

    public function restore(int $id): JsonResponse
    {
        $i = ContactInquiry::onlyTrashed()->findOrFail($id);
        $i->restore();
        $this->audit('inquiry_restore', "বার্তা #{$i->id} ফেরত");

        return $this->ok('ইনবক্সে ফেরত এসেছে।');
    }

    public function bulk(Request $request): JsonResponse
    {
        $v = $request->validate([
            'ids'    => 'required|array|min:1|max:200',
            'ids.*'  => 'integer|min:1',
            'action' => 'required|in:read,new,replied,spam,archived,inbox,trash,restore,purge',
        ]);
        $ids = array_values(array_unique($v['ids']));
        $a = $v['action'];
        if ($a === 'purge' && !auth()->user()?->isAdmin()) {
            return $this->fail('চিরতরে মুছতে পারেন শুধু অ্যাডমিন।', 403);
        }

        $n = DB::transaction(function () use ($ids, $a) {
            return match ($a) {
                'trash'   => ContactInquiry::whereIn('id', $ids)->get()->each->delete()->count(),
                'restore' => ContactInquiry::onlyTrashed()->whereIn('id', $ids)->get()->each->restore()->count(),
                'purge'   => (function () use ($ids) {
                    $rows = ContactInquiry::onlyTrashed()->whereIn('id', $ids)->pluck('id');
                    ContactInquiryReply::whereIn('inquiry_id', $rows)->delete();
                    return ContactInquiry::onlyTrashed()->whereIn('id', $rows)->forceDelete();
                })(),
                'inbox'   => ContactInquiry::whereIn('id', $ids)->update(['status' => 'read', 'updated_at' => now()]),
                default   => ContactInquiry::whereIn('id', $ids)->update(['status' => $a, 'updated_at' => now()]
                    + ($a === 'read' ? ['read_at' => now()] : []) + ($a === 'replied' ? ['replied_at' => now(), 'replied_by' => auth()->id()] : [])),
            };
        });
        Cache::forget('admin_sidebar_pending_badges');
        $this->audit('inquiry_bulk', "বার্তা ({$a}) — " . count($ids) . 'টি নির্বাচিত, ' . $n . 'টি বদলেছে');

        return $this->ok(\App\Support\Bn::digits($n) . 'টি বার্তা হালনাগাদ হয়েছে।');
    }

    /** New messages since the page was opened (the open page shows a toast and refreshes the list). */
    public function poll(Request $request): JsonResponse
    {
        $after = (int) $request->input('after', 0);

        return response()->json([
            'counts' => $this->counts(),
            'latest' => (int) ContactInquiry::max('id'),
            'fresh'  => $after > 0 ? ContactInquiry::where('id', '>', $after)->where('status', 'new')->count() : 0,
        ]);
    }

    /** Admin only: the export holds everyone's phone and e-mail. */
    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'এক্সপোর্ট শুধু অ্যাডমিনের জন্য।');
        $this->audit('inquiry_export', 'যোগাযোগ বার্তা CSV এক্সপোর্ট');

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Date', 'Name', 'Phone', 'Email', 'Subject', 'Message', 'Status', 'Replied at', 'Note']);
            ContactInquiry::orderBy('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, array_map([ContactInbox::class, 'csvSafe'], [
                        $r->id, $r->created_at?->format('Y-m-d H:i'), $r->name, $r->localPhone(), $r->email, $r->subject,
                        preg_replace('/\s+/u', ' ', $r->message), $r->status, $r->replied_at?->format('Y-m-d H:i'), $r->note,
                    ]));
                }
            });
            fclose($out);
        }, 'inquiries-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ─────────────────────────────────────────────────────────────────────────────────────────────────────

    private function counts(): array
    {
        if (!ContactInbox::ready()) {
            return array_fill_keys(self::FOLDERS, 0);
        }
        $r = DB::table('contact_inquiries')->selectRaw("
            COALESCE(SUM(CASE WHEN deleted_at IS NULL AND status IN ('new','read','replied') THEN 1 ELSE 0 END), 0) AS inbox,
            COALESCE(SUM(CASE WHEN deleted_at IS NULL AND status = 'new' THEN 1 ELSE 0 END), 0) AS fresh,
            COALESCE(SUM(CASE WHEN deleted_at IS NULL AND starred = 1 AND status != 'spam' THEN 1 ELSE 0 END), 0) AS starred,
            COALESCE(SUM(CASE WHEN deleted_at IS NULL AND status = 'replied' THEN 1 ELSE 0 END), 0) AS replied,
            COALESCE(SUM(CASE WHEN deleted_at IS NULL AND status = 'spam' THEN 1 ELSE 0 END), 0) AS spam,
            COALESCE(SUM(CASE WHEN deleted_at IS NULL AND status = 'archived' THEN 1 ELSE 0 END), 0) AS archived,
            COALESCE(SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END), 0) AS trash,
            COALESCE(SUM(CASE WHEN deleted_at IS NULL AND created_at >= ? THEN 1 ELSE 0 END), 0) AS week", [now()->subDays(7)])->first();

        return [
            'inbox' => (int) $r->inbox, 'new' => (int) $r->fresh, 'starred' => (int) $r->starred, 'replied' => (int) $r->replied,
            'spam' => (int) $r->spam, 'archived' => (int) $r->archived, 'trash' => (int) $r->trash, 'week' => (int) $r->week,
        ];
    }

    private function ok(string $message, array $extra = []): JsonResponse
    {
        return response()->json(['ok' => true, 'message' => $message] + $extra);
    }

    private function fail(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['ok' => false, 'message' => $message], $status);
    }

    private function audit(string $action, string $text): void
    {
        try {
            app(\App\Services\AdminAccessService::class)->log($action, $text);
        } catch (\Throwable) {
        }
    }
}
