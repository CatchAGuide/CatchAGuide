<?php

namespace Tests\Unit\Guiding;

use App\Models\Guiding;
use App\Presenters\Guiding\GuidingCardPresenter;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GuidingCardPresenterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::put('translate_circuit:open_until', now()->addHour()->timestamp, 3600);
    }

    public function test_location_prefers_city_and_country_over_raw_location(): void
    {
        $guiding = new Guiding();
        $guiding->forceFill([
            'id' => 1,
            'slug' => 'presenter-location-tour',
            'title' => 'Presenter Location Tour',
            'location' => 'Some stale street address 12',
            'city' => 'Ludwigshafen',
            'country' => 'Germany',
            'duration' => 6,
            'duration_type' => 'hourly',
            'max_guests' => 2,
            'price' => 120,
            'prices' => json_encode([['person' => 1, 'amount' => 120]]),
        ]);

        $label = (new GuidingCardPresenter())->locationLabel($guiding);

        $this->assertSame('Ludwigshafen, Germany', $label);
    }

    public function test_location_falls_back_to_location_when_city_is_empty(): void
    {
        $guiding = new Guiding();
        $guiding->forceFill([
            'location' => 'Po-Delta bei Adria',
            'city' => '',
            'region' => null,
            'country' => 'Italy',
        ]);

        $label = (new GuidingCardPresenter())->locationLabel($guiding);

        $this->assertSame('Po-Delta bei Adria, Italy', $label);
    }
}
