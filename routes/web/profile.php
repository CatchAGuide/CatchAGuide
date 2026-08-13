<?php

use App\Http\Controllers\Admin\GuidesController;
use App\Http\Controllers\Api\EventsController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\GuideOnboardingController;
use App\Http\Controllers\GuidingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::post('/upload/{guiding?}', [FileUploadController::class, 'upload'])
    ->middleware(['auth:web,employees', 'throttle:30,1'])
    ->name('upload');

Route::post('/newguiding', [GuidingsController::class, 'guidingsStore'])->middleware('auth:web,employees')->name('profile.newguiding.store');
Route::post('/newguiding/save-draft', [GuidingsController::class, 'saveDraft'])->middleware('auth:web,employees')->name('profile.newguiding.save-draft');
Route::post('/newguiding/save-draft-sync', [GuidingsController::class, 'saveDraftSync'])->middleware('auth:web,employees')->name('profile.newguiding.save-draft-sync');

Route::prefix('profile')->name('profile.')->middleware('auth:web')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::get('/password', [ProfileController::class, 'password'])->name('password');
    Route::put('/password', [ProfileController::class, 'passwordUpdate'])->name('password.update');
    Route::get('/z', [ProfileController::class, 'abbuchen'])->name('abbuchen');
    Route::get('/becomeguide', [ProfileController::class, 'becomeguide'])->name('becomeguide');
    Route::get('/guide-profile', [ProfileController::class, 'guideProfile'])->name('guide-profile');
    Route::put('/guide-profile', [ProfileController::class, 'guideProfileUpdate'])->name('guide-profile.update');
    Route::put('/account', [ProfileController::class, 'accountUpdate'])->name('account');
    Route::get('/favoriteguides', [ProfileController::class, 'favoriteguides'])->name('favoriteguides');
    Route::get('/myguidings', [ProfileController::class, 'myguidings'])->name('myguidings');

    Route::get('/myguidings/activate/{guiding}', [ProfileController::class, 'activate'])->name('guiding.activate');
    Route::get('/myguidings/deactivate/{guiding}', [ProfileController::class, 'deactivate'])->name('guiding.deactivate');

    Route::get('/bookings', [ProfileController::class, 'bookings'])->name('bookings');
    Route::get('/bookings/load-more', [ProfileController::class, 'loadMoreBookings'])->name('bookings.load-more');

    Route::get('showbooking/{bookingid}', [ProfileController::class, 'showbooking'])->name('showbooking');
    Route::get('stornobooking/{bookingid}', [ProfileController::class, 'stornobooking'])->name('stornobooking');

    Route::get('/guidebookings', function () {
        return redirect()->route('profile.bookings');
    })->name('guidebookings');

    Route::get('/guidebookings/accept/{booking}', [ProfileController::class, 'accept'])->name('guidebookings.accept');
    Route::get('/guidebookings/reject/{booking}', [ProfileController::class, 'reject'])->name('guidebookings.reject');

    Route::get('/newguiding', [ProfileController::class, 'newguiding'])->name('newguiding');

    Route::get('/payments', [ProfileController::class, 'payments'])->name('payments');
    Route::put('/payments', [ProfileController::class, 'paymentsUpdate'])->name('payments.update');
    Route::get('/calendar', [ProfileController::class, 'calendar'])->name('calendar');
    Route::post('/calendar/store', [EventsController::class, 'store'])->name('calendar.store');
    Route::post('/calendar/custom', [EventsController::class, 'storeCustomSchedule'])->name('calendar.store.custom');
    Route::put('/calendar/update/{id}', [EventsController::class, 'update'])->name('calendar.update');
    Route::get('/calendar/delete/{id}', [EventsController::class, 'delete'])->name('calendar.delete');
    Route::delete('/calendar/delete/{id}', [EventsController::class, 'delete'])->name('calendar.delete.ajax');
    Route::get('/calendar/guidings', [EventsController::class, 'getUserGuidings'])->name('calendar.guidings');
    Route::post('/getbalance', [ProfileController::class, 'getbalance'])->name('getbalance');

    Route::get('process-merchant-status', [ProfileController::class, 'processMerchantStatus'])->name('processmerchantstatus');
});

Route::get('/guide/onboarding', [GuideOnboardingController::class, 'show'])->name('guide.onboarding');
Route::post('/guide/onboarding', [GuideOnboardingController::class, 'store'])->name('guide.onboarding.store');

Route::post('/guide', [GuidesController::class, 'store'])->middleware('auth:web')->name('guide');

Route::middleware('auth:web')->group(function () {
    Route::get('/transaction', [CheckoutController::class, 'completeTransaction'])->name('transaction');

    Route::get('events', [EventsController::class, 'index']);

    Route::redirect('chat', '/profile', 301)->name('chat');
    Route::get('sendMessage/{user}', function () {
        return redirect()->route('profile.index');
    })->name('chat.create');

    Route::get('wishlist/add-or-remove/{guiding}', [WishlistController::class, 'addOrRemove'])->name('wishlist.add-or-remove');

    Route::post('delete-image/{id}', [GuidingsController::class, 'deleteimage'])->name('delete-image');

    Route::get('deleteguiding/{id}', [GuidingsController::class, 'deleteguiding'])->name('deleteguiding');
    Route::get('delete-image/{guiding}/{img?}', [GuidingsController::class, 'deleteImage'])->name('deleteImage');

    Route::get('guidings/{guiding}/edit', [GuidingsController::class, 'edit'])->name('guidings.edit');
    Route::get('guidings/{guiding}/edit_newguiding', [GuidingsController::class, 'edit_newguiding'])->name('guidings.edit_newguiding');
    Route::post('guidings/{guiding}/update', [GuidingsController::class, 'update'])->name('guidings.update');
});
