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
            ['name' => 'Terrasse', 'name_en' => 'Terrace', 'sort_order' => 1],
            ['name' => 'Garten', 'name_en' => 'Garden', 'sort_order' => 2],
            ['name' => 'Schwimmbad', 'name_en' => 'Swimming Pool', 'sort_order' => 3],
            ['name' => 'Private jetty / boat dock', 'name_en' => 'Private jetty / boat dock', 'sort_order' => 4],
            ['name' => 'Fish cleaning / filleting station', 'name_en' => 'Fish cleaning / filleting station', 'sort_order' => 5],
            ['name' => 'Smoker', 'name_en' => 'Smoker', 'sort_order' => 6],
            ['name' => 'Barbecue Area', 'name_en' => 'Barbecue Area', 'sort_order' => 7],
            ['name' => 'Lockable Storage for fishing equipment', 'name_en' => 'Lockable Storage for fishing equipment', 'sort_order' => 8],
            ['name' => 'WiFi', 'name_en' => 'WiFi', 'sort_order' => 9],
            ['name' => 'Fireplace / stove', 'name_en' => 'Fireplace / stove', 'sort_order' => 10],
            ['name' => 'Sauna', 'name_en' => 'Sauna', 'sort_order' => 11],
            ['name' => 'Pool / hot tub', 'name_en' => 'Pool / hot tub', 'sort_order' => 12],
            ['name' => 'Billiards / table tennis / darts / games corner', 'name_en' => 'Billiards / table tennis / darts / games corner', 'sort_order' => 13],
            ['name' => 'Balcony', 'name_en' => 'Balcony', 'sort_order' => 14],
            ['name' => 'Garden furniture / sun loungers', 'name_en' => 'Garden furniture / sun loungers', 'sort_order' => 15],
            ['name' => 'Parking spaces', 'name_en' => 'Parking spaces', 'sort_order' => 16],
            ['name' => 'Charging station for electric cars', 'name_en' => 'Charging station for electric cars', 'sort_order' => 17],
            ['name' => 'Boat Ramp nearby', 'name_en' => 'Boat Ramp nearby', 'sort_order' => 18],
            ['name' => 'TV', 'name_en' => 'TV', 'sort_order' => 19],
            ['name' => 'Sound System', 'name_en' => 'Sound System', 'sort_order' => 20],
            ['name' => 'Keybox', 'name_en' => 'Keybox', 'sort_order' => 21],
            ['name' => 'Heating', 'name_en' => 'Heating', 'sort_order' => 22],
            ['name' => 'Air Conditioning', 'name_en' => 'Air Conditioning', 'sort_order' => 23],
            ['name' => 'Fish filet Freezer', 'name_en' => 'Fish filet Freezer', 'sort_order' => 24],
        ];

        foreach ($facilities as $facility) {
            \App\Models\Facility::create($facility);
        }
    }
}
