<?php

use App\Http\Controllers\AssistantChatController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Blog\CategoriesController;
use App\Http\Controllers\Blog\ThreadsController;
use App\Http\Controllers\BookingAssistantPreviewController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\ProductReportController;
use App\Http\Controllers\RatingsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\ZoisController;
use Illuminate\Support\Facades\Route;

$registerAngelmagazin = static function (): void {
    Route::prefix('angelmagazin')->name('blogde.')->group(function () {
        Route::resource('/categories', CategoriesController::class)->only(['show']);
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/{slug}', [ThreadsController::class, 'show'])->name('thread.show');
    });
};

$registerFishingMagazine = static function (): void {
    Route::prefix('fishing-magazine')->name('blog.')->group(function () {
        Route::resource('/categories', CategoriesController::class)->only(['show']);
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/{slug}', [ThreadsController::class, 'show'])->name('thread.show');
    });
};

if (app()->environment('production')) {
    Route::middleware(['check_domain:catchaguide.de'])->group($registerAngelmagazin);
    Route::middleware(['check_domain:catchaguide.com'])->group($registerFishingMagazine);
} else {
    $registerAngelmagazin();
    $registerFishingMagazine();
}

Route::post('/search', [SearchController::class, 'search'])->name('search');

Route::post('/assistant/chat', AssistantChatController::class)
    ->middleware(['throttle:booking-assistant', 'booking.assistant.access'])
    ->name('assistant.chat');

Route::get('/hub/cag-ba-preview/{token}', BookingAssistantPreviewController::class)
    ->name('booking-assistant.preview');

Route::name('additional.')->group(function () {
    Route::view('/contact', 'pages.additional.contact')->name('contact');
    Route::view('/about-us', 'pages.additional.about-us')->name('about_us');
    Route::view('/partner', 'pages.additional.partner')->name('partner');
    Route::view('/for-agents', 'pages.additional.for-agents')->name('for_agents');
});

Route::post('sendcontact', [ZoisController::class, 'sendcontact'])
    ->middleware('throttle:10,1')
    ->name('sendcontactmail');
Route::post('sendnewsletter', [ZoisController::class, 'sendnewsletter'])
    ->middleware('throttle:5,1')
    ->name('sendnewsletter');

Route::name('ratings.')->prefix('ratings')->group(function () {
    Route::get('/notified', [RatingsController::class, 'notified'])->name('notified');
    Route::get('/review/{id}', [RatingsController::class, 'review'])->name('review');
    Route::get('/{token}', [RatingsController::class, 'show'])->name('show');
    Route::post('/{token}', [RatingsController::class, 'store'])->name('store');
});

Route::name('law.')->group(function () {
    Route::view('/imprint', 'pages.law.imprint')->name('imprint');
    Route::get('/data-protection', [TermsController::class, 'dataProtection'])->name('data-protection');
    Route::get('/agb/{section?}', [TermsController::class, 'show'])->whereNumber('section')->name('agb');
    Route::get('/faq', [FAQController::class, 'index'])->name('faq');
    Route::get('/notice-and-takedown', [ProductReportController::class, 'show'])->name('notice-and-takedown');
});

Route::post('/product-reports', [ProductReportController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('product-reports.store');
