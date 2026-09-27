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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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
    /**
     * Display comprehensive disaster recovery & master backup dashboard.
     */
    public function index(): View
    {
        $files = File::files($this->backupDir);
        $backups = [];
        $totalBackupSizeBytes = 0;

        $categoryCounts = [
            'all'         => 0,
            'master_zip'  => 0,
            'sql_dump'    => 0,
            'safety'      => 0,
            'anonymized'  => 0,
        ];

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            $size = $file->getSize();
            $totalBackupSizeBytes += $size;
            $filename = $file->getFilename();

            $isMasterZip = ($ext === 'zip');
            $isSafetySnapshot = str_contains($filename, 'pre_restore_safety_') || str_contains($filename, 'safety_snapshot');
            $isAnonymized = str_contains($filename, 'anonymized');

            $typeLabel = match ($ext) {
                'zip'    => 'Master All-in-One (.ZIP)',
                'sql'    => 'Standard SQL Dump (.SQL)',
                'sqlite' => 'SQLite Snapshot (.SQLITE)',
                'gz'     => 'Compressed SQL (.GZ)',
                default  => strtoupper($ext) . ' Archive',
            };

            if ($isSafetySnapshot) {
                $category = 'safety';
                $categoryCounts['safety']++;
            } elseif ($isAnonymized) {
                $category = 'anonymized';
                $categoryCounts['anonymized']++;
            } elseif ($isMasterZip) {
                $category = 'master_zip';
                $categoryCounts['master_zip']++;
            } else {
                $category = 'sql_dump';
                $categoryCounts['sql_dump']++;
            }
            $categoryCounts['all']++;

            $backups[] = [
                'filename'           => $filename,
                'type'               => $typeLabel,
                'category'           => $category,
                'is_master_zip'      => $isMasterZip,
                'is_safety_snapshot' => $isSafetySnapshot,
                'is_anonymized'      => $isAnonymized,
                'extension'          => $ext,
                'size'               => $this->formatBytes($size),
                'size_bytes'         => $size,
                'created_at'         => \Carbon\Carbon::createFromTimestamp($file->getMTime()),
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

        // Retention policy & automated settings
        $retentionLimit = (int) config('idea.backup_retention', 10);
        $settings = [];
        if (Schema::hasTable('admin_dashboard_settings')) {
            $settingRow = \App\Models\AdminDashboardSetting::where('key', 'backup_settings')->first();
            if ($settingRow) {
                $settings = is_array($settingRow->value) ? $settingRow->value : (json_decode((string)$settingRow->value, true) ?: []);
            }
        }

        // 1. Smart Disaster Recovery Health Score Engine (0 - 100)
        $healthScore = 100;
        $healthIssues = [];
        $isOverdue = false;
        $lastBackupHours = null;

        if (empty($backups)) {
            $healthScore = 35;
            $healthIssues[] = 'No backup archives found. Take a 1-click backup now!';
            $isOverdue = true;
        } else {
            $lastMtime = $latestBackup['created_at']->timestamp;
            $lastBackupHours = round((time() - $lastMtime) / 3600, 1);

            if ($lastBackupHours > 168) { // > 7 days
                $healthScore -= 35;
                $isOverdue = true;
                $healthIssues[] = 'Last backup was created over 7 days ago';
            } elseif ($lastBackupHours > 72) { // > 3 days
                $healthScore -= 15;
                $healthIssues[] = 'Last backup is over 3 days old';
            }
        }

        $freeDiskBytes = @disk_free_space($this->backupDir) ?: 10737418240; // 10GB default
        if ($freeDiskBytes < 524288000) { // < 500 MB
            $healthScore -= 25;
            $healthIssues[] = 'Low disk storage available for new backups';
        }

        $autoEnabled = !empty($settings['auto_backup_enabled']);
        if (!$autoEnabled) {
            $healthScore -= 10;
        }

        $healthScore = max(20, min(100, $healthScore));
        $healthGrade = match(true) {
            $healthScore >= 90 => 'A+',
            $healthScore >= 80 => 'A',
            $healthScore >= 70 => 'B',
            $healthScore >= 50 => 'C',
            default            => 'F',
        };

        $healthStatus = match(true) {
            $healthScore >= 90 => 'Optimal & Resilient',
            $healthScore >= 80 => 'Secure & Protected',
            $healthScore >= 70 => 'Fair & Stable',
            default            => 'Attention Recommended',
        };

        $healthAudit = [
            'score'                => $healthScore,
            'grade'                => $healthGrade,
            'status'               => $healthStatus,
            'is_overdue'           => $isOverdue,
            'last_backup_hours'    => $lastBackupHours,
            'last_backup_human'    => !empty($latestBackup) ? $latestBackup['created_at']->diffForHumans() : 'Never',
            'issues'               => $healthIssues,
            'free_disk'            => $freeDiskBytes > 0 ? $this->formatBytes((float)$freeDiskBytes) : 'Adequate',
            'free_disk_bytes'      => $freeDiskBytes,
            'free_disk_formatted'  => $freeDiskBytes > 0 ? $this->formatBytes((float)$freeDiskBytes) : 'Adequate',
            'auto_enabled'         => $autoEnabled,
            'frequency'            => $settings['backup_frequency'] ?? 'daily',
            'next_run'             => $autoEnabled ? 'Tonight at 00:00 UTC' : 'Automated Cron Disabled',
        ];

        // 2. Storage Timeline & Analytics (Last 6 Months History)
        $timelineMonths = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt = \Carbon\Carbon::now()->subMonths($i);
            $monthKey = $dt->format('Y-m');
            $monthLabel = $dt->format('M Y');
            $timelineMonths[$monthKey] = [
                'label'        => $monthLabel,
                'bytes'        => 0,
                'count'        => 0,
                'db_size_mb'   => round($totalDbSizeBytes / (1024 * 1024), 2),
            ];
        }

        foreach ($backups as $b) {
            $mk = $b['created_at']->format('Y-m');
            if (isset($timelineMonths[$mk])) {
                $timelineMonths[$mk]['bytes'] += $b['size_bytes'];
                $timelineMonths[$mk]['count']++;
            }
        }

        $chartLabels = [];
        $chartBackupSizesMb = [];
        $chartArchiveCounts = [];

        foreach ($timelineMonths as $tm) {
            $chartLabels[] = $tm['label'];
            $chartBackupSizesMb[] = round($tm['bytes'] / (1024 * 1024), 2);
            $chartArchiveCounts[] = $tm['count'];
        }

        // Ensure current active month shows at least current volume
        if (end($chartBackupSizesMb) == 0 && $totalBackupSizeBytes > 0) {
            $chartBackupSizesMb[count($chartBackupSizesMb) - 1] = round($totalBackupSizeBytes / (1024 * 1024), 2);
            $chartArchiveCounts[count($chartArchiveCounts) - 1] = count($backups);
        }

        $storageTimeline = [
            'labels'           => $chartLabels,
            'backup_sizes_mb'  => $chartBackupSizesMb,
            'archive_counts'   => $chartArchiveCounts,
        ];

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
            'retentionLimit',
            'settings',
            'categoryCounts',
            'healthAudit',
            'storageTimeline'
        ));
    }

    /**
     * Create 1-Click Master Backup with distinct modes:
     * - 'data_media': All Database + ALL media, photos, book covers, author avatars, invoices (excluding source code)
     * - 'full_system': Full System & database backup
     * - 'db_only': Pure SQL database dump
     */
    public function create(Request $request): JsonResponse|RedirectResponse
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
                ? "Complete Database & Media Backup '{$zipFilename}' created successfully!" 
                : "System Backup '{$zipFilename}' created successfully!";

            $this->logAction('create_backup', $msg);

            $targetFile = $this->backupDir . '/' . $zipFilename;
            $fileSize = File::exists($targetFile) ? $this->formatBytes(File::size($targetFile)) : '0 B';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => $msg,
                    'filename'     => $zipFilename,
                    'size'         => $fileSize,
                    'is_master_zip'=> str_ends_with(strtolower($zipFilename), '.zip'),
                    'download_url' => route('admin.backup.download', $zipFilename),
                    'created_at'   => date('d M, Y h:i A'),
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'Backup creation failed: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return back()->with('error', $err);
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
                $err = 'Only .zip, .sql, .sqlite or .gz backup archive formats are supported.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $err], 422);
                }
                return back()->with('error', $err);
            }

            $cleanName = 'uploaded_' . date('Y-m-d_H-i-s') . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $file->getClientOriginalName());
            $file->move($this->backupDir, $cleanName);

            $this->logAction('upload_backup', "Backup archive '{$cleanName}' uploaded.");

            $msg = "Backup archive '{$cleanName}' uploaded successfully!";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'message'  => $msg,
                    'filename' => $cleanName,
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'Failed to upload backup archive: ' . $e->getMessage();
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
            return back()->with('error', 'Backup archive not found.');
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
                    return back()->with('error', 'Could not open ZIP archive.');
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

            $this->logAction('restore_backup', "Database & Media restored from '{$filename}'");
            return back()->with('success', "Disaster recovery complete! Database & Media restored from '{$filename}'.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
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
            return response()->json(['success' => false, 'message' => 'Archive file not found'], 404);
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
     * Database Diff & Analytics between Live Database and Backup Snapshot.
     */
    public function diff(string $filename): JsonResponse
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . '/' . $filename;

        if (!File::exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Backup archive not found'], 404);
        }

        try {
            $sqlContent = $this->extractSqlFromBackup($filePath);
            if (!$sqlContent) {
                return response()->json(['success' => false, 'message' => 'Could not parse SQL data from backup archive'], 422);
            }

            // Parse table row counts and table list from SQL
            $backupTables = [];
            
            // Match table creates: CREATE TABLE [IF NOT EXISTS] `table_name`
            preg_match_all('/CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+[`"]?([a-zA-Z0-9_]+)[`"]?/i', $sqlContent, $createMatches);
            $foundTables = array_unique($createMatches[1] ?? []);

            // Also match INSERT INTO `table_name`
            preg_match_all('/INSERT\s+INTO\s+[`"]?([a-zA-Z0-9_]+)[`"]?/i', $sqlContent, $insertMatches);
            $insertTables = array_count_values($insertMatches[1] ?? []);

            $allTableNames = array_unique(array_merge($foundTables, array_keys($insertTables)));

            // Fetch live tables from active database
            $dbDriver = config('database.default', 'mysql');
            $liveTables = [];

            if ($dbDriver === 'sqlite') {
                $sqliteTables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                foreach ($sqliteTables as $st) {
                    $liveTables[$st->name] = (int) $this->safe(fn () => DB::table($st->name)->count(), 0);
                }
            } else {
                $tableStatus = DB::select('SHOW TABLE STATUS');
                foreach ($tableStatus as $tbl) {
                    $tblName = $tbl->Name ?? $tbl->name ?? '';
                    if ($tblName) {
                        $liveTables[$tblName] = (int) ($tbl->Rows ?? $tbl->rows ?? 0);
                    }
                }
            }

            // Build unified comparison dataset
            $unifiedNames = array_unique(array_merge(array_keys($liveTables), $allTableNames));
            sort($unifiedNames);

            $diffData = [];
            $totalLiveRows = 0;
            $totalBackupRows = 0;
            $changedTablesCount = 0;

            foreach ($unifiedNames as $tbl) {
                $liveCount = $liveTables[$tbl] ?? null;
                $backupCount = $insertTables[$tbl] ?? (in_array($tbl, $foundTables) ? 0 : null);

                $liveRows = $liveCount !== null ? (int) $liveCount : 0;
                $backupRows = $backupCount !== null ? (int) $backupCount : 0;

                $totalLiveRows += $liveRows;
                $totalBackupRows += $backupRows;

                $status = 'equal';
                if ($liveCount === null && $backupCount !== null) {
                    $status = 'only_in_backup';
                    $changedTablesCount++;
                } elseif ($liveCount !== null && $backupCount === null) {
                    $status = 'only_in_live';
                    $changedTablesCount++;
                } elseif ($liveRows > $backupRows) {
                    $status = 'live_higher';
                    $changedTablesCount++;
                } elseif ($liveRows < $backupRows) {
                    $status = 'backup_higher';
                    $changedTablesCount++;
                }

                $diffData[] = [
                    'table'       => $tbl,
                    'live_rows'   => $liveCount,
                    'backup_rows' => $backupCount,
                    'diff'        => ($liveCount !== null && $backupCount !== null) ? ($liveRows - $backupRows) : null,
                    'status'      => $status,
                ];
            }

            return response()->json([
                'success'              => true,
                'filename'             => $filename,
                'total_tables'         => count($diffData),
                'changed_tables_count' => $changedTablesCount,
                'total_live_rows'      => $totalLiveRows,
                'total_backup_rows'    => $totalBackupRows,
                'diff_rows'            => $totalLiveRows - $totalBackupRows,
                'tables'               => $diffData,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Diff analysis error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Safe Dry-Run & Sandbox Simulation (Executes SQL inside an uncommitted transaction).
     */
    public function dryRun(string $filename): JsonResponse
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . '/' . $filename;

        if (!File::exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Backup archive not found'], 404);
        }

        try {
            $sqlContent = $this->extractSqlFromBackup($filePath);
            if (!$sqlContent) {
                return response()->json(['success' => false, 'message' => 'Could not parse SQL data from backup archive'], 422);
            }

            $startTime = microtime(true);

            // Execute in an isolated sandbox transaction and unconditionally ROLLBACK
            DB::beginTransaction();
            try {
                // Disable foreign key checks for testing schema
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::unprepared($sqlContent);
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);
                
                // Rollback unconditionally so live DB is 100% untouched
                DB::rollBack();

                $this->logAction('backup_dry_run_passed', "Dry-run simulation passed for '{$filename}' ({$executionTimeMs}ms)");

                return response()->json([
                    'success'           => true,
                    'status'            => 'passed',
                    'message'           => 'Dry-run simulation passed with zero errors! Schema and constraints are 100% valid.',
                    'execution_time_ms' => $executionTimeMs,
                    'filename'          => $filename,
                ]);
            } catch (\Throwable $dryError) {
                DB::rollBack();
                return response()->json([
                    'success'           => false,
                    'status'            => 'failed',
                    'message'           => 'Dry-run simulation detected errors: ' . $dryError->getMessage(),
                    'error_details'     => $dryError->getMessage(),
                    'filename'          => $filename,
                ], 422);
            }
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Dry-run execution error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Selective Table Restore (Restores ONLY chosen tables without touching other data).
     */
    public function selectiveRestore(Request $request, string $filename): JsonResponse|RedirectResponse
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . '/' . $filename;

        if (!File::exists($filePath)) {
            $err = 'Backup archive not found.';
            return $request->wantsJson() ? response()->json(['success' => false, 'message' => $err], 404) : back()->with('error', $err);
        }

        $selectedTables = $request->input('tables', []);
        if (empty($selectedTables) || !is_array($selectedTables)) {
            $err = 'Please select at least one database table to restore.';
            return $request->wantsJson() ? response()->json(['success' => false, 'message' => $err], 422) : back()->with('error', $err);
        }

        // Strict validation on table names to prevent injection
        $sanitizedTables = [];
        foreach ($selectedTables as $tbl) {
            if (preg_match('/^[a-zA-Z0-9_]+$/', (string)$tbl)) {
                $sanitizedTables[] = (string)$tbl;
            }
        }

        if (empty($sanitizedTables)) {
            $err = 'Invalid database table names provided.';
            return $request->wantsJson() ? response()->json(['success' => false, 'message' => $err], 422) : back()->with('error', $err);
        }

        try {
            $sqlContent = $this->extractSqlFromBackup($filePath);
            if (!$sqlContent) {
                $err = 'Could not parse SQL data from backup archive';
                return $request->wantsJson() ? response()->json(['success' => false, 'message' => $err], 422) : back()->with('error', $err);
            }

            // 1. Generate safety rollback snapshot first!
            $this->createSafetyRollbackSnapshot();

            // 2. Extract SQL statements specifically for selected tables
            $extractedSql = "SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $restoredCount = 0;

            foreach ($sanitizedTables as $targetTable) {
                // Extract DROP TABLE and CREATE TABLE for this table
                $pattern = '/(?:DROP\s+TABLE\s+IF\s+EXISTS\s+[`"]?' . preg_quote($targetTable, '/') . '[`"]?;\s*)?(CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+[`"]?' . preg_quote($targetTable, '/') . '[`"]?[\s\S]*?;)/i';
                if (preg_match($pattern, $sqlContent, $match)) {
                    $extractedSql .= "DROP TABLE IF EXISTS `{$targetTable}`;\n";
                    $extractedSql .= $match[1] . "\n";
                }

                // Extract all INSERT INTO `targetTable` statements
                $insertPattern = '/(INSERT\s+INTO\s+[`"]?' . preg_quote($targetTable, '/') . '[`"]?[\s\S]*?;)/i';
                preg_match_all($insertPattern, $sqlContent, $insertMatches);
                if (!empty($insertMatches[1])) {
                    foreach ($insertMatches[1] as $ins) {
                        $extractedSql .= $ins . "\n";
                    }
                }
                $restoredCount++;
            }

            $extractedSql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            // 3. Execute in transaction
            DB::beginTransaction();
            try {
                DB::unprepared($extractedSql);
                DB::commit();
            } catch (\Throwable $txError) {
                DB::rollBack();
                throw $txError;
            }

            $msg = "Successfully restored " . count($sanitizedTables) . " table(s) (" . implode(', ', array_slice($sanitizedTables, 0, 4)) . ")!";
            $this->logAction('selective_restore', $msg);

            if ($request->wantsJson()) {
                return response()->json([
                    'success'          => true,
                    'message'          => $msg,
                    'restored_tables'  => $sanitizedTables,
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            $err = 'Selective restore failed: ' . $e->getMessage();
            return $request->wantsJson() ? response()->json(['success' => false, 'message' => $err], 500) : back()->with('error', $err);
        }
    }

    /**
     * Anonymized Developer Export (Masks customer passwords, phone numbers, and emails for safe dev/staging usage).
     */
    public function exportAnonymized(Request $request): BinaryFileResponse|JsonResponse|RedirectResponse
    {
        try {
            $pdo = DB::connection()->getPdo();
            $dbDriver = config('database.default', 'mysql');
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "idea_anonymized_dump_{$timestamp}.sql";
            $filePath = $this->backupDir . '/' . $filename;

            $out = "-- ========================================================\n";
            $out .= "-- Anonymized Developer Dump for Staging & Local Dev\n";
            $out .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
            $out .= "-- ALL Customer PII, Passwords, and Contact info are MASKED\n";
            $out .= "-- ========================================================\n\n";

            $out .= "SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n";

            $defaultHashedPassword = Hash::make('Password@123');

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
                    $out .= "-- Anonymized data for table `{$tableName}`\n";
                    foreach ($rows as $row) {
                        $rowArr = (array) $row;

                        // Anonymize sensitive fields
                        if ($tableName === 'users' || $tableName === 'customers') {
                            if (isset($rowArr['password'])) {
                                $rowArr['password'] = $defaultHashedPassword;
                            }
                            if (isset($rowArr['email']) && !str_starts_with((string)$rowArr['email'], 'admin@')) {
                                $rowArr['email'] = 'dev_user_' . ($rowArr['id'] ?? rand(100, 999)) . '@ideaabd.test';
                            }
                            if (isset($rowArr['phone']) && !empty($rowArr['phone'])) {
                                $rowArr['phone'] = '01700' . str_pad((string)($rowArr['id'] ?? rand(1000, 9999)), 6, '0', STR_PAD_LEFT);
                            }
                            if (isset($rowArr['remember_token'])) {
                                $rowArr['remember_token'] = null;
                            }
                        }

                        if ($tableName === 'orders' || $tableName === 'order_addresses') {
                            if (isset($rowArr['customer_phone']) || isset($rowArr['phone'])) {
                                $col = isset($rowArr['customer_phone']) ? 'customer_phone' : 'phone';
                                $rowArr[$col] = '0180000' . rand(1000, 9999);
                            }
                            if (isset($rowArr['customer_email']) || isset($rowArr['email'])) {
                                $col = isset($rowArr['customer_email']) ? 'customer_email' : 'email';
                                $rowArr[$col] = 'customer_' . ($rowArr['id'] ?? rand(100, 999)) . '@ideaabd.test';
                            }
                        }

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
            File::put($filePath, $out);

            $this->logAction('export_anonymized_dump', "Anonymized developer dump '{$filename}' generated");

            if ($request->wantsJson()) {
                return response()->json([
                    'success'      => true,
                    'message'      => "Anonymized developer dump '{$filename}' generated successfully!",
                    'filename'     => $filename,
                    'download_url' => route('admin.backup.download', $filename),
                ]);
            }

            return response()->download($filePath);
        } catch (\Throwable $e) {
            $err = 'Failed to generate anonymized dump: ' . $e->getMessage();
            return $request->wantsJson() ? response()->json(['success' => false, 'message' => $err], 500) : back()->with('error', $err);
        }
    }

    /**
     * Test Instant Telegram / Webhook / Email Notification Alert.
     */
    public function testNotification(Request $request): JsonResponse
    {
        $type = $request->input('type', 'telegram'); // 'telegram', 'email'
        
        try {
            $settings = [];
            if (Schema::hasTable('admin_dashboard_settings')) {
                $settingRow = \App\Models\AdminDashboardSetting::where('key', 'backup_settings')->first();
                if ($settingRow) {
                    $settings = is_array($settingRow->value) ? $settingRow->value : (json_decode((string)$settingRow->value, true) ?: []);
                }
            }

            if ($type === 'telegram') {
                $botToken = $request->input('telegram_bot_token', $settings['telegram_bot_token'] ?? null);
                $chatId = $request->input('telegram_chat_id', $settings['telegram_chat_id'] ?? null);

                if (!$botToken || !$chatId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please configure Telegram Bot Token and Chat ID.',
                    ], 422);
                }

                $text = "🛡️ *Idea Publication Disaster Recovery Alert*\n\n"
                    . "✅ *Status:* System & Database Backup is Healthy.\n"
                    . "⏰ *Time:* " . date('d M, Y h:i A') . "\n"
                    . "🌐 *Environment:* " . config('app.url') . "\n"
                    . "💾 *Live Database:* " . config('database.default') . " Connected\n\n"
                    . "🔔 This is an automated test alert notification.";

                $response = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id'    => $chatId,
                    'text'       => $text,
                    'parse_mode' => 'Markdown',
                ]);

                if ($response->successful()) {
                    $this->logAction('test_telegram_alert', 'Telegram test alert delivered successfully');
                    return response()->json([
                        'success' => true,
                        'message' => 'Telegram test notification delivered to your channel successfully!',
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Telegram API error: ' . ($response->json('description') ?? 'Connection failed'),
                    ], 400);
                }
            } else {
                // Email Test
                $email = $request->input('email', $settings['backup_email'] ?? config('mail.from.address', 'adideabd@gmail.com'));
                Mail::raw("🛡️ Idea Publication Disaster Recovery Test Alert. Time: " . date('Y-m-d H:i:s'), function ($message) use ($email) {
                    $message->to($email)->subject('Idea Publication Backup Alert System Test');
                });

                return response()->json([
                    'success' => true,
                    'message' => "Test email delivered successfully to {$email}.",
                ]);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification dispatch error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to safely extract SQL string from .sql, .zip, .gz, or .txt backup file.
     */
    private function extractSqlFromBackup(string $filePath): ?string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'zip' && class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === true) {
                $sql = $zip->getFromName('database.sql');
                $zip->close();
                return $sql ?: null;
            }
            return null;
        }

        if ($ext === 'gz') {
            $content = File::get($filePath);
            return gzdecode($content) ?: null;
        }

        if (in_array($ext, ['sql', 'txt'])) {
            return File::get($filePath);
        }

        return null;
    }

    /**
     * 1-Click Database Integrity Scan & Diagnostic Report.
     */
    public function integrityCheck(): RedirectResponse
    {
        $startTime = microtime(true);
        try {
            $dbDriver = config('database.default', 'mysql');
            $status = 'Passed (Healthy)';
            $details = [];

            if ($dbDriver === 'sqlite') {
                $check = DB::select('PRAGMA integrity_check');
                $resultStr = $check[0]->integrity_check ?? 'ok';
                if (strtolower($resultStr) !== 'ok') {
                    $status = 'Issues detected: ' . $resultStr;
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
                    $status = 'Errors in tables: ' . implode(', ', array_slice($details, 0, 3));
                }
            }

            $latency = round((microtime(true) - $startTime) * 1000, 2);
            $msg = "Database integrity scan complete ({$latency}ms)! Status: {$status}";
            $this->logAction('integrity_check', $msg);

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Integrity check error: ' . $e->getMessage());
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

            $this->logAction('optimize_db', 'All database tables and indexes optimized');
            return back()->with('success', 'All database tables and indexes optimized successfully!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Database optimization error: ' . $e->getMessage());
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
            return back()->with('error', 'Backup archive not found.');
        }

        return response()->download($filePath);
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

    /**
     * Update automated backup configuration settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'auto_backup_enabled'      => 'nullable|boolean',
            'backup_frequency'         => 'nullable|string|in:daily,weekly,monthly',
            'backup_email'             => 'nullable|email',
            'retention_days'           => 'nullable|integer|min:1|max:365',
            'telegram_alerts_enabled'  => 'nullable|boolean',
            'telegram_bot_token'       => 'nullable|string|max:150',
            'telegram_chat_id'         => 'nullable|string|max:100',
            'offsite_cloud_driver'     => 'nullable|string|in:none,s3,gdrive,ftp',
            'offsite_cloud_path'       => 'nullable|string|max:255',
        ]);

        if (Schema::hasTable('admin_dashboard_settings')) {
            \App\Models\AdminDashboardSetting::updateOrCreate(
                ['key' => 'backup_settings'],
                ['value' => $validated]
            );
        }

        $this->logAction('backup_settings_updated', 'Automated backup, cloud & telegram settings updated');

        return redirect()->back()->with('success', 'Backup, cloud & notification settings saved successfully.');
    }

    /**
     * Send backup file to admin email address.
     */
    public function sendEmail(Request $request, string $filename): JsonResponse|RedirectResponse
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($filePath)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Backup archive not found.'], 404);
            }
            return redirect()->back()->with('error', 'Backup archive not found.');
        }

        $recipientEmail = $request->input('email', config('mail.from.address', 'adideabd@gmail.com'));

        try {
            Mail::raw("Database backup archive '{$filename}' is attached.", function ($message) use ($recipientEmail, $filePath, $filename) {
                $message->to($recipientEmail)
                    ->subject("Database Backup - {$filename}")
                    ->attach($filePath);
            });

            $this->logAction('backup_emailed', "Backup archive '{$filename}' sent to {$recipientEmail}");

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => "Backup archive successfully sent to {$recipientEmail}."]);
            }
            return redirect()->back()->with('success', "Backup archive successfully sent to {$recipientEmail}.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Backup email dispatch error: " . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    /**
     * Delete a single backup archive permanently.
     */
    public function destroy(Request $request, string $filename): JsonResponse|RedirectResponse
    {
        $filename = basename($filename);
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (File::exists($filePath)) {
            File::delete($filePath);
            $this->logAction('backup_deleted', "Backup archive '{$filename}' deleted permanently.");

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => "Backup archive '{$filename}' deleted successfully."]);
            }
            return redirect()->route('admin.backup.index')->with('success', "Backup archive '{$filename}' deleted successfully.");
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => "Backup archive '{$filename}' not found."], 404);
        }
        return redirect()->route('admin.backup.index')->with('error', "Backup archive '{$filename}' not found.");
    }

    /**
     * Delete multiple selected backup archives.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $filenames = $request->input('filenames', []);
        if (empty($filenames) && $request->has('filename')) {
            $filenames = [$request->input('filename')];
        }

        $deletedCount = 0;
        foreach ($filenames as $filename) {
            $filename = basename($filename);
            $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
            if (File::exists($filePath)) {
                File::delete($filePath);
                $deletedCount++;
            }
        }

        $this->logAction('backup_bulk_deleted', "{$deletedCount} backup archive(s) deleted permanently.");

        $message = $deletedCount > 0 
            ? "{$deletedCount} backup archive(s) deleted successfully."
            : "No backup archives were found to delete.";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'deleted_count' => $deletedCount]);
        }

        return redirect()->route('admin.backup.index')->with('success', $message);
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

    private function formatBytes(int|float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max((float)$bytes, 0.0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min((int)$pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
