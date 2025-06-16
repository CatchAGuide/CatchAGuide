<?php

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use GuzzleHttp\Client;

if (!function_exists('two')) {
    function two($number) {
        return number_format($number, 2, ',', '.');
    }
}

if (!function_exists('twoString')) {
    function twoString($number) {
        return number_format($number, 2, '.');
    }
}

if (!function_exists('one')) {
    function one($number) {
        return number_format($number, 1, ',', '.');
    }
}

if(!function_exists('slugify')) {
    function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }
}

if (!function_exists('getLocalizedValue')) {
    function getLocalizedValue($model) {
        $locale = app()->getLocale();

        if($locale == 'de'){
            return $model->name;
        }
        if($locale == 'en'){
            if(isset($model->name_en) && !empty($model->name_en)){
                return $model->name_en;
            }else{
                return $model->name;
            }
        }

    }
}

if (!function_exists('media_upload')) {
    function media_upload($file, $directory = 'uploads', $filename = null, $quality = 75)
    {
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            
            $image = Image::make($file);
            if (!$filename) {
                $filename = basename(parse_url($file, PHP_URL_PATH));
            }

        } elseif ($file instanceof \Illuminate\Http\UploadedFile) {

            $thumbnail_path = $file->store('public/' . $directory);
            $imagePath = Storage::disk()->path($thumbnail_path);
            $image = Image::make($imagePath);
            if (!$filename) {
                $filename = $file->getClientOriginalName();
            }

        } else {
            throw new \InvalidArgumentException('Invalid input: must be a URL or an uploaded file');
        }
        
        // Check file size and resize if needed
        $fileSize = strlen($image->encode('webp', $quality)->encoded);
        if ($fileSize > 2048 * 1024) { // If larger than 2048KB
            $width = $image->width();
            $height = $image->height();
            
            // Calculate new dimensions while maintaining aspect ratio
            $ratio = sqrt(2048 * 1024 / $fileSize);
            $newWidth = round($width * $ratio);
            $newHeight = round($height * $ratio);
            
            $image->resize($newWidth, $newHeight);
        }

        // Hash the filename
        $hashedName = md5(pathinfo($filename, PATHINFO_FILENAME) . time());
        $webpImageName = $hashedName . '.webp';
        $webpImage = $image->encode('webp', $quality);
        
        $webp_path = $directory . '/' . $webpImageName;

        // Ensure the directory exists in both storage and public
        Storage::disk('public')->makeDirectory($directory);
        if (!file_exists(public_path($directory))) {
            mkdir(public_path($directory), 0755, true);
        }

        // Check if file exists and delete it
        if (Storage::disk('public')->exists($webp_path)) {
            Storage::disk('public')->delete($webp_path);
        }
        if (file_exists(public_path($webp_path))) {
            unlink(public_path($webp_path));
        }

        // Save new file
        Storage::disk('public')->put($webp_path, $webpImage->encoded);
        $webpImage->save(public_path($webp_path));
        
        return $webp_path;
    }
}

if (!function_exists('media_delete')) {
    function media_delete($path)
    {
        // Remove any double slashes
        $path = str_replace('//', '/', $path);
        
        // Delete from storage
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        
        // Delete from public path
        $publicPath = public_path($path);
        if (file_exists($publicPath)) {
            unlink($publicPath);
        }
        
        return true;
    }
}

if (!function_exists('getCoordinatesFromLocation')) {
    function getCoordinatesFromLocation(string $location, bool $cityCheck = false): ?array 
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'query' => [
                    'address' => $location,
                    'key' => env('GOOGLE_MAPS_API_KEY')
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if ($data['status'] === 'OK') {
                $result = $data['results'][0];
                $location = $result['geometry']['location'];
                
                $parsedResult = [
                    'lat' => $location['lat'],
                    'lng' => $location['lng'],
                    'city' => null,
                    'country' => null,
                    'types' => []
                ];

                foreach ($result['address_components'] as $component) {
                    if (in_array('locality', $component['types'])) {
                        $parsedResult['city'] = $component['long_name'];
                    }
                    if (in_array('administrative_area_level_1', $component['types'])) {
                        // Use state/province capital if city not found
                        if (!$parsedResult['city']) {
                            $parsedResult['city'] = $component['long_name'];
                        }
                    }
                    if (in_array('country', $component['types'])) {
                        $parsedResult['country'] = $component['long_name'];
                        // If no city foun
                        if (!$parsedResult['city'] && $cityCheck) {
                            $capitalResponse = $client->get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
                                'query' => [
                                    'query' => "{$component['long_name']} capital city",
                                    'key' => env('GOOGLE_MAPS_API_KEY')
                                ]
                            ]);
                            $capitalData = json_decode($capitalResponse->getBody(), true);
                            if ($capitalData['status'] === 'OK' && !empty($capitalData['results'])) {
                                $parsedResult['city'] = $capitalData['results'][0]['name'];
                                return getCoordinatesFromLocation("{$parsedResult['city']}, {$component['long_name']}");
                            }
                        }
                    }
                    $parsedResult['types'][] = $component['types'][0];
                }

                return $parsedResult;
            }
        } catch (\Exception $e) {
            \Log::error('Google Maps API error: ' . $e->getMessage());
        }

        return null;
    }
}

if (!function_exists('getLocationDetailsGoogle')) {
    function getLocationDetailsGoogle($city = null, $country = null, $region = null): ? array 
    {
        $searchString = implode(', ', array_filter([$city, $country, $region], fn($val) => !empty($val)));
        // First check in the locations table
        $location = \App\Models\Location::where(function($query) use ($searchString) {
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(translation, '$.city')) = ?", [$searchString])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(translation, '$.city')) LIKE ?", ['%' . $searchString . '%'])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(translation, '$.country')) = ?", [$searchString])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(translation, '$.country')) LIKE ?", ['%' . $searchString . '%'])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(translation, '$.region')) = ?", [$searchString])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(translation, '$.region')) LIKE ?", ['%' . $searchString . '%']);
        })
        ->select('city', 'country', 'region')
        ->first();
        
        if ($location) {
            if ($location->city || $location->country || $location->region) {
                return [
                    'city' => $location->city,
                    'country' => $location->country, 
                    'region' => $location->region
                ];
            }
        }

        // If not found in DB, try to translate using Gemini and check again
        try {
            $requestTranslate = json_encode(['city' => $city, 'country' => $country, 'region' => $region]);
            $translationService = new \App\Services\Translation\GeminiTranslationService();
            
            $translationPrompt = "Translate this location search to English: \"$requestTranslate\"\n\n";
            $translationPrompt .= "Return only the translated location string in the same format. Examples:\n";
            $translationPrompt .= "Only return the translated string, no explanation.";

            $translatedString = $translationService->translate($translationPrompt);
            $translatedString = json_decode($translatedString, true);
            
            if (isset($translatedString['city']) && isset($translatedString['country']) && isset($translatedString['region'])) {
                return [
                    'city' => $translatedString['city'],
                    'country' => $translatedString['country'],  
                    'region' => $translatedString['region']
                ];
            }else{
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Gemini translation error in getLocationDetailsGoogle: ' . $e->getMessage());
        }

        return null;
    }
}

if (!function_exists('getLocationDetails')) {
    function getLocationDetails(string $searchString): ?array 
    {
        // First check in the locations table
        $location = \App\Models\Location::where(function($query) use ($searchString) {
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(translation, '$.city')) = ?", [$searchString])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(translation, '$.city')) LIKE ?", ['%' . $searchString . '%'])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(translation, '$.country')) = ?", [$searchString])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(translation, '$.country')) LIKE ?", ['%' . $searchString . '%'])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(translation, '$.region')) = ?", [$searchString])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(translation, '$.region')) LIKE ?", ['%' . $searchString . '%']);
        })
        ->select('city', 'country', 'region')
        ->first();

        if ($location) {
            if ($location->city || $location->country || $location->region) {
                return [
                    'city' => $location->city,
                    'country' => $location->country, 
                    'region' => $location->region
                ];
            }
        }

        try {
            $client = new \GuzzleHttp\Client();
            
            $placeId = null;
            // First try with regions (countries) if the search string is short (likely a country name)
            if (str_word_count($searchString) === 1) {
                $autocompleteResponse = $client->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                    'query' => [
                        'input' => $searchString,
                        'types' => '(regions)',  // This includes countries and administrative areas
                        'language' => 'en',
                        'region' => 'us',  // Force English results by using US region
                        'key' => env('GOOGLE_MAPS_API_KEY')
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
                            'region' => 'us',  // Force English results by using US region
                            'key' => env('GOOGLE_MAPS_API_KEY')
                        ]
                    ]);
                    $autocompleteResult = json_decode($autocompleteResponse->getBody(), true);
                    
                    if ($autocompleteResult['status'] === 'OK' && !empty($autocompleteResult['predictions'])) {
                        $placeId = $autocompleteResult['predictions'][0]['place_id'];
                    }
                }
            } else {
                // First try with administrative areas for region searches
                $regionResponse = $client->get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
                    'query' => [
                        'query' => $searchString,
                        'type' => 'administrative_area_level_1',
                        'language' => 'en',
                        'region' => 'us',  // Force English results by using US region
                        'key' => env('GOOGLE_MAPS_API_KEY')
                    ]
                ]);
                
                $regionResult = json_decode($regionResponse->getBody(), true);
                
                if ($regionResult['status'] === 'OK' && !empty($regionResult['results'])) {
                    $placeId = $regionResult['results'][0]['place_id'];
                } else {
                    // Try with a text search for cities if region search fails
                    $searchResponse = $client->get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
                        'query' => [
                            'query' => $searchString,
                            'type' => 'locality',  // Focus on localities/cities
                            'language' => 'en',
                            'region' => 'us',  // Force English results by using US region
                            'key' => env('GOOGLE_MAPS_API_KEY')
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
                                'region' => 'us',  // Force English results by using US region
                                'key' => env('GOOGLE_MAPS_API_KEY')
                            ]
                        ]);
                        $autocompleteResult = json_decode($autocompleteResponse->getBody(), true);

                        if ($autocompleteResult['status'] === 'OK' && !empty($autocompleteResult['predictions'])) {
                            $placeId = $autocompleteResult['predictions'][0]['place_id'];
                        }
                    }
                }
            }

            if (isset($placeId)) {
                $detailsResponse = $client->get('https://maps.googleapis.com/maps/api/place/details/json', [
                    'query' => [
                        'place_id' => $placeId,
                        'fields' => 'address_component',  // Removed invalid field
                        'language' => 'en',
                        'region' => 'us',  // Force English results by using US region
                        'key' => env('GOOGLE_MAPS_API_KEY')
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

                        if (!empty($translation['city']) || !empty($translation['country'])) {
                            \App\Models\Location::updateOrCreate(
                                [
                                    'city' => $location['city'],
                                    'country' => $location['country'],
                                    'region' => $location['region']
                                ],
                                [
                                    'translation' => $translation
                                ]
                            );
                        }

                        return [
                            'city' => $location['city'],
                            'country' => $location['country'],
                            'region' => $location['region']
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Google Places API Error: ' . $e->getMessage());
        }

        return null;
    }
}