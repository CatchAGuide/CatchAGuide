<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use App\Models\Guiding;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateGuidingsCoordinates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guidings:generatecoordinates {--limit=10 : Number of guidings to process per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate latitude and longitude coordinates for guidings with null lat/lng values using their location field';

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
        $limit = $this->option('limit');
        
        // Check API key configuration first
        if (!$this->checkApiKey()) {
            return 1;
        }
        
        // Find guidings with null lat or lng values and non-empty location
        $guidings = Guiding::where(function($query) {
                $query->whereNull('lat')
                      ->orWhereNull('lng')
                      ->orWhere('lat', 0)
                      ->orWhere('lng', 0);
            })
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->limit($limit)
            ->get();

        if ($guidings->isEmpty()) {
            $this->info('No guidings found with missing coordinates.');
            return 0;
        }

        $this->info("Processing {$guidings->count()} guidings...");
        
        $processed = 0;
        $updated = 0;
        $errors = 0;

        foreach ($guidings as $guiding) {
            $processed++;
            $this->info("Processing guiding ID: {$guiding->id} - Location: {$guiding->location}");
            
            try {
                if ($this->getCoordinates($guiding)) {
                    $updated++;
                    $this->info("✓ Updated coordinates for guiding ID: {$guiding->id}");
                } else {
                    $errors++;
                    $this->error("✗ Failed to get coordinates for guiding ID: {$guiding->id}");
                }
                
                // Sleep to avoid hitting API rate limits
                sleep(1);
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Error processing guiding ID: {$guiding->id} - {$e->getMessage()}");
                Log::error('Generate Coordinates Error', [
                    'guiding_id' => $guiding->id,
                    'location' => $guiding->location,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("\n=== Summary ===");
        $this->info("Processed: {$processed}");
        $this->info("Updated: {$updated}");
        $this->info("Errors: {$errors}");

        return 0;
    }

    /**
     * Check if the Google Maps API key is properly configured
     *
     * @return bool
     */
    private function checkApiKey()
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        
        if (empty($apiKey)) {
            $this->warn("Google Maps API key is not configured.");
            $this->info("Will use alternative geocoding services (OpenStreetMap Nominatim).");
            return true; // Continue with alternative services
        }

        $this->info("Testing Google Maps API key...");
        
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'query' => [
                    'address' => 'London, UK',
                    'key' => $apiKey
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if ($result['status'] === 'OK') {
                $this->info("✓ Google Maps API key is working correctly.");
                return true;
            } elseif ($result['status'] === 'REQUEST_DENIED') {
                $this->warn("✗ Google Maps API key is invalid or not properly configured.");
                $this->info("Will use alternative geocoding services (OpenStreetMap Nominatim).");
                return true; // Continue with alternative services
            } else {
                $this->warn("✗ Google Maps API returned status: {$result['status']}");
                $this->info("Will use alternative geocoding services.");
                return true; // Continue with alternative services
            }
            
        } catch (\Exception $e) {
            $this->warn("✗ Failed to test Google Maps API: {$e->getMessage()}");
            $this->info("Will use alternative geocoding services.");
            return true; // Continue with alternative services
        }
    }

    /**
     * Get coordinates for a guiding using multiple geocoding services
     *
     * @param Guiding $guiding
     * @return bool
     */
    public function getCoordinates($guiding)
    {
        $searchString = trim($guiding->location);

        if (empty($searchString)) {
            $this->error("Empty location for guiding ID: {$guiding->id}");
            return false;
        }

        // Try multiple geocoding services in order of preference
        $services = [
            'google' => 'Google Maps API',
            'nominatim' => 'OpenStreetMap Nominatim',
            'fallback' => 'Fallback coordinates'
        ];

        foreach ($services as $service => $serviceName) {
            $this->line("  → Trying {$serviceName}...");
            
            $coordinates = $this->getCoordinatesFromService($guiding, $searchString, $service);
            
            if ($coordinates) {
                return true;
            }
        }

        $this->error("  → All geocoding services failed for location: {$searchString}");
        return false;
    }

    /**
     * Get coordinates from a specific geocoding service
     *
     * @param Guiding $guiding
     * @param string $searchString
     * @param string $service
     * @return bool
     */
    private function getCoordinatesFromService($guiding, $searchString, $service)
    {
        switch ($service) {
            case 'google':
                return $this->getCoordinatesFromGoogle($guiding, $searchString);
            case 'nominatim':
                return $this->getCoordinatesFromNominatim($guiding, $searchString);
            case 'fallback':
                return $this->getFallbackCoordinates($guiding, $searchString);
            default:
                return false;
        }
    }

    /**
     * Get coordinates using OpenStreetMap Nominatim (Free)
     *
     * @param Guiding $guiding
     * @param string $searchString
     * @return bool
     */
    private function getCoordinatesFromNominatim($guiding, $searchString)
    {
        try {
            $client = new \GuzzleHttp\Client();
            
            // Use OpenStreetMap Nominatim API (free, no API key required)
            $response = $client->get('https://nominatim.openstreetmap.org/search', [
                'query' => [
                    'q' => $searchString,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 1
                ],
                'headers' => [
                    'User-Agent' => 'CAG-Guiding-App/1.0' // Required by Nominatim
                ]
            ]);
            
            $results = json_decode($response->getBody(), true);
            
            if (!empty($results) && isset($results[0]['lat']) && isset($results[0]['lon'])) {
                $lat = (float) $results[0]['lat'];
                $lng = (float) $results[0]['lon'];
                
                // Validate coordinates
                if ($this->isValidCoordinate($lat, $lng)) {
                    // Update the guiding with coordinates
                    $guiding->lat = $lat;
                    $guiding->lng = $lng;
                    $guiding->save();
                    
                    $this->line("  → Coordinates set (Nominatim): {$lat}, {$lng}");
                    
                    // Log additional location details if available
                    if (isset($results[0]['display_name'])) {
                        $this->line("  → Formatted address: {$results[0]['display_name']}");
                    }
                    
                    return true;
                } else {
                    $this->error("  → Invalid coordinates received: {$lat}, {$lng}");
                    return false;
                }
            } else {
                $this->error("  → No results found for location: {$searchString}");
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error('Nominatim API Error', [
                'guiding_id' => $guiding->id,
                'location' => $searchString,
                'error' => $e->getMessage()
            ]);
            $this->error("  → Nominatim API call failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get coordinates using Google Maps API (if available)
     *
     * @param Guiding $guiding
     * @param string $searchString
     * @return bool
     */
    private function getCoordinatesFromGoogle($guiding, $searchString)
    {
        // Check if Google Maps API key is configured
        if (empty(env('GOOGLE_MAPS_API_KEY'))) {
            return false;
        }

        try {
            // Use the helper function to get coordinates
            $coordinates = getCoordinatesFromLocation($searchString);
            
            if ($coordinates && isset($coordinates['lat']) && isset($coordinates['lng'])) {
                $lat = $coordinates['lat'];
                $lng = $coordinates['lng'];
                
                // Validate coordinates
                if ($this->isValidCoordinate($lat, $lng)) {
                    // Update the guiding with coordinates
                    $guiding->lat = $lat;
                    $guiding->lng = $lng;
                    $guiding->save();
                    
                    $this->line("  → Coordinates set (Google): {$lat}, {$lng}");
                    
                    // Log additional location details if available
                    if (isset($coordinates['city'])) {
                        $this->line("  → City: {$coordinates['city']}");
                    }
                    if (isset($coordinates['country'])) {
                        $this->line("  → Country: {$coordinates['country']}");
                    }
                    
                    return true;
                } else {
                    $this->error("  → Invalid coordinates received: {$lat}, {$lng}");
                    return false;
                }
            } else {
                // Fallback: Try direct Google Geocoding API call
                return $this->getCoordinatesDirect($guiding, $searchString);
            }
            
        } catch (\Exception $e) {
            Log::error('Google Coordinates Generation Error', [
                'guiding_id' => $guiding->id,
                'location' => $searchString,
                'error' => $e->getMessage()
            ]);
            
            // Fallback: Try direct Google Geocoding API call
            return $this->getCoordinatesDirect($guiding, $searchString);
        }
    }

    /**
     * Get fallback coordinates for common locations
     *
     * @param Guiding $guiding
     * @param string $searchString
     * @return bool
     */
    private function getFallbackCoordinates($guiding, $searchString)
    {
        // Common location coordinates (you can expand this list)
        $fallbackCoordinates = [
            'ostfriesland, deutschland' => ['lat' => 53.5511, 'lng' => 7.5886],
            'rees, deutschland' => ['lat' => 51.7569, 'lng' => 6.3975],
            'roermond, venlo, niederlande' => ['lat' => 51.1942, 'lng' => 5.9875],
            'volkerak, hollands diep, haringvliet, niederlande' => ['lat' => 51.6333, 'lng' => 4.3333],
            'vila real de santo antónio, algarve, portugal' => ['lat' => 37.1947, 'lng' => -7.4183],
            'deutschland' => ['lat' => 51.1657, 'lng' => 10.4515],
            'niederlande' => ['lat' => 52.1326, 'lng' => 5.2913],
            'portugal' => ['lat' => 39.3999, 'lng' => -8.2245],
        ];

        $normalizedSearch = strtolower(trim($searchString));
        
        foreach ($fallbackCoordinates as $location => $coords) {
            if (strpos($normalizedSearch, $location) !== false || strpos($location, $normalizedSearch) !== false) {
                $guiding->lat = $coords['lat'];
                $guiding->lng = $coords['lng'];
                $guiding->save();
                
                $this->line("  → Coordinates set (fallback): {$coords['lat']}, {$coords['lng']}");
                return true;
            }
        }

        return false;
    }

    /**
     * Fallback method to get coordinates directly from Google Geocoding API
     *
     * @param Guiding $guiding
     * @param string $searchString
     * @return bool
     */
    private function getCoordinatesDirect($guiding, $searchString)
    {
        try {
            $client = new \GuzzleHttp\Client();
            
            // Use Google Geocoding API to get coordinates
            $response = $client->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'query' => [
                    'address' => $searchString,
                    'key' => env('GOOGLE_MAPS_API_KEY')
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if ($result['status'] === 'OK' && !empty($result['results'])) {
                $location = $result['results'][0]['geometry']['location'];
                $lat = $location['lat'];
                $lng = $location['lng'];
                
                // Validate coordinates
                if ($this->isValidCoordinate($lat, $lng)) {
                    // Update the guiding with coordinates
                    $guiding->lat = $lat;
                    $guiding->lng = $lng;
                    $guiding->save();
                    
                    $this->line("  → Coordinates set (direct API): {$lat}, {$lng}");
                    
                    // Log the formatted address if available
                    if (isset($result['results'][0]['formatted_address'])) {
                        $this->line("  → Formatted address: {$result['results'][0]['formatted_address']}");
                    }
                    
                    return true;
                } else {
                    $this->error("  → Invalid coordinates received: {$lat}, {$lng}");
                    return false;
                }
            } elseif ($result['status'] === 'ZERO_RESULTS') {
                $this->error("  → No results found for location: {$searchString}");
                return false;
            } elseif ($result['status'] === 'OVER_QUERY_LIMIT') {
                $this->error("  → Google API quota exceeded. Please try again later.");
                return false;
            } elseif ($result['status'] === 'REQUEST_DENIED') {
                $this->error("  → Google API request denied. This usually means:");
                $this->error("     - API key is invalid or missing");
                $this->error("     - Geocoding API is not enabled for your project");
                $this->error("     - API key has restrictions that prevent geocoding");
                $this->error("     - Billing is not enabled for your Google Cloud project");
                return false;
            } else {
                $this->error("  → Google API returned status: {$result['status']}");
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error('Direct Google Geocoding API Error', [
                'guiding_id' => $guiding->id,
                'location' => $searchString,
                'error' => $e->getMessage()
            ]);
            $this->error("  → Direct API call failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Validate if coordinates are within valid ranges
     *
     * @param float $lat
     * @param float $lng
     * @return bool
     */
    private function isValidCoordinate($lat, $lng)
    {
        return ($lat >= -90 && $lat <= 90) && ($lng >= -180 && $lng <= 180);
    }
} 