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
