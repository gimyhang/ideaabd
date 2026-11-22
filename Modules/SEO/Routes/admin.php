<?php

use Illuminate\Support\Facades\Route;
use Modules\SEO\Http\Controllers\Admin\AdminSeoController;

Route::get('/', [AdminSeoController::class, 'index'])->name('index');
Route::get('/scanner', [AdminSeoController::class, 'scanner'])->name('scanner');
Route::post('/scan-ajax', [AdminSeoController::class, 'scanAjax'])->name('scan-ajax');
Route::post('/batch-scan', [AdminSeoController::class, 'batchScan'])->name('batch-scan');
Route::get('/scan-plan', [AdminSeoController::class, 'scanPlan'])->name('scan-plan');
Route::post('/scan-step', [AdminSeoController::class, 'scanStep'])->middleware('throttle:120,1')->name('scan-step');
Route::post('/analyze', [AdminSeoController::class, 'analyze'])->middleware('throttle:120,1')->name('analyze');
Route::post('/fix/{id}', [AdminSeoController::class, 'fix'])->whereNumber('id')->middleware('throttle:60,1')->name('fix');
Route::get('/pages', [AdminSeoController::class, 'pages'])->name('pages');
Route::post('/pages/update', [AdminSeoController::class, 'updatePage'])->name('pages.update');
Route::get('/sitemap-manager', [AdminSeoController::class, 'sitemap'])->name('sitemap');
Route::get('/sitemap-data', [AdminSeoController::class, 'sitemapData'])->name('sitemap.data');
Route::post('/sitemap-regenerate', [AdminSeoController::class, 'regenerateSitemap'])->middleware('throttle:6,1')->name('sitemap.regenerate');
Route::post('/ping-search-engines', [AdminSeoController::class, 'pingSearchEngines'])->name('ping');
Route::get('/redirects', [AdminSeoController::class, 'redirects'])->name('redirects');
Route::post('/redirects/store', [AdminSeoController::class, 'storeRedirect'])->name('redirects.store');
Route::delete('/redirects/{id}', [AdminSeoController::class, 'deleteRedirect'])->name('redirects.delete');
Route::get('/broken-links', [AdminSeoController::class, 'brokenLinks'])->name('broken-links');
Route::post('/broken-links/bulk', [AdminSeoController::class, 'bulkBrokenLinks'])->name('broken-links.bulk');
Route::post('/broken-links/{id}/resolve', [AdminSeoController::class, 'resolveBrokenLink'])->name('broken-links.resolve');
Route::put('/update/{id}', [AdminSeoController::class, 'update'])->name('update');
