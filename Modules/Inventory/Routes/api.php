<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;
use Modules\Inventory\Http\Controllers\LocationController;
use Modules\Inventory\Http\Controllers\StockTransferController;
use Modules\Inventory\Http\Controllers\StockWastageController;

/*
|--------------------------------------------------------------------------
| Inventory Module API Routes
| Prefix: /api/inventory
|--------------------------------------------------------------------------
*/

// Session-authenticated staff only (CSRF applies to writes); previously open to anyone.
Route::prefix('inventory')->name('inventory.')->middleware(['web', 'auth', 'role:admin'])->group(function () {

    // --- Locations (Warehouses & Showrooms) ---
    Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
    Route::post('locations', [LocationController::class, 'store'])->name('locations.store');
    Route::get('locations/{id}', [LocationController::class, 'show'])->name('locations.show');
    Route::put('locations/{id}', [LocationController::class, 'update'])->name('locations.update');

    // --- Core Inventory Operations ---
    Route::get('overview', [InventoryController::class, 'overview'])->name('overview');
    Route::post('production-in', [InventoryController::class, 'productionIn'])->name('production-in');
    Route::post('sales-out', [InventoryController::class, 'salesOut'])->name('sales-out');
    Route::get('alerts/low-stock', [InventoryController::class, 'lowStockAlerts'])->name('alerts.low-stock');
    Route::get('movement-logs', [InventoryController::class, 'movementLogs'])->name('movement-logs');

    // --- Stock Transfers (Challan Log & Transfer Status) ---
    Route::get('transfers', [StockTransferController::class, 'index'])->name('transfers.index');
    Route::post('transfers', [StockTransferController::class, 'store'])->name('transfers.store');
    Route::get('transfers/{id}', [StockTransferController::class, 'show'])->name('transfers.show');
    Route::post('transfers/{id}/receive', [StockTransferController::class, 'receive'])->name('transfers.receive');
    Route::post('transfers/{id}/cancel', [StockTransferController::class, 'cancel'])->name('transfers.cancel');

    // --- Wastage & Damaged Logs ---
    Route::get('wastages', [StockWastageController::class, 'index'])->name('wastages.index');
    Route::post('wastages', [StockWastageController::class, 'store'])->name('wastages.store');
});
