<?php

namespace App\Http\Controllers\Vacation;

use App\Domain\Vacation\CountrySlug;
use App\Http\Controllers\Controller;
use App\Models\CategoryEntity;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Vacation\VacationCountryPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacationCountryController extends Controller
{
    public function __construct(
        private VacationCountryPageService $countryPage,
        private VacationDestinationRepository $destinations,
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
     * "See all countries" page for the vacations pillar — same layout as the
     * mixed destination hub, but scoped to countries that have vacations
     * listings (optionally narrowed to a single pillar via ?pillar=).
     */
    public function countries(Request $request): View
    {
        $pillar = strtolower((string) $request->query('pillar', ''));
        $pillar = in_array($pillar, ['trips', 'camps'], true) ? $pillar : null;

        $countryRoute = match ($pillar) {
            'trips' => 'vacations.trips.show',
            'camps' => 'vacations.camps.show',
            default => 'vacations.country',
        };

        $eligibleSlugs = $this->destinations->countriesForHubGrid()
            ->filter(fn (array $row) => match ($pillar) {
                'trips' => ($row['trips'] ?? 0) > 0,
                'camps' => ($row['camps'] ?? 0) > 0,
                default => ($row['trips'] ?? 0) > 0 || ($row['camps'] ?? 0) > 0,
            })
            ->map(fn (array $row) => CountrySlug::canonicalize($row['slug']) ?? $row['slug'])
            ->flip();

        $countries = CategoryEntity::countries()->get()
            ->filter(fn (CategoryEntity $country) => $eligibleSlugs->has(CountrySlug::canonicalize($country->slug) ?? strtolower((string) $country->slug)))
            ->values();

        return view('pages.countries.index', [
            'countries' => $countries,
            'destination_route' => $countryRoute,
            'title' => __('vacations.hub_title'),
            'sub_title' => __('vacations.hub_header_subtitle'),
            'introduction' => __('vacations.introduction'),
        ]);
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
                301,
            );
        }

        return redirect()->route(
            $pillar === 'trips' ? 'vacations.trips.show' : 'vacations.camps.show',
            array_merge(['slug' => $countrySlug], $query),
            301,
        );
    }

    private function countryView($vm, bool $isAllOffers = false): View
    {
        return view('pages.vacations.country', [
            'vm' => $vm,
            'isAllOffers' => $isAllOffers,
            'listingRows' => collect($vm->listings->items())->map(function (array $item) use ($vm) {
                $productQuery = $vm->filter->productPageQuery();

                return $item['type'] === 'trip'
                    ? $this->tripPresenter->presentListRow($item['model'], $vm->filter->numGuests, $productQuery)
                    : $this->campPresenter->presentListRow($item['model'], $vm->destination->id, $productQuery);
            }),
        ]);
    }
}
