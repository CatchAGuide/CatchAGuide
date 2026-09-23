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

// Host-aware, matching sitemap.xml above — public/robots.txt (the old static file, removed) served the
// exact same content on both domains, so catchaguide.de advertised catchaguide.com's sitemap and vice versa,
// and there was no way to give one domain a rule the other doesn't need.
Route::get('robots.txt', function () {
    $host = request()->getHost();
    $canonicalHost = str_replace('www.', '', $host);
    $normalizedHost = $canonicalHost;

    $lines = [
        'User-agent: *',
        'Allow: /',
        '',
        '# Disallow admin and private areas',
        'Disallow: /admin/',
        'Disallow: /profile/',
        'Disallow: /login',
        'Disallow: /register',
        'Disallow: /password/',
        'Disallow: /api/catalog/',
        // Auth-only action links (add/remove from wishlist) rendered on public cards.
        'Disallow: /wishlist/',
        '',
        '# Allow important pages',
        'Allow: /guidings',
        'Allow: /vacations',
        $normalizedHost === 'catchaguide.de' ? 'Allow: /angelmagazin/' : 'Allow: /fishing-magazine/',
        '',
        '# Sitemap',
        "Sitemap: https://{$canonicalHost}/sitemap.xml",
        '',
        'Crawl-delay: 1',
    ];

    return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
});

Route::post('/get-user-location', [WelcomeController::class, 'getUserLocation'])->name('user.location');
Route::post('/guidings/search-place-log', [GuidingSearchPlaceLogController::class, 'store'])
    ->middleware('throttle:60,1')
    ->name('guidings.search-place-log.store');
Route::get('/maps/landmarks', MapLandmarkController::class)
    ->middleware('throttle:60,1')
    ->name('maps.landmarks');
Route::post('/language/switch', [LanguageController::class, 'switchLanguage'])->name('language.switch');
