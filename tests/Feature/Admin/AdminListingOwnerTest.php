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

    public function test_owner_cell_links_providers_to_the_customer_edit_page(): void
    {
        $provider = User::factory()->create([
            'firstname' => 'Harbour',
            'lastname' => 'Host',
            'is_provider' => true,
            'is_guide' => null,
            'guide_status' => null,
        ]);

        $html = view('components.admin.owner-cell', [
            'user' => $provider->load('information'),
            'employee' => null,
            'listing' => null,
        ])->render();

        $this->assertStringContainsString('Harbour Host', $html);
        $this->assertStringContainsString(route('admin.customers.edit', $provider), $html);
        $this->assertStringContainsString(__('admin.customers.role_provider'), $html);
        $this->assertStringNotContainsString(route('admin.guides.edit', $provider), $html);
    }

    public function test_customer_search_returns_providers_for_listing_owner_picker(): void
    {
        $employee = Employee::factory()->create();
        $this->actingAs($employee, 'employees');

        $provider = User::factory()->create([
            'firstname' => 'Searchable',
            'lastname' => 'Provider'.Str::random(5),
            'is_provider' => true,
            'is_guide' => null,
            'guide_status' => null,
        ]);

        $response = $this->getJson(route('admin.customers.search', ['q' => 'Searchable']));

        $response->assertOk();
        $ids = collect($response->json('results'))->pluck('id')->all();
        $this->assertContains($provider->id, $ids);
        $match = collect($response->json('results'))->firstWhere('id', $provider->id);
        $this->assertSame('provider', $match['role']);
    }

    public function test_trips_index_shows_the_owner_column(): void
    {
        $employee = Employee::factory()->create();
        $provider = User::factory()->create([
            'firstname' => 'Trip',
            'lastname' => 'Owner'.Str::random(5),
            'is_provider' => true,
            'is_guide' => null,
            'guide_status' => null,
        ]);
        $this->actingAs($employee, 'employees');

        $trip = new Trip;
        $trip->forceFill([
            'title' => 'Owner column trip '.Str::random(6),
            'slug' => 'owner-trip-'.Str::random(8),
            'location' => 'Hitra',
            'status' => 'active',
            'user_id' => $provider->id,
            'employee_id' => $employee->id,
        ]);
        $trip->save();

        $this->get(route('admin.trips.index'))
            ->assertOk()
            ->assertSee(__('admin.listing_owner.column'))
            ->assertSee($provider->firstname)
            ->assertSee('admin/customers/'.$provider->id.'/edit', false);
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
