<?php

use Illuminate\Support\Facades\Route;
use Modules\Research\Http\Controllers\Frontend\ResearchController;

Route::prefix('research')->name('research.')->group(function () {
    Route::get('/', [ResearchController::class, 'index'])->name('index');
    Route::get('/{slug}', [ResearchController::class, 'show'])->name('show');
    Route::get('/{slug}/download', [ResearchController::class, 'download'])->name('download');
});
