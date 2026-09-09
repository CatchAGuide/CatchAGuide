<?php

use App\Http\Controllers\GuidingSearchPlaceLogController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MapLandmarkController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('sitemap.xml', function () {
    $host = request()->getHost();
    $normalizedHost = str_replace('www.', '', $host);
    $lang = $normalizedHost === 'catchaguide.de' ? 'de' : 'en';
    $path = public_path("sitemaps/sitemap_index_{$lang}.xml");

    if (! file_exists($path)) {
        abort(404, 'Sitemap index not found');
    }

    return response()->file($path, [
        'Content-Type' => 'application/xml; charset=UTF-8',
    ]);
});

Route::post('/get-user-location', [WelcomeController::class, 'getUserLocation'])->name('user.location');
Route::post('/guidings/search-place-log', [GuidingSearchPlaceLogController::class, 'store'])
    ->middleware('throttle:60,1')
    ->name('guidings.search-place-log.store');
Route::get('/maps/landmarks', MapLandmarkController::class)
    ->middleware('throttle:60,1')
    ->name('maps.landmarks');
Route::post('/language/switch', [LanguageController::class, 'switchLanguage'])->name('language.switch');

Route::get('robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Allow: /\n\n";
    $content .= "Disallow: /admin/\n";
    $content .= "Disallow: /profile/\n";
    $content .= "Disallow: /login\n";
    $content .= "Disallow: /register\n";
    $content .= "Disallow: /password/\n";
    $content .= "Disallow: /api/catalog/\n\n";
    $content .= "Allow: /guidings\n";
    $content .= "Allow: /vacations\n";
    $content .= "Allow: /angelmagazin/\n";
    $content .= "Allow: /fishing-magazine/\n\n";
    $content .= "Sitemap: https://www.catchaguide.com/sitemap.xml\n";
    $content .= "Sitemap: https://www.catchaguide.de/sitemap.xml\n";

    return response($content, 200)
        ->header('Content-Type', 'text/plain; charset=UTF-8');
});
