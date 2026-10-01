<?php

use Illuminate\Support\Facades\Route;
use Modules\SEO\Http\Controllers\Admin\AdminSeoController;

Route::get('/', [AdminSeoController::class, 'index'])->name('index');
Route::get('/scanner', [AdminSeoController::class, 'scanner'])->name('scanner');
Route::post('/scan-ajax', [AdminSeoController::class, 'scanAjax'])->name('scan-ajax');
Route::post('/batch-scan', [AdminSeoController::class, 'batchScan'])->name('batch-scan');
Route::get('/pages', [AdminSeoController::class, 'pages'])->name('pages');
Route::post('/pages/update', [AdminSeoController::class, 'updatePage'])->name('pages.update');
Route::get('/sitemap-manager', [AdminSeoController::class, 'sitemap'])->name('sitemap');
Route::post('/ping-search-engines', [AdminSeoController::class, 'pingSearchEngines'])->name('ping');
Route::get('/redirects', [AdminSeoController::class, 'redirects'])->name('redirects');
Route::post('/redirects/store', [AdminSeoController::class, 'storeRedirect'])->name('redirects.store');
Route::delete('/redirects/{id}', [AdminSeoController::class, 'deleteRedirect'])->name('redirects.delete');
Route::get('/broken-links', [AdminSeoController::class, 'brokenLinks'])->name('broken-links');
Route::post('/broken-links/{id}/resolve', [AdminSeoController::class, 'resolveBrokenLink'])->name('broken-links.resolve');
Route::put('/update/{id}', [AdminSeoController::class, 'update'])->name('update');
