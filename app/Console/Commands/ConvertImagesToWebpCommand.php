<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ImageOptimizerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ConvertImagesToWebpCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'media:convert-webp {--delete-original : Delete legacy source images after successful conversion}';

    /**
     * The console command description.
     */
    protected $description = 'Convert all legacy JPG, PNG, and AVIF images in public and storage disks to high-performance WebP and sync database paths.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting System-Wide WebP Image Transformation...');
        $deleteOriginal = (bool) $this->option('delete-original');

        $dirs = [
            storage_path('app/public'),
            public_path('images'),
        ];

        $totalConverted = 0;
        $totalBytesSaved = 0;
        $conversions = [];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $this->line("Scanning directory: <comment>{$dir}</comment>");
            $res = ImageOptimizerService::batchConvertDirectoryToWebp($dir, 85, $deleteOriginal);
            $totalConverted += $res['converted_count'];
            $totalBytesSaved += $res['bytes_saved'];
            $conversions = array_merge($conversions, $res['files']);
        }

        $formattedSaved = $this->formatBytes($totalBytesSaved);
        $this->info("Successfully converted <info>{$totalConverted}</info> images to WebP! Saved: <info>{$formattedSaved}</info>");

        // Sync Database records (update .avif, .jpg, .png references to .webp where the webp file exists)
        $this->syncDatabaseImageReferences();

        return Command::SUCCESS;
    }

    /**
     * Update database table records that point to .jpg/.png/.avif to .webp if the .webp file exists on disk.
     */
    private function syncDatabaseImageReferences(): void
    {
        $this->line("Syncing database image references to .webp...");

        $tables = [
            'books' => ['cover_image'],
            'authors' => ['avatar'],
            'users' => ['avatar'],
            'blog_posts' => ['featured_image'],
            'publishers' => ['logo'],
            'event_campaigns' => ['banner_image', 'card_bg_image', 'card_logo_image', 'card_event_logo_image'],
            'event_registrations' => ['photo', 'student_photo', 'receipt_photo'],
            'site_settings' => ['value'],
        ];

        $totalDbUpdated = 0;
        $storagePath = storage_path('app/public');

        foreach ($tables as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                $rows = DB::table($table)->whereNotNull($column)->get(['id', $column]);

                foreach ($rows as $row) {
                    $val = (string) $row->$column;
                    $ext = strtolower(pathinfo($val, PATHINFO_EXTENSION));

                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'avif', 'bmp'])) {
                        $newVal = preg_replace('/\.(jpg|jpeg|png|avif|bmp)$/i', '.webp', $val);

                        // Check if file exists in storage or public
                        $diskFile = $storagePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $newVal);
                        $publicFile = public_path(str_replace('/', DIRECTORY_SEPARATOR, $newVal));

                        if (file_exists($diskFile) || file_exists($publicFile)) {
                            DB::table($table)->where('id', $row->id)->update([$column => $newVal]);
                            $totalDbUpdated++;
                        }
                    }
                }
            }
        }

        $this->info("Updated <info>{$totalDbUpdated}</info> database image references to .webp!");
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
