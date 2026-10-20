<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Blog\Models\BlogPost;
use Tests\TestCase;

class BlogAdminUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_admin_renders_tabs_live_region_and_compact_actions(): void
    {
        $admin = User::create(['name' => 'Root', 'email' => 'r@x.test', 'phone' => '01728976982', 'password' => 'x', 'role' => 'admin', 'reg_type' => 'admin', 'reg_status' => 'approved', 'is_active' => true]);
        $author = User::create(['name' => 'W', 'email' => 'w@x.test', 'phone' => '01700000777', 'password' => 'x', 'role' => 'author', 'reg_type' => 'author', 'reg_status' => 'approved', 'is_active' => true]);
        BlogPost::create(['title' => 'First post', 'slug' => 'first-post', 'author_id' => $author->id, 'content' => '<p>x</p>', 'status' => 'published', 'mod_status' => 'approved', 'published_at' => now()]);
        BlogPost::create(['title' => 'Waiting post', 'slug' => 'waiting-post', 'author_id' => $author->id, 'content' => '<p>y</p>', 'status' => 'pending', 'mod_status' => 'pending']);

        $html = $this->actingAs($admin)->get(route('admin.blog'))->assertOk()->getContent();
        foreach (['id="blogTabs"', 'id="blogLive"', 'data-status="pending"', 'adm-more', 'First post', 'blog-admin.js'] as $needle) {
            $this->assertStringContainsString($needle, $html, "missing $needle");
        }

        // filtering by tab returns only that status, and the same markup the live script swaps in
        $pending = $this->actingAs($admin)->get(route('admin.blog', ['status' => 'pending']))->assertOk()->getContent();
        $this->assertStringContainsString('Waiting post', $pending);
        $this->assertStringNotContainsString('First post', $pending);
        $this->assertStringContainsString('id="blogLive"', $pending);

        $search = $this->actingAs($admin)->get(route('admin.blog', ['search' => 'First']))->assertOk()->getContent();
        $this->assertStringContainsString('First post', $search);
        $this->assertStringNotContainsString('Waiting post', $search);
    }
}
