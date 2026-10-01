<?php

use App\Http\Controllers\CampCheckoutController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ModernCheckoutController;
use App\Http\Controllers\TripCheckoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout')->middleware(['throttle:5,1', 'ddos:checkout']);

Route::get('/checkout', [ModernCheckoutController::class, 'index'])->name('checkout.index')->middleware(['throttle:10,1,checkout-page:', 'ddos:checkout']);
Route::post('/checkouts', [ModernCheckoutController::class, 'store'])->name('checkout.store')->middleware(['throttle:checkout-submit', 'ddos:checkout']);
Route::get('/checkout/thank-you/{bookingId}', [ModernCheckoutController::class, 'thankYou'])->name('checkout.thank-you')->whereNumber('bookingId');

// Vacation checkouts live under /checkout/{camps|trips}/{slug} so the whole funnel shares one
// path prefix for conversion tracking. Route names stay checkout.camp.* / checkout.trip.*.
Route::prefix('checkout')->group(function () {
    // Camp checkout (availability request).
    Route::get('camps/{slug}', [CampCheckoutController::class, 'show'])->name('checkout.camp.show')->middleware(['throttle:10,1,checkout-page:', 'ddos:checkout']);
    Route::post('camps/{slug}', [CampCheckoutController::class, 'store'])->name('checkout.camp.store')->middleware(['throttle:checkout-submit', 'ddos:checkout']);
    Route::get('camps/{slug}/thank-you/{requestId}', [CampCheckoutController::class, 'thankYou'])->name('checkout.camp.thank-you')->whereNumber('requestId');

    // Trip checkout (request for a departure or a preferred travel window).
    Route::get('trips/{slug}', [TripCheckoutController::class, 'show'])->name('checkout.trip.show')->middleware(['throttle:10,1,checkout-page:', 'ddos:checkout']);
    Route::post('trips/{slug}', [TripCheckoutController::class, 'store'])->name('checkout.trip.store')->middleware(['throttle:checkout-submit', 'ddos:checkout']);
    Route::get('trips/{slug}/thank-you/{requestId}', [TripCheckoutController::class, 'thankYou'])->name('checkout.trip.thank-you')->whereNumber('requestId');
});

// Former /vacations/{camps|trips}/{slug}/checkout URLs (bookmarks, open tabs) 301 to the new
// paths with their ?date&persons prefill. Registered before catalog's vacation catch-alls.
foreach (['camps' => 'checkout.camp.show', 'trips' => 'checkout.trip.show'] as $pillar => $routeName) {
    Route::get("/vacations/{$pillar}/{slug}/checkout", fn (Request $request, string $slug) => redirect()->route(
        $routeName,
        ['slug' => $slug] + $request->only(['date', 'persons', 'nights', 'accommodation']),
        301,
    ))->name("checkout.{$pillar}.legacy");
}
