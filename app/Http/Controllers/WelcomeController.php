<?php

namespace App\Http\Controllers;

use App\Models\Guiding;
use App\Models\Target;
use App\Models\Method;
use App\Models\Thread;
use App\Models\Booking;
use App\Models\CategoryPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


use Illuminate\Support\Facades\Cache;

class WelcomeController extends Controller
{
    const MAX_GUIDINGS = 8;

    public function index()
    {
        $language = app()->getLocale();
        $allCategoryPages = CategoryPage::orderBy('name', 'asc')
            ->whereIn('type', ['Targets', 'Methods'])
            ->where('is_favorite', 1)
            ->get();
        
        // Filter for Targets
        $CategoryPage = $allCategoryPages->where('type', 'Targets');
        
        // Filter for Methods
        $CategoryPageMethods = $allCategoryPages->where('type', 'Methods');

        return view('pages.newhome-latest', compact('CategoryPage', 'CategoryPageMethods'));
    }
    
    public function getUserLocation(Request $request)
    {

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        $nearestlistings = Guiding::select(['guidings.*'])
            ->selectRaw("(6371 * acos(cos(radians($latitude)) * cos(radians(lat)) * cos(radians(lng) - radians($longitude)) + sin(radians($latitude)) * sin(radians(lat)))) AS distance")
            ->orderBy('distance')
            ->limit(4)
            ->where('status',1)
            ->get();

        $nearestlistings = $nearestlistings->map(function ($listing) {
            $listing->title = translate($listing->title);
            
            // Check if 'image_0' exists before accessing it
            $images = app('guiding')->getImagesUrl($listing);
            $listing->image_url = isset($images['image_0']) ? $images['image_0'] : null; // Set to null or a default value if not set
            
            return $listing;
        });

    
        return $nearestlistings;
    }
}
