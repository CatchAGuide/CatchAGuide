<?php

namespace App\Domain\Vacation\ViewModels;

use Illuminate\Support\Collection;

final class VacationHubViewModel
{
    public function __construct(
        public readonly PillarTileViewModel $campTile,
        public readonly PillarTileViewModel $tripTile,
        public readonly Collection $popularListings,
        public readonly Collection $newTrips,
        public readonly bool $showNewTripsRail,
        public readonly Collection $newCamps,
        public readonly bool $showNewCampsRail,
        public readonly Collection $countryGrid,
        public readonly array $faqItems,
        public readonly int $totalTrips,
        public readonly int $totalCamps,
    ) {}
}
