<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\Admin\InventoryAdminController;

/*
|--------------------------------------------------------------------------
| Inventory Module Admin Routes
| Prefix: /admin/inventory — signed-in staff only; permissions are mapped in RoleMiddleware.
|--------------------------------------------------------------------------
*/

Route::prefix('admin/inventory')->name('admin.inventory.')->middleware(['web', 'auth', 'role:admin'])->group(function () {
    Route::get('/', [InventoryAdminController::class, 'overview'])->name('index');
    Route::get('books', [InventoryAdminController::class, 'searchBooks'])->name('books');
    Route::get('export', [InventoryAdminController::class, 'export'])->name('export');
    Route::post('stock-in', [InventoryAdminController::class, 'stockIn'])->name('stock-in');
    Route::post('opening-balance', [InventoryAdminController::class, 'openingBalance'])->name('opening-balance');

    Route::get('locations', [InventoryAdminController::class, 'locations'])->name('locations');
    Route::post('locations', [InventoryAdminController::class, 'storeLocation'])->name('locations.store');
    Route::put('locations/{id}', [InventoryAdminController::class, 'updateLocation'])->name('locations.update');

    Route::get('transfers', [InventoryAdminController::class, 'transfers'])->name('transfers');
    Route::post('transfers', [InventoryAdminController::class, 'storeTransfer'])->name('transfers.store');
    Route::get('transfers/{id}', [InventoryAdminController::class, 'showTransfer'])->name('transfers.show');
    Route::post('transfers/{id}/receive', [InventoryAdminController::class, 'receiveTransfer'])->name('transfers.receive');
    Route::post('transfers/{id}/cancel', [InventoryAdminController::class, 'cancelTransfer'])->name('transfers.cancel');

    Route::get('wastages', [InventoryAdminController::class, 'wastages'])->name('wastages');
    Route::post('wastages', [InventoryAdminController::class, 'storeWastage'])->name('wastages.store');

    Route::get('alerts', [InventoryAdminController::class, 'alerts'])->name('alerts');
    Route::put('alerts/{id}', [InventoryAdminController::class, 'updateThreshold'])->name('alerts.update');

    Route::get('logs', [InventoryAdminController::class, 'logs'])->name('logs');
});
