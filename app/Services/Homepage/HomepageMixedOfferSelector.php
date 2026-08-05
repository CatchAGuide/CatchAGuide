<?php

namespace App\Services\Homepage;

use App\Models\Guiding;
use App\Presenters\Offers\TourCardPresenter;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HomepageMixedOfferSelector
{
    public function __construct(
        private CampListingRepository $camps,
        private TripListingRepository $trips,
        private CampCardPresenter $campPresenter,
        private TripCardPresenter $tripPresenter,
        private TourCardPresenter $tourPresenter,
    ) {}

    /**
     * One collection per product module for homepage carousels.
     *
     * @return array{tour: Collection, trip: Collection, camp: Collection}
     */
    public function byModule(?int $perType = null): array
    {
        $perType = $perType ?? 8;
        $cacheKey = 'homepage_offer_modules_v1_'.app()->getLocale().'_'.$perType;

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($perType) {
            return [
                'tour' => $this->popularGuidings($perType)
                    ->map(fn (Guiding $g) => $this->tourPresenter->present($g))
                    ->values(),
                'trip' => $this->trips->listForHub($perType)
                    ->map(fn ($t) => $this->tripPresenter->present($t))
                    ->values(),
                'camp' => $this->camps->listForHub($perType)
                    ->map(fn ($c) => $this->campPresenter->present($c))
                    ->values(),
            ];
        });
    }

    /**
     * Flat mixed set (tests / legacy). Prefer byModule() for the homepage.
     */
    public function mixed(?int $limit = null): Collection
    {
        $limit = $limit ?? 12;
        $perType = (int) max(1, (int) ceil($limit / 3));
        $modules = $this->byModule($perType);

        return $modules['tour']
            ->merge($modules['trip'])
            ->merge($modules['camp'])
            ->take($limit)
            ->values();
    }

    private function popularGuidings(int $limit): Collection
    {
        return Guiding::query()
            ->withCount('bookings')
            ->publiclyVisible()
            ->orderByDesc('bookings_count')
            ->limit($limit)
            ->get();
    }

}
