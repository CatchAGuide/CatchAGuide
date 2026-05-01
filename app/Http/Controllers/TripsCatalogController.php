<?php

namespace App\Http\Controllers;

use App\Services\Trip\TripLocationCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public trip “country” catalog — mirrors vacations index + vacations/c/{slug} using trips + destinations.type=trips.
 */
class TripsCatalogController extends Controller
{
    public function __construct(
        private TripLocationCatalogService $catalog
    ) {}

    public function index(): View
    {
        $locations = $this->catalog->listLocationsForLocale();

        return view('pages.trips.locations', compact('locations'));
    }

    public function category(Request $request, string $location): View
    {
        $data = $this->catalog->buildCategoryPageData($request, $location);

        return view('pages.trips.category', $data);
    }
}
