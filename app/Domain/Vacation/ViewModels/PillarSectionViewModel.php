<?php

namespace App\Domain\Vacation\ViewModels;

use App\Domain\Vacation\Pillar;

final class PillarSectionViewModel
{
    public function __construct(
        public readonly Pillar $pillar,
        public readonly string $countryName,
        public readonly int $count,
        public readonly bool $visible,
    ) {}

    public function headerLabel(): string
    {
        $pillarLabel = $this->pillar === Pillar::Trip
            ? __('vacations.section_trips_header', ['country' => strtoupper($this->countryName)])
            : __('vacations.section_camps_header', ['country' => strtoupper($this->countryName)]);

        if ($this->count === 0) {
            return $pillarLabel . ' — ' . __('vacations.section_coming_soon');
        }

        return $pillarLabel . ' — ' . __('vacations.section_available', ['count' => $this->count]);
    }

    public function cssModifier(): string
    {
        return $this->pillar === Pillar::Trip ? 'trip' : 'camp';
    }
}
