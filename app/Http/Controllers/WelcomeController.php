<?php

namespace App\Http\Controllers;

use App\Models\Guiding;
use App\Models\Target;
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
        // if (Cache::has('homepage_data_' . app()->getLocale())) {
        //     // If cached, retrieve the data from the cache for the current locale
        //     $data = Cache::get('homepage_data_' . app()->getLocale());
        // } else {
        //     // If not cached, fetch the data from the database for the current locale and cache it
        //     $mostBookedGuidings = Guiding::withCount('bookings')
        //         ->where('status', 1)
        //         ->orderBy('bookings_count', 'desc')
        //         ->take(5)
        //         ->get();
        
        //     $data = [
        //         'recent_guidings' => Guiding::where('status', 1)->orderBy('created_at', 'desc')->limit(8)->get(),
        //         'most_booked_guidings' => $mostBookedGuidings,
        //         'threads' => Thread::orderBy('id', 'desc')->where('language', app()->getLocale())->limit(5)->get(),
        //         'targets' => Target::orderBy('name', 'asc')->get()
        //     ];
        
        //     // Cache the data for 5 minutes (adjust the cache duration as needed)
        //     Cache::put('homepage_data_' . app()->getLocale(), $data, 3600);
        // }
        // return view('pages.index', $data);
        $CategoryPage = CategoryPage::orderBy('name', 'asc')->whereType('Targets')->whereIsFavorite(1)->get();

        return view('pages.newhome-latest', compact('CategoryPage'));
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
