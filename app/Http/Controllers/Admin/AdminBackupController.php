<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAccessService;
use App\Services\Backup\BackupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class AdminBackupController extends Controller
{
    private const TOKEN_RULE = '/^\d{5,}:[A-Za-z0-9_-]{20,}$/';
    private const CHAT_RULE  = '/^(-?\d{4,}|@[A-Za-z0-9_]{4,})$/';

    public function __construct(
        private readonly BackupService $backups,
        private readonly ?AdminAccessService $accessService = null,
    ) {}

    public function index(): View
    {
        $files    = $this->backups->files();
        $settings = $this->backups->settings();
        $db       = $this->backups->dbStats();
        $space    = $this->backups->space($files);

        $phpLimit = min(
            $this->iniBytes((string) ini_get('upload_max_filesize')) ?: PHP_INT_MAX,
            $this->iniBytes((string) ini_get('post_max_size')) ?: PHP_INT_MAX,
            (int) config('backup.upload_max_mb', 200) * 1048576,
        );

        return view('admin.backup', [
            'backups'      => $files,
            'counts'       => $this->backups->categoryCounts($files),
            'db'           => $db,
            'space'        => $space,
            'health'       => $this->backups->health($files, $settings, $space),
            'timeline'     => $this->backups->timeline($files),
            'settings'     => ['telegram_bot_token' => ''] + $settings,
            'hasToken'     => $settings['telegram_bot_token'] !== '',
            'next'         => $this->backups->nextRun($settings),
            'last'         => $this->backups->lastRun(),
            'disks'        => $this->backups->offsiteDisks(),
            'channels'     => $this->backups->channels($settings),
            'schedulerOk'  => $this->backups->schedulerAlive(),
            'uploadMaxMb'  => (int) floor($phpLimit / 1048576),
            'maxRetention' => (int) config('backup.max_retention', 30),
            'mailMaxMb'    => (int) config('backup.mail_attach_max_mb', 20),
        ]);
    }

    /** Make a backup now. mode: db_only (.sql.gz) or data_media (.zip with uploaded files). */
    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $mode = $request->validate(['mode' => 'nullable|in:db_only,data_media,full_system'])['mode'] ?? 'db_only';

        @set_time_limit(0);
        $result = $this->backups->run($mode, 'manual');

        if (!$result['ok']) {
            return $this->fail($request, 'ব্যাকআপ ব্যর্থ: ' . $result['message'], 500);
        }

        $msg = "ব্যাকআপ তৈরি হয়েছে: {$result['filename']} ({$result['size']})" . ($result['offsite'] ? ' · ' . $result['offsite'] : '');
        $this->logAction('create_backup', $msg);

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => $msg] + $result)
            : back()->with('success', $msg);
    }

    public function upload(Request $request): JsonResponse|RedirectResponse
    {
        $max = (int) config('backup.upload_max_mb', 200) * 1024;
        $request->validate(['backup_file' => "required|file|max:{$max}"]);

        $file = $request->file('backup_file');
        $ext  = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, BackupService::EXTENSIONS, true)) {
            return $this->fail($request, 'শুধু .zip, .sql, .gz বা .sqlite ফাইল দেওয়া যাবে।', 422);
        }

        $name = 'uploaded_' . date('Y-m-d_H-i-s') . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
        $file->move($this->backups->dir(), $name);
        $path = $this->backups->dir() . '/' . $name;

        // a file that is not a backup is refused now, not when someone tries to restore it
        if (filesize($path) <= 64 * 1048576) {
            $check = $this->backups->verify($path);
            if (!$check['passed']) {
                File::delete($path);
                $bad = collect($check['checks'])->firstWhere('ok', false);

                return $this->fail($request, 'ফাইলটি বৈধ ব্যাকআপ নয়: ' . ($bad['detail'] ?: $bad['label']), 422);
            }
        }

        $this->logAction('upload_backup', "ব্যাকআপ আপলোড: {$name}");

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => "আপলোড হয়েছে: {$name}", 'filename' => $name])
            : back()->with('success', "আপলোড হয়েছে: {$name}");
    }

    public function restore(Request $request, string $filename): RedirectResponse
    {
        $path = $this->backups->path($filename);
        if (!$path) {
            return back()->with('error', 'ব্যাকআপ ফাইল পাওয়া যায়নি।');
        }

        @set_time_limit(0);
        try {
            $r = $this->backups->restore($path);
            $this->logAction('restore_backup', "রিস্টোর: {$filename}");

            return back()->with('success', "রিস্টোর সম্পন্ন: {$filename}" . ($r['media'] ? " · {$r['media']}টি মিডিয়া ফাইল" : '') . '। আগের অবস্থার সেফটি কপি রাখা হয়েছে।');
        } catch (\Throwable $e) {
            return back()->with('error', 'রিস্টোর ব্যর্থ: ' . $e->getMessage());
        }
    }

    public function inspect(string $filename): JsonResponse
    {
        $path = $this->backups->path($filename);
        if (!$path) {
            return response()->json(['success' => false, 'message' => 'ফাইল পাওয়া যায়নি।'], 404);
        }

        $list = [];
        $zip  = new ZipArchive();
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'zip' && $zip->open($path) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $st = $zip->statIndex($i);
                $list[] = ['name' => $st['name'], 'size' => $this->backups->formatBytes($st['size'])];
            }
            $zip->close();
        }

        return response()->json([
            'success'     => true,
            'size'        => $this->backups->formatBytes((int) filesize($path)),
            'manifest'    => $this->backups->manifest($path),
            'files_count' => count($list),
            'files'       => array_slice($list, 0, 100),
        ]);
    }

    public function diff(string $filename): JsonResponse
    {
        $path = $this->backups->path($filename);
        if (!$path) {
            return response()->json(['success' => false, 'message' => 'ফাইল পাওয়া যায়নি।'], 404);
        }

        try {
            return response()->json(['success' => true] + $this->backups->diff($path));
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** Checks a backup without running anything against the live database. */
    public function dryRun(string $filename): JsonResponse
    {
        $path = $this->backups->path($filename);
        if (!$path) {
            return response()->json(['success' => false, 'message' => 'ফাইল পাওয়া যায়নি।'], 404);
        }

        $started = microtime(true);
        $result  = $this->backups->verify($path);
        $this->logAction('backup_verified', "যাচাই: {$filename} — " . ($result['passed'] ? 'ঠিক আছে' : 'সমস্যা পাওয়া গেছে'));

        return response()->json([
            'success' => $result['passed'],
            'status'  => $result['passed'] ? 'passed' : 'failed',
            'ms'      => round((microtime(true) - $started) * 1000),
        ] + $result, $result['passed'] ? 200 : 422);
    }

    public function selectiveRestore(Request $request, string $filename): JsonResponse|RedirectResponse
    {
        $path = $this->backups->path($filename);
        if (!$path) {
            return $this->fail($request, 'ব্যাকআপ ফাইল পাওয়া যায়নি।', 404);
        }
        $data = $request->validate(['tables' => 'required|array|min:1|max:500', 'tables.*' => ['string', 'regex:/^[A-Za-z0-9_]+$/']]);

        try {
            $done = $this->backups->restoreTables($path, $data['tables']);
            $msg  = count($done) . 'টি টেবিল রিস্টোর হয়েছে: ' . implode(', ', array_slice($done, 0, 4)) . (count($done) > 4 ? '…' : '');
            $this->logAction('selective_restore', $msg);

            return $request->wantsJson() ? response()->json(['success' => true, 'message' => $msg, 'restored_tables' => $done]) : back()->with('success', $msg);
        } catch (\Throwable $e) {
            return $this->fail($request, 'রিস্টোর ব্যর্থ: ' . $e->getMessage(), 422);
        }
    }

    public function exportAnonymized(Request $request): JsonResponse|RedirectResponse
    {
        try {
            @set_time_limit(0);
            $name = $this->backups->maskedDump();
            $this->backups->prune();
            $this->logAction('export_anonymized_dump', "মাস্কড ডাম্প: {$name}");

            return $request->wantsJson()
                ? response()->json(['success' => true, 'message' => "মাস্কড ডাম্প তৈরি হয়েছে: {$name}", 'filename' => $name, 'download_url' => route('admin.backup.download', $name)])
                : response()->download($this->backups->dir() . '/' . $name);
        } catch (\Throwable $e) {
            return $this->fail($request, 'ডাম্প ব্যর্থ: ' . $e->getMessage(), 500);
        }
    }

    public function testNotification(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'               => 'nullable|in:telegram,email',
            'telegram_bot_token' => ['nullable', 'string', 'regex:' . self::TOKEN_RULE],
            'telegram_chat_id'   => ['nullable', 'string', 'regex:' . self::CHAT_RULE],
            'email'              => 'nullable|email',
        ]);

        if (($data['type'] ?? 'telegram') === 'email') {
            $email = $data['email'] ?? $this->backups->settings()['backup_email'];
            if (!$email) {
                return response()->json(['success' => false, 'message' => 'ইমেইল ঠিকানা দিন।'], 422);
            }
            try {
                Mail::raw('ব্যাকআপ নোটিফিকেশন টেস্ট — ' . date('d M Y, h:i A'), fn ($m) => $m->to($email)->subject('ব্যাকআপ টেস্ট'));

                return response()->json(['success' => true, 'message' => "{$email}-এ পাঠানো হয়েছে।"]);
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'message' => 'ইমেইল পাঠানো যায়নি: ' . $e->getMessage()], 500);
            }
        }

        $res = $this->backups->telegram('✅ ব্যাকআপ অ্যালার্ট টেস্ট — ' . date('d M Y, h:i A'), $data['telegram_bot_token'] ?? null, $data['telegram_chat_id'] ?? null);

        return response()->json(['success' => $res['ok'], 'message' => $res['message']], $res['ok'] ? 200 : 422);
    }

    public function integrityCheck(): RedirectResponse
    {
        $started = microtime(true);
        try {
            $bad = [];
            if (config('database.default') === 'sqlite') {
                $res = DB::select('PRAGMA integrity_check')[0]->integrity_check ?? 'ok';
                if (strtolower((string) $res) !== 'ok') {
                    $bad[] = (string) $res;
                }
            } else {
                foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $row) {
                    $cells = (array) $row;
                    $t     = (string) reset($cells);
                    $msg   = ((array) DB::select("CHECK TABLE `{$t}`")[0])['Msg_text'] ?? 'OK';
                    if (strtolower((string) $msg) !== 'ok') {
                        $bad[] = "{$t}: {$msg}";
                    }
                }
            }
            $ms  = round((microtime(true) - $started) * 1000);
            $msg = $bad ? 'সমস্যা পাওয়া গেছে — ' . implode(', ', array_slice($bad, 0, 3)) : "ডাটাবেজ ঠিক আছে ({$ms} ms)";
            $this->logAction('integrity_check', $msg);

            return $bad ? back()->with('error', $msg) : back()->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'পরীক্ষা ব্যর্থ: ' . $e->getMessage());
        }
    }

    public function optimize(): RedirectResponse
    {
        try {
            if (config('database.default') === 'sqlite') {
                DB::statement('VACUUM');
                DB::statement('PRAGMA optimize');
            } else {
                $names = [];
                foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $row) {
                    $cells   = (array) $row;
                    $names[] = '`' . reset($cells) . '`';
                }
                if ($names) {
                    DB::statement('OPTIMIZE TABLE ' . implode(', ', $names));
                }
            }
            $this->logAction('optimize_db', 'ডাটাবেজ অপটিমাইজ করা হয়েছে');

            return back()->with('success', 'ডাটাবেজ অপটিমাইজ হয়েছে।');
        } catch (\Throwable $e) {
            return back()->with('error', 'অপটিমাইজ ব্যর্থ: ' . $e->getMessage());
        }
    }

    public function download(string $filename): BinaryFileResponse|RedirectResponse
    {
        $path = $this->backups->path($filename);

        return $path ? response()->download($path) : back()->with('error', 'ব্যাকআপ ফাইল পাওয়া যায়নি।');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $disks = $this->backups->offsiteDisks();
        $data  = $request->validate([
            'auto_backup_enabled'     => 'nullable|boolean',
            'backup_frequency'        => 'required|in:daily,weekly,monthly',
            'backup_time'             => 'required|date_format:H:i',
            'backup_mode'             => 'required|in:db_only,data_media',
            'retention_days'          => 'required|integer|min:1|max:' . (int) config('backup.max_retention', 30),
            'backup_email'            => 'nullable|email',
            'telegram_alerts_enabled' => 'nullable|boolean',
            'telegram_bot_token'      => ['nullable', 'string', 'regex:' . self::TOKEN_RULE],
            'telegram_chat_id'        => ['nullable', 'string', 'regex:' . self::CHAT_RULE],
            'offsite_disk'            => ['required', Rule::in(array_merge(['none'], $disks))],
            'offsite_path'            => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\-\/.]*$/', 'not_regex:/\.\./'],
        ]);

        $data['telegram_alerts_enabled'] = $request->boolean('telegram_alerts_enabled');
        $data['auto_backup_enabled']     = $request->boolean('auto_backup_enabled');
        $data['backup_email']            = (string) ($data['backup_email'] ?? '');
        $data['telegram_chat_id']        = (string) ($data['telegram_chat_id'] ?? '');
        $data['offsite_path']            = (string) ($data['offsite_path'] ?? 'backups');

        $this->backups->saveSettings($data);
        $this->backups->prune();
        $this->logAction('backup_settings_updated', 'ব্যাকআপ সেটিংস আপডেট');

        return back()->with('success', 'সেটিংস সংরক্ষিত হয়েছে।');
    }

    public function sendEmail(Request $request, string $filename): JsonResponse|RedirectResponse
    {
        $path = $this->backups->path($filename);
        if (!$path) {
            return $this->fail($request, 'ব্যাকআপ ফাইল পাওয়া যায়নি।', 404);
        }
        $to  = $request->validate(['email' => 'required|email'])['email'];
        $max = (int) config('backup.mail_attach_max_mb', 20);

        if (filesize($path) > $max * 1048576) {
            return $this->fail($request, "ফাইলটি {$max} MB-এর বেশি, ইমেইলে পাঠানো যাবে না। ডাউনলোড করুন।", 422);
        }

        try {
            Mail::raw("ব্যাকআপ ফাইল সংযুক্ত: {$filename}", fn ($m) => $m->to($to)->subject("ব্যাকআপ — {$filename}")->attach($path));
            $this->logAction('backup_emailed', "{$filename} → {$to}");
            $msg = "{$to}-এ পাঠানো হয়েছে।";

            return $request->wantsJson() ? response()->json(['success' => true, 'message' => $msg]) : back()->with('success', $msg);
        } catch (\Throwable $e) {
            return $this->fail($request, 'ইমেইল পাঠানো যায়নি: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Request $request, string $filename): JsonResponse|RedirectResponse
    {
        $path = $this->backups->path($filename);
        if (!$path) {
            return $this->fail($request, 'ব্যাকআপ ফাইল পাওয়া যায়নি।', 404);
        }

        File::delete($path);
        $this->logAction('backup_deleted', "মুছে ফেলা: {$filename}");
        $msg = 'ব্যাকআপ মুছে ফেলা হয়েছে।';

        return $request->wantsJson() ? response()->json(['success' => true, 'message' => $msg]) : redirect()->route('admin.backup.index')->with('success', $msg);
    }

    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $names = $request->validate(['filenames' => 'required|array|min:1|max:200', 'filenames.*' => 'string|max:255'])['filenames'];

        $deleted = 0;
        foreach ($names as $name) {
            if ($path = $this->backups->path($name)) {
                $deleted += (int) File::delete($path);
            }
        }
        $this->logAction('backup_bulk_deleted', "{$deleted}টি ব্যাকআপ মুছে ফেলা হয়েছে");
        $msg = $deleted ? "{$deleted}টি ব্যাকআপ মুছে ফেলা হয়েছে।" : 'মোছার মতো কোনো ফাইল পাওয়া যায়নি।';

        return $request->wantsJson() ? response()->json(['success' => true, 'message' => $msg, 'deleted_count' => $deleted]) : redirect()->route('admin.backup.index')->with('success', $msg);
    }

    /** Delete backups beyond the retention in the settings, and enforce the storage share. */
    public function cleanupStorage(Request $request): JsonResponse|RedirectResponse
    {
        $n   = $this->backups->prune();
        $msg = $n ? "{$n}টি পুরনো ফাইল মুছে ফেলা হয়েছে।" : 'মোছার মতো পুরনো ফাইল নেই।';
        $this->logAction('backup_cleanup_storage', $msg);

        return $request->wantsJson() ? response()->json(['success' => true, 'message' => $msg]) : redirect()->route('admin.backup.index')->with('success', $msg);
    }

    private function fail(Request $request, string $message, int $status): JsonResponse|RedirectResponse
    {
        return $request->wantsJson() ? response()->json(['success' => false, 'message' => $message], $status) : back()->with('error', $message);
    }

    private function logAction(string $action, string $details): void
    {
        $this->accessService?->log($action, $details);
    }

    private function iniBytes(string $v): int
    {
        $v = trim($v);
        if ($v === '' || $v === '-1') {
            return 0;
        }
        $n = (int) $v;

        return match (strtolower(substr($v, -1))) {
            'g' => $n * 1073741824,
            'm' => $n * 1048576,
            'k' => $n * 1024,
            default => $n,
        };
    }
}
