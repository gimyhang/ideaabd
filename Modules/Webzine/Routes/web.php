<?php

use Illuminate\Support\Facades\Route;
use Modules\Webzine\Http\Controllers\Frontend\WebzineController;

Route::prefix('webzines')->name('webzine.')->group(function () {
    Route::get('/', [WebzineController::class, 'index'])->name('index');
    Route::get('/{slug}', [WebzineController::class, 'show'])->name('show');
    Route::get('/{slug}/read', [WebzineController::class, 'read'])->name('read');
});

Route::prefix('magazines')->name('magazine.')->group(function () {
    Route::get('/', [WebzineController::class, 'index'])->name('index');
    Route::get('/{slug}', [WebzineController::class, 'show'])->name('show');
    Route::get('/{slug}/read', [WebzineController::class, 'read'])->name('read');
});
