<?php

namespace Tests\Unit\Offers;

use App\Domain\Offers\OfferListingFilter;
use App\Domain\Offers\ViewModels\OfferCatalogViewModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class OfferCatalogSpeciesLockTest extends TestCase
{
    public function test_locked_scope_params_include_species_when_species_scope_locked(): void
    {
        $filter = OfferListingFilter::fromRequest([
            'species' => [42],
            'type' => 'vacation',
            'vacation' => 'trip',
        ]);

        $vm = new OfferCatalogViewModel(
            filter: $filter,
            listings: new LengthAwarePaginator([], 0, 9),
            cards: collect(),
            toursTotal: 0,
            tripsTotal: 2,
            campsTotal: 1,
            listingsTotal: 2,
            speciesOptions: collect(),
            countries: collect([['slug' => 'germany', 'name' => 'Germany']]),
            methodOptions: collect(),
            waterOptions: collect(),
            tourDurationOptions: collect(),
            tripDurationOptions: collect(),
            accommodationTypeOptions: collect(),
            faq: collect(),
            mapMarkers: [],
            suggestedCards: collect(),
            catalogUrl: 'http://localhost/category-page/targets/pike',
            lockSpeciesScope: true,
        );

        $locked = $vm->lockedScopeParams();
        $this->assertSame([42], $locked['species']);
        $this->assertArrayNotHasKey('country', $locked);

        $urls = $vm->typeToggleUrls();
        $this->assertStringStartsWith('http://localhost/category-page/targets/pike', $urls['vacation']);
        $this->assertStringContainsString('species%5B0%5D=42', $urls['vacation']);
        $this->assertStringContainsString('type=vacation', $urls['vacation']);
        $this->assertStringNotContainsString('/offers?', $urls['vacation']);
    }
}
