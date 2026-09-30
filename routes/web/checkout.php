<?php

use App\Http\Controllers\CampCheckoutController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ModernCheckoutController;
use Illuminate\Support\Facades\Route;

Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout')->middleware(['throttle:5,1', 'ddos:checkout']);

Route::get('/checkout', [ModernCheckoutController::class, 'index'])->name('checkout.index')->middleware(['throttle:10,1,checkout-page:', 'ddos:checkout']);
Route::post('/checkouts', [ModernCheckoutController::class, 'store'])->name('checkout.store')->middleware(['throttle:checkout-submit', 'ddos:checkout']);
Route::get('/checkout/thank-you/{bookingId}', [ModernCheckoutController::class, 'thankYou'])->name('checkout.thank-you')->whereNumber('bookingId');

// Camp checkout (availability request). Registered here, before catalog's vacation catch-alls.
Route::get('/vacations/camps/{slug}/checkout', [CampCheckoutController::class, 'show'])->name('checkout.camp.show')->middleware(['throttle:10,1,checkout-page:', 'ddos:checkout']);
Route::post('/vacations/camps/{slug}/checkout', [CampCheckoutController::class, 'store'])->name('checkout.camp.store')->middleware(['throttle:checkout-submit', 'ddos:checkout']);
Route::get('/vacations/camps/{slug}/checkout/thank-you/{requestId}', [CampCheckoutController::class, 'thankYou'])->name('checkout.camp.thank-you')->whereNumber('requestId');
