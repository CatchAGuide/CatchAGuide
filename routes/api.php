<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\Guide\AccountController as GuideAccountController;
use App\Http\Controllers\Api\Guide\TokensController as GuideTokensController;
use App\Http\Controllers\Api\Guide\ToursController as GuideToursController;
use App\Http\Controllers\Api\Guide\VoucherOrdersController as GuideVoucherOrdersController;
use App\Http\Controllers\Api\Guide\VoucherSettingsController as GuideVoucherSettingsController;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

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

// Guide Area API (plan decision D-3): same services as the /guide/* pages. Auth: Sanctum bearer
// token, or the site session for first-party requests from a stateful domain (config/sanctum.php).
// Same beta gate as the pages (guide.area). SetLocale: messages follow the domain / language switch.
// Sign in for a bearer token. Wrong passwords are throttled per email + IP (LoginThrottle).
Route::post('/guide/tokens', [GuideTokensController::class, 'store'])
    ->middleware([SetLocale::class, 'throttle:20,1'])
    ->name('api.guide.tokens.store');

Route::prefix('guide')
    ->name('api.guide.')
    ->middleware([EnsureFrontendRequestsAreStateful::class, SetLocale::class, 'auth:sanctum', 'guide.area', 'throttle:120,1'])
    ->group(function () {
        Route::delete('/tokens/current', [GuideTokensController::class, 'destroy'])->name('tokens.destroy');

        Route::get('/tours',[GuideToursController::class, 'index'])->name('tours.index');
        Route::post('/tours/{guiding}/go-live', [GuideToursController::class, 'goLive'])->whereNumber('guiding')->name('tours.go-live');
        Route::post('/tours/{guiding}/deactivate', [GuideToursController::class, 'deactivate'])->whereNumber('guiding')->name('tours.deactivate');
        Route::post('/tours/{guiding}/duplicate', [GuideToursController::class, 'duplicate'])->whereNumber('guiding')->name('tours.duplicate');

        Route::get('/voucher-orders', [GuideVoucherOrdersController::class, 'index'])->name('voucher-orders.index');
        Route::get('/voucher-orders/{voucherOrder}', [GuideVoucherOrdersController::class, 'show'])->whereNumber('voucherOrder')->name('voucher-orders.show');
        Route::patch('/voucher-orders/{voucherOrder}', [GuideVoucherOrdersController::class, 'update'])->whereNumber('voucherOrder')->name('voucher-orders.update');

        Route::get('/voucher-settings', [GuideVoucherSettingsController::class, 'show'])->name('voucher-settings.show');
        Route::put('/voucher-settings', [GuideVoucherSettingsController::class, 'update'])->name('voucher-settings.update');

        Route::get('/account', [GuideAccountController::class, 'show'])->name('account.show');
        Route::put('/account/personal', [GuideAccountController::class, 'updatePersonal'])->name('account.personal');
        Route::put('/account/billing', [GuideAccountController::class, 'updateBilling'])->name('account.billing');
        Route::post('/account/password', [GuideAccountController::class, 'password'])->middleware('throttle:10,1')->name('account.password');
    });
