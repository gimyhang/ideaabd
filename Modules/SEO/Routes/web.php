<?php

use Illuminate\Support\Facades\Route;
use Modules\SEO\Http\Controllers\Frontend\SitemapController;

Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('seo.robots');

// IndexNow Verification Key Endpoint
Route::get('/indexnow-key.txt', function () {
    $host = parse_url(url('/'), PHP_URL_HOST);
    $key = md5($host . 'ideaabd_seo_key');
    return response($key, 200, ['Content-Type' => 'text/plain']);
})->name('seo.indexnow-key');
