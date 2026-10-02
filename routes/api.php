<?php

use App\Http\Controllers\Api\CatalogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('catalog')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/trips', [CatalogController::class, 'trips']);
        Route::get('/guidings', [CatalogController::class, 'guidings']);
        Route::get('/vacations', [CatalogController::class, 'vacations']);
        Route::get('/camps', [CatalogController::class, 'camps']);
    });
