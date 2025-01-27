<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use App\Models\Guiding;
use Illuminate\Console\Command;

class GenerateGuidingsCountry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:generatecountry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $guidings = Guiding::all();

        foreach ($guidings as $guiding) {
            $this->getCountry($guiding);
            sleep(1);
        }
        return 0;
    }

    public function getCountry($guiding)
    {
        $searchString = $guiding->location;

        try {
            $client = new \GuzzleHttp\Client();
            
            // First try with regions (countries) if the search string is short (likely a country name)
            if (str_word_count($searchString) === 1) {
                $autocompleteResponse = $client->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                    'query' => [
                        'input' => $searchString,
                        'types' => '(regions)',  // This includes countries and administrative areas
                        'language' => 'en',
                        'key' => env('GOOGLE_MAP_API_KEY')
                    ]
                ]);
                
                $autocompleteResult = json_decode($autocompleteResponse->getBody(), true);
                
                if ($autocompleteResult['status'] === 'OK' && !empty($autocompleteResult['predictions'])) {
                    // Process country result
                    $placeId = $autocompleteResult['predictions'][0]['place_id'];
                } else {
                    // If no country found, try cities
                    $autocompleteResponse = $client->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                        'query' => [
                            'input' => $searchString,
                            'types' => '(cities)',
                            'language' => 'en',
                            'key' => env('GOOGLE_MAP_API_KEY')
                        ]
                    ]);
                    $autocompleteResult = json_decode($autocompleteResponse->getBody(), true);
                    
                    if ($autocompleteResult['status'] === 'OK' && !empty($autocompleteResult['predictions'])) {
                        $placeId = $autocompleteResult['predictions'][0]['place_id'];
                    }
                }
            } else {
                // Try with a text search first for more flexible matching
                $searchResponse = $client->get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
                    'query' => [
                        'query' => $searchString,
                        'type' => 'locality',  // Focus on localities/cities
                        'language' => 'en',
                        'key' => env('GOOGLE_MAP_API_KEY')
                    ]
                ]);
                
                $searchResult = json_decode($searchResponse->getBody(), true);

                if ($searchResult['status'] === 'OK' && !empty($searchResult['results'])) {
                    $placeId = $searchResult['results'][0]['place_id'];
                } else {
                    // Fallback to autocomplete if text search doesn't work
                    $autocompleteResponse = $client->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                        'query' => [
                            'input' => $searchString,
                            'types' => '(cities)',
                            'language' => 'en',
                            'key' => env('GOOGLE_MAP_API_KEY')
                        ]
                    ]);
                    $autocompleteResult = json_decode($autocompleteResponse->getBody(), true);

                    if ($autocompleteResult['status'] === 'OK' && !empty($autocompleteResult['predictions'])) {
                        $placeId = $autocompleteResult['predictions'][0]['place_id'];
                    }
                }
            }
            
            if (isset($placeId)) {
                $detailsResponse = $client->get('https://maps.googleapis.com/maps/api/place/details/json', [
                    'query' => [
                        'place_id' => $placeId,
                        'fields' => 'address_component',  // Removed invalid field
                        'language' => 'en',
                        'key' => env('GOOGLE_MAP_API_KEY')
                    ]
                ]);
                
                $detailsResult = json_decode($detailsResponse->getBody(), true);
                
                if ($detailsResult['status'] === 'OK') {
                    $components = $detailsResult['result']['address_components'];
                    $location = [
                        'city' => null,
                        'country' => null,
                        'region' => null,
                        'original' => $searchString,
                        'language' => null
                    ];

                    foreach ($components as $component) {
                        if (in_array('locality', $component['types'])) {
                            $location['city'] = $component['long_name'];
                        }
                        if (in_array('administrative_area_level_1', $component['types'])) {
                            $location['region'] = $component['long_name'];
                            $location['language'] = $component['short_name'];
                        }
                        if (in_array('country', $component['types'])) {
                            $location['country'] = $component['long_name'];
                            if (!$location['language']) {
                                $location['language'] = $component['short_name'];
                            }
                        }
                    }
                    
                    if ($location['city'] || $location['country']) {
                        // Save to locations table
                        $translation = [
                            'city' => [],
                            'country' => [],
                            'region' => []
                        ];

                        if ($location['city'] && $searchString !== $location['city']) {
                            $translation['city'][$searchString] = $location['language'];
                        }
                        if ($location['country'] && $searchString !== $location['country']) {
                            $translation['country'][$searchString] = $location['language'];
                        }
                        if ($location['region'] && $searchString !== $location['region']) {
                            $translation['region'][$searchString] = $location['language'];
                        }

                        if ($guiding->city != $location['city']) {
                            $guiding->city = $location['city'] ?? null;
                        }
                        if ($guiding->country != $location['country']) {
                            $guiding->country = $location['country'] ?? null;
                        }
                        if ($guiding->region != $location['region']) {
                            $guiding->region = $location['region'] ?? null;
                        }
                         // Save the region
                         $guiding->save();
            
                        $this->info('Guiding Update: ' . $guiding->id);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Google Places API Error: ' . $e->getMessage());
        }
    }
}
