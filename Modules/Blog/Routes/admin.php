<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\Admin\BlogController;

Route::resource('posts', BlogController::class);
Route::post('posts/{post}/publish', [BlogController::class, 'publish'])->name('posts.publish');
Route::post('posts/{post}/unpublish', [BlogController::class, 'unpublish'])->name('posts.unpublish');
Route::post('posts/{post}/feature', [BlogController::class, 'feature'])->name('posts.feature');
Route::post('posts/{post}/unfeature', [BlogController::class, 'unfeature'])->name('posts.unfeature');
