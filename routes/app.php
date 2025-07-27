<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\App\OAuthController;

Route::prefix('oauth')->name('oauth.')->group(function () {
    Route::get('/calendly', [OAuthController::class, 'calendly'])->name('calendly');
    Route::get('/calendly/callback', [OAuthController::class, 'calendlyCallback'])->name('calendly.callback');
    Route::post('/calendly/disconnect', [OAuthController::class, 'disconnectCalendly'])->name('calendly.disconnect');
    Route::post('/calendly/sync', [OAuthController::class, 'syncCalendly'])->name('calendly.sync');
});

// Webhook routes
Route::post('/webhooks/calendly', [App\Http\Controllers\WebhookController::class, 'calendly'])->name('webhooks.calendly');