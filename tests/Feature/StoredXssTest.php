<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Blog\Models\BlogPost;
use Tests\TestCase;

/** A reader (or the admin) opening a post written by someone else must never run that writer's script. */
class StoredXssTest extends TestCase
{
    use RefreshDatabase;

    private function writer(): int
    {
        return \App\Models\User::create(['name' => 'W', 'email' => 'w@x.test', 'phone' => '01700000777', 'password' => 'x', 'role' => 'author', 'reg_type' => 'author', 'reg_status' => 'approved', 'is_active' => true])->id;
    }

    public function test_malicious_post_body_and_title_cannot_run_in_the_page(): void
    {
        $post = BlogPost::create([
            'title'       => 'Hello </script><script>alert("title")</script>',
            'slug'        => 'xss-probe',
            'author_id'   => $this->writer(),
            'content'     => '<p>Real text</p><script>alert("body")</script><img src=x onerror="alert(1)"><a href="javascript:alert(2)">click</a><iframe src="https://evil.test"></iframe><svg onload=alert(3)></svg>',
            'status'      => 'published',
            'mod_status'  => 'approved',
            'published_at' => now()->subDay(),
        ]);

        // the content is cleaned already when it is stored …
        $this->assertStringNotContainsString('<script', $post->fresh()->content);
        $this->assertStringNotContainsString('onerror', $post->fresh()->content);

        // … and the public page carries nothing executable from the writer
        $html = $this->get(route('blog.show', 'xss-probe'))->assertOk()->getContent();

        $this->assertStringContainsString('Real text', $html);
        foreach (['alert("body")', 'onerror="alert(1)"', 'javascript:alert(2)', '<iframe src="https://evil.test"', 'onload=alert(3)', 'alert("title")</script>'] as $bad) {
            $this->assertStringNotContainsString($bad, $html, "found: $bad");
        }
    }

    public function test_content_that_was_stored_before_cleaning_existed_is_cleaned_when_shown(): void
    {
        $post = BlogPost::create(['title' => 'Old', 'slug' => 'old-dirty', 'author_id' => $this->writer(), 'content' => '<p>ok</p>', 'status' => 'published', 'mod_status' => 'approved', 'published_at' => now()->subDay()]);
        // simulate a row written directly in the database (bypasses the model)
        \Illuminate\Support\Facades\DB::table('blog_posts')->where('id', $post->id)->update(['content' => '<p>ok</p><script>alert("legacy")</script><img src=x onerror=alert(9)>']);

        $html = $this->get(route('blog.show', 'old-dirty'))->assertOk()->getContent();

        $this->assertStringContainsString('ok', $html);
        $this->assertStringNotContainsString('alert("legacy")', $html);
        $this->assertStringNotContainsString('onerror=alert(9)', $html);
    }
}
