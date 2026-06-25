<?php

namespace App\Http\Controllers\Vacation;

use App\Http\Controllers\Controller;
use App\Services\Vacation\VacationHubPageService;
use Illuminate\View\View;

class VacationHubController extends Controller
{
    public function __construct(
        private VacationHubPageService $hubPage,
    ) {}

    public function index(): View
    {
        return view('pages.vacations.hub', [
            'hub' => $this->hubPage->build(),
        ]);
    }
}
