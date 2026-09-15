<?php

use Illuminate\Support\Facades\Route;
use Modules\Publisher\Http\Controllers\Frontend\PublisherController;

Route::prefix('publishers')->name('publisher.')->group(function () {
    Route::get('/', [PublisherController::class, 'index'])->name('index');
    Route::get('/register', [PublisherController::class, 'register'])->name('register');
    Route::post('/register', [PublisherController::class, 'storeRegistration'])->name('store-registration');
    Route::get('/{slug}', [PublisherController::class, 'show'])->name('show');
});

Route::get('/register/publisher', [PublisherController::class, 'register'])->name('publisher.register.alt');
Route::post('/register/publisher', [PublisherController::class, 'storeRegistration'])->name('publisher.store-registration.alt');
