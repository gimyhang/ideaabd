<?php

use Illuminate\Support\Facades\Route;
use Modules\Publisher\Http\Controllers\Frontend\PublisherController;

Route::prefix('publishers')->name('publisher.')->group(function () {
    Route::get('/', [PublisherController::class, 'index'])->name('index');
    Route::get('/{slug}', [PublisherController::class, 'show'])->name('show');
});
