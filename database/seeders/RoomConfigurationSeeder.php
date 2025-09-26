<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoomConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roomConfigurations = [
            ['value' => 'Einzelbett', 'value_de' => 'Einzelbett', 'sort_order' => 1],
            ['value' => 'Doppelbett', 'value_de' => 'Doppelbett', 'sort_order' => 2],
            ['value' => 'Sofabett', 'value_de' => 'Sofabett', 'sort_order' => 3],
            ['value' => 'Etagenbett', 'value_de' => 'Etagenbett', 'sort_order' => 4],
            ['value' => 'Kinderbett', 'value_de' => 'Kinderbett', 'sort_order' => 5],
            ['value' => 'Klappbett', 'value_de' => 'Klappbett', 'sort_order' => 6],
        ];

        foreach ($roomConfigurations as $config) {
            \App\Models\RoomConfiguration::create($config);
        }
    }
}
