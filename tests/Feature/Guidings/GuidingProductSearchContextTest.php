<?php

namespace Tests\Feature\Guidings;

use App\Enums\GuideStatus;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class GuidingProductSearchContextTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    public function test_booking_widget_preselects_searched_guest_count(): void
    {
        $guiding = $this->guiding();
        $agent = new class
        {
            public function ismobile(): bool
            {
                return false;
            }
        };

        $html = View::make('pages.guidings.content.bookguiding', [
            'guiding' => $guiding,
            'agent' => $agent,
            'preselectedGuests' => 3,
        ])->render();

        $this->assertMatchesRegularExpression(
            '/<option[^>]*value="3"[^>]*selected/i',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<option value=""[^>]*selected/i',
            $html
        );
    }

    public function test_product_page_keeps_search_place_and_preselects_guests(): void
    {
        $guiding = $this->createPublishedTour();

        $response = $this->get($guiding->publicShowUrl([
            'place' => 'Düsseldorf, Deutschland',
            'placeLat' => '51.2277',
            'placeLng' => '6.7735',
            'city' => 'Düsseldorf',
            'country' => 'germany',
            'num_guests' => '3',
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('name="place"', $html);
        $this->assertStringContainsString('Düsseldorf, Deutschland', $html);
        $this->assertStringContainsString('name="num_guests"', $html);
        $this->assertMatchesRegularExpression(
            '/name="num_guests"[^>]*value="3"|value="3"[^>]*name="num_guests"/',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/<option[^>]*value="3"[^>]*selected/i',
            $html
        );
    }

    private function guiding(): Guiding
    {
        $guiding = new Guiding([
            'id' => 99,
            'title' => 'Rhine Perch',
            'slug' => 'rhine-perch',
            'location' => 'Düsseldorf',
            'max_guests' => 4,
            'price_type' => 'per_person',
            'price' => 150,
            'prices' => json_encode([
                ['person' => 1, 'amount' => 150],
                ['person' => 2, 'amount' => 256],
                ['person' => 3, 'amount' => 369],
                ['person' => 4, 'amount' => 480],
            ]),
        ]);
        $guiding->id = 99;

        return $guiding;
    }

    private function createPublishedTour(): Guiding
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill([
            'title' => 'Search Context Tour '.uniqid(),
            'slug' => 'search-context-tour-'.uniqid(),
            'location' => 'Düsseldorf',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 8,
            'price_type' => 'per_person',
            'price' => 150,
            'prices' => json_encode([
                ['person' => 1, 'amount' => 150],
                ['person' => 2, 'amount' => 256],
                ['person' => 3, 'amount' => 369],
                ['person' => 4, 'amount' => 480],
            ]),
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ])->save();

        return $guiding;
    }
}
