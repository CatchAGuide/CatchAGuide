<?php

namespace Database\Seeders;

use App\Models\BlockedEvent;
use App\Models\Booking;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Employee;
use App\Models\Guiding;
use App\Models\GuidingGalleryMedia;
use App\Models\GuidingTargetFish;
use App\Models\GuidingWaterType;
use App\Models\Media;
use App\Models\Payment;
use App\Models\Rating;
use App\Models\RoomConfiguration;
use App\Models\User;
use Illuminate\Database\Seeder;

class   DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(EmployeeSeeder::class);
        $this->call(GuidingsSettingsSeeder::class);
        $this->call(VacationsSeeder::class);
        // $this->call(LocationBoundarySeeder::class);
        
        // Accommodation form seeders
        $this->call(AccommodationTypeSeeder::class);
        $this->call(AccommodationDetailSeeder::class);
        $this->call(AccommodationExtraSeeder::class);
        $this->call(AccommodationInclusiveSeeder::class);
        $this->call(AccommodationRentalConditionSeeder::class);
        $this->call(AccommodationPolicySeeder::class);
        
        $this->call(RoomConfigurationSeeder::class);
        $this->call(FacilitySeeder::class);
        $this->call(KitchenEquipmentSeeder::class);
        $this->call(BathroomAmenitySeeder::class);
        $this->call(CampFacilitySeeder::class);
        $this->call(KitchenEquipmentSeeder::class);
        
        // Rental boat form seeders
        $this->call(RentalBoatRequirementSeeder::class);
        $this->call(RoomConfiguration::class);

        #User::factory()->count(50)->create();
        #Employee::factory()->count(50)->create();
        #Chat::factory()->count(50)->create();
        #ChatMessage::factory()->count(50)->create();
        #Media::factory()->count(50)->create();
        #Guiding::factory()->count(50)->create();
        #BlockedEvent::factory()->count(50)->create();
        #GuidingGalleryMedia::factory()->count(50)->create();
        #GuidingWaterType::factory()->count(50)->create();
        #GuidingTargetFish::factory()->count(50)->create();
        #Booking::factory()->count(50)->create();
        #Rating::factory()->count(50)->create();
    }
}
