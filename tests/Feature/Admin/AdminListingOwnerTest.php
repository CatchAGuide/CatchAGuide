<?php

namespace Tests\Feature\Admin;

use App\Models\Employee;
use App\Models\RentalBoat;
use App\Models\Trip;
use App\Models\User;
use App\Services\Sales\SalesCatalog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminListingOwnerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    public function test_creating_a_rental_boat_as_staff_does_not_attach_it_to_a_guide(): void
    {
        $employee = Employee::factory()->create([
            'name' => 'Staff Owner '.Str::random(8),
        ]);
        $guide = User::query()->find($employee->id);
        $this->actingAs($employee, 'employees');

        $title = 'Staff rental boat '.Str::random(8);
        $response = $this->postJson(route('admin.rental-boats.store'), [
            'title' => $title,
            'location' => 'Kiel',
            'city' => 'Kiel',
            'country' => 'Germany',
            'boat_type' => 'motorboat',
            'desc_of_boat' => 'Created by staff',
            'user_id' => $employee->id,
        ]);

        $response->assertOk();
        $boat = RentalBoat::query()->findOrFail($response->json('rental_boat_id'));

        $this->assertNull($boat->user_id);
        $this->assertSame($employee->id, (int) $boat->employee_id);
        $this->assertFalse(
            RentalBoat::query()->whereKey($boat->id)->where('user_id', $employee->id)->exists()
        );
        if ($guide) {
            $this->assertNotSame($guide->id, $boat->user_id);
        }

        $index = $this->get(route('admin.rental-boats.index'));
        $index->assertOk();
        $index->assertSee($title, false);
        $index->assertSee($employee->name, false);
        $index->assertSee(__('admin.listing_owner.admin'), false);
        $index->assertDontSee('href="'.route('admin.guides.edit', $employee->id).'"', false);
    }

    public function test_owner_cell_shows_staff_without_a_guide_profile_link(): void
    {
        $employee = Employee::factory()->create(['name' => 'Finn Staff']);

        $html = view('components.admin.owner-cell', [
            'user' => null,
            'employee' => $employee,
            'listing' => null,
        ])->render();

        $this->assertStringContainsString('Finn Staff', $html);
        $this->assertStringContainsString(__('admin.listing_owner.admin'), $html);
        $this->assertStringNotContainsString(route('admin.guides.edit', $employee->id), $html);
    }

    public function test_trip_provider_name_is_not_replaced_by_a_guide_contact(): void
    {
        $trip = new Trip;
        $trip->forceFill([
            'title' => 'Provider trip '.Str::random(6),
            'slug' => 'provider-trip-'.Str::random(8),
            'location' => 'Hitra',
            'status' => 'active',
            'provider_name' => 'Harbour Host',
            'user_id' => null,
        ]);
        $trip->save();

        $product = app(SalesCatalog::class)->trip($trip->id, 'de');

        $this->assertSame('Harbour Host', $product['partner']['name']);
        $this->assertSame('', $product['partner']['email']);
        $this->assertNull($product['partner']['id']);
    }
}
