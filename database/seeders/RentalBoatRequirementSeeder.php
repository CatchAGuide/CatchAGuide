<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RentalBoatRequirement;
use DB;

class RentalBoatRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rental_boat_requirements')->truncate();
        $requirements = [
            [
                'name' => 'Führerschein/ Lizenz',
                'name_en' => 'License',
                'input_type' => 'text',
                'placeholder' => 'Bitte geben Sie die Art der Lizenz an',
                'placeholder_en' => 'Please specify the type of license',
                'sort_order' => 1,
            ],
            [
                'name' => 'Mindestalter',
                'name_en' => 'Minimum age',
                'input_type' => 'text',
                'placeholder' => 'Mindestalter in Jahren',
                'placeholder_en' => 'Minimum age in years',
                'sort_order' => 2,
            ],
            [
                'name' => 'Identitätsnachweis',
                'name_en' => 'Proof of identity',
                'input_type' => 'text',
                'placeholder' => 'Art des Identitätsnachweises',
                'placeholder_en' => 'Type of proof of identity',
                'sort_order' => 3,
            ],
            [
                'name' => 'Kaution',
                'name_en' => 'Deposit',
                'input_type' => 'text',
                'placeholder' => 'Höhe der Kaution in €',
                'placeholder_en' => 'Deposit amount in €',
                'sort_order' => 4,
            ],
            [
                'name' => 'Schwimmwestenpflicht',
                'name_en' => 'Life jackets mandatory',
                'input_type' => 'text',
                'placeholder' => 'Details zur Schwimmwestenpflicht',
                'placeholder_en' => 'Details about life jacket requirements',
                'sort_order' => 5,
            ],
            [
                'name' => 'Sicherheitsunterweisung',
                'name_en' => 'Safety briefing',
                'input_type' => 'text',
                'placeholder' => 'Art der Sicherheitsunterweisung',
                'placeholder_en' => 'Type of safety briefing',
                'sort_order' => 6,
            ],
            [
                'name' => 'Sonstiges',
                'name_en' => 'Other',
                'input_type' => 'text',
                'placeholder' => 'Weitere Anforderungen',
                'placeholder_en' => 'Additional requirements',
                'sort_order' => 7,
            ],
        ];

        foreach ($requirements as $requirement) {
            RentalBoatRequirement::create($requirement);
        }
    }
}