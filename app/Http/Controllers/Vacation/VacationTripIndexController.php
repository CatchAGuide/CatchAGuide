<?php

namespace App\Http\Controllers\Vacation;

use App\Http\Controllers\Controller;
use App\Presenters\Vacation\TripCardPresenter;
use App\Repositories\Vacation\TripListingRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Domain\Vacation\VacationListingFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacationTripIndexController extends Controller
{
    public function __construct(
        private TripListingRepository $trips,
        private TripCardPresenter $presenter,
        private VacationDestinationRepository $destinations,
    ) {}

    public function index(Request $request): View
    {
        $filter = VacationListingFilter::fromRequest($request->all(), $request->get('country'));
        $perPage = (int) config('vacations.pillar_index_per_page', 9);

        $filterAll = new VacationListingFilter(
            pillar: 'trips',
            species: $filter->species,
            duration: $filter->duration,
            country: $filter->country,
            sortBy: $filter->sortBy,
        );

        $listings = $this->trips->paginateForCountry($filterAll, $perPage);
        $cards = collect($listings->items())->map(fn ($t) => $this->presenter->present($t));

        return view('pages.vacations.trips-index', [
            'cards' => $cards,
            'listings' => $listings,
            'filter' => $filterAll,
            'countries' => $this->destinations->countriesForHubGrid(),
            'speciesOptions' => app(\App\Services\Vacation\VacationFilterApplicator::class)->speciesOptionsForCountry(null),
        ]);
    }
}
