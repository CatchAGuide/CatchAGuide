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
            ['name' => 'Iron/ Iron board', 'name_en' => 'Iron/ Iron board', 'sort_order' => 1],
            ['name' => 'Clothes drying rack', 'name_en' => 'Clothes drying rack', 'sort_order' => 2],
            ['name' => 'Toilet', 'name_en' => 'Toilet', 'sort_order' => 3],
            ['name' => 'Washing machine', 'name_en' => 'Washing machine', 'sort_order' => 4],
            ['name' => 'Dryer', 'name_en' => 'Dryer', 'sort_order' => 5],
            ['name' => 'Separate Toilet/ Bathroom', 'name_en' => 'Separate Toilet/ Bathroom', 'sort_order' => 6],
            ['name' => 'Waschbecken', 'name_en' => 'Sink', 'sort_order' => 7],
        ];

        foreach ($bathroomAmenities as $amenity) {
            \App\Models\BathroomAmenity::create($amenity);
        }
    }
}
