<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BathroomAmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bathroomAmenities = [
            ['value' => 'Iron/ Iron board', 'value_de' => 'Bügeleisen / Bügelbrett', 'sort_order' => 1],
            ['value' => 'Clothes drying rack', 'value_de' => 'Wäscheständer', 'sort_order' => 2],
            ['value' => 'Toilet', 'value_de' => 'Toilette', 'sort_order' => 3],
            ['value' => 'Washing machine', 'value_de' => 'Waschmaschine', 'sort_order' => 4],
            ['value' => 'Dryer', 'value_de' => 'Trockner', 'sort_order' => 5],
            ['value' => 'Separate Toilet/ Bathroom', 'value_de' => 'Separate Toilette / Badezimmer', 'sort_order' => 6],
            ['value' => 'Waschbecken', 'value_de' => 'Waschbecken', 'sort_order' => 7],
        ];

        foreach ($bathroomAmenities as $amenity) {
            \App\Models\BathroomAmenity::create($amenity);
        }
    }
}
