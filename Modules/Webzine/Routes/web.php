<?php

use Illuminate\Support\Facades\Route;
use Modules\Webzine\Http\Controllers\Frontend\WebzineController;

Route::prefix('webzines')->name('webzine.')->group(function () {
    Route::get('/', [WebzineController::class, 'index'])->name('index');
    Route::get('/{slug}', [WebzineController::class, 'show'])->name('show');
    Route::get('/{slug}/read', [WebzineController::class, 'read'])->name('read');
});

Route::prefix('magazines')->group(function () {
    Route::get('/', fn() => redirect('/webzines', 301))->name('magazine.index');
    Route::get('/{slug}', fn($slug) => redirect('/webzines/' . $slug, 301))->name('magazine.show');
    Route::get('/{slug}/read', fn($slug) => redirect('/webzines/' . $slug . '/read', 301))->name('magazine.read');
});
