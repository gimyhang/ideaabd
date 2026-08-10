<?php

use Illuminate\Support\Facades\Route;
use Modules\Book\Http\Controllers\Frontend\BookController;
use App\Http\Controllers\AuthorController;

/*
|--------------------------------------------------------------------------
| Web Routes (Ideap Platform Core Routes)
|--------------------------------------------------------------------------
*/

// ১. হোম পেজ (সরাসরি বুক ক্যাটালগ ও ফ্রন্টএন্ড ফিচার লোড করবে)
Route::get('/', [BookController::class, 'index'])->name('home');

// ২. বইয়ের ক্যাটালগ ও ফিল্টারিং রাউট
Route::prefix('books')->name('book.')->group(function () {
    Route::get('/', [BookController::class, 'index'])->name('index');
    Route::get('/{slug}', [BookController::class, 'show'])->name('show');
    Route::get('/{slug}/preview', [BookController::class, 'preview'])->name('preview');
    Route::get('/{id}/quick-view', [BookController::class, 'quickView'])->name('quick-view');
});

// ২.৫ সমস্ত প্ল্যাটফর্ম হাব পেজ
Route::view('/hub', 'frontend.pages.hub')->name('hub');

// ৩. স্ট্যাটিক পেজসমূহ (About & Contact)
Route::view('/about', 'frontend.pages.about')->name('about');
Route::view('/contact', 'frontend.pages.contact')->name('contact');
Route::view('/admin', 'admin.index')->name('admin.index');

// Authors directory & profile
Route::prefix('authors')->name('authors.')->group(function () {
    Route::get('/', [AuthorController::class, 'index'])->name('index');
    Route::get('/{author}', [AuthorController::class, 'show'])->name('show');
});