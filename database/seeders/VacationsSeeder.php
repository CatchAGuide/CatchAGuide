<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class VacationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('vacations')->truncate();
        $csvFile = public_path('Productpage_Vacations.csv');
        $vacations = [];
        
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            // Skip header row
            fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== FALSE) {
                $vacations[] = [
                    'title' => $data[1],
                    'slug' => $data[2], 
                    'location' => $data[3],
                    'city' => $data[4],
                    'country' => $data[5],
                    'latitude' => $data[6], // Changed from lat to latitude
                    'longitude' => $data[7], // Changed from lng to longitude
                    'region' => $data[8],
                    'gallery' => json_encode(explode("\n", $data[9])), // Convert newline separated list to JSON array
                    'best_travel_times' => json_encode(explode("\n", $data[10])), // Convert newline separated list to JSON array
                    'surroundings_description' => $data[11],
                    'target_fish' => json_encode(array_map('trim', explode(',', $data[12]))), // Convert comma separated list to JSON array
                    'airport_distance' => $data[13],
                    'water_distance' => $data[14],
                    'shopping_distance' => $data[15],
                    'travel_included' => $data[16],
                    'travel_options' => json_encode(explode("\n", $data[17])), // Convert newline separated list to JSON array
                    'pets_allowed' => $data[18],
                    'smoking_allowed' => $data[19],
                    'disability_friendly' => $data[20],
                    'accommodation_description' => $data[21],
                    'living_area' => $data[22],
                    'bedroom_count' => $data[23],
                    'bed_count' => $data[24],
                    'max_persons' => $data[25],
                    'min_rental_days' => $data[26],
                    'amenities' => json_encode(array_map('trim', explode(',', $data[27]))), // Convert comma separated list to JSON array
                    'boat_description' => $data[28],
                    'equipment' => json_encode(array_map('trim', explode(',', $data[29]))), // Convert comma separated list to JSON array
                    'basic_fishing_description' => $data[30],
                    'catering_info' => $data[31],
                    'package_price_per_person' => $data[32],
                    'accommodation_price' => $data[33],
                    'boat_rental_price' => $data[34],
                    'guiding_price' => $data[35],
                    'additional_services' => json_encode(array_map('trim', explode(',', $data[36]))), // Convert comma separated list to JSON array
                    'included_services' => json_encode(array_map('trim', explode(',', $data[37]))), // Convert comma separated list to JSON array
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            fclose($handle);
        }
        foreach($vacations as $vacation) {
            DB::table('vacations')->insert($vacation);
        }
    }
}
