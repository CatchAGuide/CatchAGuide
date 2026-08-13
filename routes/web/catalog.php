<?php

use App\Domain\CategoryPage\CategoryPageScope;
use App\Http\Controllers\CampOfferController;
use App\Http\Controllers\Category\DestinationCountryController;
use App\Http\Controllers\Category\GuidingDestinationController;
use App\Http\Controllers\Category\TargetFishPageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GuidingsController;
use App\Http\Controllers\Offers\OffersController;
use App\Http\Controllers\TripOfferController;
use App\Http\Controllers\TripsCatalogController;
use App\Http\Controllers\Vacation\VacationCountryController;
use App\Http\Controllers\Vacation\VacationHubController;
use App\Http\Controllers\Vacation\VacationInterestController;
use App\Http\Controllers\Vacation\VacationPillarController;
use App\Http\Controllers\VacationBookingController;
use App\Http\Controllers\VacationsController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('guidings', [WelcomeController::class, 'guidingsLanding'])->name('guidings.landing');
Route::get('guidings/alloffers', [GuidingsController::class, 'index'])->name('guidings.index')->middleware('ddos:search');
Route::redirect('guidings/l', '/guidings', 301);
Route::redirect('guidings/landing', '/guidings', 301);
Route::get('guidings/countries', [GuidingDestinationController::class, 'index'])
    ->name('guidings.countries')
    ->middleware('ddos:search');
Route::get('guidings/targets/{slug}', [TargetFishPageController::class, 'show'])
    ->defaults('content_scope', CategoryPageScope::TOURS)
    ->name('guidings.targets')
    ->middleware('ddos:search');
Route::get('guidings/methods', [CategoryController::class, 'methodsIndex'])
    ->name('guidings.methods')
    ->middleware('ddos:search');
Route::get('guidings/methods/{slug}', [CategoryController::class, 'methodsShow'])
    ->name('guidings.methods.show')
    ->middleware('ddos:search');
Route::get('guidings/offer/{slug}', [GuidingsController::class, 'newShow'])
    ->where('slug', '[\p{L}0-9\-]+')
    ->name('guidings.show');
Route::get('guidings/{id}/{slug}', [GuidingsController::class, 'redirectLegacyShow'])
    ->whereNumber('id')
    ->name('guidings.show.legacy');
Route::get('guidings/{country}/{region?}/{city?}', [GuidingDestinationController::class, 'show'])
    ->where('country', '(?!alloffers$|offer$|landing$|countries$|targets$|methods$|l$)[\p{L}][\p{L}0-9\-]*')
    ->name('guidings.destination')
    ->middleware('ddos:search');
Route::get('guidings/{slug}', [GuidingsController::class, 'redirectToNewFormat'])
    ->where('slug', '^(?!alloffers$|offer$|landing$|countries$|targets$|methods$|l$)[\p{L}0-9\-]+$')
    ->middleware('ddos:search');
Route::post('newguidings', [GuidingsController::class, 'guidingsStore'])->middleware('auth:web,employees')->name('guidings.store');
Route::post('guidings/generate-cards', [GuidingsController::class, 'generateCards'])->name('guidings.generate-cards');

Route::get('offers', [OffersController::class, 'index'])->name('offers.index')->middleware('ddos:search');

Route::get('vacations', [VacationHubController::class, 'index'])->name('vacations.index')->middleware('ddos:search');
Route::get('vacations/trips', [VacationPillarController::class, 'index'])->defaults('pillar', 'trips')->name('vacations.trips.index')->middleware('ddos:search');
Route::get('vacations/camps', [VacationPillarController::class, 'index'])->defaults('pillar', 'camps')->name('vacations.camps.index')->middleware('ddos:search');
Route::get('vacations/trips/{slug}', [VacationPillarController::class, 'slug'])->defaults('pillar', 'trips')->name('vacations.trips.show')->middleware('ddos:search');
Route::get('vacations/camps/{slug}', [VacationPillarController::class, 'slug'])->defaults('pillar', 'camps')->name('vacations.camps.show')->middleware('ddos:search');
Route::get('vacations/all-offers', [VacationCountryController::class, 'allOffers'])->name('vacations.all-offers')->middleware('ddos:search');
Route::get('vacations/targets/{slug}', [TargetFishPageController::class, 'show'])
    ->defaults('content_scope', CategoryPageScope::VACATIONS)
    ->name('vacations.targets')
    ->middleware('ddos:search');
Route::get('vacations/{country}', [VacationCountryController::class, 'show'])
    ->name('vacations.country')
    ->where('country', '^(?!trips$|camps$|all-offers$|targets$)[\p{L}0-9\-]+$')
    ->middleware('ddos:search');
Route::post('/vacation-booking', [VacationBookingController::class, 'store'])
    ->name('vacation.booking.store')
    ->middleware('web');
Route::post('/vacation-interest', [VacationInterestController::class, 'store'])->name('vacations.interest.store');
Route::get('vacations/c/{country}', [VacationsController::class, 'category'])->name('vacations.category')->middleware('ddos:search');
Route::get('vacations-v2/{campId}', [CampOfferController::class, 'show'])->name('vacations.v2');

Route::get('trips-destinations', [TripsCatalogController::class, 'index'])->name('trips.index')->middleware('ddos:search');
Route::get('trips-destinations/c/{location}', [TripsCatalogController::class, 'category'])->name('trips.category')->middleware('ddos:search');
Route::get('trips/{slug}', [TripOfferController::class, 'show'])
    ->name('trips.show')
    ->middleware('ddos:search');

Route::get('searchrequest', [GuidingsController::class, 'bookingrequest'])->name('guidings.request');
Route::post('searchrequest/store', [GuidingsController::class, 'bookingRequestStore'])
    ->middleware('throttle:5,1')
    ->name('store.request');

Route::redirect('destinationen', '/destination', 301)->name('destination_de');
Route::get('destination', [DestinationCountryController::class, 'index'])->name('destination')->middleware('ddos:search');
Route::get('destination/{country}', [DestinationCountryController::class, 'country'])->name('destination.country')->middleware('ddos:search');
Route::get('destination/{country}/{region}/{city?}', [DestinationCountryController::class, 'redirectLegacyGeo'])
    ->name('destination.legacy-geo')
    ->middleware('ddos:search');

Route::get('targets', [CategoryController::class, 'targetsIndex'])
    ->name('targets.index')
    ->middleware('ddos:search');
Route::get('targets/{slug}', [TargetFishPageController::class, 'show'])
    ->defaults('content_scope', CategoryPageScope::GLOBAL)
    ->name('targets.show')
    ->middleware('ddos:search');
Route::get('category-page/{type}/', [CategoryController::class, 'index'])->name('category.types');
Route::get('category-page/{type}/{slug}', [CategoryController::class, 'targets'])->name('category.targets');
