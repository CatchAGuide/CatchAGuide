<?php

namespace Tests\Unit\Location;

use App\Enums\GuideStatus;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Location\GeospatialSearchService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GeospatialSearchServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Reproduces /guidings/alloffers?place=Deltebre,+Spanien&placeLat=...&bounds_ne_lat=...
     * Google's locality viewport for a small town is only ~2km across, which used to be
     * used as a hard bounding-box filter and silently dropped nearby listings.
     */
    public function test_city_scope_ignores_tight_places_bounds_and_uses_radius(): void
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        // ~30km north of Deltebre — well outside the town's own Places viewport,
        // but should still surface within the 50km city-scope radius.
        $nearby = $this->createTour($user, [
            'country' => 'Spanien',
            'country_iso' => 'ES',
            'lat' => 40.99,
            'lng' => 0.7176,
        ]);

        $params = [
            'place' => 'Deltebre, Spanien',
            'placeLat' => '40.72123879999999',
            'placeLng' => '0.7176492',
            'city' => 'Deltebre',
            'country' => 'Spanien',
            'region' => 'Katalonien',
            'bounds_ne_lat' => '40.72879507717366',
            'bounds_ne_lng' => '0.7507595327871844',
            'bounds_sw_lat' => '40.71088703432743',
            'bounds_sw_lng' => '0.6807395495465434',
            'country_short' => 'ES',
            'place_types' => ['locality', 'political'],
        ];

        $service = $this->app->make(GeospatialSearchService::class);
        $result = $service->search($params);

        $this->assertSame('radius', $result['area_type']);
        $this->assertSame(GeospatialSearchService::SCOPE_CITY, $result['scope']);
        $this->assertContains($nearby->id, $result['ids']->all());
    }

    private function createTour(User $user, array $overrides = []): Guiding
    {
        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Geo Radius Tour '.uniqid(),
            'slug' => 'geo-radius-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ], $overrides))->save();

        return $guiding;
    }
}
