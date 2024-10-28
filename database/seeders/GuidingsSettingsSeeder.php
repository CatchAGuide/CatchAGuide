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
        DB::table('waters')->truncate();
        $waters = [
            ['name' => 'Fluss', 'name_en' => 'River'],
            ['name' => 'See', 'name_en' => 'Lake'], 
            ['name' => 'Meer', 'name_en' => 'Sea'],
            ['name' => 'Küste', 'name_en' => 'Coast'],
            ['name' => 'Kanal', 'name_en' => 'Canal'],
            ['name' => 'Stausee', 'name_en' => 'Reservoir'],
            ['name' => 'Hochsee', 'name_en' => 'Off shore'],
            ['name' => 'Bach', 'name_en' => 'Stream']
        ];
        foreach($waters as $water) {
            DB::table('waters')->insert($water);
        }

        # Targets
        DB::table('targets')->truncate();
        $targets = [
            ['name' => 'Arktischer Saibling', 'name_en' => 'Arctic Char'],
            ['name' => 'Rapfen', 'name_en' => 'Asp'],
            ['name' => 'Bachsaibling', 'name_en' => 'Brook Trout'],
            ['name' => 'Bachforelle', 'name_en' => 'Brown Trout'],
            ['name' => 'Karpfen', 'name_en' => 'Carp'],
            ['name' => 'Wels', 'name_en' => 'Catfish'],
            ['name' => 'Brassen', 'name_en' => 'Common Bream'],
            ['name' => 'Flussbarsch', 'name_en' => 'European Perch'],
            ['name' => 'Äsche', 'name_en' => 'Grayling'],
            ['name' => 'Seeforelle', 'name_en' => 'Lake Trout'],
            ['name' => 'Hecht', 'name_en' => 'Northern Pike'],
            ['name' => 'Regenbogenforelle', 'name_en' => 'Rainbow Trout'],
            ['name' => 'Schleie', 'name_en' => 'Tench'],
            ['name' => 'Zander', 'name_en' => 'Zander'],
            ['name' => 'Atlantischer Bonito', 'name_en' => 'Atlantic Bonito'],
            ['name' => 'Kabeljau', 'name_en' => 'Atlantic Cod'],
            ['name' => 'Atlantische Makrele', 'name_en' => 'Atlantic Mackerel'],
            ['name' => 'Atlantischer Lachs', 'name_en' => 'Atlantic Salmon'],
            ['name' => 'Blauflossen-Thunfisch', 'name_en' => 'Atlantic Bluefin Tuna'],
            ['name' => 'Atlantischer Hering', 'name_en' => 'Atlantic Herring'],
            ['name' => 'Atlantischer Heilbutt', 'name_en' => 'Atlantic Halibut'],
            ['name' => 'Barrakuda', 'name_en' => 'Barracuda'],
            ['name' => 'Blauflossiger Stachelmakrele', 'name_en' => 'Bluefish'],
            ['name' => 'Seezunge', 'name_en' => 'Common Sole'],
            ['name' => 'Meeraal', 'name_en' => 'Conger Eel'],
            ['name' => 'Kliesche', 'name_en' => 'Dab'],
            ['name' => 'Europäischer Wolfsbarsch', 'name_en' => 'European Sea Bass'],
            ['name' => 'Flunder', 'name_en' => 'Flounder'],
            ['name' => 'Goldbrasse', 'name_en' => 'Gilthead Seabream'],
            ['name' => 'Großer Bernsteinfisch', 'name_en' => 'Greater Amberjack'],
            ['name' => 'Knurrhahn', 'name_en' => 'Gurnard'],
            ['name' => 'Schellfisch', 'name_en' => 'Haddock'],
            ['name' => 'Petersfisch', 'name_en' => 'John Dory'],
            ['name' => 'Leng', 'name_en' => 'Ling'],
            ['name' => 'Goldmakrele', 'name_en' => 'Mahi Mahi'],
            ['name' => 'Pollack', 'name_en' => 'Pollack'],
            ['name' => 'Rotbarbe', 'name_en' => 'Red Mullet'],
            ['name' => 'Rotbarsch', 'name_en' => 'Redfish'],
            ['name' => 'Meerbrasse', 'name_en' => 'Sea Bream'],
            ['name' => 'Meerforelle', 'name_en' => 'Seatrout'],
            ['name' => 'Tintenfisch', 'name_en' => 'Squid'],
            ['name' => 'Wittling', 'name_en' => 'Whiting'],
            ['name' => 'Katfisch', 'name_en' => 'Wolffish'],
            ['name' => 'Gelbschwanz', 'name_en' => 'Yellowtail Amberjack'],
            ['name' => 'Schwertfisch', 'name_en' => 'Swordfish'],
            ['name' => 'Atlantischer Blauer Marlin', 'name_en' => 'Atlantic Blue Marlin'],
            ['name' => 'Weißer Marlin', 'name_en' => 'White Marlin'],
            ['name' => 'Blauhai', 'name_en' => 'Blue Shark'],
            ['name' => 'Heringshai', 'name_en' => 'Porbeagle Shark'],
            ['name' => 'Fuchshai', 'name_en' => 'Thresher Shark'],
            ['name' => 'Makohai', 'name_en' => 'Shortfin Mako Shark'],
            ['name' => 'Großer Hammerhai', 'name_en' => 'Great Hammerhead Shark'],
            ['name' => 'Dornhai', 'name_en' => 'Spiny Dogfish'],
            ['name' => 'Hundshai', 'name_en' => 'Smoothhound'],
            ['name' => 'Speerfisch', 'name_en' => 'Spearfish'],
            ['name' => 'Dentex', 'name_en' => 'Dentex'],
            ['name' => 'Meeräsche', 'name_en' => 'Mullet'],
            ['name' => 'Rochen', 'name_en' => 'Ray'],
            ['name' => 'Drachenkopf', 'name_en' => 'Scorpionfish']
        ];
        foreach($targets as $target) {
            DB::table('targets')->insert($target);
        }

        # Methods
        DB::table('methods')->truncate();
        $methods = [
            ['name' => 'Fliegenfischen', 'name_en' => 'Fly fishing'],
            ['name' => 'Uferangeln', 'name_en' => 'Shore fishing'],
            ['name' => 'Ansitzangeln', 'name_en' => 'Bank fishing'],
            ['name' => 'Küstenangeln', 'name_en' => 'Coast fishing'],
            ['name' => 'Schleppangeln', 'name_en' => 'Trolling'],
            ['name' => 'Spinnfischen', 'name_en' => 'Spin fishing'],
            ['name' => 'Tiefseeangeln', 'name_en' => 'Deep sea fishing'],
            ['name' => 'Hochseefischen', 'name_en' => 'Big game fishing'],
            ['name' => 'Driftangeln', 'name_en' => 'Drift fishing'],
            ['name' => 'Ultraleicht Angeln', 'name_en' => 'Ultralight fishing'],
            ['name' => 'Eisangeln', 'name_en' => 'Ice fishing'],
            ['name' => 'Jiggen', 'name_en' => 'Jigging'],
            ['name' => 'Hardbait', 'name_en' => 'Hardbait fishing'],
            ['name' => 'Köderfischangeln', 'name_en' => 'Deadbait fishing']
        ];
        foreach($methods as $method) {
            DB::table('methods')->insert($method);
        }

        # Boat extras
        DB::table('boat_extras')->truncate();
        $boat_extras = [
            ['name' => 'GPS', 'name_en' => 'GPS'],
            ['name' => 'Echolot', 'name_en' => 'Sonar'],
            ['name' => 'Live Scope', 'name_en' => 'Live Scope'],
            ['name' => 'Radar', 'name_en' => 'Radar'],
            ['name' => 'Funk', 'name_en' => 'Radio'],
            ['name' => 'Flybridge', 'name_en' => 'Flybridge'],
            ['name' => 'WC', 'name_en' => 'WC'],
            ['name' => 'Dusche', 'name_en' => 'Shower'],
            ['name' => 'Küche', 'name_en' => 'Kitchen'],
            ['name' => 'Bett', 'name_en' => 'Bed'],
            ['name' => 'Wifi', 'name_en' => 'Wifi'],
            ['name' => 'Kühl- /Eisschrank', 'name_en' => 'Cooler / ice box'],
            ['name' => 'Klimaanlage', 'name_en' => 'Air conditioning'],
            ['name' => 'Drill Stuhl', 'name_en' => 'Fighting chair'],
            ['name' => 'E-Motor', 'name_en' => 'Electric motor'],
            ['name' => 'Felitiertisch', 'name_en' => 'Feliting table'],
            ['name' => 'Sonstiges', 'name_en' => 'Others']
        ];
        foreach($boat_extras as $extra) {
            DB::table('boat_extras')->insert($extra);
        }

        # Inclusions
        DB::table('inclussions')->truncate();
        $inclusions = [
            ['name' => 'Angelrute & Rolle', 'name_en' => 'Fishing rod & reel'],
            ['name' => 'Wathose', 'name_en' => 'Waders'],
            ['name' => 'Kunstfliegen', 'name_en' => 'Artificial flies'],
            ['name' => 'Kunstköder', 'name_en' => 'Artificial lures'],
            ['name' => 'Naturköder', 'name_en' => 'Natural bait'],
            ['name' => 'Fisch zerlegen', 'name_en' => 'Dissecting fish'],
            ['name' => 'Snacks', 'name_en' => 'Snacks'],
            ['name' => 'Drinks', 'name_en' => 'Drinks'],
            ['name' => 'Mittagessen', 'name_en' => 'lunch'],
            ['name' => 'Abendessen', 'name_en' => 'Dinner'],
            ['name' => 'Lizenz', 'name_en' => 'License'],
            ['name' => 'Catch & Cook', 'name_en' => 'Catch & Cook'],
            ['name' => 'Sprit', 'name_en' => 'Fuel'],
            ['name' => 'Abholservice', 'name_en' => 'Pick up service'],
            ['name' => 'Unterkunft', 'name_en' => 'Accommodation'],
            ['name' => 'Vollverpflegung', 'name_en' => 'Complete meals'],
            ['name' => 'Bilder & Videos vom Trip', 'name_en' => 'Pictures & videos from the trip'],
            ['name' => 'Sontiges', 'name_en' => 'Others']
        ];
        foreach($inclusions as $inclusion) {
            DB::table('inclussions')->insert($inclusion);
        }

        # Extras prices
        DB::table('extras_prices')->truncate();
        $extras_prices = [
            ['name' => 'Abholservice', 'name_en' => 'Pick up service'],
            ['name' => 'Lizenz', 'name_en' => 'License'],
            ['name' => 'Extra Stunde', 'name_en' => 'Extra hour'],
            ['name' => 'Wasser & Soda', 'name_en' => 'Water & sodas'],
            ['name' => 'Alkoholische Getränke', 'name_en' => 'Alcoholic drinks'],
            ['name' => 'Snack', 'name_en' => 'Snack'],
            ['name' => 'Vollverpflegung', 'name_en' => 'Meals / catering'],
            ['name' => 'Köder', 'name_en' => 'Bait'],
            ['name' => 'Equipment', 'name_en' => 'Fishing equipment'],
            ['name' => 'Wathose', 'name_en' => 'Waders'],
            ['name' => 'Sontiges', 'name_en' => 'Others']
        ];
        foreach($extras_prices as $extra_price) {
            DB::table('extras_prices')->insert($extra_price);
        }


    }
}
