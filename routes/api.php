<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
   
    Route::post('/queue/run-worker', function () {
        \Artisan::call('queue:work', ['--stop-when-empty' => true]); 
        return response()->json(['message' => 'Worker executed successfully']);
    }); 

    Route::post('/update/status', function () {
        // \Artisan::call('update:booking-status'); 
        // return response()->json(['message' => 'Booking status executed successfully']);
    });

    Route::post('/run/reminder', function () {
        // \Artisan::call('run:bookreminders'); 
        // return response()->json(['message' => 'Reminders executed successfully']);
    });
    
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Modern Checkout API Routes
Route::prefix('checkout')->group(function () {
    Route::get('/guiding/{id}', [\App\Http\Controllers\Api\ModernCheckoutApiController::class, 'getGuiding']);
    Route::post('/calculate-price', [\App\Http\Controllers\Api\ModernCheckoutApiController::class, 'calculatePrice']);
    Route::post('/submit-booking', [\App\Http\Controllers\Api\ModernCheckoutApiController::class, 'submitBooking']);
    Route::get('/available-dates/{guidingId}', [\App\Http\Controllers\Api\ModernCheckoutApiController::class, 'getAvailableDates']);
});

