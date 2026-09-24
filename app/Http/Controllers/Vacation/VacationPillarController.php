<?php

namespace App\Http\Controllers\Vacation;

use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationPillar;
use App\Http\Controllers\Controller;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Seo\CatalogInventoryGate;
use App\Services\Vacation\VacationPillarPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacationPillarController extends Controller
{
    public function __construct(
        private VacationPillarPageService $pages,
        private VacationDestinationRepository $destinations,
        private CatalogInventoryGate $inventoryGate,
    ) {}

    public function index(Request $request): View
    {
        $vm = $this->pages->buildIndex($request, VacationPillar::fromRequest($request));

        return view('pages.vacations.pillar-index', compact('vm'));
    }

    public function slug(Request $request, string $slug)
    {
        $pillar = VacationPillar::fromRequest($request);
        $country = CountrySlug::canonicalize($slug) ?? strtolower($slug);

        if ($this->destinations->isKnownCountrySlug($country, $pillar->value)) {
            $vm = $this->pages->buildCountry($request, $pillar, $country);

            // A country known only through CMS copy (e.g. /vacations/camps/malediven with zero
            // camps) renders a placeholder: reachable, but kept out of the index.
            $noindex = ! $this->inventoryGate->vacationCountryIndexable($country, $pillar->value);

            return view('pages.vacations.pillar-index', compact('vm', 'noindex'));
        }

        return app($pillar->offerControllerClass())->show($request, $slug);
    }
}
