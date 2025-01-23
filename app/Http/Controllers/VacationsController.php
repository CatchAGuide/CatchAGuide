<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vacation;
use App\Models\Destination;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class VacationsController extends Controller
{
    public function index(Request $request)
    {
        $countries = Destination::whereType('vacations')->get();
        return view('pages.countries.vacations', compact('countries'));
    }

    public function show($id)
    {
        $vacation = Vacation::where('id',$id)->where('status',1)->with('accommodations', 'boats', 'packages', 'guidings')->first();
        
        if (!$vacation) {
            abort(404);
        }

        $vacation->gallery = json_decode($vacation->gallery, true);
        
        // Calculate minimum guests across all services
        $minGuests = PHP_INT_MAX; // Start with maximum possible value
        
        // Check accommodations
        if ($vacation->accommodations->isNotEmpty()) {
            $minGuests = min($minGuests, $vacation->accommodations->min('min_guests') ?? PHP_INT_MAX);
        }
        
        // Check boats
        if ($vacation->boats->isNotEmpty()) {
            $minGuests = min($minGuests, $vacation->boats->min('min_guests') ?? PHP_INT_MAX);
        }
        
        // Check packages
        if ($vacation->packages->isNotEmpty()) {
            $minGuests = min($minGuests, $vacation->packages->min('min_guests') ?? PHP_INT_MAX);
        }
        
        // Check guidings
        if ($vacation->guidings->isNotEmpty()) {
            $minGuests = min($minGuests, $vacation->guidings->min('min_guests') ?? PHP_INT_MAX);
        }
        
        // If no minimum was found, set to 1 as default
        $vacation->min_guests = ($minGuests === PHP_INT_MAX) ? 1 : $minGuests;
        
        // Calculate price range
        $priceRange = $this->calculatePriceRange($vacation);
        $vacation->price_from = $priceRange['min'];
        $vacation->price_to = $priceRange['max'];
        
        // Calculate availability percentage for next 3 months
        $vacation->availability = $this->calculateAvailability($vacation);
        
        $sameCountries = Vacation::where('id', '!=', $vacation->id)
            ->where('country', $vacation->country)
            ->limit(10)
            ->get()
            ->map(function($vacation) {
                $vacation->gallery = json_decode($vacation->gallery, true);
                return $vacation;
            });
            
        return view('pages.vacations.show', compact('vacation', 'sameCountries'));
    }

    private function otherVacations(){
        $othervacations = Vacation::inRandomOrder('1234')->where('status',1)->limit(10)->get();

        return $othervacations;
    }

    private function otherVacationsBasedByLocation($latitude,$longitude){
        $nearestlisting = Vacation::select(['vacations.*']) // Include necessary attributes here
        ->selectRaw("(6371 * acos(cos(radians($latitude)) * cos(radians(lat)) * cos(radians(lng) - radians($longitude)) + sin(radians($latitude)) * sin(radians(lat)))) AS distance")
        ->orderBy('distance')
        ->where('status',1)
        ->limit(10)
        ->get();

        return $nearestlisting;
    }

    private function calculatePriceRange($vacation)
    {
        $prices = collect();
        
        // Collect all prices from different services
        if ($vacation->accommodations->isNotEmpty()) {
            $prices = $prices->merge($this->extractPricesFromDynamicFields($vacation->accommodations));
        }
        if ($vacation->boats->isNotEmpty()) {
            $prices = $prices->merge($this->extractPricesFromDynamicFields($vacation->boats));
        }
        if ($vacation->packages->isNotEmpty()) {
            $prices = $prices->merge($this->extractPricesFromDynamicFields($vacation->packages));
        }
        if ($vacation->guidings->isNotEmpty()) {
            $prices = $prices->merge($this->extractPricesFromDynamicFields($vacation->guidings));
        }
        
        return [
            'min' => $prices->min() ?? 0,
            'max' => $prices->max() ?? 0
        ];
    }

    private function extractPricesFromDynamicFields($items)
    {
        return $items->flatMap(function($item) {
            $fields = json_decode($item->dynamic_fields, true);
            return $fields['prices'] ?? [];
        })->filter();
    }

    private function calculateAvailability($vacation)
    {
        // This would need to be implemented based on your booking system
        // For now, returning a placeholder percentage
        return 75; // Example: 75% available
    }

    public function category(Request $request, $country)
    {
        $place_location = $country;
        $query = Destination::with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit']);

        $country_row = Destination::whereSlug($country)->whereType('vacations')->first();
        if (is_null($country_row)) {
            abort(404);
        }
        $query = $query->whereType('vacations')->whereId($country_row->id);
        $row_data = $query->first();

        if (is_null($row_data)) {
            abort(404);
        }

        $faq = $row_data->faq;
        $fish_chart = $row_data->fish_chart;
        $fish_size_limit = $row_data->fish_size_limit;
        $fish_time_limit = $row_data->fish_time_limit;

        $locale = Config::get('app.locale');

        $title = 'Holidays';
        $randomSeed = Session::get('random_seed');
        if (!$randomSeed) {
            $randomSeed = rand();
            Session::put('random_seed', $randomSeed);
        }

        $query = Vacation::where('status',1)->where('country',$country);

        if (empty($request->all())) {
            $query->orderByRaw("RAND($randomSeed)");
        }

        if($request->has('page')){
            $title .= __('vacations.Page') . ' ' . $request->page . ' - ';
        }

        if($request->has('num_guests') && !empty($request->get('num_guests'))){
            $title .= __('vacations.Guest') . ' ' . $request->num_guests . ' | ';
            // $q = $query->where('max_guests','>=',$request->get('num_guests'));
        }

        if($request->has('sortby') && !empty($request->get('sortby'))){
            switch ($request->get('sortby')) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;

                case 'price-asc':
                    // $query->orderBy(DB::raw('lowest_price'), 'asc');
                    break;

                case 'price-desc':
                    // $query->orderBy(DB::raw('lowest_price'), 'desc');
                    break;

                case 'long-duration':
                    // $query->orderBy('duration', 'desc');
                    break;

                case 'short-duration':
                    // $query->orderBy('duration', 'asc');
                    break;

                default:
                    if (empty($request->all())) {
                        $query->orderByRaw("RAND($randomSeed)");
                    }
            }
        }

        $filterData = json_decode($row_data->filters, true);

        $searchMessage = '';
        $placeLat = $filterData['placeLat'];
        $placeLng = $filterData['placeLng'];

        $title .= __('vacations.Country') . ' ' . $country . ' | ';
        if(!empty($placeLat) && !empty($placeLng) && !empty($request->get('place'))){

            $title .= __('vacations.Coordinates') . ' Lat ' . $placeLat . ' Lang ' . $placeLng . ' | ';
            $vacationFilter = Vacation::locationFilter($request->get('city'), $request->get('country'), null, $placeLat, $placeLng);
            $searchMessage = $vacationFilter['message'];
            $query->whereIn('id', $vacationFilter['ids']);
        }

        // if($request->has('price_range') && !empty($request->get('price_range'))){
        //     $price_range = explode('-', $request->get('price_range'));
            
        //     if(count($price_range) == 2) {
        //         $min_price = $price_range[0];
        //         $max_price = $price_range[1];
        //         $title .= __('guidings.Price') . ' ' . $min_price . '€ - ' . $max_price . '€ | ';
                
        //         $query->having(DB::raw('lowest_price'), '>=', $min_price)
        //               ->having(DB::raw('lowest_price'), '<=', $max_price);
        //     } elseif(count($price_range) == 1) {
        //         // Handle single value (350 and more)
        //         $min_price = $price_range[0];
        //         $title .= __('guidings.Price') . ' ' . $min_price . '€+ | ';
                
        //         $query->having(DB::raw('lowest_price'), '>=', $min_price);
        //     }
        // }

        $vacations_total = $query->count();
        $allVacations = $query->get();

        $othervacations = array();

        if($allVacations->isEmpty()){

            if($request->has('placeLat') && $request->has('placeLng') && !empty($request->get('placeLat')) && !empty($request->get('placeLng')) ){
                $latitude = $request->get('placeLat');
                $longitude = $request->get('placeLng');
            
                $othervacations = $this->otherVacationsBasedByLocation($latitude,$longitude);
            }else{
                $othervacations = $this->otherVacations();
            }
        }

       
        $vacations = $query->paginate(6);
        $vacations->appends(request()->except('page'));

        $data = compact('row_data', 'faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit', 'vacations', 'allVacations', 'othervacations', 'title', 'vacations_total');

        return view('pages.vacations.index', $data);
    }

}