<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class AccommodationInclusiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accommodation_inclusives')->truncate();
        $accommodationInclusives = [
            ['name' => 'Bettwäsche', 'name_en' => 'Bed linen', 'sort_order' => 1],
            ['name' => 'Handtücher', 'name_en' => 'Towels', 'sort_order' => 2],
            ['name' => 'Endreinigung', 'name_en' => 'Final cleaning', 'sort_order' => 3],
        ];

        foreach ($accommodationInclusives as $inclusive) {
            \App\Models\AccommodationInclusive::create($inclusive);
        }
    }
}

