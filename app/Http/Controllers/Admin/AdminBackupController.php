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
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
     * Display database backup manager and list of available backup files.
     */
    public function index(): View
    {
        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename'   => $file->getFilename(),
                'size'       => $this->formatBytes($file->getSize()),
                'size_bytes' => $file->getSize(),
                'created_at' => \Carbon\Carbon::createFromTimestamp($file->getMTime()),
            ];
        }

        // Sort latest backups first
        usort($backups, fn ($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        $dbName = config('database.connections.' . config('database.default') . '.database');
        $dbDriver = config('database.default');

        // Database Tables Statistics
        $tables = [];
        $totalDbSizeBytes = 0;
        $totalRowsCount = 0;

        try {
            $tableStatus = DB::select('SHOW TABLE STATUS');
            foreach ($tableStatus as $tbl) {
                $tblName = $tbl->Name ?? $tbl->name ?? '';
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
        } catch (\Throwable) {
            // fallback for non-mysql drivers
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
     * Upload an existing backup SQL file to storage.
     */
    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => 'required|file|max:102400', // max 100MB
        ]);

        try {
            $file = $request->file('backup_file');
            $ext = strtolower($file->getClientOriginalExtension());

            if (!in_array($ext, ['sql', 'txt', 'gz'])) {
                return back()->with('error', 'শুধুমাত্র .sql, .txt বা .gz ফরম্যাটের ফাইল আপলোড করা যাবে।');
            }

            $cleanName = 'uploaded_backup_' . date('Y-m-d_H-i-s') . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $file->getClientOriginalName());
            $file->move($this->backupDir, $cleanName);

            if ($this->accessService) {
                $this->accessService->log('upload_backup', "ডাটাবেজ ব্যাকআপ ফাইল '{$cleanName}' আপলোড করা হয়েছে");
            }

            return back()->with('success', "ব্যাকআপ ফাইল '{$cleanName}' সফলভাবে আপলোড হয়েছে!");
        } catch (\Throwable $e) {
            return back()->with('error', 'ব্যাকআপ ফাইল আপলোডে ত্রুটি: ' . $e->getMessage());
        }
    }

    /**
     * Restore database from a backup SQL file.
     */
    public function restore(Request $request, string $filename): RedirectResponse
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . '/' . $filename;

        if (!File::exists($filePath)) {
            return back()->with('error', 'ব্যাকআপ ফাইলটি পাওয়া যায়নি।');
        }

        try {
            $sqlContent = File::get($filePath);
            DB::unprepared($sqlContent);

            if ($this->accessService) {
                $this->accessService->log('restore_backup', "ডাটাবেজ '{$filename}' ফাইল থেকে রিস্টোর করা হয়েছে");
            }

            return back()->with('success', "অভিনন্দন! ডাটাবেজ ব্যাকআপ '{$filename}' থেকে সফলভাবে রিস্টোর করা হয়েছে!");
        } catch (\Throwable $e) {
            return back()->with('error', 'ডাটাবেজ রিস্টোরে ত্রুটি: ' . $e->getMessage());
        }
    }

    /**
     * Create a fresh database backup SQL file.
     */
    public function create(Request $request): RedirectResponse
    {
        try {
            $filename = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = $this->backupDir . '/' . $filename;

            // Generate SQL Dump via PDO
            $sql = $this->generateSqlDump();
            File::put($filePath, $sql);

            if ($this->accessService) {
                $this->accessService->log('create_backup', "ডাটাবেজ ব্যাকআপ ফাইল '{$filename}' তৈরি করা হয়েছে");
            }

            return back()->with('success', "ডাটাবেজ ব্যাকআপ '{$filename}' সফলভাবে তৈরি হয়েছে!");
        } catch (\Throwable $e) {
            return back()->with('error', 'ব্যাকআপ তৈরিতে ত্রুটি: ' . $e->getMessage());
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
            if ($this->accessService) {
                $this->accessService->log('delete_backup', "ডাটাবেজ ব্যাকআপ '{$filename}' মুছে ফেলা হয়েছে");
            }
            return back()->with('success', "ব্যাকআপ ফাইল '{$filename}' সফলভাবে মুছে ফেলা হয়েছে!");
        }

        return back()->with('error', 'ফাইলটি পাওয়া যায়নি।');
    }

    /**
     * Pure PHP PDO SQL Dumper for maximum portability.
     */
    private function generateSqlDump(): string
    {
        $pdo = DB::connection()->getPdo();
        $dbName = config('database.connections.' . config('database.default') . '.database');

        $out = "-- ========================================================\n";
        $out .= "-- Database Backup for: " . $dbName . "\n";
        $out .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
        $out .= "-- Application: Idea Publication (ideaabd.com)\n";
        $out .= "-- ========================================================\n\n";
        $out .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $out .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $out .= "SET time_zone = \"+06:00\";\n\n";

        $tables = DB::select('SHOW TABLES');
        $keyName = 'Tables_in_' . $dbName;

        foreach ($tables as $tableObj) {
            $table = (array)$tableObj;
            $tableName = reset($table);

            if (empty($tableName)) continue;

            // 1. Table structure
            $createTableRes = DB::select("SHOW CREATE TABLE `{$tableName}`");
            if (!empty($createTableRes)) {
                $createTableArr = (array)$createTableRes[0];
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
                    $rowArr = (array)$row;
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
        $out .= "-- End of Database Backup\n";

        return $out;
    }

    /**
     * Format bytes.
     */
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
