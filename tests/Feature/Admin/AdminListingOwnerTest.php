<?php

namespace Tests\Feature\Admin;

use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\Employee;
use App\Models\RentalBoat;
use App\Models\SpecialOffer;
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

    public function test_camp_update_saves_the_chosen_provider(): void
    {
        [$employee, $provider] = $this->staffAndProvider();
        $camp = Camp::query()->create([
            'title' => 'Owner camp '.Str::random(6),
            'slug' => 'owner-camp-'.Str::random(8),
            'description_camp' => 'Camp',
            'description_area' => 'Area',
            'description_fishing' => 'Fishing',
            'location' => 'Kiel',
            'status' => 'active',
            'user_id' => null,
            'employee_id' => $employee->id,
        ]);

        $this->putJson(route('admin.camps.update', $camp), [
            'title' => $camp->title,
            'location' => $camp->location,
            'user_id' => $provider->id,
            'listing_owner_assigned' => '1',
            'is_draft' => '0',
        ])->assertOk();

        $this->assertSame($provider->id, (int) $camp->refresh()->user_id);
        $this->assertSame($employee->id, (int) $camp->employee_id);
    }

    public function test_trip_update_saves_the_chosen_provider(): void
    {
        [$employee, $provider] = $this->staffAndProvider();
        $trip = new Trip;
        $trip->forceFill([
            'title' => 'Owner trip '.Str::random(6),
            'slug' => 'owner-trip-'.Str::random(8),
            'location' => 'Hitra',
            'status' => 'active',
            'user_id' => null,
            'employee_id' => $employee->id,
        ]);
        $trip->save();

        $this->putJson(route('admin.trips.update', $trip), [
            'title' => $trip->title,
            'location' => $trip->location,
            'user_id' => $provider->id,
            'listing_owner_assigned' => '1',
            'is_draft' => '0',
        ])->assertOk();

        $this->assertSame($provider->id, (int) $trip->refresh()->user_id);
    }

    public function test_accommodation_update_saves_the_chosen_provider(): void
    {
        [$employee, $provider] = $this->staffAndProvider();
        $accommodation = Accommodation::query()->create([
            'status' => 'active',
            'user_id' => null,
            'employee_id' => $employee->id,
            'title' => 'Owner stay '.Str::random(6),
            'slug' => 'owner-stay-'.Str::random(8),
            'location' => 'Kiel',
            'city' => 'Kiel',
            'country' => 'Germany',
            'region' => 'Schleswig-Holstein',
            'accommodation_type' => 'cabin',
        ]);

        $this->putJson(route('admin.accommodations.update', $accommodation), [
            'title' => $accommodation->title,
            'location' => $accommodation->location,
            'user_id' => $provider->id,
            'listing_owner_assigned' => '1',
            'is_draft' => '0',
        ])->assertOk();

        $this->assertSame($provider->id, (int) $accommodation->refresh()->user_id);
    }

    public function test_rental_boat_update_saves_the_chosen_provider(): void
    {
        [$employee, $provider] = $this->staffAndProvider();
        $boat = RentalBoat::query()->create([
            'status' => 'active',
            'user_id' => null,
            'employee_id' => $employee->id,
            'title' => 'Owner boat '.Str::random(6),
            'slug' => 'owner-boat-'.Str::random(8),
            'location' => 'Kiel',
            'city' => 'Kiel',
            'country' => 'Germany',
            'boat_type' => 'skiff',
            'desc_of_boat' => 'A boat',
            'price_type' => 'per_day',
            'prices' => [],
        ]);

        $this->putJson(route('admin.rental-boats.update', $boat), [
            'title' => $boat->title,
            'location' => $boat->location,
            'city' => $boat->city,
            'country' => $boat->country,
            'boat_type' => $boat->boat_type,
            'desc_of_boat' => $boat->desc_of_boat,
            'price_type' => $boat->price_type,
            'user_id' => $provider->id,
            'listing_owner_assigned' => '1',
            'is_draft' => '0',
        ])->assertOk();

        $this->assertSame($provider->id, (int) $boat->refresh()->user_id);
    }

    public function test_special_offer_update_saves_the_chosen_provider(): void
    {
        [$employee, $provider] = $this->staffAndProvider();
        $offer = SpecialOffer::query()->create([
            'title' => 'Owner offer '.Str::random(6),
            'slug' => 'owner-offer-'.Str::random(8),
            'location' => 'Kiel',
            'status' => 'active',
            'user_id' => null,
            'employee_id' => $employee->id,
        ]);

        $this->putJson(route('admin.special-offers.update', $offer), [
            'title' => $offer->title,
            'location' => $offer->location,
            'user_id' => $provider->id,
            'listing_owner_assigned' => '1',
            'is_draft' => '0',
        ])->assertOk();

        $this->assertSame($provider->id, (int) $offer->refresh()->user_id);
    }

    public function test_camp_update_saves_a_provider_whose_id_matches_an_employee(): void
    {
        $employee = Employee::factory()->create();
        $provider = User::factory()->create([
            'is_provider' => true,
            'is_guide' => null,
            'guide_status' => null,
        ]);
        $shadow = Employee::factory()->make([
            'email' => 'shadow-camp-'.$provider->id.'-'.Str::random(4).'@example.test',
        ]);
        $shadow->id = $provider->id;
        $shadow->save();
        $this->actingAs($employee, 'employees');

        $camp = Camp::query()->create([
            'title' => 'Collision camp '.Str::random(6),
            'slug' => 'collision-camp-'.Str::random(8),
            'description_camp' => 'Camp',
            'description_area' => 'Area',
            'description_fishing' => 'Fishing',
            'location' => 'Kiel',
            'status' => 'active',
            'user_id' => null,
            'employee_id' => $employee->id,
        ]);

        $this->putJson(route('admin.camps.update', $camp), [
            'title' => $camp->title,
            'location' => $camp->location,
            'user_id' => $provider->id,
            'listing_owner_assigned' => '1',
            'is_draft' => '0',
        ])->assertOk();

        $this->assertSame($provider->id, (int) $camp->refresh()->user_id);
    }

    /**
     * @return array{0: Employee, 1: User}
     */
    private function staffAndProvider(): array
    {
        $employee = Employee::factory()->create();
        $provider = User::factory()->create([
            'firstname' => 'Saved',
            'lastname' => 'Provider'.Str::random(4),
            'is_provider' => true,
            'is_guide' => null,
            'guide_status' => null,
        ]);
        $this->actingAs($employee, 'employees');

        return [$employee, $provider];
    }
}
