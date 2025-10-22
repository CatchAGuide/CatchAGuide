<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class AccommodationPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accommodation_policies')->truncate();
        $accommodationPolicies = [
            ['name' => 'Haustiere erlaubt', 'name_en' => 'Pets allowed', 'sort_order' => 1],
            ['name' => 'Rauchen erlaubt', 'name_en' => 'Smoking allowed', 'sort_order' => 2],
            ['name' => 'Kinder erlaubt', 'name_en' => 'Children allowed', 'sort_order' => 3],
            ['name' => 'Barrierefrei', 'name_en' => 'Barrier-free', 'sort_order' => 4],
            ['name' => 'Energieverbrauch inklusive', 'name_en' => 'Energy consumption included', 'sort_order' => 5],
            ['name' => 'Wasserverbrauch inklusive', 'name_en' => 'Water consumption included', 'sort_order' => 6],
            ['name' => 'Nur angemeldete Gäste', 'name_en' => 'Registered guests only', 'sort_order' => 7],
        ];

        foreach ($accommodationPolicies as $policy) {
            \App\Models\AccommodationPolicy::create($policy);
        }
    }
}

