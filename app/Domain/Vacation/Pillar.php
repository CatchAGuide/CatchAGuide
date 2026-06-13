<?php

namespace App\Domain\Vacation;

enum Pillar: string
{
    case Camp = 'camp';
    case Trip = 'trip';

    public function labelKey(): string
    {
        return match ($this) {
            self::Camp => 'vacations.pillar_camp',
            self::Trip => 'vacations.pillar_trip',
        };
    }
}
