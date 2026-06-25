<?php

namespace App\Services\Vacation;

use App\Domain\Vacation\Pillar;
use App\Domain\Vacation\ProductTypeResolver;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use Illuminate\Support\Collection;

class PopularListingSelector
{
    public function __construct(
        private CampListingRepository $camps,
        private TripListingRepository $trips,
        private CampCardPresenter $campPresenter,
        private TripCardPresenter $tripPresenter,
        private ProductTypeResolver $productTypes,
    ) {}

    public function mixedForHub(?int $limit = null): Collection
    {
        $limit = $limit ?? (int) config('vacations.popular_listing_limit', 6);
        $half = (int) ceil($limit / 2);

        $campItems = $this->camps->listForHub($half)->map(fn ($c) => $this->campPresenter->present($c));
        $tripItems = $this->trips->listForHub($limit - $half)->map(fn ($t) => $this->tripPresenter->present($t));

        return $campItems->merge($tripItems)->shuffle()->take($limit)->values();
    }
}
