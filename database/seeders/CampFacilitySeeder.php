<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CampFacility;
use DB;

class CampFacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('camp_facilities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $facilities = [
            [
                'name' => 'Swimming pool',
                'name_de' => 'Schwimmbad',
                'name_en' => 'Swimming pool',
                'is_active' => true,
            ],
            [
                'name' => 'Private jetty / boat mooring',
                'name_de' => 'Privater Steg / Bootsanlegestelle',
                'name_en' => 'Private jetty / boat mooring',
                'is_active' => true,
            ],
            [
                'name' => 'Fish cleaning / filleting station',
                'name_de' => 'Fischreinigungs- / Filetierstation',
                'name_en' => 'Fish cleaning / filleting station',
                'is_active' => true,
            ],
            [
                'name' => 'Smoking oven',
                'name_de' => 'Räucherofen',
                'name_en' => 'Smoking oven',
                'is_active' => true,
            ],
            [
                'name' => 'Barbecue area',
                'name_de' => 'Grillplatz',
                'name_en' => 'Barbecue area',
                'is_active' => true,
            ],
            [
                'name' => 'Lockable storage room for fishing equipment',
                'name_de' => 'Abschließbarer Abstellraum für Angelausrüstung',
                'name_en' => 'Lockable storage room for fishing equipment',
                'is_active' => true,
            ],
            [
                'name' => 'Fireplace / stove',
                'name_de' => 'Kamin / Ofen',
                'name_en' => 'Fireplace / stove',
                'is_active' => true,
            ],
            [
                'name' => 'Sauna',
                'name_de' => 'Sauna',
                'name_en' => 'Sauna',
                'is_active' => true,
            ],
            [
                'name' => 'Pool / whirlpool',
                'name_de' => 'Pool / Whirlpool',
                'name_en' => 'Pool / whirlpool',
                'is_active' => true,
            ],
            [
                'name' => 'Pool table / table tennis / darts / games corner',
                'name_de' => 'Billard / Tischtennis / Darts / Spielecke',
                'name_en' => 'Pool table / table tennis / darts / games corner',
                'is_active' => true,
            ],
            [
                'name' => 'Parking spaces',
                'name_de' => 'Parkplätze',
                'name_en' => 'Parking spaces',
                'is_active' => true,
            ],
            [
                'name' => 'Charging station for electric cars',
                'name_de' => 'Ladestation für Elektroautos',
                'name_en' => 'Charging station for electric cars',
                'is_active' => true,
            ],
            [
                'name' => 'Boat ramp nearby',
                'name_de' => 'Bootsrampe in der Nähe',
                'name_en' => 'Boat ramp nearby',
                'is_active' => true,
            ],
            [
                'name' => 'Reception',
                'name_de' => 'Rezeption',
                'name_en' => 'Reception',
                'is_active' => true,
            ],
            [
                'name' => 'Fish freezer',
                'name_de' => 'Fisch-Gefrierschrank',
                'name_en' => 'Fish freezer',
                'is_active' => true,
            ],
        ];

        foreach ($facilities as $facility) {
            CampFacility::create($facility);
        }
    }
}
