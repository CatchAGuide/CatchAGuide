<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class KitchenEquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('kitchen_equipment')->truncate();
        $kitchenEquipment = [
            ['name' => 'Refrigerator', 'name_en' => 'Refrigerator', 'sort_order' => 1],
            ['name' => 'Freezer or freezer compartment', 'name_en' => 'Freezer or freezer compartment', 'sort_order' => 2],
            ['name' => 'Backofen', 'name_en' => 'Oven', 'sort_order' => 3],
            ['name' => 'Mikrowelle', 'name_en' => 'Microwave', 'sort_order' => 4],
            ['name' => 'Geschirrspüler', 'name_en' => 'Dishwasher', 'sort_order' => 5],
            ['name' => 'Kaffeemaschine', 'name_en' => 'Coffee machine', 'sort_order' => 6],
            ['name' => 'Kettle', 'name_en' => 'Kettle', 'sort_order' => 7],
            ['name' => 'Toaster', 'name_en' => 'Toaster', 'sort_order' => 8],
            ['name' => 'Blender cooking supplies', 'name_en' => 'Blender cooking supplies', 'sort_order' => 9],
            ['name' => 'Baking equipment', 'name_en' => 'Baking equipment', 'sort_order' => 10],
            ['name' => 'Dishwashing Items', 'name_en' => 'Dishwashing Items', 'sort_order' => 11],
            ['name' => 'Wine Glasses', 'name_en' => 'Wine Glasses', 'sort_order' => 12],
            ['name' => 'Pfannen & Töpfe', 'name_en' => 'Pans & Pots', 'sort_order' => 13],
            ['name' => 'Spülbecken', 'name_en' => 'Sink', 'sort_order' => 14],
            ['name' => 'Basics: Spices, Oil, etc.', 'name_en' => 'Basics: Spices, Oil, etc.', 'sort_order' => 15],
            ['name' => 'Herd & Geschirr', 'name_en' => 'Stove & Dishes ', 'sort_order' => 16],
        ];

        foreach ($kitchenEquipment as $equipment) {
            \App\Models\KitchenEquipment::create($equipment);
        }
    }
}
