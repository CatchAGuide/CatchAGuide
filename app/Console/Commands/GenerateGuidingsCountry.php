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
        }
        return 0;
    }

    public function getCountry($guiding)
    {

        $apiKey = env('GOOGLE_MAP_API_KEY');
        $client = new Client();

        $link = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$guiding->lat},{$guiding->lng}&key={$apiKey}";

        if (!$guiding->lat || !$guiding->lng) {
            return;
        }

        $response = $client->get($link);
        $data = json_decode($response->getBody(), true);

        // Check if the response is successful
        if ($data['status'] === 'OK') {
            foreach ($data['results'][0]['address_components'] as $component) {
                if (in_array('country', $component['types'])) {
                    $guiding->country = $component['long_name'] ?? null;
                    $guiding->save();

                    $this->info('Guiding ' . $guiding->id);
                }
            }
        }
    }
}
