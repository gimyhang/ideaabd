<?php

use Modules\BulkOrder\Http\Controllers\Frontend\BulkOrderController;

Route::middleware(['api'])->prefix('api/bulk-order')->group(function () {
    Route::get('/create', [BulkOrderController::class, 'create'])->middleware('auth');
    Route::post('/', [BulkOrderController::class, 'store'])->middleware('auth');
    Route::get('/my-orders', [BulkOrderController::class, 'myOrders'])->middleware('auth');
    Route::get('/{order}', [BulkOrderController::class, 'show'])->middleware('auth');
});
