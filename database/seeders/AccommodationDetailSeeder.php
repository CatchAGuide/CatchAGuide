<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AccommodationDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $accommodationDetails = [
            ['name' => 'Wohnfläche (qm)', 'name_en' => 'Living Space (sqm)', 'input_type' => 'number', 'placeholder' => 'Enter living space in square meters', 'sort_order' => 1],
            ['name' => 'Max. Belegung', 'name_en' => 'Max. Occupancy', 'input_type' => 'number', 'placeholder' => 'Enter maximum occupancy', 'sort_order' => 2],
            ['name' => 'Anzahl Schlafzimmer', 'name_en' => 'Number of Bedrooms', 'input_type' => 'number', 'placeholder' => 'Enter number of bedrooms', 'sort_order' => 3],
            ['name' => 'Badezimmer', 'name_en' => 'Bathrooms', 'input_type' => 'number', 'placeholder' => 'Enter number of bathrooms', 'sort_order' => 4],
            ['name' => 'Etagenanzahl / Stockwerk', 'name_en' => 'Number of Floors', 'input_type' => 'number', 'placeholder' => 'Enter number of floors', 'sort_order' => 5],
            ['name' => 'Baujahr oder letzte Renovierung', 'name_en' => 'Year of Construction or Last Renovation', 'input_type' => 'number', 'placeholder' => 'Enter year of construction or last renovation', 'sort_order' => 6],
            ['name' => 'Wohnzimmer', 'name_en' => 'Living Room', 'input_type' => 'text', 'placeholder' => 'Enter living room details', 'sort_order' => 7],
            ['name' => 'Esszimmer', 'name_en' => 'Dining Room', 'input_type' => 'text', 'placeholder' => 'Enter dining room details', 'sort_order' => 8],
        ];

        foreach ($accommodationDetails as $detail) {
            \App\Models\AccommodationDetail::create($detail);
        }
    }
}
