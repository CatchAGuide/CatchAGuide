<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class GuidingsSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $waters = ['Bach', 'Baggersee', 'Fluss', 'Hafen', 'Kanal', 'Meer', 'Natursee', 'Stausee', 'Strom', 'Talsperre'];
        foreach($waters as $water) {
            DB::table('waters')->insert([
                'name' => $water
            ]);
        }

        $targets = ['Aal', 'Aland', 'Äsche', 'Barbe', 'Barsch', 'Brasse', 'Döbel', 'Forelle', 'Hecht', 'Huchen', 'Karpfen', 'Nase', 'Rapfen', 'Rotauge', 'Rotfeder', 'Schleie', 'Wels', 'Zander'];
        foreach($targets as $target) {
            DB::table('targets')->insert([
                'name' => $target
            ]);
        }

        $methods = ['Carolina-Rig', 'Dropshot', 'Eisfischen', 'Feederangeln', 'Fliegenfischen', 'Grundblei', 'Hardbait', 'Jerkbaitangeln', 'Jiggen', 'Köderfisch', 'Pose', 'Schleppangeln', 'Texas-Rig', 'Topwater', 'Vertikal-Angeln', 'Wurm'];
        foreach($methods as $method) {
            DB::table('methods')->insert([
                'name' => $method
            ]);
        }

    }
}
