<?php

namespace Tests\Feature;

use App\Models\LoginSecurityLog;
use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginRateLimitRecaptchaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        LoginSecurityLog::query()->delete();
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('আইডিয়া প্রকাশন ডিজিটাল পোর্টাল');
        $response->assertSee('Login / লগইন');
    }

    public function test_first_two_failed_logins_do_not_require_captcha(): void
    {
        $response1 = $this->postJson('/login', [
            'email'    => 'nonexistent@example.com',
            'password' => 'wrongpass123',
        ]);

        $response1->assertStatus(422);
        $response1->assertJson([
            'success'      => false,
            'show_captcha' => false,
            'attempts'     => 1,
        ]);

        $response2 = $this->postJson('/login', [
            'email'    => 'nonexistent@example.com',
            'password' => 'wrongpass123',
        ]);

        $response2->assertStatus(422);
        $response2->assertJson([
            'success'      => false,
            'show_captcha' => false,
            'attempts'     => 2,
        ]);
    }

    public function test_third_failed_login_triggers_recaptcha_requirement(): void
    {
        // 1st attempt
        $this->postJson('/login', [
            'email'    => 'testuser@example.com',
            'password' => 'wrongpass',
        ]);

        // 2nd attempt
        $this->postJson('/login', [
            'email'    => 'testuser@example.com',
            'password' => 'wrongpass',
        ]);

        // 3rd attempt
        $response3 = $this->postJson('/login', [
            'email'    => 'testuser@example.com',
            'password' => 'wrongpass',
        ]);

        $response3->assertStatus(422);
        $response3->assertJson([
            'success'          => false,
            'show_captcha'     => true,
            'captcha_required' => true,
            'attempts'         => 3,
        ]);

        $this->assertTrue(LoginSecurityLog::requiresCaptcha('127.0.0.1'));
    }

    public function test_fourth_login_blocked_without_recaptcha_token(): void
    {
        // Setup 3 failed attempts in DB
        LoginSecurityLog::create([
            'ip_address'        => '127.0.0.1',
            'last_username'     => 'testuser@example.com',
            'attempt_count'     => 3,
            'is_security_issue' => true,
        ]);

        $response = $this->postJson('/login', [
            'email'    => 'testuser@example.com',
            'password' => 'somepassword',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success'          => false,
            'show_captcha'     => true,
            'captcha_required' => true,
        ]);
    }

    public function test_login_rejected_when_recaptcha_verification_fails(): void
    {
        // Setup 3 failed attempts in DB
        LoginSecurityLog::create([
            'ip_address'        => '127.0.0.1',
            'last_username'     => 'testuser@example.com',
            'attempt_count'     => 3,
            'is_security_issue' => true,
        ]);

        // Mock Google Verification returning false
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false], 200),
        ]);

        $response = $this->postJson('/login', [
            'email'                => 'testuser@example.com',
            'password'             => 'somepassword',
            'g-recaptcha-response' => 'fake_invalid_token',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success'          => false,
            'show_captcha'     => true,
            'captcha_required' => true,
        ]);
    }

    public function test_successful_login_with_valid_recaptcha_resets_counter(): void
    {
        // Create user
        $user = User::create([
            'name'       => 'Real Test User',
            'email'      => 'realuser@example.com',
            'password'   => Hash::make('CorrectPassword123!'),
            'is_active'  => true,
            'role'       => 'buyer',
            'reg_status' => 'approved',
        ]);

        // Setup 3 failed attempts
        LoginSecurityLog::create([
            'ip_address'        => '127.0.0.1',
            'last_username'     => $user->email,
            'attempt_count'     => 3,
            'is_security_issue' => true,
        ]);

        // Mock Google Verification returning true
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);

        $response = $this->postJson('/login', [
            'email'                => $user->email,
            'password'             => 'CorrectPassword123!',
            'g-recaptcha-response' => 'valid_mocked_token',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Failed attempts log should be cleared
        $this->assertDatabaseMissing('login_security_logs', [
            'ip_address' => '127.0.0.1',
        ]);
    }
}
