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
Illuminate\Support\Facades\Schedule::call(function () {
    Illuminate\Support\Facades\Cache::put('backup:scheduler_heartbeat', time(), 3600);
    // lets /admin/backup warn when the cron talks to a different database than the site
    Illuminate\Support\Facades\Cache::put('backup:scheduler_db', App\Services\Backup\BackupService::databaseFingerprint(), 3600);
})->everyMinute()->name('backup-scheduler-heartbeat');

// Keeps the admin dashboard's cached figures warm, so the first click on "Admin" never waits for dozens
// of queries (needs the same server cron as above).
Illuminate\Support\Facades\Schedule::command('dashboard:warm')
    ->everyThreeMinutes()
    ->withoutOverlapping(10)
    ->runInBackground();


// Bulk SMS campaigns left unfinished (browser closed, PHP stopped): continue them from the saved position.
Illuminate\Support\Facades\Schedule::call(fn () => App\Services\SmsCampaignRunner::runDue(45))
    ->everyMinute()
    ->name('sms-campaigns')
    ->withoutOverlapping(5);
