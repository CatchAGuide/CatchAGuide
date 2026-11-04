<?php

namespace Tests\Feature\Admin;

use App\Models\Camp;
use App\Models\CampFacility;
use App\Models\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Regression coverage for PUT /admin/camps/{camp}: the facility picker lists localized names
 * (name_de on catchaguide.de), but the save looked them up in the English `name` column only.
 * Every facility except "Sauna" (spelled alike in both) was dropped without an error.
 */
class AdminCampFacilitiesSyncTest extends TestCase
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

    public function test_german_facility_names_are_saved(): void
    {
        $german = CampFacility::query()->create([
            'name' => 'Test smoking oven',
            'name_de' => 'Test Räucherofen',
            'name_en' => 'Test smoking oven',
            'is_active' => true,
        ]);
        $english = CampFacility::query()->create([
            'name' => 'Test fish freezer',
            'name_de' => 'Test Fisch-Gefrierschrank',
            'name_en' => 'Test fish freezer',
            'is_active' => true,
        ]);

        $camp = Camp::query()->create([
            'title' => 'Facilities Test Camp',
            'slug' => 'facilities-test-camp-'.uniqid(),
            'description_camp' => 'Camp description',
            'description_area' => 'Area description',
            'description_fishing' => 'Fishing description',
            'location' => 'Test Location',
            'status' => 'active',
        ]);

        $this->withHeaders(['Accept' => 'application/json'])
            ->put(route('admin.camps.update', $camp), [
                'is_update' => '1',
                'is_draft' => '0',
                'title' => 'Facilities Test Camp',
                'camp_facilities' => 'Test Räucherofen,Test fish freezer',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEqualsCanonicalizing(
            [$german->id, $english->id],
            $camp->fresh()->facilities()->pluck('camp_facilities.id')->all()
        );
    }
}
