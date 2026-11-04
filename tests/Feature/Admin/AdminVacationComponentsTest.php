<?php

namespace Tests\Feature\Admin;

use App\Models\Employee;
use App\Models\Vacation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Regression coverage for PUT /admin/vacations/{id}:
 * - every save deleted and recreated all packages/guidings/..., so each got a new id and
 *   bookings (package_id, guiding_id) and translations lost their link;
 * - an empty "add" card failed the whole save (description and capacity are NOT NULL);
 * - keeping the stored location sent city/country/coordinates empty, and the NOT NULL
 *   country column failed the save.
 */
class AdminVacationComponentsTest extends TestCase
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

    public function test_saving_keeps_component_ids_adds_new_ones_and_removes_dropped_ones(): void
    {
        $vacation = $this->makeVacation();
        $marine = $vacation->packages()->create(['title' => 'Marine', 'description' => '<p>Marine</p>', 'capacity' => 4]);
        $luxus = $vacation->packages()->create(['title' => 'Luxus', 'description' => '<p>Luxus</p>', 'capacity' => 6]);
        $standard = $vacation->packages()->create(['title' => 'Standard', 'description' => '<p>Standard</p>', 'capacity' => 2]);
        $guiding = $vacation->guidings()->create(['title' => 'Guiding 8h', 'description' => '<p>Guiding</p>', 'capacity' => 2]);
        $extra = $vacation->extras()->create(['description' => 'Endreinigung', 'price' => 50, 'type' => 'overall']);

        $this->put(route('admin.vacations.update', $vacation->id), [
            ...$this->baseFields($vacation),
            'packages' => [
                0 => ['id' => $marine->id, 'title' => 'Marine renamed', 'description' => '<p>Marine</p>', 'capacity' => 4],
                // Luxus (index 1) was removed in the form.
                3 => ['id' => $standard->id, 'title' => 'Standard', 'description' => '<p>Standard</p>', 'capacity' => 2],
                4 => ['id' => '', 'title' => 'New package', 'description' => '<p>New</p>', 'capacity' => 3],
                5 => ['id' => '', 'title' => '', 'description' => '', 'capacity' => ''],
            ],
            'guidings' => [
                0 => ['id' => $guiding->id, 'title' => 'Guiding 8h', 'description' => '<p>Guiding</p>', 'capacity' => 2],
            ],
            'extras' => [
                0 => ['id' => $extra->id, 'description' => 'Endreinigung', 'price' => 60, 'price_type' => 'overall'],
            ],
        ])->assertRedirect(route('admin.vacations.index'));

        $packages = $vacation->fresh()->packages()->orderBy('id')->get();

        $this->assertSame(
            [$marine->id, $standard->id],
            $packages->take(2)->pluck('id')->all(),
            'Kept packages must keep their ids so bookings stay linked.'
        );
        $this->assertSame('Marine renamed', $packages->first()->title);
        $this->assertSame(['Marine renamed', 'Standard', 'New package'], $packages->pluck('title')->all());
        $this->assertNull($vacation->packages()->find($luxus->id));

        $this->assertSame([$guiding->id], $vacation->guidings()->pluck('id')->all());
        $this->assertSame([$extra->id], $vacation->extras()->pluck('id')->all());
        $this->assertEquals(60, $vacation->extras()->first()->price);
    }

    public function test_keeping_the_stored_location_does_not_fail_the_save(): void
    {
        $vacation = $this->makeVacation();

        $this->put(route('admin.vacations.update', $vacation->id), [
            ...$this->baseFields($vacation),
            'title' => 'Renamed vacation',
            'city' => '',
            'country' => '',
            'region' => '',
            'latitude' => '',
            'longitude' => '',
        ])->assertRedirect(route('admin.vacations.index'));

        $vacation->refresh();

        $this->assertSame('Renamed vacation', $vacation->title);
        $this->assertSame('Spanien', $vacation->country);
        $this->assertSame('41.2', (string) $vacation->latitude);
    }

    public function test_an_empty_gallery_entry_is_not_stored(): void
    {
        $vacation = $this->makeVacation();

        $this->put(route('admin.vacations.update', $vacation->id), [
            ...$this->baseFields($vacation),
            'existing_gallery' => json_encode(['', 'vacations/1/photo.webp']),
        ])->assertRedirect(route('admin.vacations.index'));

        // Read the way VacationsController@show does: the array cast hands back the JSON string.
        $gallery = $vacation->fresh()->gallery;
        $gallery = is_string($gallery) ? json_decode($gallery, true) : $gallery;

        $this->assertSame(['vacations/1/photo.webp'], $gallery);
    }

    private function makeVacation(): Vacation
    {
        return Vacation::query()->create([
            'title' => 'Test vacation',
            'slug' => 'test-vacation-'.uniqid(),
            'location' => 'Massaluca',
            'city' => 'La Pobla de Massaluca',
            'country' => 'Spanien',
            'region' => 'Katalonien',
            'latitude' => '41.2',
            'longitude' => '0.35',
            'best_travel_times' => 'Frühling',
            'surroundings_description' => '<p>Ebro</p>',
            'target_fish' => json_encode([]),
            'status' => 1,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function baseFields(Vacation $vacation): array
    {
        return [
            'title' => $vacation->title,
            'slug' => $vacation->slug,
            'location' => $vacation->location,
            'best_travel_times' => 'Frühling',
            'surroundings_description' => '<p>Ebro</p>',
        ];
    }
}
