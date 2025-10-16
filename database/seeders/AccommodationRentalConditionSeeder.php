<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class AccommodationRentalConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accommodation_rental_conditions')->truncate();
        $accommodationRentalConditions = [
            ['name' => 'Eincheck-/ Auscheckzeiten', 'name_en' => 'Check-in/check-out times', 'input_type' => 'text', 'placeholder' => 'Enter check-in/check-out times', 'sort_order' => 1],
            ['name' => 'Selbstständiges Einchecken', 'name_en' => 'Self check-in', 'input_type' => 'text', 'placeholder' => 'Enter self check-in details', 'sort_order' => 2],
            ['name' => 'Ruhezeiten', 'name_en' => 'Quiet hours', 'input_type' => 'text', 'placeholder' => 'Enter quiet hours', 'sort_order' => 3],
            ['name' => 'Müllentsorgung / Recycling-Regeln', 'name_en' => 'Garbage disposal/recycling rules', 'input_type' => 'text', 'placeholder' => 'Enter garbage disposal rules', 'sort_order' => 4],
            ['name' => 'Kaution erforderlich', 'name_en' => 'Deposit required', 'input_type' => 'text', 'placeholder' => 'Enter deposit amount', 'sort_order' => 5],
        ];

        foreach ($accommodationRentalConditions as $condition) {
            \App\Models\AccommodationRentalCondition::create($condition);
        }
    }
}

