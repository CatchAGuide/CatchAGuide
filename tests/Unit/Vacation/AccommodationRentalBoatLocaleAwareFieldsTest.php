<?php

namespace Tests\Unit\Vacation;

use App\Models\AccommodationExtra;
use App\Models\Accommodation;
use App\Models\BoatExtra;
use App\Models\Facility;
use App\Models\RentalBoat;
use App\Models\RentalBoatRequirement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AccommodationRentalBoatLocaleAwareFieldsTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        app()->setLocale('de');

        parent::tearDown();
    }

    public function test_accommodation_amenities_resolve_master_data_by_locale(): void
    {
        $marker = 'test-facility-'.uniqid();

        $facility = Facility::query()->create([
            'name' => $marker.'-de',
            'name_en' => $marker.'-en',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $accommodation = new Accommodation();
        $accommodation->setRawAttributes([
            'amenities' => json_encode([
                ['id' => $facility->id, 'value' => $marker.'-de'],
                ['value' => 'Custom freeform amenity with no master id'],
            ]),
        ]);

        app()->setLocale('de');
        $deAmenities = $accommodation->amenities;
        $this->assertSame($marker.'-de', $deAmenities[0]['name']);

        app()->setLocale('en');
        $enAmenities = $accommodation->amenities;
        $this->assertSame($marker.'-en', $enAmenities[0]['name']);

        // Freeform entries with no matching master row keep their raw value
        // instead of being dropped.
        $this->assertNull($enAmenities[1]['name']);
        $this->assertSame('Custom freeform amenity with no master id', $enAmenities[1]['value']);
    }

    public function test_accommodation_extras_resolve_master_data_by_locale(): void
    {
        $marker = 'test-extra-'.uniqid();

        $extra = AccommodationExtra::query()->create([
            'name' => $marker.'-de',
            'name_en' => $marker.'-en',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $accommodation = new Accommodation();
        $accommodation->setRawAttributes([
            'extras' => json_encode([
                ['id' => $extra->id, 'value' => $marker.'-de'],
            ]),
        ]);

        app()->setLocale('de');
        $this->assertSame($marker.'-de', $accommodation->extras[0]['name']);

        app()->setLocale('en');
        $this->assertSame($marker.'-en', $accommodation->extras[0]['name']);
    }

    public function test_rental_boat_extras_resolve_master_data_by_locale(): void
    {
        $marker = 'test-boat-extra-'.uniqid();

        $boatExtra = BoatExtra::query()->create([
            'name' => $marker.'-de',
            'name_en' => $marker.'-en',
        ]);

        $boat = new RentalBoat();
        $boat->setRawAttributes([
            'boat_extras' => json_encode([
                ['id' => $boatExtra->id, 'value' => $marker.'-de'],
                ['value' => 'Custom freeform equipment'],
            ]),
        ]);

        app()->setLocale('de');
        $deExtras = $boat->boat_extras;
        $this->assertSame($marker.'-de', $deExtras[0]['name']);
        $this->assertNull($deExtras[1]['name']);
        $this->assertSame('Custom freeform equipment', $deExtras[1]['value']);

        app()->setLocale('en');
        $this->assertSame($marker.'-en', $boat->boat_extras[0]['name']);
    }

    public function test_rental_boat_requirements_resolve_master_data_by_locale(): void
    {
        $marker = 'test-requirement-'.uniqid();

        $requirement = RentalBoatRequirement::query()->create([
            'name' => $marker.'-de',
            'name_en' => $marker.'-en',
            'input_type' => 'text',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $boat = new RentalBoat();
        $boat->setRawAttributes([
            'requirements' => json_encode([
                ['id' => $requirement->id, 'value' => 'Answer text entered by the host'],
            ]),
        ]);

        app()->setLocale('de');
        $deRequirements = $boat->requirements;
        $this->assertSame($marker.'-de', $deRequirements[0]['name']);
        $this->assertSame('Answer text entered by the host', $deRequirements[0]['value']);

        app()->setLocale('en');
        $this->assertSame($marker.'-en', $boat->requirements[0]['name']);
    }

    public function test_rental_boat_requirements_handle_legacy_comma_separated_strings(): void
    {
        $boat = new RentalBoat();
        $boat->setRawAttributes([
            'requirements' => 'Minimum age 18, Valid ID required',
        ]);

        $requirements = $boat->requirements;

        $this->assertCount(2, $requirements);
        $this->assertSame('Minimum age 18', $requirements[0]['value']);
        $this->assertSame('Valid ID required', $requirements[1]['value']);
    }
}
