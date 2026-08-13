<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ModernCheckoutController;
use Illuminate\Support\Facades\Route;

Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout')->middleware(['throttle:5,1', 'ddos:checkout']);

Route::get('/checkout', [ModernCheckoutController::class, 'index'])->name('checkout.index')->middleware(['throttle:10,1,checkout-page:', 'ddos:checkout']);
Route::post('/checkouts', [ModernCheckoutController::class, 'store'])->name('checkout.store')->middleware(['throttle:5,1,checkout-store:', 'ddos:checkout']);
Route::get('/checkout/thank-you/{bookingId}', [ModernCheckoutController::class, 'thankYou'])->name('checkout.thank-you');
