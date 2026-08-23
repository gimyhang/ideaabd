<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payment Gateway Routes (bKash & Nagad)
|--------------------------------------------------------------------------
*/

Route::prefix('payment')->name('api.payment.')->controller(PaymentController::class)->group(function () {

    // পেমেন্ট শুরু করার রাউট (শুধুমাত্র লগইন করা ব্যবহারকারীদের জন্য)
    Route::middleware(['auth'])->group(function () {
        Route::post('/bkash/create', 'createBkashPayment')->name('bkash.create');
        Route::post('/nagad/create', 'createNagadPayment')->name('nagad.create');
    });

    // পেমেন্ট গেটওয়ে কলব্যাক (GET ও POST উভয় রিকোয়েস্ট হ্যান্ডেল করার জন্য)
    Route::match(['get', 'post'], '/bkash/callback', 'bkashCallback')->name('bkash.callback');
    Route::match(['get', 'post'], '/nagad/callback', 'nagadCallback')->name('nagad.callback');

    // সার্বজনীন স্ট্যাটাস পেজ
    Route::get('/success', 'success')->name('success');
    Route::get('/fail', 'fail')->name('fail');
    Route::get('/cancel', 'cancel')->name('cancel');
});