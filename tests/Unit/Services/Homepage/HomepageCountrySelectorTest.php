<?php

namespace Tests\Unit\Services\Homepage;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Language;
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

        $selector = app(HomepageCountrySelector::class);
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

        $featured = app(HomepageCountrySelector::class)->featured(20);

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

        $beforeUnique = app(HomepageCountrySelector::class)->totalCount();
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

        $afterUnique = app(HomepageCountrySelector::class)->totalCount();

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

        $selector = app(HomepageCountrySelector::class);
        $featured = $selector->featured();

        $this->assertSame($selector->totalCount(), $featured->count());
        $this->assertTrue($featured->contains(fn (array $row) => $row['slug'] === $marker));
        $this->assertLessThanOrEqual(8, $selector->featured(8)->count());
    }

    public function test_featured_with_tours_scope_only_includes_countries_with_tours_content(): void
    {
        Cache::flush();

        $marker = 'test-tours-scope-'.uniqid();

        $withTours = Country::query()->create([
            'name' => 'Toursland',
            'slug' => $marker.'-tours',
            'countrycode' => 'T8',
            'thumbnail_path' => 'assets/images/'.$marker.'-tours.jpg',
        ]);

        $globalOnly = Country::query()->create([
            'name' => 'Globalland',
            'slug' => $marker.'-global',
            'countrycode' => 'G8',
            'thumbnail_path' => 'assets/images/'.$marker.'-global.jpg',
        ]);

        $emptyTours = Country::query()->create([
            'name' => 'Emptyland',
            'slug' => $marker.'-empty',
            'countrycode' => 'E8',
            'thumbnail_path' => 'assets/images/'.$marker.'-empty.jpg',
        ]);

        Language::query()->create([
            'source_id' => (string) $withTours->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'en',
            'title' => 'Toursland Guidings',
            'sub_title' => 'Tours subtitle',
            'introduction' => 'Tours intro',
            'content' => 'Tours body',
            'faq_title' => '',
        ]);

        Language::query()->create([
            'source_id' => (string) $globalOnly->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => 'en',
            'title' => 'Globalland Hub',
            'sub_title' => 'Global subtitle',
            'introduction' => 'Global intro',
            'content' => 'Global body',
            'faq_title' => '',
        ]);

        Language::query()->create([
            'source_id' => (string) $emptyTours->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'en',
            'title' => '',
            'sub_title' => '',
            'introduction' => '',
            'content' => '',
            'faq_title' => '',
        ]);

        $featured = app(HomepageCountrySelector::class)->featured(categoryScope: CategoryPageScope::TOURS);
        $slugs = $featured->pluck('slug');

        $this->assertTrue($slugs->contains($withTours->slug));
        $this->assertFalse($slugs->contains($globalOnly->slug));
        $this->assertFalse($slugs->contains($emptyTours->slug));
    }

    public function test_featured_with_tours_scope_includes_iso_duplicate_when_content_is_on_other_row(): void
    {
        Cache::flush();

        $marker = 'test-tours-iso-'.uniqid();

        $preferred = Country::query()->create([
            'name' => 'Preferredland',
            'slug' => $marker.'-preferred',
            'countrycode' => 'P8',
            'thumbnail_path' => 'assets/images/'.$marker.'-preferred.jpg',
        ]);

        $contentRow = Country::query()->create([
            'name' => 'Contentland',
            'slug' => $marker.'-content',
            'countrycode' => 'P8',
            'thumbnail_path' => null,
        ]);

        Language::query()->create([
            'source_id' => (string) $contentRow->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'en',
            'title' => 'Contentland Tours',
            'sub_title' => 'Tours subtitle',
            'introduction' => 'Tours intro',
            'content' => 'Tours body',
            'faq_title' => '',
        ]);

        $featured = app(HomepageCountrySelector::class)->featured(categoryScope: CategoryPageScope::TOURS);
        $p8Rows = $featured->filter(fn (array $row) => strtoupper((string) ($row['countrycode'] ?? '')) === 'P8');

        $this->assertCount(1, $p8Rows);
        $this->assertSame($preferred->slug, $p8Rows->first()['slug']);
    }
}

