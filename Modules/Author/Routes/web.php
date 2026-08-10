<?php

use Illuminate\Support\Facades\Route;
use Modules\Author\Http\Controllers\Frontend\AuthorController;

Route::prefix('authors')->name('author.')->group(function () {
    Route::get('/', [AuthorController::class, 'index'])->name('index');
    Route::get('/register', [AuthorController::class, 'register'])->name('register');
    Route::post('/register', [AuthorController::class, 'storeRegistration'])->name('store-registration');
    Route::get('/{slug}', [AuthorController::class, 'show'])->name('show');
});
