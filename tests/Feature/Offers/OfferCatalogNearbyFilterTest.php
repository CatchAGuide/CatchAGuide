<?php

namespace Tests\Feature\Offers;

use App\Enums\GuideStatus;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class OfferCatalogNearbyFilterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_nearby_section_does_not_repeat_tours_that_fail_the_guest_filter(): void
    {
        // Isolated from real European inventory so the 50km / 200km radii only see these rows.
        $lat = -70.25;
        $lng = 10.25;

        $fitsThree = $this->createTour('Fits Three', $lat, $lng, 3);
        $onlyTwo = $this->createTour('Only Two', $lat + 0.01, $lng, 2);
        $otherTown = $this->createTour('Other Town', $lat + 0.04, $lng, 3);

        $request = Request::create('/guidings/alloffers', 'GET', [
            'type' => 'tour',
            'place' => 'Matchtown',
            'city' => 'Matchtown',
            'placeLat' => (string) $lat,
            'placeLng' => (string) $lng,
            'place_types' => ['locality'],
            'num_guests' => 3,
        ]);

        $vm = app(OfferCatalogPageService::class)->buildForTours($request);

        $mainIds = $vm->cards->pluck('id')->map(fn ($id) => (int) $id)->all();
        $suggestedIds = $vm->suggestedCards->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertContains($fitsThree->id, $mainIds);
        $this->assertContains($otherTown->id, $mainIds);
        $this->assertNotContains($onlyTwo->id, $mainIds);
        $this->assertNotContains($onlyTwo->id, $suggestedIds);
    }

    private function createTour(string $title, float $lat, float $lng, int $maxGuests): Guiding
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => $title.' '.uniqid(),
            'slug' => 'nearby-filter-'.uniqid(),
            'location' => 'Matchtown',
            'city' => 'Matchtown',
            'status' => 1,
            'max_guests' => $maxGuests,
            'duration' => 4,
            'price' => 300,
            'lat' => $lat,
            'lng' => $lng,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ])->save();

        return $guiding;
    }
}
