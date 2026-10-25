<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatic backups: runs only when the schedule saved in /admin/backup is due (needs the server cron: * * * * * php artisan schedule:run)
Illuminate\Support\Facades\Schedule::command('backup:run --scheduled')
    ->everyMinute()
    ->when(fn () => app(App\Services\Backup\BackupService::class)->isDue())
    ->withoutOverlapping(120);

// Shows on /admin/backup whether the server cron is running the scheduler at all
Illuminate\Support\Facades\Schedule::call(fn () => Illuminate\Support\Facades\Cache::put('backup:scheduler_heartbeat', time(), 3600))->everyMinute()->name('backup-scheduler-heartbeat');
