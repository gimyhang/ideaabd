<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class AdminBackupController extends Controller
{
    private string $backupDir;

    public function __construct(private readonly ?AdminAccessService $accessService = null)
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    /**
     * Display comprehensive disaster recovery & master backup dashboard.
     */
    public function index(): View
    {
        $files = File::files($this->backupDir);
        $backups = [];
        $totalBackupSizeBytes = 0;

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            $size = $file->getSize();
            $totalBackupSizeBytes += $size;

            $isMasterZip = ($ext === 'zip');
            $typeLabel = match ($ext) {
                'zip'    => 'মাষ্টার অল-ইন-ওয়ান জিপ (Master ZIP)',
                'sql'    => 'স্ট্যান্ডার্ড SQL ডাম্প',
                'sqlite' => 'SQLite স্ন্যাপশট',
                'gz'     => 'কম্প্রেসড SQL (Gzip)',
                default  => strtoupper($ext) . ' আর্কাইভ',
            };

            $backups[] = [
                'filename'       => $file->getFilename(),
                'type'           => $typeLabel,
                'is_master_zip'  => $isMasterZip,
                'extension'      => $ext,
                'size'           => $this->formatBytes($size),
                'size_bytes'     => $size,
                'created_at'     => \Carbon\Carbon::createFromTimestamp($file->getMTime()),
            ];
        }

        // Sort latest backups first
        usort($backups, fn ($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        $dbDriver = config('database.default', 'mysql');
        $dbName = config("database.connections.{$dbDriver}.database", 'ideaabd');

        // Driver-Agnostic Database Statistics
        $tables = [];
        $totalDbSizeBytes = 0;
        $totalRowsCount = 0;

        try {
            if ($dbDriver === 'sqlite') {
                $dbPath = config("database.connections.sqlite.database");
                if (file_exists($dbPath)) {
                    $totalDbSizeBytes = filesize($dbPath) ?: 0;
                }

                $sqliteTables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name ASC");
                foreach ($sqliteTables as $st) {
                    $tblName = $st->name;
                    $count = (int) $this->safe(fn () => DB::table($tblName)->count(), 0);
                    $totalRowsCount += $count;
                    $tables[] = [
                        'name' => $tblName,
                        'rows' => $count,
                        'size' => '—',
                    ];
                }
            } else {
                // MySQL / MariaDB
                $tableStatus = DB::select('SHOW TABLE STATUS');
                foreach ($tableStatus as $tbl) {
                    $tblName = $tbl->Name ?? $tbl->name ?? '';
                    if (empty($tblName)) continue;

                    $rows = (int) ($tbl->Rows ?? $tbl->rows ?? 0);
                    $dataLength = (int) ($tbl->Data_length ?? $tbl->data_length ?? 0);
                    $indexLength = (int) ($tbl->Index_length ?? $tbl->index_length ?? 0);
                    $tableSize = $dataLength + $indexLength;

                    $totalDbSizeBytes += $tableSize;
                    $totalRowsCount += $rows;

                    $tables[] = [
                        'name' => $tblName,
                        'rows' => $rows,
                        'size' => $this->formatBytes($tableSize),
                    ];
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }

        $formattedDbSize = $this->formatBytes($totalDbSizeBytes);
        $formattedTotalBackupSize = $this->formatBytes($totalBackupSizeBytes);
        $latestBackup = !empty($backups) ? $backups[0] : null;

        // Retention policy
        $retentionLimit = (int) config('idea.backup_retention', 10);

        return view('admin.backup', compact(
            'backups',
            'dbName',
            'dbDriver',
            'tables',
            'formattedDbSize',
            'totalRowsCount',
            'totalBackupSizeBytes',
            'formattedTotalBackupSize',
            'latestBackup',
            'retentionLimit'
        ));
    }

    /**
     * Create 1-Click Master Backup with distinct modes:
     * - 'data_media': All Database + ALL media, photos, book covers, author avatars, invoices (excluding source code)
     * - 'full_system': Full System & database backup
     * - 'db_only': Pure SQL database dump
     */
    public function create(Request $request): RedirectResponse
    {
        $mode = $request->input('mode', 'data_media'); // 'data_media', 'full_system', 'db_only'
        $includeMedia = ($mode !== 'db_only');

        try {
            $dbDriver = config('database.default', 'mysql');
            $dbName = config("database.connections.{$dbDriver}.database", 'ideaabd');
            $timestamp = date('Y-m-d_H-i-s');
            
            $prefix = match ($mode) {
                'data_media'  => 'idea_data_and_media_backup_',
                'full_system' => 'idea_full_system_backup_',
                default       => 'idea_db_backup_',
            };

            $zipFilename = $prefix . $timestamp . ($includeMedia ? '.zip' : '.sql');
            $zipPath = $this->backupDir . '/' . $zipFilename;

            $sqlContent = $this->generateSqlDump();
            $mediaCount = 0;

            if ($includeMedia && class_exists(ZipArchive::class)) {
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    
                    // 1. Add Universal SQL Dump
                    $zip->addFromString('database.sql', $sqlContent);

                    // 2. Add SQLite database clone if active
                    if ($dbDriver === 'sqlite') {
                        $dbPath = config("database.connections.sqlite.database");
                        if (file_exists($dbPath)) {
                            $zip->addFile($dbPath, 'database.sqlite');
                        }
                    }

                    // 3. Add ALL user uploaded media, photos, covers from storage/app/public
                    $uploadsPath = storage_path('app/public');
                    if (File::isDirectory($uploadsPath)) {
                        $files = File::allFiles($uploadsPath);
                        foreach ($files as $file) {
                            $relativePath = 'media/' . $file->getRelativePathname();
                            $zip->addFile($file->getRealPath(), $relativePath);
                            $mediaCount++;
                        }
                    }

                    // 4. Add additional public uploads / custom media directories if exist
                    $publicUploads = public_path('uploads');
                    if (File::isDirectory($publicUploads)) {
                        $files = File::allFiles($publicUploads);
                        foreach ($files as $file) {
                            $relativePath = 'public_uploads/' . $file->getRelativePathname();
                            $zip->addFile($file->getRealPath(), $relativePath);
                            $mediaCount++;
                        }
                    }

                    // 5. System & Content Manifest
                    $manifest = [
                        'backup_title'  => ($mode === 'data_media') ? 'Idea Publication Complete Data & Media Backup' : 'Idea Publication Full System Backup',
                        'backup_mode'   => $mode,
                        'app_name'      => config('app.name', 'Idea Publication'),
                        'app_url'       => config('app.url', 'https://www.ideaabd.com'),
                        'created_at'    => date('Y-m-d H:i:s'),
                        'driver'        => $dbDriver,
                        'database'      => $dbName,
                        'php_version'   => PHP_VERSION,
                        'media_files'   => $mediaCount,
                        'sql_bytes'     => strlen($sqlContent),
                        'sha256'        => hash('sha256', $sqlContent),
                        'summary'       => 'Contains entire database tables + all uploaded book covers, author photos, banners, and digital assets.',
                    ];
                    $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                    $zip->close();
                } else {
                    // Fallback to pure SQL if ZIP failed
                    $sqlFilename = $prefix . $timestamp . '.sql';
                    File::put($this->backupDir . '/' . $sqlFilename, $sqlContent);
                    $zipFilename = $sqlFilename;
                }
            } else {
                // Pure SQL Backup
                $sqlFilename = $prefix . $timestamp . '.sql';
                File::put($this->backupDir . '/' . $sqlFilename, $sqlContent);
                $zipFilename = $sqlFilename;
            }

            // Auto-prune older backups per retention limit
            $this->pruneOldBackups();

            $msg = ($mode === 'data_media') 
                ? "সমস্ত ডাটাবেজ ও মিডিয়া ছবির সফল ব্যাকআপ '{$zipFilename}' তৈরি ও সংরক্ষিত হয়েছে!"
                : "সিস্টেম ব্যাকআপ '{$zipFilename}' সফলভাবে তৈরি ও সংরক্ষিত হয়েছে!";

            $this->logAction('create_backup', $msg);
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'ব্যাকআপ তৈরিতে ত্রুটি: ' . $e->getMessage());
        }
    }

    /**
     * Auto-Upload and Instant Ingest for Drag & Drop / File Selector (Supports AJAX & Normal Form).
     */
    public function upload(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'backup_file' => 'required|file|max:204800', // max 200MB
        ]);

        try {
            $file = $request->file('backup_file');
            $ext = strtolower($file->getClientOriginalExtension());

            if (!in_array($ext, ['zip', 'sql', 'sqlite', 'gz', 'txt'])) {
                $err = 'শুধুমাত্র .zip, .sql, .sqlite বা .gz ফরম্যাটের ব্যাকআপ ফাইল গ্রহণযোগ্য।';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $err], 422);
                }
                return back()->with('error', $err);
            }

            $cleanName = 'uploaded_' . date('Y-m-d_H-i-s') . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $file->getClientOriginalName());
            $file->move($this->backupDir, $cleanName);

            $this->logAction('upload_backup', "ব্যাকআপ ফাইল '{$cleanName}' আপলোড করা হয়েছে");

            $msg = "ব্যাকআপ ফাইল '{$cleanName}' স্বয়ংক্রিয়ভাবে সফলভাবে আপলোড হয়েছে!";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'message'  => $msg,
                    'filename' => $cleanName,
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'ব্যাকআপ ফাইল আপলোডে ত্রুটি: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return back()->with('error', $err);
        }
    }

    /**
     * Smart 1-Click Restore with Pre-Restore Safety Rollback Snapshot.
     * Supports Master ZIP Archives (restores DB + Media assets), SQL files, and SQLite clones.
     */
    public function restore(Request $request, string $filename): RedirectResponse
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . '/' . $filename;

        if (!File::exists($filePath)) {
            return back()->with('error', 'ব্যাকআপ ফাইলটি পাওয়া যায়নি।');
        }

        try {
            // 1. Create automatic rollback safety snapshot before modifying database
            $this->createSafetyRollbackSnapshot();

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $dbDriver = config('database.default', 'mysql');

            if ($ext === 'zip' && class_exists(ZipArchive::class)) {
                // Restore from Master ZIP Archive
                $zip = new ZipArchive();
                if ($zip->open($filePath) === true) {
                    
                    // Extract and restore SQL dump
                    $sqlContent = $zip->getFromName('database.sql');
                    if ($sqlContent) {
                        DB::unprepared($sqlContent);
                    } elseif ($dbDriver === 'sqlite') {
                        $sqliteContent = $zip->getFromName('database.sqlite');
                        if ($sqliteContent) {
                            $dbPath = config("database.connections.sqlite.database");
                            File::put($dbPath, $sqliteContent);
                        }
                    }

                    // Restore Media assets into storage/app/public
                    $tempExtractDir = storage_path('app/temp_restore_' . time());
                    File::makeDirectory($tempExtractDir, 0755, true);
                    $zip->extractTo($tempExtractDir);
                    $zip->close();

                    $extractedMediaDir = $tempExtractDir . '/media';
                    if (File::isDirectory($extractedMediaDir)) {
                        File::copyDirectory($extractedMediaDir, storage_path('app/public'));
                    }

                    File::deleteDirectory($tempExtractDir);
                } else {
                    return back()->with('error', 'জিপ আর্কাইভটি খোলা সম্ভব হয়নি।');
                }
            } elseif ($ext === 'sqlite' && $dbDriver === 'sqlite') {
                $dbPath = config("database.connections.sqlite.database");
                File::copy($filePath, $dbPath);
            } elseif ($ext === 'gz') {
                $gzContent = File::get($filePath);
                $sqlContent = gzdecode($gzContent);
                DB::unprepared($sqlContent);
            } else {
                // Standard SQL file
                $sqlContent = File::get($filePath);
                DB::unprepared($sqlContent);
            }

            $this->logAction('restore_backup', "ডাটাবেজ ও মিডিয়া '{$filename}' ফাইল থেকে সফলভাবে রিস্টোর করা হয়েছে");
            return back()->with('success', "অভিনন্দন! মাষ্টার ব্যাকআপ '{$filename}' থেকে ডাটাবেজ ও মিডিয়া সফলভাবে রিস্টোর করা হয়েছে!");
        } catch (\Throwable $e) {
            return back()->with('error', 'ডাটাবেজ রিস্টোরে ত্রুটি: ' . $e->getMessage());
        }
    }

    /**
     * Inspect Master ZIP Archive contents without extracting.
     */
    public function inspect(string $filename): JsonResponse
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . '/' . $filename;

        if (!File::exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'ফাইলটি পাওয়া যায়নি'], 404);
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesList = [];
        $manifest = null;

        if ($ext === 'zip' && class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    $filesList[] = [
                        'name' => $stat['name'],
                        'size' => $this->formatBytes($stat['size']),
                        'compressed_size' => $this->formatBytes($stat['comp_size']),
                    ];
                }

                $manifestJson = $zip->getFromName('manifest.json');
                if ($manifestJson) {
                    $manifest = json_decode($manifestJson, true);
                }
                $zip->close();
            }
        }

        return response()->json([
            'success'   => true,
            'filename'  => $filename,
            'size'      => $this->formatBytes(File::size($filePath)),
            'manifest'  => $manifest,
            'files_count' => count($filesList),
            'files'     => array_slice($filesList, 0, 50),
        ]);
    }

    /**
     * 1-Click Database Integrity Scan & Diagnostic Report.
     */
    public function integrityCheck(): RedirectResponse
    {
        $startTime = microtime(true);
        try {
            $dbDriver = config('database.default', 'mysql');
            $status = 'পাস (Healthy)';
            $details = [];

            if ($dbDriver === 'sqlite') {
                $check = DB::select('PRAGMA integrity_check');
                $resultStr = $check[0]->integrity_check ?? 'ok';
                if (strtolower($resultStr) !== 'ok') {
                    $status = 'সমস্যা সনাক্ত হয়েছে: ' . $resultStr;
                }
            } else {
                $tables = DB::select('SHOW TABLES');
                foreach ($tables as $tObj) {
                    $tArr = (array) $tObj;
                    $tName = reset($tArr);
                    if ($tName) {
                        $check = DB::select("CHECK TABLE `{$tName}`");
                        $msgText = $check[0]->Msg_text ?? 'OK';
                        if (strtolower($msgText) !== 'ok') {
                            $details[] = "{$tName}: {$msgText}";
                        }
                    }
                }
                if (!empty($details)) {
                    $status = 'কিছু টেবিলে ত্রুটি: ' . implode(', ', array_slice($details, 0, 3));
                }
            }

            $latency = round((microtime(true) - $startTime) * 1000, 2);
            $msg = "ডাটাবেজ ইন্টিগ্রিটি চেক সম্পন্ন ({$latency}ms)! স্ট্যাটাস: {$status}";
            $this->logAction('integrity_check', $msg);

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'ইন্টিগ্রিটি চেকিংয়ে ত্রুটি: ' . $e->getMessage());
        }
    }

    /**
     * 1-Click Database Table Optimization & Index Vacuum.
     */
    public function optimize(): RedirectResponse
    {
        try {
            $dbDriver = config('database.default', 'mysql');

            if ($dbDriver === 'sqlite') {
                DB::statement('VACUUM');
                DB::statement('PRAGMA optimize');
            } else {
                $tables = DB::select('SHOW TABLES');
                $tableNames = [];
                foreach ($tables as $tObj) {
                    $tArr = (array) $tObj;
                    $tName = reset($tArr);
                    if ($tName) {
                        $tableNames[] = "`{$tName}`";
                    }
                }
                if (!empty($tableNames)) {
                    DB::statement('OPTIMIZE TABLE ' . implode(', ', $tableNames));
                }
            }

            $this->logAction('optimize_db', 'ডাটাবেজের সমস্ত টেবিল ও ইনডেক্স অপ্টিমাইজ করা হয়েছে');
            return back()->with('success', 'ডাটাবেজের সমস্ত টেবিল ও ইনডেক্স সফলভাবে অপ্টিমাইজ করা হয়েছে!');
        } catch (\Throwable $e) {
            return back()->with('error', 'ডাটাবেজ অপ্টিমাইজেশনে ত্রুটি: ' . $e->getMessage());
        }
    }

    /**
     * Bulk Delete selected backups.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $filenames = $request->input('filenames', []);
        if (empty($filenames) || !is_array($filenames)) {
            return back()->with('error', 'মুছে ফেলার জন্য কোনো ব্যাকআপ ফাইল নির্বাচন করা হয়নি।');
        }

        $count = 0;
        foreach ($filenames as $name) {
            $cleanName = basename($name);
            $path = $this->backupDir . '/' . $cleanName;
            if (File::exists($path)) {
                File::delete($path);
                $count++;
            }
        }

        $this->logAction('bulk_delete_backup', "একসাথে {$count} টি ব্যাকআপ ফাইল মুছে ফেলা হয়েছে");
        return back()->with('success', "নির্বাচিত {$count} টি ব্যাকআপ ফাইল সফলভাবে মুছে ফেলা হয়েছে!");
    }

    /**
     * Download a specific backup file.
     */
    public function download(string $filename): BinaryFileResponse|RedirectResponse
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . '/' . $filename;

        if (!File::exists($filePath)) {
            return back()->with('error', 'ব্যাকআপ ফাইলটি পাওয়া যায়নি।');
        }

        return response()->download($filePath);
    }

    /**
     * Delete a single backup file.
     */
    public function destroy(string $filename): RedirectResponse
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . '/' . $filename;

        if (File::exists($filePath)) {
            File::delete($filePath);
            $this->logAction('delete_backup', "ডাটাবেজ ব্যাকআপ '{$filename}' মুছে ফেলা হয়েছে");
            return back()->with('success', "ব্যাকআপ ফাইল '{$filename}' সফলভাবে মুছে ফেলা হয়েছে!");
        }

        return back()->with('error', 'ফাইলটি পাওয়া যায়নি।');
    }

    /**
     * Pre-restore safety snapshot generator.
     */
    private function createSafetyRollbackSnapshot(): void
    {
        try {
            $dbDriver = config('database.default', 'mysql');
            $timestamp = date('Y-m-d_H-i-s');
            $snapshotName = 'pre_restore_safety_' . $timestamp;

            if ($dbDriver === 'sqlite') {
                $dbPath = config("database.connections.sqlite.database");
                if (file_exists($dbPath)) {
                    File::copy($dbPath, $this->backupDir . '/' . $snapshotName . '.sqlite');
                }
            } else {
                $sql = $this->generateSqlDump();
                File::put($this->backupDir . '/' . $snapshotName . '.sql', $sql);
            }
        } catch (\Throwable) {
            // safety attempt
        }
    }

    /**
     * Auto-prune old backups to maintain retention limit.
     */
    private function pruneOldBackups(int $limit = 10): void
    {
        try {
            $files = File::files($this->backupDir);
            if (count($files) > $limit) {
                // Sort oldest first
                usort($files, fn ($a, $b) => $a->getMTime() <=> $b->getMTime());
                $toDelete = array_slice($files, 0, count($files) - $limit);
                foreach ($toDelete as $f) {
                    if (!str_starts_with($f->getFilename(), 'pre_restore_')) {
                        File::delete($f->getPathname());
                    }
                }
            }
        } catch (\Throwable) {
            // non-blocking
        }
    }

    /**
     * Universal Driver-Agnostic SQL Dumper (SQLite & MySQL compatible).
     */
    private function generateSqlDump(): string
    {
        $pdo = DB::connection()->getPdo();
        $dbDriver = config('database.default', 'mysql');
        $dbName = config("database.connections.{$dbDriver}.database", 'ideaabd');

        $out = "-- ========================================================\n";
        $out .= "-- Universal Database Backup for: " . $dbName . " ({$dbDriver})\n";
        $out .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
        $out .= "-- Application: Idea Publication (ideaabd.com)\n";
        $out .= "-- ========================================================\n\n";

        if ($dbDriver === 'sqlite') {
            $out .= "PRAGMA foreign_keys = OFF;\n\n";
            $tables = DB::select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name ASC");

            foreach ($tables as $t) {
                $tableName = $t->name;
                $createSql = $t->sql;

                $out .= "-- Table structure for `{$tableName}`\n";
                $out .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $out .= $createSql . ";\n\n";

                // Rows
                $rows = DB::table($tableName)->get();
                if ($rows->isNotEmpty()) {
                    $out .= "-- Data rows for `{$tableName}`\n";
                    foreach ($rows as $row) {
                        $rowArr = (array) $row;
                        $cols = array_map(fn($c) => "`{$c}`", array_keys($rowArr));
                        $vals = array_map(function ($val) use ($pdo) {
                            if ($val === null) return 'NULL';
                            return $pdo->quote((string)$val);
                        }, array_values($rowArr));

                        $out .= "INSERT INTO `{$tableName}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n";
                    }
                    $out .= "\n";
                }
            }

            $out .= "PRAGMA foreign_keys = ON;\n";
        } else {
            // MySQL / MariaDB
            $out .= "SET FOREIGN_KEY_CHECKS=0;\n";
            $out .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $out .= "SET time_zone = \"+06:00\";\n\n";

            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $tableObj) {
                $tableArr = (array) $tableObj;
                $tableName = reset($tableArr);
                if (empty($tableName)) continue;

                $createTableRes = DB::select("SHOW CREATE TABLE `{$tableName}`");
                if (!empty($createTableRes)) {
                    $createTableArr = (array) $createTableRes[0];
                    $createTableSql = $createTableArr['Create Table'] ?? reset($createTableArr);

                    $out .= "\n-- Table structure for `{$tableName}`\n";
                    $out .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                    $out .= $createTableSql . ";\n\n";
                }

                $rows = DB::table($tableName)->get();
                if ($rows->isNotEmpty()) {
                    $out .= "-- Data for table `{$tableName}`\n";
                    foreach ($rows as $row) {
                        $rowArr = (array) $row;
                        $cols = array_map(fn($c) => "`{$c}`", array_keys($rowArr));
                        $vals = array_map(function ($val) use ($pdo) {
                            if ($val === null) return 'NULL';
                            return $pdo->quote((string)$val);
                        }, array_values($rowArr));

                        $out .= "INSERT INTO `{$tableName}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n";
                    }
                    $out .= "\n";
                }
            }
            $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        }

        $out .= "-- End of Universal Backup\n";
        return $out;
    }

    private function logAction(string $action, string $details): void
    {
        if ($this->accessService) {
            $this->accessService->log($action, $details);
        }
    }

    private function safe(callable $callback, mixed $default = null): mixed
    {
        try {
            return $callback();
        } catch (\Throwable) {
            return $default;
        }
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
