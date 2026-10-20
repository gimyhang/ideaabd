<?php

namespace Tests\Feature;

use App\Support\HtmlSanitizer;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    /** @dataProvider attacks */
    public function test_attacks_are_neutralised(string $input, array $mustNotContain): void
    {
        $out = strtolower(HtmlSanitizer::clean($input));
        foreach ($mustNotContain as $bad) {
            $this->assertStringNotContainsString(strtolower($bad), $out, "'$bad' survived in: $out");
        }
    }

    public static function attacks(): array
    {
        return [
            'script tag'          => ['<p>Hi</p><script>alert(1)</script>', ['<script', 'alert(1)']],
            'img onerror'         => ['<img src=x onerror=alert(1)>', ['onerror', 'alert']],
            'svg onload'          => ['<svg onload=alert(1)><circle/></svg>', ['<svg', 'onload']],
            'javascript href'     => ['<a href="javascript:alert(1)">x</a>', ['javascript:']],
            'obfuscated js href'  => ['<a href="jav&#x09;ascript:alert(1)">x</a>', ['alert(1)"', 'href="jav']],
            'newline js href'     => ["<a href=\"java\nscript:alert(1)\">x</a>", ['javascript:', 'script:alert']],
            'vbscript'            => ['<a href="vbscript:msgbox(1)">x</a>', ['vbscript:']],
            'data html href'      => ['<a href="data:text/html;base64,PHNjcmlwdD4=">x</a>', ['data:text']],
            'iframe'              => ['<iframe src="https://evil.test"></iframe>text', ['<iframe']],
            'form + input'        => ['<form action="https://evil.test"><input name=pw></form>', ['<form', '<input']],
            'event on div'        => ['<div onmouseover="steal()">hover</div>', ['onmouseover', 'steal']],
            'style expression'    => ['<p style="width: expression(alert(1)); color: red">x</p>', ['expression', 'alert']],
            'style url'           => ['<p style="background: url(javascript:alert(1))">x</p>', ['javascript', 'url(']],
            'meta refresh'        => ['<meta http-equiv="refresh" content="0;url=https://evil.test">', ['<meta']],
            'link stylesheet'     => ['<link rel="stylesheet" href="https://evil.test/x.css">', ['<link']],
            'base tag'            => ['<base href="https://evil.test/"><a href="/x">x</a>', ['<base']],
            'object / embed'      => ['<object data="x.swf"></object><embed src="x.swf">', ['<object', '<embed']],
            'math / template'     => ['<math><mi xlink:href="javascript:alert(1)">x</mi></math>', ['javascript:', '<math']],
            'comment trick'       => ['<!--[if IE]><script>alert(1)</script><![endif]-->ok', ['<script', 'alert(1)']],
            'img data html'       => ['<img src="data:text/html;base64,PHNjcmlwdD4=">', ['data:text']],
            'close textarea'      => ['</textarea><script>alert(1)</script>', ['<script', 'alert(1)']],
            'entity encoded tags' => ['&lt;script&gt;alert(1)&lt;/script&gt;', ['<script']],
        ];
    }

    public function test_normal_writing_survives(): void
    {
        $html = '<h2>শিরোনাম</h2><p>প্রথম <strong>অনুচ্ছেদ</strong> ও <em>বাঁকা</em> লেখা।<br>নতুন লাইন</p><blockquote>উদ্ধৃতি</blockquote>'
            . '<ul><li>এক</li><li>দুই</li></ul><p><a href="https://example.com/x" target="_blank">লিংক</a> <a href="/books">ভেতরের</a> <a href="mailto:a@b.co">মেইল</a></p>'
            . '<p style="text-align: center; color: #333">মাঝখানে</p><img src="/images/a.jpg" alt="ছবি" width="300">';

        $out = HtmlSanitizer::clean($html);

        foreach (['<h2>শিরোনাম</h2>', '<strong>অনুচ্ছেদ</strong>', '<em>বাঁকা</em>', '<blockquote>উদ্ধৃতি</blockquote>', '<li>এক</li>', 'href="https://example.com/x"', 'href="/books"', 'href="mailto:a@b.co"', 'text-align: center', 'src="/images/a.jpg"', 'alt="ছবি"'] as $keep) {
            $this->assertStringContainsString($keep, $out, "lost: $keep");
        }
        $this->assertStringContainsString('noopener', $out);
    }

    public function test_plain_text_and_empty_values(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(null));
        $this->assertSame('', HtmlSanitizer::clean('   '));
        $this->assertStringContainsString('শুধু লেখা', HtmlSanitizer::clean('শুধু লেখা'));
    }

    public function test_base64_image_is_allowed_but_only_image_types(): void
    {
        $ok = HtmlSanitizer::clean('<img src="data:image/png;base64,iVBORw0KGgo=">');
        $this->assertStringContainsString('data:image/png;base64', $ok);

        $bad = HtmlSanitizer::clean('<img src="data:image/svg+xml;base64,PHN2Zz4=">');
        $this->assertStringNotContainsString('svg', $bad);
    }
}
