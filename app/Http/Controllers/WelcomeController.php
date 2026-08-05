<?php

namespace App\Http\Controllers;

use App\Models\Guiding;
use App\Services\Homepage\HomepageLandingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    const MAX_GUIDINGS = 8;

    public function __construct(
        private HomepageLandingService $landing,
    ) {}

    public function index(): View
    {
        return view('pages.home.landing', $this->landing->build());
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
