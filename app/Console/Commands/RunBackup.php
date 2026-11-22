<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupService;
use Illuminate\Console\Command;

class RunBackup extends Command
{
    protected $signature = 'backup:run {--mode= : db_only or data_media (default: the mode saved in the backup settings)} {--scheduled : only run when the saved schedule is due}';

    protected $description = 'Create a backup (prune, copy offsite and notify according to the backup settings)';

    public function handle(BackupService $backups): int
    {
        if ($this->option('scheduled') && !$backups->isDue()) {
            return self::SUCCESS;
        }

        // one automatic backup at a time (the admin pages can start one too)
        if ($this->option('scheduled') && !\Illuminate\Support\Facades\Cache::add('backup:auto_lock', time(), 1800)) {
            return self::SUCCESS;
        }

        $mode = $this->option('mode') ?: $backups->settings()['backup_mode'];
        try {
            $result = $backups->run($mode, $this->option('scheduled') ? 'auto' : 'manual');
        } finally {
            if ($this->option('scheduled')) {
                \Illuminate\Support\Facades\Cache::forget('backup:auto_lock');
            }
        }

        if (!$result['ok']) {
            $this->error('ব্যাকআপ ব্যর্থ: ' . $result['message']);

            return self::FAILURE;
        }

        $this->info("ব্যাকআপ তৈরি হয়েছে: {$result['filename']} ({$result['size']})" . ($result['offsite'] ? ' · ' . $result['offsite'] : ''));

        return self::SUCCESS;
    }
}
