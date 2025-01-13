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
        // Disable foreign key checks before truncating
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('vacations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $csvFile = public_path('Productpage_Vacations.csv');
        $vacations = [];
        
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            // Skip header row
            fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== FALSE) {
                $vacations[] = [
                    'title' => mb_convert_encoding($data[1], 'UTF-8', 'ISO-8859-1'),
                    'slug' => mb_convert_encoding($data[2], 'UTF-8', 'ISO-8859-1'), 
                    'location' => mb_convert_encoding($data[3], 'UTF-8', 'ISO-8859-1'),

                    'city' => "",
                    'country' => "",
                    'latitude' => "", 
                    'longitude' => "", 
                    'region' => "",

                    'gallery' => json_encode(explode("\n", mb_convert_encoding($data[9], 'UTF-8', 'ISO-8859-1')), JSON_UNESCAPED_UNICODE), // Convert newline separated list to JSON array
                    'best_travel_times' => json_encode(explode("\n", mb_convert_encoding($data[10], 'UTF-8', 'ISO-8859-1')), JSON_UNESCAPED_UNICODE), // Convert newline separated list to JSON array
                    'surroundings_description' => mb_convert_encoding($data[11], 'UTF-8', 'ISO-8859-1'),
                    'target_fish' => json_encode(array_map('trim', explode(',', mb_convert_encoding($data[12], 'UTF-8', 'ISO-8859-1'))), JSON_UNESCAPED_UNICODE), // Convert comma separated list to JSON array
                    'airport_distance' => mb_convert_encoding($data[13], 'UTF-8', 'ISO-8859-1'),
                    'water_distance' => mb_convert_encoding($data[14], 'UTF-8', 'ISO-8859-1'),
                    'shopping_distance' => mb_convert_encoding($data[15], 'UTF-8', 'ISO-8859-1'),
                    'travel_included' => mb_convert_encoding($data[16], 'UTF-8', 'ISO-8859-1'),
                    'travel_options' => json_encode(explode("\n", mb_convert_encoding($data[17], 'UTF-8', 'ISO-8859-1')), JSON_UNESCAPED_UNICODE), // Convert newline separated list to JSON array
                    'pets_allowed' => mb_convert_encoding($data[18], 'UTF-8', 'ISO-8859-1'),
                    'smoking_allowed' => mb_convert_encoding($data[19], 'UTF-8', 'ISO-8859-1'),
                    'disability_friendly' => mb_convert_encoding($data[20], 'UTF-8', 'ISO-8859-1'),
                    'accommodation_description' => mb_convert_encoding($data[21], 'UTF-8', 'ISO-8859-1'),
                    'living_area' => mb_convert_encoding($data[22], 'UTF-8', 'ISO-8859-1'),
                    'bedroom_count' => mb_convert_encoding($data[23], 'UTF-8', 'ISO-8859-1'),
                    'bed_count' => mb_convert_encoding($data[24], 'UTF-8', 'ISO-8859-1'),
                    'max_persons' => mb_convert_encoding($data[25], 'UTF-8', 'ISO-8859-1'),
                    'min_rental_days' => mb_convert_encoding($data[26], 'UTF-8', 'ISO-8859-1'),
                    'amenities' => json_encode(array_map('trim', explode(',', mb_convert_encoding($data[27], 'UTF-8', 'ISO-8859-1'))), JSON_UNESCAPED_UNICODE), // Convert comma separated list to JSON array
                    'boat_description' => mb_convert_encoding($data[28], 'UTF-8', 'ISO-8859-1'),
                    'equipment' => json_encode(array_map('trim', explode(',', mb_convert_encoding($data[29], 'UTF-8', 'ISO-8859-1'))), JSON_UNESCAPED_UNICODE), // Convert comma separated list to JSON array
                    'basic_fishing_description' => mb_convert_encoding($data[30], 'UTF-8', 'ISO-8859-1'),
                    'catering_info' => mb_convert_encoding($data[31], 'UTF-8', 'ISO-8859-1'),
                    'package_price_per_person' => mb_convert_encoding($data[32], 'UTF-8', 'ISO-8859-1'),
                    'accommodation_price' => mb_convert_encoding($data[33], 'UTF-8', 'ISO-8859-1'),
                    'boat_rental_price' => mb_convert_encoding($data[34], 'UTF-8', 'ISO-8859-1'),
                    'guiding_price' => mb_convert_encoding($data[35], 'UTF-8', 'ISO-8859-1'),
                    'additional_services' => json_encode(array_map('trim', explode(',', mb_convert_encoding($data[36], 'UTF-8', 'ISO-8859-1'))), JSON_UNESCAPED_UNICODE), // Convert comma separated list to JSON array
                    'included_services' => json_encode(array_map('trim', explode(',', mb_convert_encoding($data[37], 'UTF-8', 'ISO-8859-1'))), JSON_UNESCAPED_UNICODE), // Convert comma separated list to JSON array
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
