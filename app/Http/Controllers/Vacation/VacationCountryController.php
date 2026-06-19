<?php

namespace App\Http\Controllers\Vacation;

use App\Http\Controllers\Controller;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Services\Vacation\VacationCountryPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacationCountryController extends Controller
{
    public function __construct(
        private VacationCountryPageService $countryPage,
        private TripCardPresenter $tripPresenter,
        private CampCardPresenter $campPresenter,
    ) {}

    public function show(Request $request, string $country): View
    {
        $country = strtolower($country);
        $vm = $this->countryPage->build($request, $country);

        return view('pages.vacations.country', [
            'vm' => $vm,
            'listingRows' => collect($vm->listings->items())->map(function (array $item) use ($vm) {
                return $item['type'] === 'trip'
                    ? $this->tripPresenter->presentListRow($item['model'])
                    : $this->campPresenter->presentListRow($item['model'], $vm->destination->id);
            }),
        ]);
    }
}
