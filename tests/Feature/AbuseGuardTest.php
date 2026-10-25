<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AbuseGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['security.test_mode' => true, 'security.enabled' => true]);
        Cache::flush();
        RateLimiter::clear('x');
    }

    public function test_vulnerability_scanners_and_scrapers_are_refused_but_search_engines_are_not(): void
    {
        $this->withHeader('User-Agent', 'sqlmap/1.7')->get('/')->assertForbidden();
        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; AhrefsBot/7.0)')->get('/')->assertForbidden();

        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')->get('/')->assertOk();
        $this->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/126.0')->get('/')->assertOk();
    }

    public function test_probing_for_missing_files_gets_the_ip_blocked(): void
    {
        foreach (['/wp-login.php', '/.env', '/phpmyadmin/index.php', '/xmlrpc.php'] as $path) {
            $this->withHeader('User-Agent', 'Mozilla/5.0')->get($path)->assertNotFound();
        }
        // after the strikes the whole IP is shut out for a while
        $this->withHeader('User-Agent', 'Mozilla/5.0')->get('/')->assertForbidden();
    }

    public function test_downloading_a_backup_dump_is_not_a_probe_and_never_blocks_the_ip(): void
    {
        $h = ['User-Agent' => 'Mozilla/5.0'];

        // more requests than the strike limit, for a file name that ends in .sql
        for ($i = 0; $i < 8; $i++) {
            $this->withHeaders($h)->get('/admin/backup/download/idea_anonymized_2026-10-25_15-44-38.sql')->assertRedirect(route('login'));
        }

        $this->assertFalse(Cache::has('abuse:block:127.0.0.1'));
        $this->withHeaders($h)->get('/')->assertOk();

        // a real probe for a dump in the web root still counts
        $this->withHeaders($h)->get('/dump.sql')->assertNotFound();
    }

    public function test_real_pages_are_not_mistaken_for_probes(): void
    {
        $this->withHeader('User-Agent', 'Mozilla/5.0')->get('/books')->assertOk();
        $this->withHeader('User-Agent', 'Mozilla/5.0')->get('/blog')->assertOk();
        $this->assertFalse(Cache::has('abuse:block:127.0.0.1'));
    }

    public function test_sms_code_requests_are_limited_per_ip_and_per_phone(): void
    {
        $h = ['User-Agent' => 'Mozilla/5.0', 'Accept' => 'application/json'];

        // per IP: 4 a minute
        for ($i = 1; $i <= 4; $i++) {
            $this->withHeaders($h)->post('/register/send-otp', ['phone' => '0171100000' . $i]);
        }
        $this->withHeaders($h)->post('/register/send-otp', ['phone' => '01711000009'])->assertStatus(429);
    }

    public function test_one_phone_number_cannot_be_flooded_with_codes(): void
    {
        $h = ['User-Agent' => 'Mozilla/5.0', 'Accept' => 'application/json'];
        $target = 'abuse:otp:target:' . sha1('01733445566');
        RateLimiter::hit($target, 86400);
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($target, 86400);
        }

        $this->withHeaders($h)->post('/register/send-otp', ['phone' => '01733445566'])->assertStatus(429);
    }

    public function test_the_whole_site_is_rate_limited_per_ip(): void
    {
        config(['security.global_per_minute' => 5]);
        $h = ['User-Agent' => 'Mozilla/5.0'];

        for ($i = 0; $i < 5; $i++) {
            $this->withHeaders($h)->get('/books')->assertOk();
        }
        $this->withHeaders($h)->get('/books')->assertStatus(429);
    }

    public function test_robot_form_posts_are_dropped_silently(): void
    {
        $h = ['User-Agent' => 'Mozilla/5.0'];

        // honeypot filled in
        $this->withHeaders($h)->post(route('contact.submit'), ['name' => 'Bot', 'email' => 'b@x.test', 'message' => 'spam', 'website_url' => 'http://spam.test'])
            ->assertSessionHas('success', 'ধন্যবাদ।');

        // submitted in the same second the form was served
        $this->withHeaders($h)->post(route('contact.submit'), ['name' => 'Bot', 'email' => 'b@x.test', 'message' => 'spam', '_ts' => Crypt::encryptString((string) time())])
            ->assertSessionHas('success', 'ধন্যবাদ।');

        // a person who took a few seconds is let through to normal validation
        $res = $this->withHeaders($h)->post(route('contact.submit'), ['_ts' => Crypt::encryptString((string) (time() - 30))]);
        $res->assertSessionHasErrors();
    }

    public function test_forms_carry_the_trap_fields(): void
    {
        $html = $this->withHeader('User-Agent', 'Mozilla/5.0')->get(route('contact'))->assertOk()->getContent();
        $this->assertStringContainsString('name="website_url"', $html);
        $this->assertStringContainsString('name="_ts"', $html);
    }
}
