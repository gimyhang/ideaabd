<?php

namespace Tests\Feature;

use App\Models\AdminDashboardSetting;
use App\Models\User;
use App\Services\Backup\BackupService;
use App\Services\Backup\SqlSplitter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use ZipArchive;

class BackupHubTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;
    private BackupService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir() . '/bk_' . uniqid();
        config([
            'backup.dir'      => $this->dir,
            'backup.quota_mb' => 0,
            'backup.media'    => ['media' => $this->dir . '_media', 'public_uploads' => $this->dir . '_uploads'],
        ]);
        File::ensureDirectoryExists($this->dir . '_media');
        $this->svc = app(BackupService::class);

        Schema::create('bk_probe', function ($t) {
            $t->id();
            $t->text('note');
        });
        DB::table('bk_probe')->insert([['note' => "semi;colon and 'quote' and\nnew line"], ['note' => 'two']]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        File::deleteDirectory($this->dir);
        File::deleteDirectory($this->dir . '_media');
        parent::tearDown();
    }

    private ?User $adminUser = null;

    private function admin(): User
    {
        return $this->adminUser ??= User::create([
            'name' => 'Root', 'email' => 'bk-admin@example.test', 'phone' => '01700000066',
            'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function touchFile(string $name, int $ageDays, string $content = 'x'): void
    {
        File::ensureDirectoryExists($this->dir);
        $path = $this->dir . '/' . $name;
        File::put($path, $content);
        touch($path, time() - $ageDays * 86400);
    }

    // ---- SQL splitting

    public function test_the_splitter_keeps_semicolons_and_line_breaks_inside_text(): void
    {
        $sql = "-- header; not a statement\nDROP TABLE IF EXISTS `t`;\nINSERT INTO `t` (`a`) VALUES ('x; y\nz''s \\' end');\n# c\nINSERT INTO `t` (`a`) VALUES (\"q;\");\n";
        $st  = iterator_to_array(SqlSplitter::statements($sql), false);

        $this->assertCount(3, $st);
        $this->assertSame('DROP TABLE IF EXISTS `t`', $st[0]);
        $this->assertStringContainsString("x; y\nz''s \\' end", $st[1]);
        $this->assertSame('INSERT', SqlSplitter::verb($st[2]));
        $this->assertSame('t', SqlSplitter::table($st[1]));
    }

    // ---- create / list

    public function test_a_database_backup_is_a_gzip_dump_and_a_media_backup_is_a_verified_zip(): void
    {
        $db = $this->svc->create('db_only');
        $this->assertStringEndsWith('.sql.gz', $db['filename']);
        $this->assertStringContainsString("semi;colon", gzdecode(File::get($this->dir . '/' . $db['filename'])));

        $zip = $this->svc->create('data_media');
        $this->assertTrue($zip['is_zip']);

        $manifest = $this->svc->manifest($this->dir . '/' . $zip['filename']);
        $this->assertSame(hash('sha256', $this->svc->readSql($this->dir . '/' . $zip['filename'])), $manifest['sha256']);
        $this->assertTrue($this->svc->verify($this->dir . '/' . $zip['filename'])['passed']);
    }

    public function test_only_backup_files_are_listed(): void
    {
        $this->touchFile('.gitignore', 0, '*');
        $this->touchFile('notes.txt', 0);
        $this->touchFile('idea_db_x.sql.gz', 0, gzencode('SELECT 1;'));

        $names = array_column($this->svc->files(), 'filename');
        $this->assertSame(['idea_db_x.sql.gz'], $names);
    }

    // ---- verify / safety

    public function test_a_tampered_backup_fails_the_checksum(): void
    {
        $res  = $this->svc->create('data_media');
        $path = $this->dir . '/' . $res['filename'];

        $zip = new ZipArchive();
        $zip->open($path);
        $zip->addFromString('database.sql', $zip->getFromName('database.sql') . "\nINSERT INTO `bk_probe` (`note`) VALUES ('injected');\n");
        $zip->close();

        $check = $this->svc->verify($path);
        $this->assertFalse($check['passed']);
        $this->assertContains('চেকসাম', array_column(array_filter($check['checks'], fn ($c) => !$c['ok']), 'label'));
    }

    public function test_a_dump_may_only_contain_dump_statements(): void
    {
        foreach (["DELETE FROM users;", "UPDATE users SET role='admin';", "GRANT ALL ON *.* TO 'x'@'%';", "CREATE USER 'x'@'%';", "DROP DATABASE app;", "SELECT 1 INTO OUTFILE '/tmp/x';", "SET GLOBAL general_log=1;", "TRUNCATE users;"] as $bad) {
            $this->assertFalse($this->svc->statementAllowed(rtrim($bad, ';')), $bad);
        }
        foreach (["DROP TABLE IF EXISTS `a`", "CREATE TABLE `a` (id int)", "INSERT INTO `a` (`id`) VALUES (1)", "SET FOREIGN_KEY_CHECKS=0", "ALTER TABLE `a` ADD PRIMARY KEY (`id`)", "PRAGMA foreign_keys = OFF", "LOCK TABLES `a` WRITE"] as $good) {
            $this->assertTrue($this->svc->statementAllowed($good), $good);
        }
    }

    public function test_verify_is_a_real_no_op_for_the_live_database(): void
    {
        $res = $this->svc->create('db_only');
        DB::table('bk_probe')->insert(['note' => 'added after backup']);

        $this->svc->verify($this->dir . '/' . $res['filename']);

        $this->assertSame(3, DB::table('bk_probe')->count());
    }

    public function test_a_hostile_upload_is_refused_and_not_kept(): void
    {
        $file = UploadedFile::fake()->createWithContent('evil.sql', "DROP TABLE IF EXISTS `bk_probe`;\nDELETE FROM users;\n");

        $this->actingAs($this->admin())->postJson('/admin/backup/upload', ['backup_file' => $file])->assertStatus(422)->assertJsonPath('success', false);
        $this->assertSame([], $this->svc->files());

        $php = UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;');
        $this->actingAs($this->admin())->postJson('/admin/backup/upload', ['backup_file' => $php])->assertStatus(422);
    }

    public function test_a_good_upload_is_accepted(): void
    {
        $res = $this->svc->create('db_only');
        $copy = UploadedFile::fake()->createWithContent('mine.sql.gz', File::get($this->dir . '/' . $res['filename']));

        $this->actingAs($this->admin())->postJson('/admin/backup/upload', ['backup_file' => $copy])->assertOk()->assertJsonPath('success', true);
        $this->assertCount(2, $this->svc->files());
    }

    // ---- restore

    public function test_restoring_selected_tables_brings_back_only_those_rows(): void
    {
        $res = $this->svc->create('db_only');
        DB::table('bk_probe')->delete();
        DB::table('bk_probe')->insert(['note' => 'new']);

        $done = $this->svc->restoreTables($this->dir . '/' . $res['filename'], ['bk_probe']);

        $this->assertSame(['bk_probe'], $done);
        $this->assertSame(2, DB::table('bk_probe')->count());
        $this->assertSame("semi;colon and 'quote' and\nnew line", DB::table('bk_probe')->orderBy('id')->value('note'));
        $this->assertNotEmpty(array_filter($this->svc->files(), fn ($f) => $f['category'] === 'safety'), 'a safety snapshot is taken first');
    }

    public function test_a_full_restore_puts_the_database_back(): void
    {
        $res = $this->svc->create('db_only');
        DB::table('bk_probe')->delete();

        $r = $this->svc->restore($this->dir . '/' . $res['filename']);

        $this->assertGreaterThan(0, $r['statements']);
        $this->assertSame(2, DB::table('bk_probe')->count());
    }

    public function test_media_restore_skips_scripts_and_paths_that_escape(): void
    {
        $path = $this->dir . '/media_test.zip';
        File::ensureDirectoryExists($this->dir);
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE);
        $zip->addFromString('database.sql', "DROP TABLE IF EXISTS `bk_probe`;\nCREATE TABLE `bk_probe` (`id` integer primary key autoincrement, `note` text);\nINSERT INTO `bk_probe` (`id`, `note`) VALUES (1, 'a');\n");
        $zip->addFromString('media/bk_ok.jpg', 'img');
        $zip->addFromString('media/bk_evil.php', '<?php echo 1;');
        $zip->addFromString('media/../bk_escape.txt', 'no');
        $zip->close();

        $r = $this->svc->restore($path);

        $this->assertSame(1, $r['media']);
        $this->assertFileExists($this->dir . '_media/bk_ok.jpg');
        $this->assertFileDoesNotExist($this->dir . '_media/bk_evil.php');
        $this->assertFileDoesNotExist(dirname($this->dir) . '/bk_escape.txt');
        $this->assertFileDoesNotExist($this->dir . '/bk_escape.txt');
    }

    public function test_a_failed_check_blocks_the_restore(): void
    {
        $this->touchFile('bad.sql', 0, "DELETE FROM users;\n");

        $this->expectException(\RuntimeException::class);
        $this->svc->restore($this->dir . '/bad.sql');
    }

    // ---- keep / prune / space

    public function test_prune_keeps_the_newest_few_and_the_safety_copies(): void
    {
        foreach ([1, 2, 3, 4, 5] as $age) {
            $this->touchFile("idea_db_{$age}.sql.gz", $age, 'x');
        }
        foreach ([1, 2, 3] as $age) {
            $this->touchFile("pre_restore_{$age}.sql", $age, 'x');
        }

        $this->svc->prune(2);

        $left = array_column($this->svc->files(), 'filename');
        sort($left);
        $this->assertSame(['idea_db_1.sql.gz', 'idea_db_2.sql.gz', 'pre_restore_1.sql', 'pre_restore_2.sql'], $left);
    }

    public function test_space_follows_the_quota_setting_and_the_real_disk_otherwise(): void
    {
        $this->touchFile('idea_db_a.sql.gz', 0, str_repeat('a', 1048576));

        config(['backup.quota_mb' => 100]);
        $s = $this->svc->space($this->svc->files());
        $this->assertSame(100 * 1048576, $s['total_bytes']);
        $this->assertEqualsWithDelta(1.0, $s['pct'], 0.1);
        $this->assertTrue($s['is_quota']);

        config(['backup.quota_mb' => 0]);
        $auto = $this->svc->space($this->svc->files());
        $this->assertFalse($auto['is_quota']);
        $this->assertGreaterThan(100 * 1048576, $auto['total_bytes']);
    }

    // ---- schedule

    public function test_the_schedule_is_due_once_per_slot(): void
    {
        Carbon::setTestNow('2026-10-25 10:00:00');
        $this->svc->saveSettings(['auto_backup_enabled' => true, 'backup_frequency' => 'daily', 'backup_time' => '02:30', 'backup_mode' => 'db_only', 'retention_days' => 3]);

        $this->assertTrue($this->svc->isDue(), 'never ran: due');
        $this->assertSame('2026-10-25 02:30:00', $this->svc->lastSlot($this->svc->settings())->toDateTimeString());
        $this->assertSame('2026-10-26 02:30:00', $this->svc->nextRun($this->svc->settings())->toDateTimeString());

        $this->artisan('backup:run', ['--scheduled' => true])->assertSuccessful();
        $this->assertCount(1, $this->svc->files());
        $this->assertFalse($this->svc->isDue(), 'served: not due again');

        $this->artisan('backup:run', ['--scheduled' => true])->assertSuccessful();
        $this->assertCount(1, $this->svc->files(), 'a second tick does not make a second backup');

        Carbon::setTestNow('2026-10-26 03:00:00');
        $this->assertTrue($this->svc->isDue(), 'next day: due again');
    }

    public function test_weekly_and_monthly_slots_and_off_switch(): void
    {
        Carbon::setTestNow('2026-10-25 10:00:00'); // a Sunday
        $weekly = ['auto_backup_enabled' => true, 'backup_frequency' => 'weekly', 'backup_time' => '00:00'];
        $this->assertSame('2026-10-23 00:00:00', $this->svc->lastSlot($weekly)->toDateTimeString()); // the Friday before

        $monthly = ['auto_backup_enabled' => true, 'backup_frequency' => 'monthly', 'backup_time' => '01:00'];
        $this->assertSame('2026-10-01 01:00:00', $this->svc->lastSlot($monthly)->toDateTimeString());

        $this->svc->saveSettings(['auto_backup_enabled' => false, 'backup_frequency' => 'daily', 'backup_time' => '00:00', 'backup_mode' => 'db_only', 'retention_days' => 3]);
        $this->assertFalse($this->svc->isDue());
        $this->assertNull($this->svc->nextRun($this->svc->settings()));
    }

    public function test_automatic_backups_are_on_by_default_and_the_page_says_when_the_scheduler_is_not_running(): void
    {
        $this->assertTrue($this->svc->settings()['auto_backup_enabled']);
        $admin = $this->admin();

        \Illuminate\Support\Facades\Cache::forget('backup:scheduler_heartbeat');
        $this->assertFalse($this->svc->schedulerAlive());
        $this->actingAs($admin)->get('/admin/backup')->assertOk()->assertSee('সিডিউলার চলছে না');

        \Illuminate\Support\Facades\Cache::put('backup:scheduler_heartbeat', time(), 600);
        $this->assertTrue($this->svc->schedulerAlive());
        $this->actingAs($admin)->get('/admin/backup')->assertOk()->assertDontSee('সিডিউলার চলছে না');

        \Illuminate\Support\Facades\Cache::put('backup:scheduler_heartbeat', time() - 3600, 600);
        $this->assertFalse($this->svc->schedulerAlive());
    }

    public function test_remote_disks_are_listed_only_when_their_credentials_are_filled_in(): void
    {
        config(['filesystems.disks.s3' => ['driver' => 's3', 'key' => null, 'secret' => null, 'bucket' => null]]);
        $this->assertNotContains('s3', $this->svc->offsiteDisks());

        config(['filesystems.disks.s3' => ['driver' => 's3', 'key' => 'k', 'secret' => 's', 'bucket' => 'b', 'region' => 'us-east-1']]);
        config(['filesystems.disks.nas' => ['driver' => 'sftp', 'host' => 'nas.local', 'username' => 'u']]);
        $this->assertEqualsCanonicalizing(['s3', 'nas'], array_values(array_intersect($this->svc->offsiteDisks(), ['s3', 'nas'])));
        $this->assertNotContains('local', $this->svc->offsiteDisks());
    }

    public function test_the_page_shows_which_channels_are_on(): void
    {
        $s = $this->svc->settings();
        $this->assertSame(['telegram' => false, 'email' => false, 'offsite' => false], $this->svc->channels($s));

        $this->svc->saveSettings(['backup_email' => 'me@example.test', 'telegram_alerts_enabled' => true, 'telegram_bot_token' => '123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ_12345', 'telegram_chat_id' => '-1001234567890', 'backup_frequency' => 'daily', 'backup_time' => '00:00', 'backup_mode' => 'db_only', 'retention_days' => 3]);
        $this->assertSame(['telegram' => true, 'email' => true, 'offsite' => false], $this->svc->channels($this->svc->settings()));
    }

    public function test_a_manual_backup_does_not_use_up_the_scheduled_one(): void
    {
        Carbon::setTestNow('2026-10-25 10:00:00');
        $this->svc->saveSettings(['auto_backup_enabled' => true, 'backup_frequency' => 'daily', 'backup_time' => '02:00', 'backup_mode' => 'db_only', 'retention_days' => 3]);

        $this->svc->run('db_only', 'manual');

        $this->assertTrue($this->svc->isDue());
    }

    // ---- settings and notifications

    public function test_the_telegram_token_is_stored_encrypted_kept_when_blank_and_never_shown(): void
    {
        $admin = $this->admin();
        $token = '123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ_12345';
        $form  = fn (array $extra = []) => $extra + ['backup_frequency' => 'daily', 'backup_time' => '01:00', 'backup_mode' => 'db_only', 'retention_days' => 3, 'offsite_disk' => 'none', 'offsite_path' => 'backups'];

        $this->actingAs($admin)->post('/admin/backup/settings', $form(['telegram_alerts_enabled' => 1, 'telegram_bot_token' => $token, 'telegram_chat_id' => '-1001234567890']))->assertSessionHasNoErrors();

        $stored = AdminDashboardSetting::where('key', 'backup_settings')->first()->value;
        $this->assertStringStartsWith('enc:', $stored['telegram_bot_token']);
        $this->assertSame($token, $this->svc->settings()['telegram_bot_token']);

        $this->actingAs($admin)->post('/admin/backup/settings', $form(['telegram_bot_token' => '', 'telegram_chat_id' => '-1001234567890']));
        $this->assertSame($token, $this->svc->settings()['telegram_bot_token']);

        $html = $this->actingAs($admin)->get('/admin/backup')->assertOk()->getContent();
        $this->assertStringNotContainsString($token, $html);

        $this->actingAs($admin)->post('/admin/backup/settings', $form(['telegram_bot_token' => 'not a token']))->assertSessionHasErrors('telegram_bot_token');
        $this->actingAs($admin)->post('/admin/backup/settings', $form(['offsite_disk' => 'nope']))->assertSessionHasErrors('offsite_disk');
        $this->actingAs($admin)->post('/admin/backup/settings', $form(['offsite_path' => '../../etc']))->assertSessionHasErrors('offsite_path');
    }

    public function test_a_finished_backup_is_announced_on_telegram_when_it_is_on(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        $this->svc->saveSettings([
            'telegram_alerts_enabled' => true, 'telegram_bot_token' => '123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ_12345', 'telegram_chat_id' => '-1001234567890',
            'backup_frequency' => 'daily', 'backup_time' => '00:00', 'backup_mode' => 'db_only', 'retention_days' => 3,
        ]);

        $this->svc->run('db_only', 'manual');

        Http::assertSent(fn ($r) => str_contains($r->url(), '/bot123456789:ABC') && str_contains($r['text'], 'ব্যাকআপ তৈরি হয়েছে'));
    }

    public function test_the_telegram_test_button_uses_the_saved_or_given_values(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);

        $this->actingAs($this->admin())->postJson('/admin/backup/test-notification', ['type' => 'telegram'])->assertStatus(422);
        $this->actingAs($this->admin())->postJson('/admin/backup/test-notification', ['telegram_bot_token' => '../evil', 'telegram_chat_id' => '1'])->assertStatus(422);
        $this->actingAs($this->admin())->postJson('/admin/backup/test-notification', ['telegram_bot_token' => '123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ_12345', 'telegram_chat_id' => '-1001234567890'])->assertOk();
    }

    public function test_emailing_a_backup_validates_the_address_and_the_size(): void
    {
        Mail::fake();
        $res  = $this->svc->create('db_only');
        $name = $res['filename'];

        $this->actingAs($this->admin())->postJson("/admin/backup/email/{$name}", ['email' => 'nope'])->assertStatus(422);
        $this->actingAs($this->admin())->postJson("/admin/backup/email/{$name}", ['email' => 'me@example.test'])->assertOk();

        config(['backup.mail_attach_max_mb' => 0]);
        $this->actingAs($this->admin())->postJson("/admin/backup/email/{$name}", ['email' => 'me@example.test'])->assertStatus(422);
    }

    // ---- masked dump

    public function test_the_masked_dump_hides_passwords_emails_and_phones(): void
    {
        $this->admin();
        $reader = User::create(['name' => 'Real Reader', 'email' => 'reader@real.example', 'phone' => '01812345678', 'password' => Hash::make('Secret#12345'), 'role' => 'customer', 'is_active' => true, 'email_verified_at' => now()]);

        $sql = File::get($this->dir . '/' . $this->svc->maskedDump());

        $this->assertStringNotContainsString('reader@real.example', $sql);
        $this->assertStringNotContainsString('01812345678', $sql);
        $this->assertStringNotContainsString($reader->password, $sql);
        $this->assertStringContainsString("dev_{$reader->id}@ideaabd.test", $sql);
        $this->assertStringContainsString("semi;colon", $sql, 'other data is untouched');
    }

    // ---- diff

    public function test_compare_counts_rows_exactly(): void
    {
        $res = $this->svc->create('db_only');
        DB::table('bk_probe')->insert(['note' => 'extra']);

        $d   = $this->svc->diff($this->dir . '/' . $res['filename']);
        $row = collect($d['tables'])->firstWhere('table', 'bk_probe');

        $this->assertSame(3, $row['live_rows']);
        $this->assertSame(2, $row['backup_rows']);
        $this->assertSame('live_higher', $row['status']);
    }

    // ---- pages and access

    public function test_the_page_shows_dynamic_values_and_no_hardcoded_hosting_claims(): void
    {
        $this->svc->saveSettings(['auto_backup_enabled' => true, 'backup_frequency' => 'weekly', 'backup_time' => '03:15', 'backup_mode' => 'db_only', 'retention_days' => 4]);
        $this->svc->create('db_only');
        config(['backup.quota_mb' => 500]);

        $html = $this->actingAs($this->admin())->get('/admin/backup')->assertOk()->getContent();

        $this->assertStringContainsString('500 MB', $html);                 // quota comes from the setting
        $this->assertStringContainsString('প্রতি সপ্তাহে · 03:15', $html);  // schedule comes from the saved settings
        $this->assertStringNotContainsString('2.0 GB Shared Hosting', $html);
        $this->assertStringNotContainsString('100% Protected', $html);
        $this->assertStringContainsString('idea_db_', $html);
    }

    public function test_only_admins_can_use_the_backup_routes_and_names_cannot_escape_the_folder(): void
    {
        $this->get('/admin/backup')->assertRedirect(route('login'));

        $customer = User::create(['name' => 'R', 'email' => 'c@example.test', 'phone' => '01700000067', 'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => 'customer', 'is_active' => true, 'email_verified_at' => now()]);
        $this->actingAs($customer)->get('/admin/backup')->assertForbidden();
        $this->actingAs($customer)->post('/admin/backup/create')->assertForbidden();

        $this->assertNull($this->svc->path('../../.env'));
        $this->assertNull($this->svc->path('..\\..\\.env'));
        $this->actingAs($this->admin())->get('/admin/backup/download/..%2F..%2F.env')->assertNotFound();
    }

    public function test_bulk_delete_and_single_delete_only_touch_backup_files(): void
    {
        $this->touchFile('idea_db_a.sql.gz', 0);
        $this->touchFile('idea_db_b.sql.gz', 0);
        $this->touchFile('keep.txt', 0);
        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/admin/backup/bulk-delete', ['filenames' => ['idea_db_a.sql.gz', 'keep.txt', '../x.sql']])->assertOk()->assertJsonPath('deleted_count', 1);
        $this->assertFileExists($this->dir . '/keep.txt');

        $this->actingAs($admin)->postJson('/admin/backup/bulk-delete', ['filenames' => 'oops'])->assertStatus(422);
        $this->actingAs($admin)->deleteJson('/admin/backup/idea_db_b.sql.gz')->assertOk();
        $this->actingAs($admin)->deleteJson('/admin/backup/idea_db_b.sql.gz')->assertStatus(404);
    }

    public function test_creating_from_the_page_returns_the_new_file(): void
    {
        $this->actingAs($this->admin())->postJson('/admin/backup/create', ['mode' => 'db_only'])
            ->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['filename', 'size']);

        $this->actingAs($this->admin())->postJson('/admin/backup/create', ['mode' => 'weird'])->assertStatus(422);
        $this->assertCount(1, $this->svc->files());
    }
}
