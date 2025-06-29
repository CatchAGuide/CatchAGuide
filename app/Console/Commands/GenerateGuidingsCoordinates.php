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
     * Get coordinates for a guiding using Google Geocoding API
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

        try {
            $client = new Client();
            
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
                    
                    $this->line("  → Coordinates set: {$lat}, {$lng}");
                    
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
                $this->error("  → Google API request denied. Check your API key and permissions.");
                return false;
            } else {
                $this->error("  → Google API returned status: {$result['status']}");
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error('Google Geocoding API Error', [
                'guiding_id' => $guiding->id,
                'location' => $searchString,
                'error' => $e->getMessage()
            ]);
            throw $e;
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