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
            ['value' => 'Wohnfläche (qm)', 'value_de' => 'Wohnfläche (qm)', 'input_type' => 'number', 'placeholder' => 'Enter living space in square meters', 'sort_order' => 1],
            ['value' => 'Max. Belegung', 'value_de' => 'Max. Belegung', 'input_type' => 'number', 'placeholder' => 'Enter maximum occupancy', 'sort_order' => 2],
            ['value' => 'Anzahl Schlafzimmer', 'value_de' => 'Anzahl Schlafzimmer', 'input_type' => 'number', 'placeholder' => 'Enter number of bedrooms', 'sort_order' => 3],
            ['value' => 'Badezimmer', 'value_de' => 'Badezimmer', 'input_type' => 'number', 'placeholder' => 'Enter number of bathrooms', 'sort_order' => 4],
            ['value' => 'Etagenanzahl / Stockwerk', 'value_de' => 'Etagenanzahl / Stockwerk', 'input_type' => 'number', 'placeholder' => 'Enter number of floors', 'sort_order' => 5],
            ['value' => 'Baujahr oder letzte Renovierung', 'value_de' => 'Baujahr oder letzte Renovierung', 'input_type' => 'number', 'placeholder' => 'Enter year of construction or last renovation', 'sort_order' => 6],
            ['value' => 'Wohnzimmer', 'value_de' => 'Wohnzimmer', 'input_type' => 'text', 'placeholder' => 'Enter living room details', 'sort_order' => 7],
            ['value' => 'Esszimmer', 'value_de' => 'Esszimmer', 'input_type' => 'text', 'placeholder' => 'Enter dining room details', 'sort_order' => 8],
        ];

        foreach ($accommodationDetails as $detail) {
            \App\Models\AccommodationDetail::create($detail);
        }
    }
}
