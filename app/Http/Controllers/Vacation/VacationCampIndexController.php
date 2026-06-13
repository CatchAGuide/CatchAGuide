<?php

namespace App\Http\Controllers\Vacation;

use App\Http\Controllers\Controller;
use App\Presenters\Vacation\CampCardPresenter;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Domain\Vacation\VacationListingFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacationCampIndexController extends Controller
{
    public function __construct(
        private CampListingRepository $camps,
        private CampCardPresenter $presenter,
        private VacationDestinationRepository $destinations,
    ) {}

    public function index(Request $request): View
    {
        $filter = VacationListingFilter::fromRequest($request->all(), $request->get('country'));
        $perPage = (int) config('vacations.pillar_index_per_page', 9);

        $filterAll = new VacationListingFilter(
            pillar: 'camps',
            species: $filter->species,
            duration: $filter->duration,
            country: $filter->country,
            sortBy: $filter->sortBy,
        );

        $listings = $this->camps->paginateForCountry($filterAll, $perPage);
        $cards = collect($listings->items())->map(fn ($c) => $this->presenter->present($c));

        return view('pages.vacations.camps-index', [
            'cards' => $cards,
            'listings' => $listings,
            'filter' => $filterAll,
            'countries' => $this->destinations->countriesForHubGrid(),
            'speciesOptions' => app(\App\Services\Vacation\VacationFilterApplicator::class)->speciesOptionsForCountry(null),
        ]);
    }
}
