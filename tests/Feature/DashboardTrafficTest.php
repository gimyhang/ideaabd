<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The dashboard's traffic panel shows only what the visit log really holds: no invented live
 * numbers, no made-up countries, no random trends.
 */
class DashboardTrafficTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Traffic Admin', 'email' => 'traffic@example.test', 'phone' => '01710000022', 'password' => Hash::make('x'),
            'role' => 'admin', 'reg_type' => 'admin', 'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    private function visit(string $ip, string $country, string $code, string $device, \DateTimeInterface $at): void
    {
        VisitorLog::forceCreate([
            'ip_address' => $ip, 'url' => '/', 'country' => $country, 'country_code' => $code,
            'device' => $device, 'visited_at' => $at,
        ]);
    }

    public function test_with_no_visits_nothing_is_made_up(): void
    {
        Cache::flush();

        $html = $this->actingAs($this->admin())->get('/admin/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('No visits recorded yet.', $html);
        $this->assertMatchesRegularExpression('/id="liveTrafficCounter"[^>]*>0</', $html, 'live count is a real zero');
        foreach (['1,420', '1420', 'Saudi Arabia', 'United Arab Emirates'] as $invented) {
            $this->assertStringNotContainsString($invented, $html, "no made-up country figures ($invented)");
        }
        $this->assertStringNotContainsString('Math.random', $html);
    }

    public function test_live_count_and_country_list_come_from_the_visit_log(): void
    {
        Cache::flush();
        $now = now();
        $this->visit('1.1.1.1', 'Bangladesh', 'BD', 'mobile', $now->copy()->subMinute());
        $this->visit('1.1.1.1', 'Bangladesh', 'BD', 'mobile', $now->copy()->subMinutes(2));   // same person
        $this->visit('2.2.2.2', 'Bangladesh', 'BD', 'desktop', $now->copy()->subMinutes(3));
        $this->visit('3.3.3.3', 'United States', 'US', 'mobile', $now->copy()->subMinutes(4));
        $this->visit('4.4.4.4', 'Bangladesh', 'BD', 'mobile', $now->copy()->subHour());          // long gone

        $html = $this->actingAs($this->admin())->get('/admin/dashboard')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="liveTrafficCounter"[^>]*>3</', $html, '3 different visitors in the last 5 minutes');
        $this->assertStringContainsString('United States', $html);
        $this->assertStringContainsString('Bangladesh', $html);
        $this->assertStringContainsString('Mobile: <strong>', $html);
        $this->assertStringContainsString('<title>Bangladesh: 4 visits', $html, 'map beacon shows the real count');
        $this->assertStringNotContainsString('No visits recorded yet.', $html);
    }

    public function test_the_live_endpoint_returns_the_real_number_to_admins_only(): void
    {
        Cache::flush();
        $this->visit('9.9.9.9', 'Bangladesh', 'BD', 'mobile', now()->subMinute());

        $this->actingAs($this->admin())->getJson('/admin/dashboard/live-visitors')
            ->assertOk()->assertExactJson(['count' => 1]);

        auth()->logout();
        $this->getJson('/admin/dashboard/live-visitors')->assertStatus(401);
    }
}
