<?php

namespace Tests\Unit\Offers;

use App\Domain\Offers\OfferListingFilter;
use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\SpecialOffer;
use App\Models\User;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ReflectionMethod;
use Tests\TestCase;

class OfferCatalogCampCountryResultsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_spain_camp_search_uses_nested_special_offer_capacity(): void
    {
        $withRooms = $this->makeCamp([
            'title' => 'Spain Lodge '.uniqid(),
            'country' => 'spanien',
        ]);
        $this->attachCampAccommodation($withRooms, 6);

        $offerOnly = $this->makeCamp([
            'title' => 'Arcos Offer Camp '.uniqid(),
            'country' => 'spanien',
            'location' => 'Córdoba, Spanien',
            'city' => 'Córdoba',
            'region' => 'Andalusien',
            'latitude' => 37.88930250,
            'longitude' => -4.77927530,
        ]);
        $this->attachOfferAccommodation($offerOnly, 8);

        $tooSmallDirect = $this->makeCamp([
            'title' => 'Tiny Cabin Camp '.uniqid(),
            'country' => 'spanien',
        ]);
        $this->attachCampAccommodation($tooSmallDirect, 2);

        $tooSmallOffer = $this->makeCamp([
            'title' => 'Tiny Offer Camp '.uniqid(),
            'country' => 'spanien',
        ]);
        $this->attachOfferAccommodation($tooSmallOffer, 2);

        $idsForOneGuest = $this->queryCampIds($this->spainCampSearch(1));

        $this->assertContains($withRooms->id, $idsForOneGuest);
        $this->assertContains($offerOnly->id, $idsForOneGuest);
        $this->assertContains($tooSmallDirect->id, $idsForOneGuest);
        $this->assertContains($tooSmallOffer->id, $idsForOneGuest);

        $idsForFourGuests = $this->queryCampIds($this->spainCampSearch(4));

        $this->assertContains($withRooms->id, $idsForFourGuests);
        $this->assertContains($offerOnly->id, $idsForFourGuests);
        $this->assertNotContains($tooSmallDirect->id, $idsForFourGuests);
        $this->assertNotContains($tooSmallOffer->id, $idsForFourGuests);
    }

    /**
     * @return array<string, mixed>
     */
    private function spainCampSearch(int $guests): array
    {
        return [
            'type' => 'vacation',
            'vacation' => 'camp',
            'country' => 'spain',
            'place' => 'Spain',
            'country_short' => 'ES',
            'num_guests' => $guests,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return list<int>
     */
    private function queryCampIds(array $input): array
    {
        $filter = OfferListingFilter::fromRequest($input);
        $service = $this->app->make(OfferCatalogPageService::class);
        $method = new ReflectionMethod(OfferCatalogPageService::class, 'queryCamps');
        $method->setAccessible(true);

        return $method->invoke($service, $filter, $filter->toVacationFilter())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function makeCamp(array $overrides = []): Camp
    {
        $user = User::factory()->create();

        $camp = new Camp();
        $camp->forceFill(array_merge([
            'title' => 'Test Camp '.uniqid(),
            'slug' => 'test-camp-'.uniqid(),
            'description_camp' => 'Camp desc',
            'description_area' => 'Area desc',
            'description_fishing' => 'Fishing desc',
            'location' => 'Mequinenza, Spanien',
            'city' => 'Mequinenza',
            'region' => 'Aragonien',
            'country' => 'spanien',
            'status' => 'active',
            'user_id' => $user->id,
        ], $overrides))->save();

        return $camp;
    }

    private function attachCampAccommodation(Camp $camp, int $maxOccupancy): void
    {
        $camp->accommodations()->attach($this->makeAccommodation($camp, $maxOccupancy)->id);
    }

    private function attachOfferAccommodation(Camp $camp, int $maxOccupancy): void
    {
        $offer = SpecialOffer::query()->create([
            'title' => 'Test Package '.uniqid(),
            'slug' => 'test-package-'.uniqid(),
            'location' => $camp->location,
            'country' => $camp->country,
            'city' => $camp->city,
            'region' => $camp->region,
            'status' => 'active',
            'user_id' => $camp->user_id,
        ]);

        $offer->accommodations()->attach($this->makeAccommodation($camp, $maxOccupancy)->id);
        $camp->specialOffers()->attach($offer->id);
    }

    private function makeAccommodation(Camp $camp, int $maxOccupancy): Accommodation
    {
        return Accommodation::query()->create([
            'status' => 'active',
            'user_id' => $camp->user_id,
            'title' => 'Test Cabin '.uniqid(),
            'slug' => 'test-cabin-'.uniqid(),
            'location' => $camp->location,
            'city' => $camp->city ?: 'Test City',
            'country' => $camp->country,
            'region' => $camp->region ?: 'Test Region',
            'accommodation_type' => 'cabin',
            'max_occupancy' => $maxOccupancy,
        ]);
    }
}
