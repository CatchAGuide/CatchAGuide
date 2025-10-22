<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class AccommodationExtraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accommodation_extras')->truncate();
        $accommodationExtras = [
            ['name' => 'Bettwäsche', 'name_en' => 'Bed linen', 'sort_order' => 1],
            ['name' => 'Handtücher', 'name_en' => 'Towels', 'sort_order' => 2],
            ['name' => 'Endreinigung', 'name_en' => 'Final cleaning', 'sort_order' => 3],
        ];

        foreach ($accommodationExtras as $extra) {
            \App\Models\AccommodationExtra::create($extra);
        }
    }
}

