<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAccessService;
use Illuminate\Contracts\View\View;
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
     * Display database backup manager, health metrics, and list of backups.
     */
    public function index(): View
    {
        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            $typeLabel = match ($ext) {
                'sql'    => 'SQL Dump',
                'sqlite' => 'SQLite Snapshot',
                'gz'     => 'Compressed Gzip',
                'zip'    => 'Full Media & DB Zip',
                default  => strtoupper($ext) . ' Backup',
            };

            $backups[] = [
                'filename'   => $file->getFilename(),
                'type'       => $typeLabel,
                'extension'  => $ext,
                'size'       => $this->formatBytes($file->getSize()),
                'size_bytes' => $file->getSize(),
                'created_at' => \Carbon\Carbon::createFromTimestamp($file->getMTime()),
            ];
        }

        // Sort latest backups first
        usort($backups, fn ($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        $dbDriver = config('database.default', 'mysql');
        $dbName = config("database.connections.{$dbDriver}.database", 'ideaabd');

        // Universal Driver-Agnostic Database Statistics
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

        return view('admin.backup', compact(
            'backups',
            'dbName',
            'dbDriver',
            'tables',
            'formattedDbSize',
            'totalRowsCount'
        ));
    }

    /**
     * Create a fresh database / full backup archive.
     */
    public function create(Request $request): RedirectResponse
    {
        $backupType = $request->input('backup_type', 'sql'); // sql, sqlite, zip, gz

        try {
            $dbDriver = config('database.default', 'mysql');

            // 1. Direct SQLite Snapshot
            if ($backupType === 'sqlite' && $dbDriver === 'sqlite') {
                $dbPath = config("database.connections.sqlite.database");
                if (file_exists($dbPath)) {
                    $filename = 'db_snapshot_' . date('Y-m-d_H-i-s') . '.sqlite';
                    File::copy($dbPath, $this->backupDir . '/' . $filename);

                    $this->logAction('create_backup', "SQLite ডাটাবেজ স্ন্যাপশট '{$filename}' তৈরি করা হয়েছে");
                    return back()->with('success', "SQLite ডাটাবেজ স্ন্যাপশট '{$filename}' সফলভাবে তৈরি হয়েছে!");
                }
            }

            // 2. Full Media & Database ZIP Archive
            if ($backupType === 'zip' && class_exists(ZipArchive::class)) {
                $sqlContent = $this->generateSqlDump();
                $zipFilename = 'full_backup_' . date('Y-m-d_H-i-s') . '.zip';
                $zipPath = $this->backupDir . '/' . $zipFilename;

                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $zip->addFromString('database.sql', $sqlContent);

                    // Add public storage uploads if exist
                    $uploadsPath = storage_path('app/public');
                    if (File::isDirectory($uploadsPath)) {
                        $files = File::allFiles($uploadsPath);
                        foreach ($files as $file) {
                            $relativePath = 'uploads/' . $file->getRelativePathname();
                            $zip->addFile($file->getRealPath(), $relativePath);
                        }
                    }
                    $zip->close();

                    $this->logAction('create_backup', "ফুল মিডিয়া ও ডাটাবেজ জিপ ব্যাকআপ '{$zipFilename}' তৈরি করা হয়েছে");
                    return back()->with('success', "ফুল মিডিয়া ও ডাটাবেজ ব্যাকআপ '{$zipFilename}' সফলভাবে তৈরি হয়েছে!");
                }
            }

            // 3. Compressed Gzip SQL Dump
            if ($backupType === 'gz' && function_exists('gzencode')) {
                $sqlContent = $this->generateSqlDump();
                $filename = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql.gz';
                $filePath = $this->backupDir . '/' . $filename;
                File::put($filePath, gzencode($sqlContent, 9));

                $this->logAction('create_backup', "কম্প্রেসড ডাটাবেজ ব্যাকআপ '{$filename}' তৈরি করা হয়েছে");
                return back()->with('success', "কম্প্রেসড ডাটাবেজ ব্যাকআপ '{$filename}' সফলভাবে তৈরি হয়েছে!");
            }

            // 4. Standard Pure SQL Dump
            $filename = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = $this->backupDir . '/' . $filename;
            $sqlContent = $this->generateSqlDump();
            File::put($filePath, $sqlContent);

            $this->logAction('create_backup', "ডাটাবেজ ব্যাকআপ ফাইল '{$filename}' তৈরি করা হয়েছে");
            return back()->with('success', "ডাটাবেজ ব্যাকআপ '{$filename}' সফলভাবে তৈরি হয়েছে!");
        } catch (\Throwable $e) {
            return back()->with('error', 'ব্যাকআপ তৈরিতে ত্রুটি: ' . $e->getMessage());
        }
    }

    /**
     * Restore database from a backup SQL / SQLite file.
     */
    public function restore(Request $request, string $filename): RedirectResponse
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . '/' . $filename;

        if (!File::exists($filePath)) {
            return back()->with('error', 'ব্যাকআপ ফাইলটি পাওয়া যায়নি।');
        }

        try {
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $dbDriver = config('database.default', 'mysql');

            if ($ext === 'sqlite' && $dbDriver === 'sqlite') {
                $dbPath = config("database.connections.sqlite.database");
                File::copy($filePath, $dbPath);
            } elseif ($ext === 'gz') {
                $gzContent = File::get($filePath);
                $sqlContent = gzdecode($gzContent);
                DB::unprepared($sqlContent);
            } else {
                // SQL file
                $sqlContent = File::get($filePath);
                DB::unprepared($sqlContent);
            }

            $this->logAction('restore_backup', "ডাটাবেজ '{$filename}' ফাইল থেকে রিস্টোর করা হয়েছে");
            return back()->with('success', "অভিনন্দন! ডাটাবেজ ব্যাকআপ '{$filename}' থেকে সফলভাবে রিস্টোর করা হয়েছে!");
        } catch (\Throwable $e) {
            return back()->with('error', 'ডাটাবেজ রিস্টোরে ত্রুটি: ' . $e->getMessage());
        }
    }

    /**
     * 1-Click Database Optimization & Health Maintenance.
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
                foreach ($tables as $tableObj) {
                    $tableArr = (array) $tableObj;
                    $tableName = reset($tableArr);
                    if ($tableName) {
                        DB::statement("OPTIMIZE TABLE `{$tableName}`");
                    }
                }
            }

            $this->logAction('optimize_db', 'ডাটাবেজ টেবিলসমূহ অপ্টিমাইজ ও ইনডেক্স ক্লিন করা হয়েছে');
            return back()->with('success', 'ডাটাবেজের সমস্ত টেবিল ও ইনডেক্স সফলভাবে অপ্টিমাইজ করা হয়েছে!');
        } catch (\Throwable $e) {
            return back()->with('error', 'ডাটাবেজ অপ্টিমাইজেশনে ত্রুটি: ' . $e->getMessage());
        }
    }

    /**
     * Upload an existing backup SQL/ZIP file.
     */
    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => 'required|file|max:102400', // max 100MB
        ]);

        try {
            $file = $request->file('backup_file');
            $ext = strtolower($file->getClientOriginalExtension());

            if (!in_array($ext, ['sql', 'sqlite', 'txt', 'gz', 'zip'])) {
                return back()->with('error', 'শুধুমাত্র .sql, .sqlite, .gz বা .zip ফরম্যাটের ফাইল আপলোড করা যাবে।');
            }

            $cleanName = 'uploaded_' . date('Y-m-d_H-i-s') . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $file->getClientOriginalName());
            $file->move($this->backupDir, $cleanName);

            $this->logAction('upload_backup', "ডাটাবেজ ব্যাকআপ ফাইল '{$cleanName}' আপলোড করা হয়েছে");
            return back()->with('success', "ব্যাকআপ ফাইল '{$cleanName}' সফলভাবে আপলোড হয়েছে!");
        } catch (\Throwable $e) {
            return back()->with('error', 'ব্যাকআপ ফাইল আপলোডে ত্রুটি: ' . $e->getMessage());
        }
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
     * Delete a backup file.
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
     * Universal PDO SQL Dumper supporting SQLite, MySQL, and MariaDB.
     */
    private function generateSqlDump(): string
    {
        $pdo = DB::connection()->getPdo();
        $dbDriver = config('database.default', 'mysql');
        $dbName = config("database.connections.{$dbDriver}.database", 'ideaabd');

        $out = "-- ========================================================\n";
        $out .= "-- Database Backup for: " . $dbName . " ({$dbDriver})\n";
        $out .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
        $out .= "-- Application: Idea Publication (ideaabd.com)\n";
        $out .= "-- ========================================================\n\n";

        if ($dbDriver === 'sqlite') {
            $out .= "PRAGMA foreign_keys = OFF;\n\n";
            $tables = DB::select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name ASC");

            foreach ($tables as $t) {
                $tableName = $t->name;
                $createSql = $t->sql;

                $out .= "-- Structure for table `{$tableName}`\n";
                $out .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $out .= $createSql . ";\n\n";

                // Data Rows
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

                // 1. Table structure
                $createTableRes = DB::select("SHOW CREATE TABLE `{$tableName}`");
                if (!empty($createTableRes)) {
                    $createTableArr = (array) $createTableRes[0];
                    $createTableSql = $createTableArr['Create Table'] ?? reset($createTableArr);

                    $out .= "\n-- Table structure for table `{$tableName}`\n";
                    $out .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                    $out .= $createTableSql . ";\n\n";
                }

                // 2. Table rows
                $rows = DB::table($tableName)->get();
                if ($rows->isNotEmpty()) {
                    $out .= "-- Dumping data for table `{$tableName}`\n";
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

        $out .= "-- End of Database Backup\n";
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
