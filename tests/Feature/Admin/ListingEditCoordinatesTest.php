<?php

namespace Tests\Feature\Admin;

use App\Models\Accommodation;
use App\Models\Employee;
use App\Models\RentalBoat;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Regression coverage: the accommodation and rental boat edit forms read
 * $formData['lat'] / ['lng'], but the edit data only has latitude/longitude.
 * The hidden coordinate inputs rendered empty, so every save wiped the
 * listing's lat/lng unless the location was picked again.
 */
class ListingEditCoordinatesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');

        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');
    }

    public function test_accommodation_edit_form_renders_stored_coordinates(): void
    {
        $accommodation = Accommodation::query()->firstOrFail();
        $accommodation->forceFill(['lat' => 63.9211985, 'lng' => 16.3396832])->save();

        $this->get(route('admin.accommodations.edit', $accommodation))
            ->assertOk()
            ->assertSee('name="latitude" id="latitude" value="63.92119850"', false)
            ->assertSee('name="longitude" id="longitude" value="16.33968320"', false);
    }

    public function test_rental_boat_edit_form_renders_stored_coordinates(): void
    {
        $rentalBoat = RentalBoat::query()->firstOrFail();
        $rentalBoat->forceFill(['lat' => 59.3293235, 'lng' => 18.0685808])->save();

        $html = $this->get(route('admin.rental-boats.edit', $rentalBoat))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/name="latitude" id="latitude" value="59\.3293235/', $html);
        $this->assertMatchesRegularExpression('/name="longitude" id="longitude" value="18\.0685808/', $html);
    }
}
