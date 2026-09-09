<?php

use App\Http\Controllers\BookingController;
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
Route::get('/booking/reschedule/{token}', [BookingController::class, 'reschedule'])
    ->middleware('throttle:10,1')
    ->name('booking.reschedule');
Route::post('/booking/reschedule/store', [BookingController::class, 'rescheduleStore'])
    ->middleware('throttle:10,1')
    ->name('booking.reschedule.store');

Route::get('/reject/success', function () {
    return view('pages.additional.reject_success');
})->name('booking.rejectsuccess');

Route::get('/booking-request/thank-you', function () {
    return view('pages.additional.thank_you_request');
})->name('request.thank-you');

Route::get('thank-you/{booking}', [CheckoutController::class, 'thankYou'])->name('thank-you');

Route::get('/all-countries', function () {
    return view('pages.countries.index');
})->name('allcountries');
