<?php

use Modules\Tag\Http\Controllers\Frontend\TagController;

Route::middleware('api')->prefix('api/tags')->group(function () {
    Route::get('/search', [TagController::class, 'search']);
    Route::get('/popular', [TagController::class, 'popular']);
    Route::get('/cloud', [TagController::class, 'cloud']);
    Route::get('/{tag:slug}', [TagController::class, 'show']);
    Route::get('/', [TagController::class, 'index']);
});
