<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ModernCheckoutApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('checkout')->group(function () {
    Route::get('/guiding/{id}', [ModernCheckoutApiController::class, 'getGuiding']);
    Route::post('/calculate-price', [ModernCheckoutApiController::class, 'calculatePrice'])
        ->middleware('throttle:checkout-price');
    Route::post('/submit-booking', [ModernCheckoutApiController::class, 'submitBooking'])
        ->middleware('throttle:checkout-submit');
    Route::get('/available-dates/{guidingId}', [ModernCheckoutApiController::class, 'getAvailableDates']);
});

Route::prefix('catalog')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/trips', [CatalogController::class, 'trips']);
        Route::get('/guidings', [CatalogController::class, 'guidings']);
        Route::get('/vacations', [CatalogController::class, 'vacations']);
        Route::get('/camps', [CatalogController::class, 'camps']);
    });
