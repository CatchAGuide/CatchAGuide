<?php

namespace App\Domain\Vacation\ViewModels;

use Illuminate\Support\Collection;

final class VacationHubViewModel
{
    public function __construct(
        public readonly PillarTileViewModel $campTile,
        public readonly PillarTileViewModel $tripTile,
        public readonly Collection $popularListings,
        public readonly Collection $newListings,
        public readonly bool $showNewListingsRail,
        public readonly Collection $countryGrid,
        public readonly array $faqItems,
        public readonly int $totalTrips,
        public readonly int $totalCamps,
        public readonly Collection $targetFishTiles,
        public readonly Collection $testimonials,
    ) {}
}
