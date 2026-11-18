<?php

use Illuminate\Support\Facades\Route;
use Modules\SEO\Http\Controllers\Frontend\SitemapController;

Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('seo.robots');

// llms.txt: a short plain-text guide for AI assistants/crawlers (was a frequent 404).
Route::get('/llms.txt', function () {
    $name = \App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
    $links = [
        'বই (Books)'          => url('/books'),
        'ই-বুক (E-books)'     => url('/ebooks'),
        'লেখক (Authors)'      => url('/authors'),
        'ব্লগ (Blog)'         => url('/blog'),
        'ওয়েবজিন (Webzines)' => url('/webzines'),
        'অর্ডার ট্র্যাকিং'     => url('/track-order'),
        'Sitemap'             => url('/sitemap.xml'),
    ];
    $lines = ["# {$name}", '', "> {$name} — বাংলাদেশের একটি প্রকাশনা সংস্থা: বই, ই-বুক, লেখক ও পাঠকের জন্য অনলাইন বইয়ের দোকান।", '', '## Main sections'];
    foreach ($links as $label => $u) {
        $lines[] = "- [{$label}]({$u})";
    }
    $lines[] = '';
    $lines[] = '## Contact';
    $lines[] = '- Phone: ' . \App\Support\SiteSetting::helplinePhone();
    $lines[] = '- Email: ' . \App\Support\SiteSetting::helplineEmail();

    return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=utf-8', 'Cache-Control' => 'public, max-age=86400']);
})->name('seo.llms');

// IndexNow Verification Key Endpoint
Route::get('/indexnow-key.txt', function () {
    $host = parse_url(url('/'), PHP_URL_HOST);
    $key = md5($host . 'ideaabd_seo_key');
    return response($key, 200, ['Content-Type' => 'text/plain']);
})->name('seo.indexnow-key');
