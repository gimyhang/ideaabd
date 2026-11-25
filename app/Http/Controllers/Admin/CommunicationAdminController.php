<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Inquiries: the messages people send from the website's contact form (stored in communication_logs with
 * channel = inquiry by the /contact/submit route). Sending SMS / e-mail lives in one place only: admin/sms.
 *
 * status: delivered = new, read, replied.
 */
class CommunicationAdminController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->value();
        $search = $request->string('search')->trim()->value();
        $ready = Schema::hasTable('communication_logs');
        if ($ready) {
            self::ensureStatusColumn();
        }

        $base = fn () => CommunicationLog::query()->where('channel', 'inquiry');

        $inquiries = $ready
            ? $base()
                ->when(in_array($status, ['new', 'read', 'replied'], true), fn ($q) => $q->where('status', $status === 'new' ? 'delivered' : $status))
                ->when($search, function ($q, $term) {
                    $like = '%' . $term . '%';
                    $q->where(fn ($w) => $w->where('recipient', 'like', $like)->orWhere('subject', 'like', $like)->orWhere('error_message', 'like', $like));
                })
                ->latest('created_at')->latest('id')->paginate(20)->withQueryString()
            : null;

        $counts = ['all' => 0, 'new' => 0, 'read' => 0, 'replied' => 0, 'week' => 0];
        if ($ready) {
            $row = $base()->toBase()->selectRaw("COUNT(*) AS a,
                COALESCE(SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END), 0) AS n,
                COALESCE(SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END), 0) AS r,
                COALESCE(SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END), 0) AS p,
                COALESCE(SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END), 0) AS w", [now()->subDays(7)])->first();
            $counts = ['all' => (int) $row->a, 'new' => (int) $row->n, 'read' => (int) $row->r, 'replied' => (int) $row->p, 'week' => (int) $row->w];
        }

        return view('admin.communication.index', compact('inquiries', 'counts', 'status', 'search', 'ready'));
    }

    public function updateStatus(Request $request, CommunicationLog $log): RedirectResponse
    {
        abort_unless($log->channel === 'inquiry', 404);
        $request->validate(['status' => 'required|in:new,read,replied']);
        self::ensureStatusColumn();
        $log->update(['status' => $request->input('status') === 'new' ? 'delivered' : $request->input('status')]);

        return back()->with('success', 'বার্তার অবস্থা হালনাগাদ হয়েছে।');
    }

    public function destroy(CommunicationLog $log): RedirectResponse
    {
        abort_unless($log->channel === 'inquiry', 404);
        $log->delete();

        return back()->with('success', 'বার্তাটি মুছে ফেলা হয়েছে।');
    }

    /**
     * The table was made with status enum(sent, delivered, failed); an inbox also needs read / replied.
     * Done from a web request (the production command line talks to another database), once.
     */
    public static function ensureStatusColumn(): void
    {
        if (\Illuminate\Support\Facades\Cache::get('inq:status:v1')) {
            return;
        }
        try {
            $db = \Illuminate\Support\Facades\DB::connection();
            if (in_array($db->getDriverName(), ['mysql', 'mariadb'], true)) {
                $type = (string) $db->selectOne("SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'communication_logs' AND COLUMN_NAME = 'status'")?->t;
                if (str_starts_with(strtolower($type), 'enum')) {
                    $db->statement("ALTER TABLE `communication_logs` MODIFY `status` VARCHAR(20) NOT NULL DEFAULT 'sent'");
                }
            }
            \Illuminate\Support\Facades\Cache::forever('inq:status:v1', true);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Inquiries status column: ' . $e->getMessage());
        }
    }

    /** Split the stored row into name / phone / email / subject / message. */
    public static function parse(CommunicationLog $log): array
    {
        [$phone, $email] = array_pad(array_map('trim', explode(' / ', (string) $log->recipient, 2)), 2, null);
        $subject = (string) $log->subject;
        $name = '';
        if (($p = mb_strrpos($subject, ' — ')) !== false) {
            $name = trim(mb_substr($subject, $p + 3));
            $subject = trim(mb_substr($subject, 0, $p));
        }

        return [
            'name'    => $name ?: 'নাম নেই',
            'phone'   => $phone ?: null,
            'email'   => $email ?: null,
            'subject' => $subject ?: 'সাধারণ বার্তা',
            'message' => (string) $log->error_message,
            'status'  => $log->status === 'delivered' ? 'new' : $log->status,
        ];
    }
}
