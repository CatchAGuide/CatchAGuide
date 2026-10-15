<?php

namespace App\Http\Controllers;

use App\Models\Guiding;
use App\Services\Guidings\GuidingsLandingService;
use App\Services\Homepage\HomepageLandingService;
use App\Services\Seo\SeoRobotsPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __construct(
        private HomepageLandingService $landing,
        private GuidingsLandingService $guidingsLandingService,
    ) {}

    public function index(): View
    {
        return view('pages.home.landing', $this->landing->build());
    }

    /**
     * Former production homepage — now the Guidings marketing landing
     * reached from the homepage product chooser.
     *
     * /guidings used to be the listing, and Google still crawls its old filter and page URLs
     * (?guide_id=…&page=14). Those rendered this landing page as indexable duplicates, so they
     * move to the listing, which noindexes filtered views.
     */
    public function guidingsLanding(Request $request, SeoRobotsPolicy $robots): View|RedirectResponse
    {
        if ($robots->hasListingParams($request)) {
            return redirect()->route('guidings.index', $request->query(), 301);
        }

        return view('pages.newhome-latest', $this->guidingsLandingService->build());
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
