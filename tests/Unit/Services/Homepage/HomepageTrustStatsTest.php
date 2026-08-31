<?php

namespace Tests\Unit\Services\Homepage;

use App\Services\CategoryPage\FavoriteTargetSpeciesResolver;
use App\Services\Homepage\HomepageCountrySelector;
use App\Services\Homepage\HomepageLandingService;
use App\Services\Homepage\HomepageMixedOfferSelector;
use App\Services\Reviews\TestimonialSelector;
use Illuminate\Support\Facades\Cache;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class HomepageTrustStatsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_trust_stats_keep_offers_at_450_plus_and_use_country_count(): void
    {
        Cache::flush();

        $countries = Mockery::mock(HomepageCountrySelector::class);
        $mixedOffers = Mockery::mock(HomepageMixedOfferSelector::class);
        $favoriteTargetSpecies = Mockery::mock(FavoriteTargetSpeciesResolver::class);
        $testimonialSelector = Mockery::mock(TestimonialSelector::class);

        $service = new HomepageLandingService($countries, $mixedOffers, $favoriteTargetSpecies, $testimonialSelector);

        $method = new ReflectionMethod(HomepageLandingService::class, 'trustStats');
        $method->setAccessible(true);

        /** @var array $trust */
        $trust = $method->invoke($service, 10);

        $this->assertSame('450+', $trust['offers']);
        $this->assertSame(HomepageLandingService::TRUST_OFFERS_LABEL, $trust['offers']);
        $this->assertSame('24', $trust['countries']);
        $this->assertSame(HomepageLandingService::TRUST_COUNTRIES_FLOOR, (int) $trust['countries']);
        $this->assertSame(__('homepage.trust_offers_label'), $trust['offers_label']);
        $this->assertSame(__('homepage.trust_countries_label'), $trust['countries_label']);

        $aboveFloor = $method->invoke($service, 30);
        $this->assertSame('30', $aboveFloor['countries']);
    }

    public function test_trust_rating_uses_comma_decimal_in_german(): void
    {
        Cache::flush();
        app()->setLocale('de');

        $countries = Mockery::mock(HomepageCountrySelector::class);
        $mixedOffers = Mockery::mock(HomepageMixedOfferSelector::class);
        $favoriteTargetSpecies = Mockery::mock(FavoriteTargetSpeciesResolver::class);
        $testimonialSelector = Mockery::mock(TestimonialSelector::class);

        $service = new HomepageLandingService($countries, $mixedOffers, $favoriteTargetSpecies, $testimonialSelector);

        $method = new ReflectionMethod(HomepageLandingService::class, 'trustStats');
        $method->setAccessible(true);

        /** @var array $trust */
        $trust = $method->invoke($service, 24);

        if ($trust['rating'] === null) {
            $this->markTestSkipped('No reviews with scores available to format.');
        }

        $this->assertMatchesRegularExpression('/^\d,\d\/10$/', $trust['rating']);
    }
}
