<?php

namespace Tests\Unit\Services\Homepage;

use App\Models\Country;
use App\Models\CountryTranslation;
use App\Services\Homepage\HomepageCountrySelector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomepageCountrySelectorTest extends TestCase
{
    use DatabaseTransactions;

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

    public function test_featured_dedupes_same_iso_country_variants(): void
    {
        Cache::flush();
        app()->setLocale('de');

        $marker = 'test-fi-dedupe-'.uniqid();

        $finnland = Country::query()->create([
            'name' => 'Finnland',
            'slug' => $marker.'-finnland',
            'countrycode' => 'FI',
            'thumbnail_path' => 'assets/images/'.$marker.'-fi.jpg',
        ]);

        CountryTranslation::query()->create([
            'country_id' => $finnland->id,
            'language' => 'de',
            'title' => 'Finnland',
        ]);

        Country::query()->create([
            'name' => 'Finland',
            'slug' => $marker.'-finland',
            'countrycode' => 'FI',
            'thumbnail_path' => 'assets/images/'.$marker.'-fi-en.jpg',
        ]);

        $featured = (new HomepageCountrySelector())->featured(20);

        $finlandRows = $featured->filter(function (array $row) {
            $code = strtoupper((string) ($row['countrycode'] ?? ''));

            return $code === 'FI'
                || in_array(mb_strtolower((string) $row['name']), ['finland', 'finnland'], true)
                || str_contains((string) $row['slug'], 'finland')
                || str_contains((string) $row['slug'], 'finnland');
        });

        $this->assertCount(1, $finlandRows, 'Finland/Finnland ISO duplicates must collapse to one tile');
        $this->assertSame('Finnland', $finlandRows->first()['name']);
    }

    public function test_total_count_counts_unique_isos(): void
    {
        Cache::flush();

        $beforeUnique = (new HomepageCountrySelector())->totalCount();
        Cache::flush();

        $marker = 'test-count-'.uniqid();

        Country::query()->create([
            'name' => 'Finnland',
            'slug' => $marker.'-finnland',
            'countrycode' => 'ZZ',
        ]);

        Country::query()->create([
            'name' => 'Finland',
            'slug' => $marker.'-finland',
            'countrycode' => 'ZZ',
        ]);

        $afterUnique = (new HomepageCountrySelector())->totalCount();

        $this->assertSame($beforeUnique + 1, $afterUnique);
    }

    public function test_featured_without_limit_returns_all_unique_countries(): void
    {
        Cache::flush();

        $marker = 'test-all-rail-'.uniqid();

        Country::query()->create([
            'name' => 'Zedonia',
            'slug' => $marker,
            'countrycode' => '',
            'thumbnail_path' => 'assets/images/'.$marker.'.jpg',
        ]);

        $selector = new HomepageCountrySelector();
        $featured = $selector->featured();

        $this->assertSame($selector->totalCount(), $featured->count());
        $this->assertTrue($featured->contains(fn (array $row) => $row['slug'] === $marker));
        $this->assertLessThanOrEqual(8, $selector->featured(8)->count());
    }
}

