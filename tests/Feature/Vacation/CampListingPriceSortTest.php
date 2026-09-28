<?php

namespace Tests\Feature\Vacation;

use App\Domain\Vacation\VacationListingFilter;
use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\User;
use App\Repositories\Vacation\CampListingRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CampListingPriceSortTest extends TestCase
{
    use DatabaseTransactions;

    private string $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = 'sorttestland'.uniqid();
    }

    public function test_price_sorts_order_camps_by_their_lowest_price_with_unpriced_last(): void
    {
        $mid = $this->createCamp(120);
        $cheap = $this->createCamp(45);
        $unpriced = $this->createCamp(null);
        $expensive = $this->createCamp(300);

        $asc = $this->paginate('price-asc', 10);
        $this->assertSame([$cheap->id, $mid->id, $expensive->id, $unpriced->id], $this->ids($asc));

        $desc = $this->paginate('price-desc', 10);
        $this->assertSame([$expensive->id, $mid->id, $cheap->id, $unpriced->id], $this->ids($desc));
    }

    public function test_price_sort_paginates_across_the_whole_sorted_set(): void
    {
        $third = $this->createCamp(90);
        $first = $this->createCamp(30);
        $second = $this->createCamp(60);

        request()->merge(['page' => 2]);
        $page2 = $this->paginate('price-asc', 2);

        $this->assertSame(3, $page2->total());
        $this->assertSame([$third->id], $this->ids($page2));
        $this->assertTrue($page2->items()[0]->relationLoaded('accommodations'));

        request()->merge(['page' => 1]);
        $this->assertSame([$first->id, $second->id], $this->ids($this->paginate('price-asc', 2)));
    }

    private function paginate(string $sort, int $perPage)
    {
        $filter = VacationListingFilter::fromRequest(['pillar' => 'camps', 'sortby' => $sort], $this->country);

        return app(CampListingRepository::class)->paginateForCountry($filter, $perPage);
    }

    private function ids($paginator): array
    {
        return collect($paginator->items())->map(fn (Camp $camp) => (int) $camp->id)->all();
    }

    private function createCamp(?float $pricePerNight): Camp
    {
        $user = User::factory()->create();

        $camp = new Camp();
        $camp->forceFill([
            'title' => 'Sort Camp '.uniqid(),
            'description_camp' => 'Camp desc',
            'description_area' => 'Area desc',
            'description_fishing' => 'Fishing desc',
            'location' => 'Somewhere',
            'country' => $this->country,
            'status' => 'active',
            'user_id' => $user->id,
        ])->save();

        if ($pricePerNight !== null) {
            $accommodation = new Accommodation();
            $accommodation->forceFill([
                'user_id' => $user->id,
                'title' => 'Cabin '.uniqid(),
                'slug' => 'cabin-'.uniqid(),
                'location' => 'Somewhere',
                'city' => 'Somewhere',
                'country' => $this->country,
                'region' => 'Somewhere',
                'accommodation_type' => 'cabin',
                'status' => 'active',
                'per_person_pricing' => json_encode([['person' => 1, 'price_per_night' => $pricePerNight]]),
            ])->save();
            $camp->accommodations()->attach($accommodation->id);
        }

        return $camp;
    }
}
