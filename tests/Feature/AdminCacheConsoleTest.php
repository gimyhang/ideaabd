<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCacheConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_run_a_safe_allowlisted_artisan_command(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson(route('admin.cache.run-artisan'), ['command' => 'about'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('command', 'about');
    }

    public function test_console_rejects_shell_and_unapproved_commands(): void
    {
        $admin = $this->admin();

        foreach (['git status', 'git push', 'cache:clear && whoami', 'route:list'] as $command) {
            $this->actingAs($admin)
                ->postJson(route('admin.cache.run-artisan'), ['command' => $command])
                ->assertForbidden()
                ->assertJsonPath('success', false);
        }
    }

    public function test_console_requires_a_string_command(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson(route('admin.cache.run-artisan'), ['command' => ['about']])
            ->assertUnprocessable();
    }

    public function test_console_page_only_offers_approved_commands(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.cache.manage'))
            ->assertOk()
            ->assertSee('Restricted Artisan Console')
            ->assertSee('value="about"', false)
            ->assertDontSee('customArtisanCmd');
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Cache Console Admin',
            'email' => 'cache-console-admin@example.test',
            'phone' => '01710000001',
            'password' => Hash::make('test-password'),
            'role' => 'admin',
            'reg_type' => 'admin',
            'reg_status' => 'approved',
            'is_active' => true,
        ]);
    }
}
