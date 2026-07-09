<?php

namespace App\Http\Controllers\Vacation;

use App\Domain\Vacation\VacationPillar;
use App\Http\Controllers\Controller;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Vacation\VacationPillarPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacationPillarController extends Controller
{
    public function __construct(
        private VacationPillarPageService $pages,
        private VacationDestinationRepository $destinations,
    ) {}

    public function index(Request $request): View
    {
        $vm = $this->pages->buildIndex($request, VacationPillar::fromRequest($request));

        return view('pages.vacations.pillar-index', compact('vm'));
    }

    public function slug(Request $request, string $slug)
    {
        $pillar = VacationPillar::fromRequest($request);
        $country = strtolower($slug);

        if ($this->destinations->isKnownCountrySlug($country, $pillar->value)) {
            $vm = $this->pages->buildCountry($request, $pillar, $country);

            return view('pages.vacations.pillar-index', compact('vm'));
        }

        return app($pillar->offerControllerClass())->show($request, $slug);
    }
}
