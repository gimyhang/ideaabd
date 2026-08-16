<?php

use Illuminate\Support\Facades\Route;
use Modules\Ebook\Http\Controllers\Frontend\EbookController;

Route::prefix('ebooks')->name('ebook.')->group(function () {
    Route::get('/', [EbookController::class, 'index'])->name('index');
    Route::get('/{slug}', [EbookController::class, 'show'])->name('show');
    Route::get('/{slug}/read', [EbookController::class, 'read'])->name('read');
    Route::get('/{slug}/download', [EbookController::class, 'download'])->name('download');
});

Route::get('/ebook', fn () => redirect()->route('ebook.index'));
