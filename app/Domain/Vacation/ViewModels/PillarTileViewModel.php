<?php

namespace App\Domain\Vacation\ViewModels;

use App\Domain\Vacation\Pillar;

final class PillarTileViewModel
{
    public function __construct(
        public readonly Pillar $pillar,
        public readonly string $title,
        public readonly string $description,
        public readonly int $listingCount,
        public readonly int $countryCount,
        public readonly ?float $minPrice,
        public readonly string $currency,
        public readonly string $url,
        public readonly ?string $descriptor = null,
    ) {}
}
