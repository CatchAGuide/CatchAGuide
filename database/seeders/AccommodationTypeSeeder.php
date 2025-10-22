<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class AccommodationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accommodation_types')->truncate();
        $accommodationTypes = [
            ['name' => 'Villa', 'name_en' => 'Villa', 'sort_order' => 1],
            ['name' => 'Ferienhaus', 'name_en' => 'Holiday House', 'sort_order' => 2],
            ['name' => 'Studio', 'name_en' => 'Studio', 'sort_order' => 3],
            ['name' => 'Appartement / Ferienwohnung', 'name_en' => 'Apartment / Vacation Rental', 'sort_order' => 4],
            ['name' => 'Blockhütte', 'name_en' => 'Log Cabin', 'sort_order' => 5],
            ['name' => 'Bungalow', 'name_en' => 'Bungalow', 'sort_order' => 6],
            ['name' => 'Tiny House', 'name_en' => 'Tiny House', 'sort_order' => 7],
            ['name' => 'Mobilheim', 'name_en' => 'Mobile Home', 'sort_order' => 8],
            ['name' => 'Camping', 'name_en' => 'Camping', 'sort_order' => 9],
            ['name' => 'Hotel', 'name_en' => 'Hotel', 'sort_order' => 10],
            ['name' => 'Hausboot', 'name_en' => 'Houseboat', 'sort_order' => 11],
        ];

        foreach ($accommodationTypes as $type) {
            \App\Models\AccommodationType::create($type);
        }
    }
}
