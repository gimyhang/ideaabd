<?php

use Modules\KidsZone\Http\Controllers\Frontend\KidsZoneController;

Route::middleware('api')->prefix('api/kids-zone')->group(function () {
    Route::get('/', [KidsZoneController::class, 'index']);
    Route::get('/{zone:slug}', [KidsZoneController::class, 'show']);
    Route::get('/{zone:slug}/api', [KidsZoneController::class, 'api']);
});
