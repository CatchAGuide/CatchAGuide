<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class RoomConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('room_configurations')->truncate();
        $roomConfigurations = [
            ['name' => 'Einzelbett', 'name_en' => 'Single Bed', 'sort_order' => 1],
            ['name' => 'Doppelbett', 'name_en' => 'Double Bed', 'sort_order' => 2],
            ['name' => 'Sofabett', 'name_en' => 'Sofa Bed', 'sort_order' => 3],
            ['name' => 'Etagenbett', 'name_en' => 'Bunk Bed', 'sort_order' => 4],
            ['name' => 'Kinderbett', 'name_en' => 'Children\'s Bed', 'sort_order' => 5],
            ['name' => 'Klappbett', 'name_en' => 'Folding Bed', 'sort_order' => 6],
        ];

        foreach ($roomConfigurations as $config) {
            \App\Models\RoomConfiguration::create($config);
        }
    }
}
