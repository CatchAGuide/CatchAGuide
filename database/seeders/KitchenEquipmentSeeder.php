<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class KitchenEquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kitchenEquipment = [
            ['value' => 'Refrigerator', 'value_de' => 'Kühlschrank', 'sort_order' => 1],
            ['value' => 'Freezer or freezer compartment', 'value_de' => 'Gefrierschrank oder Gefrierfach', 'sort_order' => 2],
            ['value' => 'Backofen', 'value_de' => 'Backofen', 'sort_order' => 3],
            ['value' => 'Mikrowelle', 'value_de' => 'Mikrowelle', 'sort_order' => 4],
            ['value' => 'Geschirrspüler', 'value_de' => 'Geschirrspüler', 'sort_order' => 5],
            ['value' => 'Kaffeemaschine', 'value_de' => 'Kaffeemaschine', 'sort_order' => 6],
            ['value' => 'Kettle', 'value_de' => 'Wasserkocher', 'sort_order' => 7],
            ['value' => 'Toaster', 'value_de' => 'Toaster', 'sort_order' => 8],
            ['value' => 'Blender cooking supplies', 'value_de' => 'Mixer Kochzubehör', 'sort_order' => 9],
            ['value' => 'Baking equipment', 'value_de' => 'Backzubehör', 'sort_order' => 10],
            ['value' => 'Dishwashing Items', 'value_de' => 'Spülzubehör', 'sort_order' => 11],
            ['value' => 'Wine Glasses', 'value_de' => 'Weingläser', 'sort_order' => 12],
            ['value' => 'Pfannen & Töpfe', 'value_de' => 'Pfannen & Töpfe', 'sort_order' => 13],
            ['value' => 'Spülbecken', 'value_de' => 'Spülbecken', 'sort_order' => 14],
            ['value' => 'Basics: Spices, Oil, etc.', 'value_de' => 'Grundausstattung: Gewürze, Öl, etc.', 'sort_order' => 15],
        ];

        foreach ($kitchenEquipment as $equipment) {
            \App\Models\KitchenEquipment::create($equipment);
        }
    }
}
