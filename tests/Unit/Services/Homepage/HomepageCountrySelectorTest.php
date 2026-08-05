<?php

namespace Tests\Unit\Services\Homepage;

use App\Models\Country;
use App\Services\Homepage\HomepageCountrySelector;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomepageCountrySelectorTest extends TestCase
{
    public function test_featured_maps_country_fields_and_respects_limit(): void
    {
        Cache::flush();

        if (! Country::query()->exists()) {
            $this->markTestSkipped('No countries in test database.');
        }

        $selector = new HomepageCountrySelector();
        $featured = $selector->featured(2);

        $this->assertLessThanOrEqual(2, $featured->count());
        $this->assertTrue($featured->every(fn ($row) => array_key_exists('slug', $row)
            && array_key_exists('name', $row)
            && array_key_exists('thumbnail', $row)
            && array_key_exists('from_price', $row)
            && array_key_exists('from_price_label', $row)));
        $this->assertTrue($featured->every(fn ($row) => ! str_contains((string) $row['name'], ' – ')));
        $this->assertGreaterThan(0, $selector->totalCount());
    }
}
