<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $facilities = [
            ['value' => 'Terrasse', 'value_de' => 'Terrasse', 'sort_order' => 1],
            ['value' => 'Garten', 'value_de' => 'Garten', 'sort_order' => 2],
            ['value' => 'Schwimmbad', 'value_de' => 'Schwimmbad', 'sort_order' => 3],
            ['value' => 'Private jetty / boat dock', 'value_de' => 'Private Anlegestelle / Bootsdock', 'sort_order' => 4],
            ['value' => 'Fish cleaning / filleting station', 'value_de' => 'Fischverarbeitungsstation', 'sort_order' => 5],
            ['value' => 'Smoker', 'value_de' => 'Räucherofen', 'sort_order' => 6],
            ['value' => 'Barbecue Area', 'value_de' => 'Grillbereich', 'sort_order' => 7],
            ['value' => 'Lockable Storage for fishing equipment', 'value_de' => 'Verschließbarer Lagerraum für Angelausrüstung', 'sort_order' => 8],
            ['value' => 'WiFi', 'value_de' => 'WiFi', 'sort_order' => 9],
            ['value' => 'Fireplace / stove', 'value_de' => 'Kamin / Ofen', 'sort_order' => 10],
            ['value' => 'Sauna', 'value_de' => 'Sauna', 'sort_order' => 11],
            ['value' => 'Pool / hot tub', 'value_de' => 'Pool / Whirlpool', 'sort_order' => 12],
            ['value' => 'Billiards / table tennis / darts / games corner', 'value_de' => 'Billiard / Tischtennis / Darts / Spielecke', 'sort_order' => 13],
            ['value' => 'Balcony', 'value_de' => 'Balkon', 'sort_order' => 14],
            ['value' => 'Garden furniture / sun loungers', 'value_de' => 'Gartenmöbel / Sonnenliegen', 'sort_order' => 15],
            ['value' => 'Parking spaces', 'value_de' => 'Parkplätze', 'sort_order' => 16],
            ['value' => 'Charging station for electric cars', 'value_de' => 'Ladestation für Elektroautos', 'sort_order' => 17],
            ['value' => 'Boat Ramp nearby', 'value_de' => 'Bootsrampe in der Nähe', 'sort_order' => 18],
            ['value' => 'TV', 'value_de' => 'TV', 'sort_order' => 19],
            ['value' => 'Sound System', 'value_de' => 'Soundsystem', 'sort_order' => 20],
            ['value' => 'Keybox', 'value_de' => 'Schlüsselbox', 'sort_order' => 21],
            ['value' => 'Heating', 'value_de' => 'Heizung', 'sort_order' => 22],
            ['value' => 'Air Conditioning', 'value_de' => 'Klimaanlage', 'sort_order' => 23],
            ['value' => 'Fish filet Freezer', 'value_de' => 'Fischfilet-Gefrierschrank', 'sort_order' => 24],
        ];

        foreach ($facilities as $facility) {
            \App\Models\Facility::create($facility);
        }
    }
}
