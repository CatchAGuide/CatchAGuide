<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingRescheduleController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/booking-accept/{token}', [BookingController::class, 'accept'])
    ->middleware('throttle:10,1')
    ->name('booking.accept');
Route::get('/booking-reject/{token}', [BookingController::class, 'reject'])
    ->middleware('throttle:10,1')
    ->name('booking.reject');
Route::post('/update/reject/{token}', [BookingController::class, 'rejectProcess'])
    ->middleware('throttle:10,1')
    ->name('booking.rejection');
// Customer reschedule after a guide declined: the emailed /{token} link is exchanged for a
// session and redirected to the token-free page (see BookingRescheduleController).
Route::get('/booking/reschedule', [BookingRescheduleController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('booking.reschedule.show');
Route::post('/booking/reschedule/store', [BookingRescheduleController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('booking.reschedule.store');
Route::get('/booking/reschedule/{token}', [BookingRescheduleController::class, 'enter'])
    ->middleware('throttle:10,1')
    ->name('booking.reschedule');

Route::get('/reject/success', function () {
    return view('pages.additional.reject_success');
})->name('booking.rejectsuccess');

Route::get('/booking-request/thank-you', function () {
    return view('pages.additional.thank_you_request');
})->name('request.thank-you');

Route::get('thank-you/{booking}', [CheckoutController::class, 'thankYou'])->name('thank-you');

// Legacy "all countries" link: the view it rendered needs a $countries list this closure never
// passed (it 500'd). /destination is the maintained all-countries hub.
Route::permanentRedirect('/all-countries', '/destination')->name('allcountries');

// Legacy booking-request URL: still the call to action in ~20 magazine/guide articles, but it
// fell through to guidings/{slug} and 404'd. Registered here, before catalog.php's catch-all.
Route::get('guidings/bookingrequest', fn () => redirect()->route('guidings.request', request()->query(), 301))
    ->name('guidings.bookingrequest.legacy');
