<?php

namespace App\Console\Commands;

use App\Models\Guiding;
use Illuminate\Console\Command;
use App\Models\GuidingTarget;
use App\Models\Target;
use App\Models\Method;
use App\Models\GuidingRequirements;
use App\Models\GuidingRecommendations;
use App\Models\Inclussion;
use App\Models\GuidingBoatType;
use App\Models\GuidingExtras;
use App\Models\Water;
use App\Models\GuidingBoatDescription;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\GuidingBackup;

class MigrateOldToNewGuidingsDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:guidings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Guidings from old to new data structure';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // First check if we need to do the table rename and creation
            if (!Schema::hasTable('guidings_backup')) {
                // Store current table structure
                if (Schema::hasTable('guidings')) {
                    try {
                        Schema::rename('guidings', 'guidings_backup');
                        
                        Schema::create('guidings', function (Blueprint $table) {
                            $table->id();
                            $table->string('title');
                            $table->string('slug');
                            $table->string('location');
                            $table->string('city')->nullable();
                            $table->string('country')->nullable();
                            $table->integer('duration');
                            $table->string('duration_type');
                            $table->integer('max_guests');
                            $table->text('description')->nullable();
                            $table->string('status')->default('active');
                            $table->foreignId('user_id')->constrained('users')->name('guidings_user_id_foreign_new');
                            $table->decimal('lat', 10, 8)->nullable();
                            $table->decimal('lng', 11, 8)->nullable();
                            $table->string('thumbnail_path')->nullable();
                            $table->json('boat_information')->nullable();
                            $table->integer('allowed_booking_advance')->nullable();
                            $table->integer('booking_window')->nullable();
                            $table->boolean('seasonal_trip')->default(false);
                            $table->boolean('is_boat')->default(false);
                            $table->integer('boat_type')->nullable();
                            $table->json('boat_extra')->nullable();
                            $table->json('target_fish')->nullable();
                            $table->json('fishing_methods')->nullable();
                            $table->json('water_types')->nullable();
                            $table->json('inclussions')->nullable();
                            $table->json('requirements')->nullable();
                            $table->json('recommendations')->nullable();
                            $table->text('other_information')->nullable();
                            $table->string('price_type');
                            $table->json('prices');
                            $table->json('pricing_extra')->nullable();
                            $table->string('tour_type');
                            $table->json('months')->nullable();
                            $table->json('gallery_images')->nullable();
                            $table->text('desc_course_of_action')->nullable();
                            $table->text('desc_meeting_point')->nullable();
                            $table->text('desc_tour_unique')->nullable();
                            $table->text('desc_starting_time')->nullable();
                            $table->foreignId('fishing_type_id')->nullable()->constrained('fishing_types');
                            $table->timestamps();
                            $table->softDeletes();
                        });

                        $this->info('Created new guidings table and renamed old one to guidings_backup');
                    } catch (\Exception $e) {
                        // If schema operations fail, try to restore original state
                        if (Schema::hasTable('guidings_backup')) {
                            Schema::dropIfExists('guidings');
                            Schema::rename('guidings_backup', 'guidings');
                        }
                        throw $e;
                    }
                } else {
                    throw new \Exception('Original guidings table not found');
                }
            }

            // Now start transaction for data migration
            \DB::beginTransaction();

            // Get path to JSON file
            $jsonPath = "E:\Programs\laragon\www\customscripts\python\output.json";

            // Check if file exists
            if (!file_exists($jsonPath)) {
                $this->error('JSON file not found at: ' . $jsonPath);
                return 1;
            }

            // Read and decode JSON file
            $jsonData = json_decode(file_get_contents($jsonPath), true);

            // Check if JSON was decoded successfully
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Failed to parse JSON file: ' . json_last_error_msg());
                return 1;
            }

            // Store JSON data in variable for later use
            $migrationData = $jsonData;
            
            $output = [];

            $guidings = GuidingBackup::with('guidingTargets', 'guidingMethods', 'guidingWaters', 'guidingInclussions')
                ->where('is_newguiding', 0)
                ->get();
            
            foreach($guidings as $index => $guiding) {
                try {
                    $boat_type = null;
                    $target_fish_data = null;
                    $fishing_methods_data = null;
                    $water_types_data = null;
                    $requirement_id = null;
                    $requirements = null;
                    $inclusions_data = null;
                    $recommended_id = null;
                    $recommended = null;
                    $desc_course_of_action = null;
                    $desc_meeting_point = null;
                    $pricing_extra_data = null;
                    $desc_tour_unique = null;
                    $desc_starting_time = null;
                    $boat_information_id = null;
                    $boat_information = null;

                    if (isset($migrationData[$guiding->id])) {
                        $target_fish_data = $migrationData[$guiding->id]['target_fish'] ?? null;
                        $boat_type = $migrationData[$guiding->id]['boat_type'] ?? null;
                        $fishing_methods_data = $migrationData[$guiding->id]['fishing_methods'] ?? null; 
                        $water_types_data = $migrationData[$guiding->id]['water_types'] ?? null;
                        $requirement_id = $migrationData[$guiding->id]['requirements_id'] ?? null;
                        $requirements = isset($migrationData[$guiding->id]['requirements']) ? mb_convert_encoding($migrationData[$guiding->id]['requirements'], 'UTF-8', 'auto') : null;
                        $inclusions_data = isset($migrationData[$guiding->id]['inclusions']) ? mb_convert_encoding($migrationData[$guiding->id]['inclusions'], 'UTF-8', 'auto') : null;
                        $recommended_id = $migrationData[$guiding->id]['recommendations_id'] ?? null;
                        $recommended = isset($migrationData[$guiding->id]['recommendations']) ? mb_convert_encoding($migrationData[$guiding->id]['recommendations'], 'UTF-8', 'auto') : null;
                        $desc_course_of_action = isset($migrationData[$guiding->id]['desc_course_of_action']) ? mb_convert_encoding($migrationData[$guiding->id]['desc_course_of_action'], 'UTF-8', 'auto') : null;
                        $pricing_extra_data = $migrationData[$guiding->id]['pricing_extra'] ?? null;
                        $desc_tour_unique = isset($migrationData[$guiding->id]['desc_tour_unique']) ? mb_convert_encoding($migrationData[$guiding->id]['desc_tour_unique'], 'UTF-8', 'auto') : null;
                        $desc_starting_time = isset($migrationData[$guiding->id]['desc_starting_time']) ? mb_convert_encoding($migrationData[$guiding->id]['desc_starting_time'], 'UTF-8', 'auto') : null;
                        $boat_information_id = $migrationData[$guiding->id]['boat_information_id'] ?? null;
                        $boat_information = isset($migrationData[$guiding->id]['boat_information']) ? mb_convert_encoding($migrationData[$guiding->id]['boat_information'], 'UTF-8', 'auto') : null;
                    }

                    //fishing from
                    $guiding->is_boat = $guiding->fishing_from_id == 1 ? 1 : 0;
                    if ($boat_type) {
                        $boat_type_data = GuidingBoatType::find((int) $boat_type);
                        if ($boat_type_data) {
                            $guiding->boat_type = (int) $boat_type_data->id;
                        }
                    }

                    //target fish
                    $target_fish = $this->processDataArray($target_fish_data, Target::class);
                    foreach($guiding->guidingTargets as $targetFishData) {
                        $migratedTargetFish = $this->targetFishMigration($targetFishData->id);
                        if ($migratedTargetFish !== null && !in_array($migratedTargetFish, $target_fish)) {
                            array_push($target_fish, $migratedTargetFish);
                        }
                    }
                    $guiding->target_fish = json_encode(array_values(array_unique($target_fish)));

                    //method
                    $methods = $this->processDataArray($fishing_methods_data, Method::class);
                    $threeMethods = explode(',', $guiding->threeMethods());
                    foreach($threeMethods as $threeMethod) {
                        $threeMethod = trim($threeMethod);
                        if ($threeMethod === 'alle') {
                            continue;
                        }
                        $migratedThreeMethod = $this->threeMethodsMigration($threeMethod);
                        if ($migratedThreeMethod !== null) {
                            $migratedMethod = $this->methodMigration($migratedThreeMethod);
                            if ($migratedMethod !== null && !in_array($migratedMethod, $methods)) {
                                array_push($methods, $migratedMethod);
                            }
                        }
                    }
                    if ($guiding->guidingMethods) {
                        foreach($guiding->guidingMethods as $method) {
                            $migratedMethodGuiding = $this->methodMigration($method->id);
                            if ($migratedMethodGuiding !== null && !in_array($migratedMethodGuiding, $methods)) {
                                array_push($methods, $migratedMethodGuiding);
                            }
                        }
                    }
                    $guiding->fishing_methods = json_encode(array_values(array_unique($methods)));

                    //water types
                    $water_types = $this->processDataArray($water_types_data, Water::class);
                    $threeWaters = explode(',', $guiding->threeWaters());
                    foreach($threeWaters as $threeWater) {
                        $threeWater = trim($threeWater);
                        if ($threeWater === 'alle') {
                            continue;
                        }
                        $migratedThreeWater = $this->threeWatersMigration($threeWater);
                        if ($migratedThreeWater !== null) {
                            $migratedWater = $this->waterTypeMigration($migratedThreeWater);
                            if ($migratedWater !== null && !in_array($migratedWater, $water_types)) {
                                array_push($water_types, $migratedWater);
                            }
                        }
                    }
                    if ($guiding->guidingWaters) {
                        foreach($guiding->guidingWaters as $waterType) {
                            $migratedWaterType = $this->waterTypeMigration($waterType->id);
                            if ($migratedWaterType !== null && !in_array($migratedWaterType, $water_types)) {
                                array_push($water_types, $migratedWaterType);
                            }
                        }
                    }
                    $guiding->water_types = json_encode(array_values(array_unique($water_types)));
                    
                    //inclusions
                    $inclusions_input = [];
                    if ($inclusions_data) {
                        foreach($inclusions_data as $inclusionIndex => $inclusion_row) {
                            $inclusionData = Inclussion::find((int) $inclusion_row);
                            if ($inclusionData) {
                                array_push($inclusions_input, $inclusionData->id);
                            }
                        }
                    }
                    if ($guiding->guidingInclussions) {
                        foreach($guiding->guidingInclussions as $inclusion_value) {
                            $migratedInclusion = $this->inclusionsMigration($inclusion_value->id);
                            if ($migratedInclusion !== null && !in_array($migratedInclusion, $inclusions_input)) {
                                array_push($inclusions_input, $migratedInclusion);
                            }
                        }
                    }
                    $guiding->inclusions = json_encode($inclusions_input);

                    $guiding->description = html_entity_decode($desc_course_of_action[0] ?? $guiding->description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $guiding->desc_course_of_action = html_entity_decode($desc_course_of_action[0] ?? $guiding->description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $guiding->desc_tour_unique = html_entity_decode($desc_tour_unique[0] ?? null, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $guiding->desc_starting_time = html_entity_decode($desc_starting_time[0] ?? null, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $guiding->desc_meeting_point = html_entity_decode($desc_meeting_point[0] ?? $guiding->meeting_point, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                    
                    $boat_information_input = [];
                    if ($boat_information_id) {
                        foreach($boat_information_id as $boatInformationIndex => $boatInformation) {
                            
                            if (preg_match('/^\d+$/', $boatInformation)) {
                                $boatInformation = GuidingBoatDescription::find((int) $boatInformation);
                            } else {
                                $boatInformation = GuidingBoatDescription::where('name_en', $boatInformation)->orWhere('name', $boatInformation)->first();
                            }

                            if ($boatInformation) {
                                array_push($boat_information_input, [
                                    'value' => $boat_information[$boatInformationIndex],
                                    'id' => $boatInformation->id
                                ]);
                            }
                        }
                    }   
                    $guiding->boat_information = json_encode($boat_information_input);

                    $requirements_input = [];
                    if ($requirement_id) {
                        foreach($requirement_id as $requimentIndex => $requirement) {
                            $requirement_query = GuidingRequirements::find((int) $requirement);
                            if ($requirement_query) {
                                array_push($requirements_input, [
                                    'value' => $requirements[$requimentIndex],
                                    'id' => $requirement_query->id
                                ]);
                            }
                        }
                    }
                    $guiding->requirements = json_encode($requirements_input);

                    $recommendations_input = [];
                    if ($recommended_id) {
                        foreach($recommended_id as $recommendationIndex => $recommendation) {
                            $recommendation = GuidingRecommendations::find((int) $recommendation);
                            if ($recommendation) {
                                array_push($recommendations_input, [
                                    'value' => $recommended[$recommendationIndex],
                                    'id' => $recommendation->id
                                ]);
                            }
                        }
                    }
                    $guiding->recommendations = json_encode($recommendations_input);

                    //duration
                    $duration = (int)$guiding->duration;
                    
                    if ($duration <= 5) {
                        $guiding->duration_type = "half_day";
                    } else if ($duration <= 12) {
                        $guiding->duration_type = "full_day"; 
                    } else {
                        $guiding->duration_type = "multi_day";
                        $days = ceil($duration / 12);
                        $guiding->duration = $days;
                    }

                    //price
                    $guiding->price_type = 'per_person';
                    $prices = [];
                    if (!empty($guiding->price) && $guiding->price > 0) {
                        array_push($prices, [
                            "person" => "1",
                            "amount" => (string)$guiding->price
                        ]);
                    }
                    if (!empty($guiding->price_two_persons) && $guiding->price_two_persons > 0) {
                        array_push($prices, [
                            "person" => "2",
                            "amount" => (string)$guiding->price_two_persons
                        ]);
                    }
                    if (!empty($guiding->price_three_persons) && $guiding->price_three_persons > 0) {
                        array_push($prices, [
                            "person" => "3",
                            "amount" => (string)$guiding->price_three_persons
                        ]);
                    }
                    if (!empty($guiding->price_four_persons) && $guiding->price_four_persons > 0) {
                        array_push($prices, [
                            "person" => "4",
                            "amount" => (string)$guiding->price_four_persons
                        ]);
                    }
                    if (!empty($guiding->price_five_persons) && $guiding->price_five_persons > 0) {
                        array_push($prices, [
                            "person" => "5",
                            "amount" => (string)$guiding->price_five_persons
                        ]);
                    }
                    $guiding->prices = json_encode($prices);

                    //pricing extra
                    $pricingExtra = [];
                    $guidingExtras = GuidingExtras::where('guiding_id', $guiding->id)->get();
                    if ($guidingExtras) {
                        foreach ($guidingExtras as $extra) {
                            array_push($pricingExtra, [
                                'name' => $extra->name,
                                'price' => (string)$extra->price
                            ]);
                        }
                    }
                    $guiding->pricing_extra = json_encode($pricingExtra);

                    //galery images
                    $galeryImages = [];
                    $galleries = json_decode($guiding->galleries);

                    if (!empty($galleries)) {
                        foreach ($galleries as $image) {
                            array_push($galeryImages, $image);
                        }
                    }
                    if (!empty($guiding->thumbnail_path)) {
                        array_push($galeryImages, $guiding->thumbnail_path);
                    }
                    $guiding->galery_images = json_encode($galeryImages);

                    // Create new guiding record
                    Guiding::create([
                        'id' => $guiding->id,
                        'title' => html_entity_decode($guiding->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'slug' => $guiding->slug,
                        'location' => $guiding->location,
                        'city' => $guiding->city,
                        'country' => $guiding->country,
                        'duration' => $guiding->duration,
                        'duration_type' => $guiding->duration_type,
                        'max_guests' => $guiding->max_guests,
                        'description' => html_entity_decode($guiding->description, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 
                        'status' => $guiding->status,
                        'user_id' => $guiding->user_id,
                        'lat' => $guiding->lat,
                        'lng' => $guiding->lng,
                        'thumbnail_path' => $guiding->thumbnail_path,
                        'boat_information' => $guiding->boat_information ?? '[]',
                        'allowed_booking_advance' => $guiding->allowed_booking_advance ?? 0,
                        'booking_window' => $guiding->booking_window ?? 0,
                        'seasonal_trip' => $guiding->seasonal_trip ?? false,
                        'is_boat' => $guiding->is_boat,
                        'boat_type' => $guiding->boat_type,
                        'boat_extra' => $guiding->boat_extra ?? null,
                        'target_fish' => $guiding->target_fish ?? '[]',
                        'fishing_methods' => $guiding->fishing_methods ?? '[]',
                        'water_types' => $guiding->water_types ?? '[]',
                        'inclussions' => $guiding->inclusions,
                        'requirements' => $guiding->requirements ?? '[]',
                        'recommendations' => $guiding->recommendations ?? '[]',
                        'other_information' => $guiding->other_information ?? null,
                        'price_type' => $guiding->price_type,
                        'prices' => $guiding->prices,
                        'pricing_extra' => $guiding->pricing_extra ?? '[]',
                        'tour_type' => $guiding->tour_type ?? 'private',
                        'months' => $guiding->months ?? '[]',
                        'gallery_images' => $guiding->galery_images,
                        'desc_course_of_action' => html_entity_decode($guiding->desc_course_of_action, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'desc_meeting_point' => html_entity_decode($guiding->desc_meeting_point, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'desc_tour_unique' => html_entity_decode($guiding->desc_tour_unique, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'desc_starting_time' => html_entity_decode($guiding->desc_starting_time, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'fishing_type_id' => $guiding->fishing_type_id,
                    ]);

                    $this->info("Migrated guiding ID: {$guiding->id}");
                } catch (\Exception $e) {
                    \DB::rollBack();
                    $this->error("Failed to migrate guiding ID {$guiding->id}: " . $e->getMessage());
                    
                    // Restore original table structure if needed
                    if (Schema::hasTable('guidings_backup')) {
                        Schema::dropIfExists('guidings');
                        Schema::rename('guidings_backup', 'guidings');
                    }
                    
                    return 1;
                }
            }

            \DB::commit();
            $this->info('Migration completed successfully!');
            return 0;

        } catch (\Exception $e) {
            // Only rollback if we have an active transaction
            if (\DB::transactionLevel() > 0) {
                \DB::rollBack();
            }
            
            $this->error('Migration failed: ' . $e->getMessage());
            
            // Try to restore original table structure if needed
            try {
                if (Schema::hasTable('guidings_backup') && !Schema::hasTable('guidings')) {
                    Schema::rename('guidings_backup', 'guidings');
                    $this->info('Restored original guidings table');
                }
            } catch (\Exception $restoreException) {
                $this->error('Failed to restore original table: ' . $restoreException->getMessage());
            }
            
            return 1;
        }
    }

    public function targetFishMigration($id) {
        $data = [
            1 => 9,
            2 => 4,
            3 => 8,
            4 => 11,
            6 => 18,
            7 => 12,
            8 => 2,
            9 => 3,
            11 => 6,
            12 => 14,
            13 => 61,
            14 => 7,
            15 => 62,
            17 => 5,
            21 => 13,
            23 => 64,
            24 => 1,
            24 => 16,
            27 => 25,
            28 => 20,
            29 => 19,
            30 => 63,
            31 => 24,
            32 => 17,
            33 => 55,
            34 => 36,
            36 => 65,
            37 => 66,
            38 => 39,
            39 => 67,
            41 => 60,
            42 => 58,
            43 => 68,
            45 => 31,
            46 => 32,
            48 => 30,
            49 => 69,
            50 => 70,
            51 => 27,
            52 => 22,
            53 => 71,
            54 => 72,
            55 => 51,
            56 => 21,
            57 => 53,
            59 => 57,
            60 => 35,
            61 => 52,
            62 => 41,
            63 => 73,
            64 => 77,
            65 => 78,
            66 => 74,
            67 => 40,
            68 => 15,
            69 => 79,
            71 => 75,
            72 => 76,
            73 => 80
        ];

        return isset($data[(int)$id]) ? $data[(int)$id] : null;
    }

    public function methodMigration($id) {
        $data = [
            1 => 3,
            2 => 6,
            3 => 16,
            4 => 1,
            5 => 8,
            6 => 4,
            7 => 2
        ];

        return isset($data[(int)$id]) ? $data[(int)$id] : null;
    }

    public function threeMethodsMigration($id) {
        $data = [
            'Ansitzangeln' => 1,
            'Spinnfischen' => 2,
            'Bootsangeln' => 3,
            'Fliegenfischen' => 4,
            'Hochseefischen' => 5,
            'Küstenangeln' => 6,
            'Uferangeln' => 7,
        ];

        return isset($data[(string)$id]) ? $data[(string)$id] : null;
    }

    public function waterTypeMigration($id) {
        $data = [
            1 => 1,
            2 => 3,
            3 => 2,
            4 => 8,
            5 => 5,
            6 => 9,
            7 => 6,
            8 => 10
        ];

        return isset($data[(int)$id]) ? $data[(int)$id] : null;
    }

    public function threeWatersMigration($id) {
        $data = [
            'Fluss' => 1,
            'Meer' => 2,
            'See' => 3,
            'Bach' => 4,
            'Kanal' => 5,
            'Hafen' => 6,
            'Talsperre' => 7,
            'Angelkurs an Land' => 8,
        ];

        return isset($data[(string)$id]) ? $data[(string)$id] : null;
    }

    public function inclusionsMigration($id) {
        $data =  [
            1 => 1,
            2 => 11,
            3 => 4,
            4 => 8,
            5 => 13,
            6 => 14,
            7 => 7,
            8 => 9
        ];

        return isset($data[(int)$id]) ? $data[(int)$id] : null;
    }

    private function findModelByIdOrName($data, $model)
    {
        if (preg_match('/^\d+$/', $data)) {
            return $model::find((int) $data);
        }
        return $model::where('name_en', $data)->orWhere('name', $data)->first();
    }

    private function processDataArray($dataArray, $model)
    {
        $results = [];
        if ($dataArray) {
            foreach ($dataArray as $data) {
                $item = $this->findModelByIdOrName($data, $model);
                if ($item) {
                    array_push($results, $item->id);
                } else {
                    array_push($results, $data);
                }
            }
        }
        return $results;
    }
}