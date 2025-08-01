<?php

use App\Http\Controllers\App\OAuthController;
use App\Http\Controllers\ICalFeedController;
use App\Http\Controllers\UserICalFeedController;
use Illuminate\Support\Facades\Route;

// OAuth Routes (for future integrations)
Route::prefix('oauth')->name('oauth.')->group(function () {
    Route::get('/{provider}', [OAuthController::class, 'redirect'])->name('redirect');
    Route::get('/{provider}/callback', [OAuthController::class, 'callback'])->name('callback');
    Route::post('/{provider}/disconnect', [OAuthController::class, 'disconnect'])->name('disconnect');
    Route::post('/{provider}/sync', [OAuthController::class, 'sync'])->name('sync');
});

// iCal Feed Routes (Import) - Protected by auth
Route::prefix('ical-feeds')->name('ical-feeds.')->middleware('auth:web')->group(function () {
    Route::get('/', [ICalFeedController::class, 'index'])->name('index');
    Route::post('/', [ICalFeedController::class, 'store'])->name('store');
    Route::get('/{feed}', [ICalFeedController::class, 'show'])->name('show');
    Route::put('/{feed}', [ICalFeedController::class, 'update'])->name('update');
    Route::delete('/{feed}', [ICalFeedController::class, 'destroy'])->name('destroy');
    Route::post('/{feed}/sync', [ICalFeedController::class, 'sync'])->name('sync');
    Route::post('/sync-all', [ICalFeedController::class, 'syncAll'])->name('sync-all');
    Route::post('/validate-url', [ICalFeedController::class, 'validateUrl'])->name('validate-url');
});

// User iCal Feed Routes (Export) - Protected by auth
Route::prefix('user-ical-feeds')->name('user-ical-feeds.')->middleware('auth:web')->group(function () {
    Route::get('/', [UserICalFeedController::class, 'index'])->name('index');
    Route::post('/', [UserICalFeedController::class, 'store'])->name('store');
    Route::get('/{feed}', [UserICalFeedController::class, 'show'])->name('show');
    Route::put('/{feed}', [UserICalFeedController::class, 'update'])->name('update');
    Route::delete('/{feed}', [UserICalFeedController::class, 'destroy'])->name('destroy');
    Route::get('/{feed}/feed', [UserICalFeedController::class, 'getFeed'])->name('feed');
    Route::post('/{feed}/regenerate-otp', [UserICalFeedController::class, 'regenerateOtp'])->name('regenerate-otp');
});

// Public iCal Feed Route (no auth required for accessing feeds)
Route::get('ical/feed/{token}', [UserICalFeedController::class, 'generateFeed'])->name('ical.feed');

// Webhook Routes
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    // Future webhook integrations can be added here
});