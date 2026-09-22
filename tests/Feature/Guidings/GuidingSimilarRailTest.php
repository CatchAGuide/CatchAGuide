<?php

namespace Tests\Feature\Guidings;

use App\Enums\GuideStatus;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GuidingSimilarRailTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
        Cache::put('translate_circuit:open_until', now()->addHour()->timestamp, 3600);

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    public function test_product_page_renders_listing_cards_for_nearby_tours_only(): void
    {
        $source = $this->createTour([
            'title' => 'SOURCE_PRODUCT_TOUR',
            'slug' => 'source-product-tour-'.uniqid(),
            'city' => 'Ludwigshafen',
            'country' => 'Germany',
            'lat' => 49.477,
            'lng' => 8.445,
        ]);

        $nearby = $this->createTour([
            'title' => 'NEARBY_PRODUCT_CARD_TOUR',
            'city' => 'Ludwigshafen',
            'region' => 'Rhineland-Palatinate',
            'country' => 'Germany',
            'location' => 'Some stale street address',
            'lat' => 49.48,
            'lng' => 8.45,
        ]);

        $farAway = $this->createTour([
            'title' => 'FAR_PRODUCT_CARD_TOUR',
            'city' => 'Madrid',
            'country' => 'Spain',
            'lat' => 40.4168,
            'lng' => -3.7038,
            'target_fish' => $source->target_fish,
        ]);

        $response = $this->get($source->publicShowUrl());

        $response->assertOk();
        $response->assertSee('guiding-similar-rail', false);
        $response->assertSee('guiding-card-wrapper', false);
        $response->assertSee('gc-mob-book-btn', false);
        $response->assertSee('NEARBY_PRODUCT_CARD_TOUR', false);
        $response->assertDontSee('FAR_PRODUCT_CARD_TOUR', false);
        $response->assertDontSee('cag-home-offer', false);
        $response->assertDontSee('guiding-tile', false);
        $response->assertSee('Ludwigshafen, Germany', false);
        $response->assertDontSee('Some stale street address', false);
        $this->assertFalse(
            str_contains($response->getContent(), 'data-offer-rail="similar-guidings"')
        );

        $seeAll = $response->viewData('similar_guidings_see_all_url');
        $this->assertIsString($seeAll);
        $this->assertStringContainsString('city=Ludwigshafen', $seeAll);

        $guidings = $response->viewData('other_guidings');
        $this->assertTrue($guidings->contains(fn ($guiding) => $guiding->id === $nearby->id));
        $this->assertFalse($guidings->contains(fn ($guiding) => $guiding->id === $farAway->id));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createTour(array $overrides = []): Guiding
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Similar Rail Tour '.uniqid(),
            'slug' => 'similar-rail-tour-'.uniqid(),
            'location' => 'Ludwigshafen',
            'status' => 1,
            'max_guests' => 2,
            'duration' => 6,
            'price_type' => 'per_person',
            'price' => 120,
            'prices' => json_encode([
                ['person' => 1, 'amount' => 120],
            ]),
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
            'target_fish' => json_encode([11]),
        ], $overrides))->save();

        return $guiding;
    }
}
