<?php

namespace App\Http\Controllers\Offers;

use App\Http\Controllers\Controller;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OffersController extends Controller
{
    public function __construct(
        private OfferCatalogPageService $catalog,
    ) {}

    public function index(Request $request): View
    {
        $vm = $this->catalog->build($request);

        return view('pages.offers.index', [
            'vm' => $vm,
        ]);
    }
}
