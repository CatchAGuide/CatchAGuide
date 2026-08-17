<?php

namespace Tests\Unit\Offers;

use App\Domain\Offers\OfferListingFilter;
use App\Domain\Offers\ViewModels\OfferCatalogViewModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class OfferCatalogViewModelTitleTest extends TestCase
{
    public function test_unfiltered_catalog_keeps_type_title(): void
    {
        $this->assertSame(__('offers.title'), $this->vm()->pageTitle());
        $this->assertSame(__('offers.title_tours'), $this->vm(type: 'tour')->pageTitle());
        $this->assertSame(__('offers.title_vacations'), $this->vm(type: 'vacation')->pageTitle());
        $this->assertSame(
            __('offers.title_trips'),
            $this->vm(type: 'vacation', vacation: 'trip')->pageTitle()
        );
    }

    public function test_country_filter_appends_location_to_type_title(): void
    {
        $title = $this->vm(
            type: 'vacation',
            country: 'spain',
            countries: collect([['slug' => 'spain', 'name' => 'Spain']]),
        )->pageTitle();

        $this->assertSame(__('offers.title_in', [
            'type' => __('offers.title_vacations'),
            'place' => 'Spain',
        ]), $title);
    }

    public function test_search_place_wins_over_country_filter(): void
    {
        $title = $this->vm(
            type: 'tour',
            place: 'Barcelona',
            country: 'spain',
            countries: collect([['slug' => 'spain', 'name' => 'Spain']]),
        )->pageTitle();

        $this->assertSame(__('offers.title_in', [
            'type' => __('offers.title_tours'),
            'place' => 'Barcelona',
        ]), $title);
    }

    public function test_suggested_place_label_uses_country_when_place_is_empty(): void
    {
        $vm = $this->vm(
            type: 'vacation',
            vacation: 'camp',
            country: 'lettland',
            countries: collect([['slug' => 'lettland', 'name' => 'Latvia']]),
        );

        $this->assertSame('Latvia', $vm->suggestedPlaceLabel());
        $this->assertSame(__('offers.breadcrumb'), $this->vm()->suggestedPlaceLabel());
    }

    public function test_single_species_appends_target_fish(): void
    {
        $title = $this->vm(
            type: 'tour',
            speciesIds: [8],
            speciesOptions: collect([
                ['id' => 8, 'name' => 'Albacore'],
                ['id' => 5, 'name' => 'Pike'],
            ]),
        )->pageTitle();

        $this->assertSame(__('offers.title_for', [
            'type' => __('offers.title_tours'),
            'species' => 'Albacore',
        ]), $title);
    }

    public function test_country_and_species_combine_in_title(): void
    {
        $title = $this->vm(
            type: 'tour',
            country: 'spain',
            speciesIds: [8],
            countries: collect([['slug' => 'spain', 'name' => 'Spain']]),
            speciesOptions: collect([['id' => 8, 'name' => 'Albacore']]),
        )->pageTitle();

        $this->assertSame(__('offers.title_in_for', [
            'type' => __('offers.title_tours'),
            'place' => 'Spain',
            'species' => 'Albacore',
        ]), $title);
    }

    public function test_multiple_species_are_omitted_from_title(): void
    {
        $title = $this->vm(
            type: 'tour',
            country: 'spain',
            speciesIds: [8, 5],
            countries: collect([['slug' => 'spain', 'name' => 'Spain']]),
            speciesOptions: collect([
                ['id' => 8, 'name' => 'Albacore'],
                ['id' => 5, 'name' => 'Pike'],
            ]),
        )->pageTitle();

        $this->assertSame(__('offers.title_in', [
            'type' => __('offers.title_tours'),
            'place' => 'Spain',
        ]), $title);
    }

    public function test_german_copy_uses_auf_for_species(): void
    {
        app()->setLocale('de');

        $title = $this->vm(
            type: 'tour',
            country: 'spain',
            speciesIds: [8],
            countries: collect([['slug' => 'spain', 'name' => 'Spanien']]),
            speciesOptions: collect([['id' => 8, 'name' => 'Weißer Thun']]),
        )->pageTitle();

        $this->assertSame('Angeltouren in Spanien auf Weißer Thun', $title);
    }

    /**
     * @param  list<int>  $speciesIds
     */
    private function vm(
        string $type = 'all',
        string $vacation = 'all',
        ?string $place = null,
        ?string $country = null,
        array $speciesIds = [],
        $speciesOptions = null,
        $countries = null,
    ): OfferCatalogViewModel {
        $filter = OfferListingFilter::fromRequest(array_filter([
            'type' => $type,
            'vacation' => $vacation !== 'all' ? $vacation : null,
            'place' => $place,
            'country' => $country,
            'species' => $speciesIds !== [] ? $speciesIds : null,
        ], fn ($v) => $v !== null && $v !== ''));

        return new OfferCatalogViewModel(
            filter: $filter,
            listings: new LengthAwarePaginator([], 0, 9),
            cards: collect(),
            toursTotal: 0,
            tripsTotal: 0,
            campsTotal: 0,
            listingsTotal: 0,
            speciesOptions: $speciesOptions ?? collect(),
            countries: $countries ?? collect(),
            methodOptions: collect(),
            waterOptions: collect(),
            tourDurationOptions: collect(),
            tripDurationOptions: collect(),
            accommodationTypeOptions: collect(),
            faq: collect(),
            mapMarkers: [],
            suggestedCards: collect(),
        );
    }
}
