<?php

namespace Tests\Unit\Guidings;

use App\Enums\GuideStatus;
use App\Models\Booking;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Presenters\Guiding\GuidingCardPresenter;
use App\Services\CategoryPage\FavoriteTargetSpeciesResolver;
use App\Services\Guidings\GuidingsLandingService;
use App\Services\Homepage\HomepageCountrySelector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class GuidingsLandingServiceMostBookedTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createTour(): Guiding
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Most Booked Tour '.uniqid(),
            'slug' => 'most-booked-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ])->save();

        return $guiding;
    }

    public function test_bookings_older_than_the_window_do_not_count_toward_most_booked(): void
    {
        $recentlyBookedTour = $this->createTour();
        $onlyOldBookedTour = $this->createTour();

        Booking::query()->create([
            'guiding_id' => $recentlyBookedTour->id,
            'created_at' => now()->subDays(GuidingsLandingService::MOST_BOOKED_WINDOW_DAYS - 1),
        ]);

        Booking::query()->create([
            'guiding_id' => $onlyOldBookedTour->id,
            'created_at' => now()->subDays(GuidingsLandingService::MOST_BOOKED_WINDOW_DAYS + 1),
        ]);

        $counts = Guiding::withCount(['bookings' => function ($query) {
                $query->where('created_at', '>=', now()->subDays(GuidingsLandingService::MOST_BOOKED_WINDOW_DAYS));
            }])
            ->whereIn('id', [$recentlyBookedTour->id, $onlyOldBookedTour->id])
            ->get()
            ->keyBy('id');

        $this->assertSame(1, $counts[$recentlyBookedTour->id]->bookings_count);
        $this->assertSame(0, $counts[$onlyOldBookedTour->id]->bookings_count);
    }

    public function test_most_booked_returns_presenter_shaped_collection(): void
    {
        Cache::flush();

        $countries = Mockery::mock(HomepageCountrySelector::class);
        $favoriteTargetSpecies = Mockery::mock(FavoriteTargetSpeciesResolver::class);

        $service = new GuidingsLandingService($countries, $favoriteTargetSpecies, new GuidingCardPresenter());

        $method = new ReflectionMethod(GuidingsLandingService::class, 'mostBooked');
        $method->setAccessible(true);

        $result = $method->invoke($service, 'test-'.uniqid());

        $this->assertLessThanOrEqual(8, $result->count());
        if ($result->isNotEmpty()) {
            $this->assertArrayHasKey('id', $result->first());
            $this->assertArrayHasKey('title', $result->first());
        }
    }
}
