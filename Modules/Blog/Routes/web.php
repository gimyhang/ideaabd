<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\AuthorBlogController;

Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/write', [AuthorBlogController::class, 'writeGateway'])->name('write');
    Route::get('/category/{slug}', [BlogController::class, 'category'])->name('category');
    Route::get('/tag/{slug}', [BlogController::class, 'tag'])->name('tag');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
});
