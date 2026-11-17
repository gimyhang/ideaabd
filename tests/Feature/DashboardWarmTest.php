<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardWarmTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Warm Admin', 'email' => 'warm@example.test', 'phone' => '01710000033', 'password' => Hash::make('x'),
            'role' => 'admin', 'reg_type' => 'admin', 'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    /** @return string[] cache keys the dashboard had to compute */
    private function missedDashboardKeys(User $admin): array
    {
        $missed = [];
        Event::listen(CacheMissed::class, function ($e) use (&$missed) { $missed[] = $e->key; });
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();

        return array_values(array_unique(array_filter($missed, fn ($k) => str_starts_with($k, 'admin_dash_') || $k === 'admin_pending_registrations_count')));
    }

    public function test_a_cold_dashboard_computes_its_figures_itself(): void
    {
        $admin = $this->admin();
        Cache::flush();

        $this->assertNotEmpty($this->missedDashboardKeys($admin), 'sanity: without the warm-up the page does the work');
    }

    public function test_after_the_warm_command_opening_the_dashboard_computes_nothing(): void
    {
        $admin = $this->admin();
        Cache::flush();

        $this->artisan('dashboard:warm')->assertExitCode(0);

        $this->assertSame([], $this->missedDashboardKeys($admin), 'every dashboard figure is already cached');
    }

    public function test_the_warm_command_is_scheduled_every_few_minutes(): void
    {
        $events = collect(app(Schedule::class)->events())->filter(fn ($e) => str_contains((string) $e->command, 'dashboard:warm'));

        $this->assertCount(1, $events);
        $this->assertSame('*/3 * * * *', $events->first()->expression);
        $this->assertTrue($events->first()->withoutOverlapping, 'a slow run never piles up');
    }

    public function test_the_admin_portal_link_prefetches_on_hover_and_shows_the_click_registered(): void
    {
        $html = $this->actingAs($this->admin())->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('class="md-portal md-portal--admin"', $html);
        $this->assertStringContainsString("l.rel = 'prefetch'", $html);
        $this->assertStringContainsString("'pointerenter', 'touchstart', 'focus'", $html);
        $this->assertStringContainsString("a.setAttribute('aria-busy', 'true')", $html);
    }
}
