<?php

namespace App\Http\Controllers;

use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryPage;
use App\Models\Guiding;
use App\Services\Homepage\HomepageCountrySelector;
use App\Services\Homepage\HomepageLandingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    const MAX_GUIDINGS = 8;

    public function __construct(
        private HomepageLandingService $landing,
        private HomepageCountrySelector $countries,
    ) {}

    public function index(): View
    {
        return view('pages.home.landing', $this->landing->build());
    }

    /**
     * Former production homepage — now the Guidings marketing landing
     * reached from the homepage product chooser.
     */
    public function guidingsLanding(): View
    {
        $allCategoryPages = CategoryPage::query()
            ->orderBy('name', 'asc')
            ->whereIn('type', ['Targets', 'Methods'])
            ->where('is_favorite', 1)
            ->get();

        return view('pages.newhome-latest', [
            'CategoryPage' => $allCategoryPages->where('type', 'Targets'),
            'CategoryPageMethods' => $allCategoryPages->where('type', 'Methods'),
            'featuredCountries' => $this->countries->featured(null, CategoryPageScope::TOURS),
        ]);
    }

    public function getUserLocation(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return response()->json([]);
        }

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return response()->json([]);
        }

        $nearestlistings = Guiding::select(['guidings.*'])
            ->selectRaw('(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance', [$latitude, $longitude, $latitude])
            ->orderBy('distance')
            ->limit(4)
            ->publiclyVisible()
            ->get();

        $nearestlistings = $nearestlistings->map(function ($listing) {
            $listing->title = translate($listing->title);

            $images = app('guiding')->getImagesUrl($listing);
            $listing->image_url = isset($images['image_0']) ? $images['image_0'] : null;

            return $listing;
        });

        return $nearestlistings;
    }
}
