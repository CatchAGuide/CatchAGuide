<?php

namespace App\Http\Controllers\Vacation;

use App\Domain\Vacation\CountrySlug;
use App\Http\Controllers\Controller;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Services\Vacation\VacationCountryPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacationCountryController extends Controller
{
    public function __construct(
        private VacationCountryPageService $countryPage,
        private TripCardPresenter $tripPresenter,
        private CampCardPresenter $campPresenter,
    ) {}

    public function show(Request $request, string $country): View|RedirectResponse
    {
        $country = CountrySlug::canonicalize($country) ?? strtolower($country);

        if ($redirect = $this->redirectPillarToLanding($request, $country)) {
            return $redirect;
        }

        $vm = $this->countryPage->build($request, $country);

        return $this->countryView($vm);
    }

    public function allOffers(Request $request): View|RedirectResponse
    {
        if (strtolower((string) $request->query('country', '')) === 'all-offers') {
            return redirect()->route('vacations.all-offers', $request->except('country'));
        }

        if ($redirect = $this->redirectPillarToLanding($request)) {
            return $redirect;
        }

        $vm = $this->countryPage->buildAllOffers($request);

        return $this->countryView($vm, isAllOffers: true);
    }

    /**
     * Send trips/camps pillar filters to their dedicated landing pages
     * so page title and subtitle update correctly.
     */
    private function redirectPillarToLanding(Request $request, ?string $countrySlug = null): ?RedirectResponse
    {
        $pillar = strtolower((string) $request->query('pillar', ''));
        if (! in_array($pillar, ['trips', 'camps'], true)) {
            return null;
        }

        $query = $request->except('pillar', 'country');

        if ($countrySlug === null) {
            return redirect()->route(
                $pillar === 'trips' ? 'vacations.trips.index' : 'vacations.camps.index',
                $query,
            );
        }

        return redirect()->route(
            $pillar === 'trips' ? 'vacations.trips.show' : 'vacations.camps.show',
            array_merge(['slug' => $countrySlug], $query),
        );
    }

    private function countryView($vm, bool $isAllOffers = false): View
    {
        return view('pages.vacations.country', [
            'vm' => $vm,
            'isAllOffers' => $isAllOffers,
            'listingRows' => collect($vm->listings->items())->map(function (array $item) use ($vm) {
                return $item['type'] === 'trip'
                    ? $this->tripPresenter->presentListRow($item['model'])
                    : $this->campPresenter->presentListRow($item['model'], $vm->destination->id);
            }),
        ]);
    }
}
