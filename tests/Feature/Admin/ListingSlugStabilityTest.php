<?php

namespace Tests\Feature\Admin;

use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\Employee;
use App\Models\RentalBoat;
use App\Models\SpecialOffer;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Regression coverage: PUT /admin/{trips,camps,accommodations,rental-boats,special-offers}/{id}
 * used to regenerate the `slug` from the title whenever the title text changed at all
 * (even a typo fix), silently changing the listing's public URL with no redirect.
 * Trip and Camp slugs are the canonical public URL (/trips/{slug},
 * /vacations/camps/{slug}), so this broke backlinks/SEO on every edit. The slug must
 * now stay fixed after creation and only be backfilled if missing.
 */
class ListingSlugStabilityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    private function actingAsEmployee(): void
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');
    }

    private function ownerId(): int
    {
        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No user available to own a test listing.');
        }

        return $user->id;
    }

    public function test_updating_a_trip_title_does_not_change_its_slug(): void
    {
        $this->actingAsEmployee();

        $trip = Trip::query()->create([
            'title' => 'Original Trip Title',
            'slug' => 'original-trip-slug-' . uniqid(),
            'location' => 'Test Location',
            'status' => 'active',
            'user_id' => $this->ownerId(),
        ]);
        $originalSlug = $trip->slug;

        $response = $this->putJson(route('admin.trips.update', $trip), [
            'title' => 'A Completely Different Trip Title',
            'location' => $trip->location,
            'is_draft' => '0',
        ]);

        $response->assertOk();
        $trip->refresh();

        $this->assertSame(
            $originalSlug,
            $trip->slug,
            'Editing a trip title must never change its slug/public URL.'
        );
    }

    public function test_updating_a_camp_title_does_not_change_its_slug(): void
    {
        $this->actingAsEmployee();

        $camp = Camp::query()->create([
            'title' => 'Original Camp Title',
            'slug' => 'original-camp-slug-' . uniqid(),
            'description_camp' => 'Camp description',
            'description_area' => 'Area description',
            'description_fishing' => 'Fishing description',
            'location' => 'Test Location',
            'status' => 'active',
            'user_id' => $this->ownerId(),
        ]);
        $originalSlug = $camp->slug;

        $response = $this->putJson(route('admin.camps.update', $camp), [
            'title' => 'A Completely Different Camp Title',
            'location' => $camp->location,
            'is_draft' => '0',
        ]);

        $response->assertOk();
        $camp->refresh();

        $this->assertSame(
            $originalSlug,
            $camp->slug,
            'Editing a camp title must never change its slug/public URL.'
        );
    }

    public function test_updating_an_accommodation_title_does_not_change_its_slug(): void
    {
        $this->actingAsEmployee();

        $accommodation = Accommodation::query()->create([
            'status' => 'active',
            'user_id' => $this->ownerId(),
            'title' => 'Original Accommodation Title',
            'slug' => 'original-accommodation-slug-' . uniqid(),
            'location' => 'Test Location',
            'city' => 'Test City',
            'country' => 'Test Country',
            'region' => 'Test Region',
            'accommodation_type' => 'cabin',
        ]);
        $originalSlug = $accommodation->slug;

        $response = $this->putJson(route('admin.accommodations.update', $accommodation), [
            'title' => 'A Completely Different Accommodation Title',
            'location' => $accommodation->location,
            'is_draft' => '0',
        ]);

        $response->assertOk();
        $accommodation->refresh();

        $this->assertSame($originalSlug, $accommodation->slug);
    }

    public function test_updating_a_rental_boat_title_does_not_change_its_slug(): void
    {
        $this->actingAsEmployee();

        $rentalBoat = RentalBoat::query()->create([
            'status' => 'active',
            'user_id' => $this->ownerId(),
            'title' => 'Original Rental Boat Title',
            'slug' => 'original-rental-boat-slug-' . uniqid(),
            'location' => 'Test Location',
            'city' => 'Test City',
            'country' => 'Test Country',
            'boat_type' => 'skiff',
            'desc_of_boat' => 'A boat',
            'price_type' => 'per_day',
            'prices' => [],
        ]);
        $originalSlug = $rentalBoat->slug;

        $response = $this->putJson(route('admin.rental-boats.update', $rentalBoat), [
            'title' => 'A Completely Different Rental Boat Title',
            'location' => $rentalBoat->location,
            'city' => $rentalBoat->city,
            'country' => $rentalBoat->country,
            'boat_type' => $rentalBoat->boat_type,
            'desc_of_boat' => $rentalBoat->desc_of_boat,
            'price_type' => $rentalBoat->price_type,
            'is_draft' => '0',
        ]);

        $response->assertOk();
        $rentalBoat->refresh();

        $this->assertSame($originalSlug, $rentalBoat->slug);
    }

    public function test_updating_a_special_offer_title_does_not_change_its_slug(): void
    {
        $this->actingAsEmployee();

        $specialOffer = SpecialOffer::query()->create([
            'title' => 'Original Special Offer Title',
            'slug' => 'original-special-offer-slug-' . uniqid(),
            'location' => 'Test Location',
            'status' => 'active',
            'user_id' => $this->ownerId(),
        ]);
        $originalSlug = $specialOffer->slug;

        $response = $this->putJson(route('admin.special-offers.update', $specialOffer), [
            'title' => 'A Completely Different Special Offer Title',
            'location' => $specialOffer->location,
            'is_draft' => '0',
        ]);

        $response->assertOk();
        $specialOffer->refresh();

        $this->assertSame($originalSlug, $specialOffer->slug);
    }
}
