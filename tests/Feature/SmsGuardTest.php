<?php

namespace Tests\Feature;

use App\Models\SmsBalanceSnapshot;
use App\Models\SmsLog;
use App\Models\SmsNumberRestriction;
use App\Models\User;
use App\Services\SmsGuard;
use App\Services\SmsService;
use App\Support\PhoneNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsGuardTest extends TestCase
{
    use RefreshDatabase;

    private const REAL = '01839274615';

    protected function setUp(): void
    {
        parent::setUp();
        config(['sms.guard.test_mode' => true, 'sms.guard.enabled' => true, 'sms.guard.allowed_numbers' => []]);
        Cache::flush();
        $this->gateway(['*bulksmsbd.net/api/smsapi*' => Http::response(['response_code' => 202, 'success_message' => 'SMS Submitted Successfully'], 200)]);
    }

    /** Http::fake() keeps the first matching stub, so a test that needs another answer starts from a fresh client. */
    private function gateway(array $stubs): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake($stubs);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function otp(string $phone = self::REAL): array
    {
        return SmsService::send($phone, 'ideaabd.com: Your account verification code is 482913', ['source' => 'system', 'purpose' => 'otp']);
    }

    public function test_fake_and_malformed_numbers_are_recognised(): void
    {
        foreach (['01711000001', '01711000009', '01712345678', '01912345678', '01700000000', '01711111111', '01987654321', '01212121212', '0171234', 'abcdef', '01012345678'] as $fake) {
            $this->assertNotNull(SmsGuard::inspectNumber(PhoneNormalizer::normalize($fake)), "$fake should be refused");
        }
        foreach (['01839274615', '01726976982', '01555123987', '+8801911987345', '8801611203948'] as $real) {
            $this->assertNull(SmsGuard::inspectNumber(PhoneNormalizer::normalize($real)), "$real should pass");
        }
    }

    public function test_a_fake_number_never_reaches_the_gateway_and_is_written_to_the_history(): void
    {
        $res = SmsService::send('01711000001', 'hello');

        $this->assertFalse($res['success']);
        $this->assertSame('fake_number', $res['code']);
        $this->assertNotEmpty($res['message']);
        Http::assertNothingSent();
        $this->assertDatabaseHas('sms_logs', ['phone' => '8801711000001', 'status' => 'rejected', 'reason' => 'fake_number']);
    }

    public function test_a_real_number_is_sent_and_logged_with_cost(): void
    {
        $res = SmsService::send(self::REAL, 'আইডিয়া প্রকাশন: আপনার অর্ডার নিশ্চিত হয়েছে।');

        $this->assertTrue($res['success']);
        $this->assertSame(1, $res['sent']);
        $log = SmsLog::firstOrFail();
        $this->assertSame('sent', $log->status);
        $this->assertSame('8801839274615', $log->phone);
        $this->assertGreaterThan(0, (float) $log->cost);
    }

    public function test_the_third_message_in_a_day_locks_the_number_for_24_hours(): void
    {
        $this->assertTrue($this->otp()['success']);
        $this->assertTrue($this->otp()['success']);

        $third = $this->otp();
        $this->assertFalse($third['success']);
        $this->assertSame('restricted', $third['code']);
        $this->assertStringContainsString('ঘণ্টা', $third['message']);

        // still locked a minute later, and only the first two ever reached the gateway
        $this->assertFalse($this->otp()['success']);
        Http::assertSentCount(2);

        $row = SmsNumberRestriction::where('phone', '8801839274615')->firstOrFail();
        $this->assertTrue($row->isRestricted());
        $this->assertFalse($row->isBlocked());
    }

    public function test_the_number_works_again_after_24_hours(): void
    {
        $this->otp();
        $this->otp();
        $this->assertFalse($this->otp()['success']);

        Carbon::setTestNow(now()->addHours(25));

        $this->assertTrue($this->otp()['success']);
    }

    public function test_repeating_it_on_three_different_days_blocks_otp_for_the_number(): void
    {
        foreach ([0, 1, 2] as $day) {
            $this->assertTrue($this->otp()['success'], "day {$day} first");
            $this->assertTrue($this->otp()['success'], "day {$day} second");
            $this->assertFalse($this->otp()['success'], "day {$day} third");
            Carbon::setTestNow(now()->addHours(25));
        }

        $row = SmsNumberRestriction::where('phone', '8801839274615')->firstOrFail();
        $this->assertTrue($row->isBlocked());

        $next = $this->otp();
        $this->assertFalse($next['success']);
        $this->assertSame('number_blocked', $next['code']);

        // a blocked number is only cut off from OTP, not from an order notice
        $this->assertTrue(SmsService::send(self::REAL, 'Your order has shipped', ['purpose' => 'notification'])['success']);
    }

    public function test_two_days_of_abuse_do_not_block(): void
    {
        foreach ([0, 1] as $day) {
            $this->otp();
            $this->otp();
            $this->otp();
            Carbon::setTestNow(now()->addHours(25));
        }

        $this->assertFalse(SmsNumberRestriction::first()->isBlocked());
        $this->assertTrue($this->otp()['success']);
    }

    public function test_an_admin_releasing_a_number_restarts_its_count(): void
    {
        $this->otp();
        $this->otp();
        $this->assertFalse($this->otp()['success']);

        SmsGuard::release('8801839274615');

        $this->assertTrue($this->otp()['success']);
        $this->assertTrue($this->otp()['success']);
        $this->assertFalse($this->otp()['success']);
    }

    public function test_admin_sends_skip_the_daily_limit_but_not_the_fake_number_check(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue(SmsService::send(self::REAL, 'Campaign ' . $i, ['source' => 'admin', 'purpose' => 'campaign'])['success']);
        }
        $this->assertSame(5, SmsLog::where('status', 'sent')->count());

        $res = SmsService::send(['01711000001', self::REAL], 'Campaign', ['source' => 'admin']);
        $this->assertTrue($res['success']);
        $this->assertSame(1, $res['sent']);
        $this->assertCount(1, $res['denied']);
    }

    public function test_the_same_person_written_three_ways_is_sent_once(): void
    {
        $res = SmsService::send(['01839274615', '+8801839274615', '8801839274615'], 'Notice', ['source' => 'admin']);

        $this->assertSame(1, $res['sent']);
        $this->assertSame(1, SmsLog::count());
    }

    public function test_allowed_numbers_skip_every_check(): void
    {
        config(['sms.guard.allowed_numbers' => ['01711000001']]);

        for ($i = 0; $i < 4; $i++) {
            $this->assertTrue(SmsService::send('01711000001', 'dev test')['success']);
        }
    }

    public function test_the_otp_code_is_not_kept_in_the_history(): void
    {
        $this->otp();

        $this->assertStringNotContainsString('482913', (string) SmsLog::first()->message);
    }

    public function test_a_gateway_error_is_logged_as_failed_and_does_not_count_against_the_limit(): void
    {
        $this->gateway(['*bulksmsbd.net/api/smsapi*' => Http::response(['response_code' => 1003, 'error_message' => 'Insufficient balance'], 200)]);

        $this->assertFalse($this->otp()['success']);
        $this->assertFalse($this->otp()['success']);
        $this->assertSame(2, SmsLog::where('status', 'failed')->count());

        $this->gateway(['*bulksmsbd.net/api/smsapi*' => Http::response(['response_code' => 202], 200)]);
        $this->assertTrue($this->otp()['success']);
    }

    public function test_a_server_error_is_not_retried_to_avoid_sending_twice(): void
    {
        $this->gateway(['*bulksmsbd.net/api/smsapi*' => Http::response('oops', 500)]);

        SmsService::send(self::REAL, 'x', ['source' => 'admin']);

        Http::assertSentCount(1);
    }

    public function test_many_to_many_checks_each_number(): void
    {
        $this->gateway(['*bulksmsbd.net/api/smsapimany*' => Http::response(['response_code' => 202], 200)]);

        $res = SmsService::sendManyToMany([
            ['to' => self::REAL, 'message' => 'Hi Rahim'],
            ['to' => '01711000001', 'message' => 'Hi Fake'],
        ], ['source' => 'admin']);

        $this->assertTrue($res['success']);
        $this->assertSame(1, $res['total_sent']);
        $this->assertCount(1, $res['denied']);
    }

    public function test_balance_changes_are_recorded_as_topups_and_usage(): void
    {
        $fake = fn (float $b) => $this->gateway(['*getBalanceApi*' => Http::response(['response_code' => 202, 'balance' => $b], 200)]);

        $fake(500);
        SmsService::checkBalance(true);
        $fake(480);
        SmsService::checkBalance(true);
        $fake(2480);
        SmsService::checkBalance(true);
        SmsService::checkBalance(true); // unchanged: nothing new

        $this->assertSame(['initial', 'usage', 'topup'], SmsBalanceSnapshot::orderBy('id')->pluck('type')->all());
        $this->assertEquals(2000, SmsBalanceSnapshot::latest('id')->first()->delta);
    }

    public function test_a_balance_failure_is_not_cached_and_reports_no_balance(): void
    {
        $this->gateway(['*getBalanceApi*' => Http::response('', 500)]);
        $bad = SmsService::checkBalance(true);
        $this->assertNull($bad['balance']);

        $this->gateway(['*getBalanceApi*' => Http::response(['response_code' => 202, 'balance' => 321], 200)]);
        $this->assertEquals(321, SmsService::checkBalance()['balance']);
    }

    // ---- admin dashboard ----

    private function admin(): User
    {
        return User::create([
            'name' => 'Root', 'email' => 'sms-admin@example.test', 'phone' => '01700000077',
            'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    public function test_the_dashboard_shows_balance_history_and_protection_and_hides_secrets(): void
    {
        $this->gateway(['*getBalanceApi*' => Http::response(['response_code' => 202, 'balance' => 1234], 200)]);
        $this->otp();
        $this->otp();
        $this->otp(); // gets restricted

        $html = $this->actingAs($this->admin())->get('/admin/sms')->assertOk()->getContent();

        $this->assertStringContainsString('1,234', $html);
        $this->assertStringContainsString('ট্রানজেকশন হিস্টোরি', $html);
        $this->assertStringContainsString('01839274615', $html);
        $this->assertStringContainsString('সীমিত ও ব্লক করা নম্বর', $html);
        $this->assertStringNotContainsString(SmsService::DEFAULT_API_KEY, $html);
    }

    public function test_the_history_can_be_filtered_and_exported_and_csv_formulas_are_neutralised(): void
    {
        SmsService::send(self::REAL, '=HYPERLINK("http://evil")', ['source' => 'admin']);
        SmsService::send('01711000001', 'x', ['source' => 'admin']);
        $admin = $this->admin();

        $page = $this->actingAs($admin)->get('/admin/sms?status=rejected')->assertOk()->getContent();
        $this->assertStringContainsString('01711000001', $page);
        $this->assertStringNotContainsString('HYPERLINK', $page);

        $csv = $this->actingAs($admin)->get('/admin/sms/transactions/export')->assertOk()->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString(",=HYPERLINK", $csv);
    }

    public function test_admin_can_block_and_release_a_number(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/sms/restrictions/block', ['phone' => self::REAL, 'reason' => 'abuse'])->assertRedirect();
        $this->assertFalse($this->otp()['success']);

        $this->actingAs($admin)->post('/admin/sms/restrictions/release', ['phone' => self::REAL])->assertRedirect();
        $this->assertTrue($this->otp()['success']);
    }

    public function test_the_new_sms_routes_need_the_right_permission(): void
    {
        $perm = fn (string $route, string $method = 'POST') => \App\Http\Middleware\RoleMiddleware::permissionFor($route, $method);

        $this->assertSame('support.broadcast', $perm('admin.sms.transactions.export', 'GET'));
        $this->assertSame('settings.manage', $perm('admin.sms.restrictions.release'), 'releasing a locked number is a settings-level action');
        $this->assertSame('settings.manage', $perm('admin.sms.restrictions.block'));
        $this->assertSame('support.broadcast', $perm('admin.sms.broadcast'));
    }

    public function test_only_admins_can_reach_the_sms_pages(): void
    {
        $this->get('/admin/sms')->assertRedirect(route('login'));

        $customer = User::create([
            'name' => 'Reader', 'email' => 'reader@example.test', 'phone' => '01700000078', 'password' => Hash::make('Zx9#Tq!mW4$vLr7'),
            'role' => 'customer', 'is_active' => true, 'email_verified_at' => now(),
        ]);
        $this->actingAs($customer)->get('/admin/sms')->assertForbidden();
        $this->actingAs($customer)->post('/admin/sms/restrictions/release', ['phone' => self::REAL])->assertForbidden();
    }

    public function test_the_api_key_and_smtp_password_are_stored_encrypted_and_kept_when_left_blank(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/sms/settings', [
            'provider' => 'bulksmsbd', 'url' => 'https://bulksmsbd.net/api/smsapi', 'api_key' => 'SECRETKEY12345', 'sender_id' => 'IDEA',
        ])->assertSessionHasNoErrors();

        $stored = \App\Models\AdminDashboardSetting::where('key', 'sms_gateway_settings')->first()->value['api_key'];
        $this->assertStringStartsWith('enc:', $stored);
        $this->assertSame('SECRETKEY12345', SmsService::getCredentials()['api_key']);

        $this->actingAs($admin)->post('/admin/sms/settings', [
            'provider' => 'bulksmsbd', 'url' => 'https://bulksmsbd.net/api/smsapi', 'api_key' => '', 'sender_id' => 'NEWID',
        ])->assertSessionHasNoErrors();
        $this->assertSame('SECRETKEY12345', SmsService::getCredentials()['api_key']);
        $this->assertSame('NEWID', SmsService::getCredentials()['sender_id']);

        $this->actingAs($admin)->post('/admin/sms/settings', [
            'provider' => 'bulksmsbd', 'url' => 'http://insecure.example/api', 'api_key' => 'x', 'sender_id' => 'IDEA',
        ])->assertSessionHasErrors('url');
    }

    public function test_only_otp_counts_toward_the_two_a_day_lock_by_default(): void
    {
        $notice = fn () => SmsService::send(self::REAL, 'Welcome to Idea Prokashon', ['source' => 'system', 'purpose' => 'notification']);

        $this->assertTrue($notice()['success']);
        $this->assertTrue($notice()['success']);
        $this->assertTrue($this->otp()['success']);
        $this->assertTrue($this->otp()['success']);
        $this->assertFalse($this->otp()['success']); // 3rd OTP locks OTP …
        $this->assertTrue($notice()['success']);      // … but not order or welcome messages
    }

    public function test_non_otp_messages_have_a_looser_cap_that_stops_flooding_a_number(): void
    {
        $notice = fn () => SmsService::send(self::REAL, 'Your order has been placed', ['source' => 'system', 'purpose' => 'notification']);

        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($notice()['success'], "notice {$i}");
        }
        $sixth = $notice();
        $this->assertFalse($sixth['success']);
        $this->assertSame('daily_limit', $sixth['code']);
        $this->assertNull(SmsNumberRestriction::first(), 'a notice cap never locks the number');
    }

    public function test_the_welcome_message_goes_to_a_number_only_once(): void
    {
        $welcome = fn () => SmsService::send(self::REAL, 'Welcome to Idea Prokashon! Your account is ready.', ['source' => 'system']);

        $this->assertTrue($welcome()['success']);
        $second = $welcome();
        $this->assertFalse($second['success']);
        $this->assertSame('welcome_sent', $second['code']);

        Carbon::setTestNow(now()->addDays(40));
        $this->assertFalse($welcome()['success'], 'once means once, not once a day');
        Http::assertSentCount(1);
    }

    public function test_order_messages_go_every_time_even_when_otp_is_locked_or_blocked(): void
    {
        $order = fn (string $no) => SmsService::sendOrderPlacementSms(self::REAL, $no, 'Rahim', 450.0);

        // every new order gets its message, however many a day, with no limit
        for ($i = 1; $i <= 12; $i++) {
            $this->assertTrue($order("IDP-{$i}")['success'], "order {$i}");
        }

        $this->otp();
        $this->otp();
        $this->assertFalse($this->otp()['success']);
        SmsGuard::blockNumber('8801839274615');

        $this->assertTrue($order('IDP-13')['success']);
        $this->assertTrue(SmsService::send(self::REAL, 'আপনার অর্ডার #IDP-1002 শিপ করা হয়েছে', ['source' => 'system'])['success']);
        $this->assertSame('order', SmsLog::where('message', 'like', '%IDP-1002%')->value('purpose'));
    }

    public function test_one_order_gets_one_message(): void
    {
        $first = SmsService::sendOrderPlacementSms(self::REAL, 'IDP-2001', 'Rahim', 450.0);
        $again = SmsService::sendOrderPlacementSms(self::REAL, 'IDP-2001', 'Rahim', 450.0); // confirmed twice / double click
        $other = SmsService::sendOrderPlacementSms(self::REAL, 'IDP-2002', 'Rahim', 450.0);

        $this->assertTrue($first['success']);
        $this->assertFalse($again['success']);
        $this->assertSame('order_sent', $again['code']);
        $this->assertTrue($other['success']);
        Http::assertSentCount(2);

        // a different status of the same order is its own message, but not twice
        $this->assertTrue(SmsService::sendOrderNotification(self::REAL, 'IDP-2001', 'শিপ করা হয়েছে')['success']);
        $this->assertFalse(SmsService::sendOrderNotification(self::REAL, 'IDP-2001', 'শিপ করা হয়েছে')['success']);
    }

    public function test_the_order_otp_still_follows_the_otp_rule_not_the_order_rule(): void
    {
        $orderOtp = fn () => \App\Services\CommunicationService::sendOrderOtp(['phone' => self::REAL], '482913', 'IDP-3001', 'sms');

        $this->assertTrue($orderOtp()['success']);
        $this->assertTrue($orderOtp()['success']);
        $this->assertFalse($orderOtp()['success']); // 3rd OTP in a day is locked
        $this->assertSame(2, SmsLog::where('purpose', 'otp')->where('status', 'sent')->count());
    }

    public function test_scope_all_applies_the_two_a_day_lock_to_every_automatic_message(): void
    {
        config(['sms.guard.limit_scope' => 'all']);
        $notice = fn () => SmsService::send(self::REAL, 'Welcome', ['source' => 'system', 'purpose' => 'notification']);

        $this->assertTrue($notice()['success']);
        $this->assertTrue($notice()['success']);
        $this->assertSame('restricted', $notice()['code']);
    }

    public function test_an_admin_broadcast_runs_after_the_response_and_is_recorded(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/sms/broadcast', ['target_group' => 'custom', 'custom_numbers' => "01839274615, 01711000001\n+8801839274615", 'message' => 'বইমেলা শুরু'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, SmsLog::where('status', 'sent')->count());
        $this->assertSame(1, SmsLog::where('status', 'rejected')->count());

        $campaign = \App\Models\AdminDashboardSetting::where('key', 'messaging_campaign_logs')->first()->value[0];
        $this->assertSame(2, $campaign['total']);   // the +880 spelling is the same person
        $this->assertSame(1, $campaign['sent']);
        $this->assertSame(1, $campaign['refused']);
    }

    public function test_the_settings_warn_while_the_built_in_default_key_is_in_use(): void
    {
        $admin = $this->admin();
        $save  = fn (string $key) => $this->actingAs($admin)->post('/admin/sms/settings', [
            'provider' => 'bulksmsbd', 'url' => 'https://bulksmsbd.net/api/smsapi', 'api_key' => $key, 'sender_id' => 'IDEA',
        ]);
        $warned = fn () => str_contains($this->actingAs($admin)->get('/admin/sms')->getContent(), 'ডিফল্ট API Key');

        $save(SmsService::DEFAULT_API_KEY);
        $this->assertTrue($warned());

        $save('MYOWNKEY123456');
        $this->assertFalse($warned());
    }
}
