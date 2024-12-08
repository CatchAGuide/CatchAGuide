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
            ['name' => 'Meer', 'name_en' => 'Sea/ ocean'],
            ['name' => 'Küste', 'name_en' => 'Coast'],
            ['name' => 'Kanal', 'name_en' => 'Canal'],
            ['name' => 'Stausee', 'name_en' => 'Reservoir'],
            ['name' => 'Hochsee', 'name_en' => 'Off shore'],
            ['name' => 'Bach', 'name_en' => 'Stream'],
            ['name' => 'Hafen', 'name_en' => 'Port/ Harbour'],
            ['name' => 'Angelkurs auf dem Trockenen', 'name_en' => 'Fishing course on land'],
            ['name' => 'Fjord', 'name_en' => 'Fjord'],
            ['name' => 'Brackwasser', 'name_en' => 'Brackish water'],
            ['name' => 'Polder', 'name_en' => 'Polder'],
            ['name' => 'Altarm', 'name_en' => 'Oxbow lake'],
            ['name' => 'Teich', 'name_en' => 'Pond']
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
            ['name' => 'Bonito', 'name_en' => 'Bonito'],
            ['name' => 'Dorsch', 'name_en' => 'Cod'],
            ['name' => 'Makrele', 'name_en' => 'Mackerel'],
            ['name' => 'Atlantischer Lachs', 'name_en' => 'Atlantic Salmon'],
            ['name' => 'Hering', 'name_en' => 'Herring'],
            ['name' => 'Heilbutt', 'name_en' => 'Halibut'],
            ['name' => 'Barrakuda', 'name_en' => 'Barracuda'],
            ['name' => 'Bluefish', 'name_en' => 'Bluefish'],
            ['name' => 'Meeraal', 'name_en' => 'Conger Eel'],
            ['name' => 'Kliesche', 'name_en' => 'Dab'],
            ['name' => 'Flunder', 'name_en' => 'Flounder'],
            ['name' => 'Goldbrasse', 'name_en' => 'Gilthead Seabream'],
            ['name' => 'Amberjack', 'name_en' => 'Amberjack'],
            ['name' => 'Knurrhahn', 'name_en' => 'Gurnard'],
            ['name' => 'Schellfisch', 'name_en' => 'Haddock'],
            ['name' => 'Leng', 'name_en' => 'Ling'],
            ['name' => 'Goldmakrele', 'name_en' => 'Mahi Mahi'],
            ['name' => 'Pollack', 'name_en' => 'Pollack'],
            ['name' => 'Rotbarbe', 'name_en' => 'Red Mullet'],
            ['name' => 'Rotbarsch', 'name_en' => 'Redfish'],
            ['name' => 'Meerbrasse', 'name_en' => 'Sea Bream'],
            ['name' => 'Meerforelle', 'name_en' => 'Seatrout'],
            ['name' => 'Seezunge', 'name_en' => 'Sole'],
            ['name' => 'Tintenfisch', 'name_en' => 'Squid'],
            ['name' => 'Seewolf', 'name_en' => 'Wolffish'],
            ['name' => 'Blauflossen-Thunfisch', 'name_en' => 'Bluefin Tuna'],
            ['name' => 'Schwertfisch', 'name_en' => 'Swordfish'],
            ['name' => 'Weißer Marlin', 'name_en' => 'White Marlin'],
            ['name' => 'Blauhai', 'name_en' => 'Blue Shark'],
            ['name' => 'Heringshai', 'name_en' => 'Porbeagle Shark'],
            ['name' => 'Fuchshai', 'name_en' => 'Thresher Shark'],
            ['name' => 'Makohai', 'name_en' => 'Shortfin Mako Shark'],
            ['name' => 'Großer Hammerhai', 'name_en' => 'Great Hammerhead Shark'],
            ['name' => 'Dornhai', 'name_en' => 'Spiny Dogfish'],
            ['name' => 'Hundshai', 'name_en' => 'Smoothhound'],
            ['name' => 'Hundshai', 'name_en' => 'Tope Shark'],
            ['name' => 'Blauer Marlin', 'name_en' => 'Blue Marlin'],
            ['name' => 'Speerfisch', 'name_en' => 'Spearfish'],
            ['name' => 'Dentex', 'name_en' => 'Dentex'],
            ['name' => 'Petersfisch', 'name_en' => 'John Dory'],
            ['name' => 'Meeräsche', 'name_en' => 'Mullet'],
            ['name' => 'Rochen', 'name_en' => 'Ray'],
            ['name' => 'Drachenkopf', 'name_en' => 'Scorpionfish'],
            ['name' => 'Europäischer Wolfsbarsch', 'name_en' => 'Seabass (European)'],
            ['name' => 'Wittling', 'name_en' => 'Whiting'],
            ['name' => 'Barbe', 'name_en' => 'Barbel'],
            ['name' => 'Giebel', 'name_en' => 'Prussian Carp'],
            ['name' => 'Hornhecht', 'name_en' => 'Garfish'],
            ['name' => 'Quappe', 'name_en' => 'Burbot'],
            ['name' => 'Scholle', 'name_en' => 'Plaice'],
            ['name' => 'Seelachs', 'name_en' => 'Coalfish'],
            ['name' => 'Steinbutt', 'name_en' => 'Turbot'],
            ['name' => 'Aal', 'name_en' => 'Eel'],
            ['name' => 'Schwarzbarsch', 'name_en' => 'Black Bass'],
            ['name' => 'Dorade', 'name_en' => 'Gilthead'],
            ['name' => 'Snapper', 'name_en' => 'Snapper'],
            ['name' => 'Döbel', 'name_en' => 'Chub'],
            ['name' => 'Albacore', 'name_en' => 'Albacore'],
            ['name' => 'Schwarzer Bonito', 'name_en' => 'Black Skipjack'],
            ['name' => 'False Albacore', 'name_en' => 'False Albacore'],
            ['name' => 'Zackenbarsch', 'name_en' => 'Grouper'],
            ['name' => 'Bigeye Tuna', 'name_en' => 'Bigeye Tuna'],
            ['name' => 'Blackfin Tuna', 'name_en' => 'Blackfin Tuna'],
            ['name' => 'Dogtooth Tuna', 'name_en' => 'Dogtooth Tuna'],
            ['name' => 'Angelkurs auf dem Trockenen', 'name_en' => 'Fishing Course on land'],
            ['name' => 'Kleiner Thunfisch', 'name_en' => 'Little Thunny'],
            ['name' => 'Rotbrasse', 'name_en' => 'Red Porgy'],
            ['name' => 'Drückerfisch', 'name_en' => 'Triggerfish'],
            ['name' => 'Sechskiemenhai', 'name_en' => 'Sixgill Shark']
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
            ['name' => 'Köderfischangeln', 'name_en' => 'Deadbait fishing'],
            ['name' => 'Angelkurs auf dem Trockenen', 'name_en' => 'Fishing course on land'],
            ['name' => 'Bootsangeln', 'name_en' => 'Boat fishing'],
            ['name' => 'Grundangeln', 'name_en' => 'Bottom fishing'],
            ['name' => 'LIvescope', 'name_en' => 'Livescope'],
            ['name' => 'Vertikalangeln', 'name_en' => 'Vertical fishing'],
            ['name' => 'Watangeln', 'name_en' => 'Wading'],
            ['name' => 'Klopfen', 'name_en' => 'Klopfen'],
            ['name' => 'Pelagisch angeln', 'name_en' => 'Pelagic fishing']
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
            ['name' => 'Sontiges', 'name_en' => 'Others']
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
            ['name' => 'Sontiges', 'name_en' => 'Others'],
            ['name' => 'Kaffee', 'name_en' => 'Coffee']
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

        # Boat Types
        DB::table('guiding_boat_types')->truncate();
        $boat_types = [
            ['name' => 'Kajak', 'name_en' => 'Kayak'],
            ['name' => 'Belly Boot', 'name_en' => 'Belly Boat'], 
            ['name' => 'Ruderboot', 'name_en' => 'Rowing Boat'],
            ['name' => 'Drift Boot', 'name_en' => 'Drift Boat'],
            ['name' => 'Sportangelboot', 'name_en' => 'Sport Fishing Boat'],
            ['name' => 'Yacht', 'name_en' => 'Yacht'],
            ['name' => 'Segelboot', 'name_en' => 'Sailing Boat'],
            ['name' => 'Schlauchboot', 'name_en' => 'Inflatable Boat'],
            ['name' => 'Aluboot', 'name_en' => 'Aluminum Boat']
        ];
        foreach($boat_types as $boat_type) {
            DB::table('guiding_boat_types')->insert($boat_type);
        }

        # Boat Descriptions
        DB::table('guiding_boat_descriptions')->truncate();
        $boat_descriptions = [
            ['name' => 'Anzahl der Sitzplätze', 'name_en' => 'Number of seats'],
            ['name' => 'Länge', 'name_en' => 'Length'],
            ['name' => 'Breite', 'name_en' => 'Width'],
            ['name' => 'Baujahr', 'name_en' => 'Year built'],
            ['name' => 'Motorenhersteller', 'name_en' => 'Engine manufacturer'],
            ['name' => 'Motorleistung', 'name_en' => 'Engine power'],
            ['name' => 'Höchstgeschwindigkeit', 'name_en' => 'Max speed'],
            ['name' => 'Hersteller', 'name_en' => 'Manufacturer'],
            ['name' => 'Beschreibung', 'name_en' => 'Description']
        ];
        foreach($boat_descriptions as $boat_description) {
            DB::table('guiding_boat_descriptions')->insert($boat_description);
        }

        # Additional Information
        DB::table('guiding_additional_informations')->truncate();
        $additional_information = [
            ['name' => 'Kinderfreundlich', 'name_en' => 'Child-friendly'],
            ['name' => 'Behindertenfreundlich', 'name_en' => 'Handicapped friendly'],
            ['name' => 'Rauchen verboten', 'name_en' => 'No smoking'],
            ['name' => 'Alkohol verboten', 'name_en' => 'No alcohol'],
            ['name' => 'Fang wird behalten', 'name_en' => 'Keep the catch'],
            ['name' => 'Catch & Release allowed', 'name_en' => 'Catch & Release allowed'],
            ['name' => 'Catch & Release only', 'name_en' => 'Catch & Release only'],
            ['name' => 'Stellplatz', 'name_en' => 'Parking space'],
            ['name' => 'Sontiges', 'name_en' => 'Others']
        ];
        foreach($additional_information as $info) {
            DB::table('guiding_additional_informations')->insert($info);
        }

        # Requirements
        DB::table('guiding_requirements')->truncate();
        $requirements = [
            ['name' => 'Lizenzen / Erlaubnis', 'name_en' => 'Licenses / Permits'],
            ['name' => 'Bekleidung', 'name_en' => 'Clothing'],
            ['name' => 'Bestimmtes Erfahrungslevel', 'name_en' => 'Specific experience level'],
            ['name' => 'Equipment', 'name_en' => 'Equipment'],
            ['name' => 'Alter', 'name_en' => 'Age'],
            ['name' => 'Sontiges', 'name_en' => 'Others']
        ];
        foreach($requirements as $requirement) {
            DB::table('guiding_requirements')->insert($requirement);
        }

        # Recommendations
        DB::table('guiding_recommendations')->truncate();
        $recommendations = [
            ['name' => 'Sonnenschutz', 'name_en' => 'Sun protection'],
            ['name' => 'Verpflegung', 'name_en' => 'Food and drink'],
            ['name' => 'Wahl des Equipments', 'name_en' => 'Choice of equipment'],
            ['name' => 'Bestimmte Kleidung', 'name_en' => 'Specific clothing'],
            ['name' => 'Sontiges', 'name_en' => 'Others']
        ];
        foreach($recommendations as $recommendation) {
            DB::table('guiding_recommendations')->insert($recommendation);
        }

    }
}
