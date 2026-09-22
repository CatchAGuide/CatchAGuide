<?php

namespace Tests\Unit\Guiding;

use App\Enums\GuideStatus;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Guiding\SimilarGuidingsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SimilarGuidingsServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::put('translate_circuit:open_until', now()->addHour()->timestamp, 3600);
    }

    public function test_prefers_nearby_tours_and_excludes_far_away_fish_matches(): void
    {
        $source = $this->createTour([
            'title' => 'SOURCE_RHINE_TOUR',
            'city' => 'Ludwigshafen',
            'location' => 'Wrong Street Label 99',
            'region' => 'Rhineland-Palatinate',
            'country' => 'Germany',
            'lat' => 49.477,
            'lng' => 8.445,
            'target_fish' => json_encode([11, 22]),
        ]);

        $nearby = $this->createTour([
            'title' => 'NEARBY_MAINZ_TOUR',
            'city' => 'Mainz',
            'location' => 'Mainz',
            'region' => 'Rhineland-Palatinate',
            'country' => 'Germany',
            'lat' => 49.992,
            'lng' => 8.247,
            'target_fish' => json_encode([11]),
        ]);

        $farAway = $this->createTour([
            'title' => 'FAR_MADRID_TOUR',
            'city' => 'Madrid',
            'location' => 'Madrid',
            'region' => 'Madrid',
            'country' => 'Spain',
            'lat' => 40.4168,
            'lng' => -3.7038,
            'target_fish' => json_encode([11, 22]),
        ]);

        $result = app(SimilarGuidingsService::class)->queryFor(
            $source,
            SimilarGuidingsService::CANDIDATE_LIMIT
        );

        $ids = $result->pluck('id')->all();

        $this->assertContains($nearby->id, $ids);
        $this->assertNotContains($farAway->id, $ids);
        $this->assertNotContains($source->id, $ids);
    }

    public function test_same_guide_tours_are_excluded_from_similar_results(): void
    {
        $source = $this->createTour([
            'title' => 'SOURCE_SAME_GUIDE',
            'city' => 'Ludwigshafen',
            'country' => 'Germany',
            'lat' => 49.477,
            'lng' => 8.445,
        ]);

        $sameGuide = $this->createTour([
            'title' => 'SAME_GUIDE_OTHER_TOUR',
            'city' => 'Ludwigshafen',
            'country' => 'Germany',
            'lat' => 49.48,
            'lng' => 8.45,
            'user_id' => $source->user_id,
        ]);

        $otherGuide = $this->createTour([
            'title' => 'OTHER_GUIDE_NEARBY',
            'city' => 'Ludwigshafen',
            'country' => 'Germany',
            'lat' => 49.49,
            'lng' => 8.44,
        ]);

        $ids = app(SimilarGuidingsService::class)->queryFor($source)->pluck('id')->all();

        $this->assertContains($otherGuide->id, $ids);
        $this->assertNotContains($sameGuide->id, $ids);
    }

    public function test_falls_back_to_same_city_when_coordinates_are_missing(): void
    {
        $source = $this->createTour([
            'title' => 'SOURCE_NO_COORDS',
            'city' => 'Ludwigshafen',
            'country' => 'Germany',
            'location' => 'Ludwigshafen',
        ]);

        $sameCity = $this->createTour([
            'title' => 'SAME_CITY_NO_COORDS',
            'city' => 'Ludwigshafen',
            'country' => 'Germany',
            'location' => 'Ludwigshafen am Rhein',
        ]);

        $otherCountry = $this->createTour([
            'title' => 'OTHER_COUNTRY_NO_COORDS',
            'city' => 'Madrid',
            'country' => 'Spain',
            'location' => 'Madrid',
        ]);

        $ids = app(SimilarGuidingsService::class)->queryFor($source)->pluck('id')->all();

        $this->assertContains($sameCity->id, $ids);
        $this->assertNotContains($otherCountry->id, $ids);
    }

    public function test_catalog_url_keeps_the_source_location(): void
    {
        $source = $this->createTour([
            'city' => 'Ludwigshafen',
            'country' => 'Germany',
            'lat' => 49.477,
            'lng' => 8.445,
        ]);

        $url = app(SimilarGuidingsService::class)->catalogUrl($source);

        $this->assertStringContainsString('city=Ludwigshafen', $url);
        $this->assertStringContainsString('country=Germany', $url);
        $this->assertStringContainsString('placeLat=49.477', $url);
    }

    public function test_product_page_payload_uses_listing_cards_and_city_country(): void
    {
        $source = $this->createTour([
            'city' => 'Ludwigshafen',
            'country' => 'Germany',
            'lat' => 49.477,
            'lng' => 8.445,
        ]);

        $this->createTour([
            'title' => 'NEARBY_CARD_TOUR',
            'city' => 'Ludwigshafen',
            'region' => 'Rhineland-Palatinate',
            'country' => 'Germany',
            'location' => 'Some stale street address',
            'lat' => 49.48,
            'lng' => 8.45,
        ]);

        $payload = app(SimilarGuidingsService::class)->forProductPage($source);
        $guiding = $payload['guidings']->firstWhere('title', 'NEARBY_CARD_TOUR');

        $this->assertNotNull($guiding);
        $this->assertInstanceOf(Guiding::class, $guiding);
        $this->assertSame('Ludwigshafen', $guiding->city);
        $this->assertSame('Germany', $guiding->country);

        $html = view('pages.guidings.partials.guiding-card', [
            'guidings' => collect([$guiding]),
        ])->render();

        $this->assertStringContainsString('guiding-card-wrapper', $html);
        $this->assertStringContainsString('gc-mob-book-btn', $html);
        $this->assertStringContainsString('Ludwigshafen, Germany', $html);
        $this->assertStringNotContainsString('stale street', $html);
        $this->assertStringNotContainsString('cag-home-offer', $html);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createTour(array $overrides = []): Guiding
    {
        $userId = $overrides['user_id'] ?? User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ])->id;

        unset($overrides['user_id']);

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Similar Tour '.uniqid(),
            'slug' => 'similar-tour-'.uniqid(),
            'location' => 'Ludwigshafen',
            'status' => 1,
            'max_guests' => 2,
            'duration' => 6,
            'price_type' => 'per_person',
            'price' => 120,
            'prices' => json_encode([
                ['person' => 1, 'amount' => 120],
                ['person' => 2, 'amount' => 200],
            ]),
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $userId,
        ], $overrides))->save();

        return $guiding;
    }
}
